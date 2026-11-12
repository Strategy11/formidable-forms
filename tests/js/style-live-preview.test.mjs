import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { test } from 'node:test';
import { runInNewContext } from 'node:vm';
import { parseSync } from '@babel/core';

// Load the real nested functions without initializing the styler or copying its logic.
const source = readFileSync( new URL( '../../js/admin/style.js', import.meta.url ), 'utf8' );
const ast = parseSync( source, { configFile: false, babelrc: false } );
const styler = ast.program.body[ 0 ].expression.callee.body.body;
const editor = styler.find( node => node.type === 'FunctionDeclaration' && node.id.name === 'initEditPage' );
const names = [ 'canUpdateLive', 'usesBaseFontSize', 'isSafeLiveValue', 'isIncompleteHexColor', 'updatePreview', 'changeStyling' ];
const functions = names.map( name => {
	const declaration = editor.body.body.find(
		node => node.type === 'FunctionDeclaration' && node.id.name === name
	);
	assert.ok( declaration, `${ name } must exist in the styler` );
	return source.slice( declaration.start, declaration.end );
} ).join( '\n' );

/**
 * Load the preview decisions with independent settings for each test.
 *
 * @since x.x
 *
 * @return {Object} The production decision functions.
 */
function loadDecisions() {
	return runInNewContext( `${ functions }\n({ canUpdateLive, isSafeLiveValue })`, {
		livePreview: {
			settings: new Set( [ 'title_color', 'field_font_size', 'title_size' ] ),
			baseFontSizeSettings: new Set( [ 'field_font_size', 'title_size' ] )
		}
	} );
}

/**
 * Load preview updates with observable rendering and request scheduling.
 *
 * @return {Object} The preview state and update function.
 */
function loadPreview() {
	const preview = {
		values: { title_color: '#000' },
		renders: 0,
		requests: 0,
		rechecks: [],
		ajax: [],
		vars: new Map()
	};
	preview.livePreview = {
		settings: new Set( [ 'title_color' ] ),
		colorSettings: new Set( [ 'title_color' ] ),
		baseFontSizeSettings: new Set(),
		values: { ...preview.values },
		vars: preview.vars,
		updateCount: 0
	};
	const jQuery = () => ( { serializeArray: () => [] } );
	jQuery.ajax = options => preview.ajax.push( options );
	[ preview.update, preview.changeStyling ] = runInNewContext( `${ functions }\n[ updatePreview, changeStyling ]`, {
		livePreview: preview.livePreview,
		jQuery,
		ajaxurl: '',
		frmGlobal: {},
		getStyleInputs: () => [],
		getStyleSettingValues: () => preview.values,
		getCssVarName: key => key,
		prepareLiveValue: ( key, value ) => value,
		renderLiveVars: () => preview.renders++,
		debouncedChangeStyling: () => preview.requests++,
		queueMicrotask: callback => preview.rechecks.push( callback )
	} );
	return preview;
}

test( 'each intermediate local value renders immediately without scheduling a request', () => {
	const preview = loadPreview();
	for ( const color of [ '#123', '#456', '#789' ] ) {
		preview.values = { title_color: color };
		preview.update();
		assert.equal( preview.vars.get( 'title_color' ).value, color );
	}
	assert.equal( preview.renders, 3 );
	assert.equal( preview.requests, 0 );
	assert.equal( preview.rechecks.length, 0 );
} );

test( 'mixed changes render local values while scheduling server CSS for the rest', () => {
	const preview = loadPreview();
	preview.values = { title_color: '#abc', custom_setting: '12px' };
	preview.update();
	assert.equal( preview.vars.get( 'title_color' ).value, '#abc' );
	assert.equal( preview.vars.has( 'custom_setting' ), false );
	assert.equal( preview.renders, 1 );
	assert.equal( preview.requests, 1 );
} );

test( 'late component values render in a microtask without a debounce or repeated rechecks', () => {
	const preview = loadPreview();
	preview.update();
	assert.equal( preview.rechecks.length, 1 );
	preview.values = { title_color: '#abc' };
	preview.rechecks.shift()();
	assert.equal( preview.vars.get( 'title_color' ).value, '#abc' );
	assert.equal( preview.rechecks.length, 0 );
	preview.update();
	preview.rechecks.shift()();
	assert.equal( preview.rechecks.length, 0 );
} );

test( 'safe CSS values can update live', () => {
	const { isSafeLiveValue } = loadDecisions();
	for ( const value of [ '#abc', 'abcdef', '0', '12px', ' 1.5rem ', '10px 20px', '100%',
		'rgb(10, 20, 30)', 'rgba(0, 0, 0, 0.5)', 'hsl(120, 50%, 50%)',
		'hsla(120, 50%, 50%, 0.5)', 'hwb(120 0% 0%)', 'var(--title-color)',
		'calc(100% - (2 * 1rem))' ] ) {
		assert.equal( isSafeLiveValue( value ), true, value );
	}
} );

test( 'missing, empty and non-string values require server CSS', () => {
	const { isSafeLiveValue } = loadDecisions();
	for ( const value of [ undefined, null, false, true, 0, 12, [], {}, '', ' \t\n ' ] ) {
		assert.equal( isSafeLiveValue( value ), false, String( value ) );
	}
} );

test( 'values changed by server sanitization require server CSS', () => {
	const { isSafeLiveValue } = loadDecisions();
	for ( const character of [ '{', '}', '[', ']', ';', '<', '>', '&', '"', "'", '\\' ] ) {
		assert.equal( isSafeLiveValue( `red${ character }` ), false, character );
	}
	for ( const value of [ '%3c', '%3C', 'red%00', 'function(test)', 'FUNCTION(test)',
		'rgb(1, 2, 3', 'red)', 'calc((1px)' ] ) {
		assert.equal( isSafeLiveValue( value ), false, value );
	}
} );

test( 'only allowlisted settings with safe submitted values update live', () => {
	const { canUpdateLive } = loadDecisions();
	assert.equal( canUpdateLive( 'title_color', { title_color: '#abc' } ), true );
	assert.equal( canUpdateLive( 'style_name', { style_name: 'Example' } ), false );
	assert.equal( canUpdateLive( 'custom_setting', { custom_setting: '12px' } ), false );
	assert.equal( canUpdateLive( 'title_color', {} ), false );
	assert.equal( canUpdateLive( 'title_color', { title_color: '' } ), false );
	assert.equal( canUpdateLive( 'title_color', { title_color: 'red; color: blue' } ), false );
} );

test( 'scaled font sizes require server CSS when base font size is enabled', () => {
	const { canUpdateLive } = loadDecisions();
	for ( const useBaseFontSize of [ '1', 'true', 'on' ] ) {
		const values = {
			base_font_size: '16px',
			use_base_font_size: useBaseFontSize,
			field_font_size: '20px',
			title_size: '24px',
			title_color: '#abc'
		};
		assert.equal( canUpdateLive( 'field_font_size', values ), false );
		assert.equal( canUpdateLive( 'title_size', values ), false );
		assert.equal( canUpdateLive( 'title_color', values ), true );
	}
} );

test( 'font sizes update live when base font size is absent or disabled', () => {
	const { canUpdateLive } = loadDecisions();
	for ( const baseFontSize of [ undefined, '', '0' ] ) {
		assert.equal( canUpdateLive( 'field_font_size', {
			field_font_size: '20px', base_font_size: baseFontSize, use_base_font_size: '1'
		} ), true );
	}
	for ( const useBaseFontSize of [ undefined, '', '0', 'false' ] ) {
		assert.equal( canUpdateLive( 'field_font_size', {
			field_font_size: '20px', base_font_size: '16px', use_base_font_size: useBaseFontSize
		} ), true );
	}
} );

test( 'partial hex colors typed by hand wait until the color is complete', () => {
	const preview = loadPreview();
	for ( const color of [ '#', '#1', '#12', '#1234', '#12345', '1' ] ) {
		preview.values = { title_color: color };
		preview.update( false, true );
	}
	assert.equal( preview.renders, 0 );
	assert.equal( preview.requests, 0 );
	assert.equal( preview.livePreview.values.title_color, '#000' );

	for ( const color of [ '#123', '#123456', '12345678' ] ) {
		preview.values = { title_color: color };
		preview.update( false, true );
		assert.equal( preview.vars.get( 'title_color' ).value, color );
	}
	assert.equal( preview.renders, 3 );
} );

test( 'clearing a color or leaving a partial hex color still updates the preview', () => {
	const preview = loadPreview();
	preview.values = { title_color: '' };
	preview.update( false, true );
	assert.equal( preview.requests, 1 );

	preview.values = { title_color: '#12' };
	preview.update();
	assert.equal( preview.vars.get( 'title_color' ).value, '#12' );
} );

test( 'a failed styling request makes the next change send every setting again', () => {
	const preview = loadPreview();
	preview.values = { title_color: '#000', custom_setting: '70px' };
	preview.update();
	assert.equal( preview.requests, 1 );

	preview.changeStyling();
	preview.ajax[ 0 ].error();

	preview.values = { title_color: '#abc', custom_setting: '70px' };
	preview.update();
	assert.equal( preview.requests, 2 );
	assert.equal( preview.vars.get( 'title_color' ).value, '#abc' );
} );
