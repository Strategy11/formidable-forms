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
const names = [ 'canUpdateLive', 'usesBaseFontSize', 'isSafeLiveValue' ];
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
