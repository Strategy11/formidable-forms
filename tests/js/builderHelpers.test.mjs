import assert from 'node:assert/strict';
import { test } from 'node:test';
import { installFakeDocument, installFakeHooks, el, flushMicrotasks } from './helpers/fakeDom.mjs';
import { applyLivePreview, getSettingKey, clampToInputRange, truncateText } from '../../js/src/admin/livePreview.js';
import { createFieldReadiness, getFieldPreviewBody } from '../../js/src/admin/fieldReadiness.js';
import { createRefreshFieldPreview } from '../../js/src/admin/refreshFieldPreview.js';

/**
 * Add a setting input and its preview targets to a fresh document.
 *
 * @param {Object} inputAttributes The setting input attributes.
 * @param {number} targetCount     How many targets to add.
 * @return {Object} The input, the targets, and the recorded hook actions.
 */
function setup( inputAttributes, targetCount = 1 ) {
	const document = installFakeDocument();
	const { recorded } = installFakeHooks();
	const targets = Array.from( { length: targetCount }, () => el( 'span', { class: 'frm-target' } ) );
	const input = el( 'input', { type: 'text', 'data-frmpreview': '.frm-target', ...inputAttributes } );
	document.root.append( el( 'div', { class: 'frm-single-settings', 'data-fid': '7' }, [ input ] ), ...targets );
	return { input, targets, recorded };
}

test( 'getSettingKey parses field_options[key_ID]', () => {
	assert.deepEqual( getSettingKey( { name: 'field_options[hide_opt_12]' } ), { key: 'hide_opt', fieldId: '12' } );
	assert.equal( getSettingKey( { name: 'field_options[options_12][0]' } ), null );
	assert.equal( getSettingKey( { name: 'default_value_12' } ), null );
	assert.equal( getSettingKey( null ), null );
} );

// Expected values come from FrmAppHelper::truncate with the same arguments.
test( 'truncateText matches FrmAppHelper::truncate', () => {
	assert.equal( truncateText( 'Short text', 60 ), 'Short text' );
	assert.equal( truncateText( 'abcdefghijkl', 5 ), 'abcde...' );
	assert.equal( truncateText( 'one two three four five six', 12 ), 'one two...' );
	assert.equal( truncateText( 'x'.repeat( 80 ), 20 ), 'x'.repeat( 30 ) );
} );

test( 'numeric values are clamped to the input min and max', () => {
	const input = el( 'input', { min: '1', max: '10' } );
	assert.equal( clampToInputRange( input, '0' ), '1' );
	assert.equal( clampToInputRange( input, '11' ), '10' );
	assert.equal( clampToInputRange( input, '5' ), '5' );
	assert.equal( clampToInputRange( input, 'abc' ), 'abc' );
	assert.equal( clampToInputRange( el( 'input' ), '500' ), '500' );
} );

test( 'text mode uses the fallback, truncates, and updates every target', () => {
	const { input, targets, recorded } = setup( { 'data-frmpreview-mode': 'text', 'data-frmpreview-fallback': 'Apply', 'data-frmpreview-max': '5' }, 2 );
	input.value = '';
	applyLivePreview( input, {} );
	assert.deepEqual( targets.map( target => target.textContent ), [ 'Apply', 'Apply' ] );
	input.value = 'Redeem now';
	applyLivePreview( input, {} );
	assert.equal( targets[ 0 ].textContent, 'Redee...' );
	assert.deepEqual( recorded.at( -1 ), [ 'frm_after_live_change', input, targets ] );
} );

test( 'attr and bool-attr modes set and remove attributes', () => {
	const { input, targets } = setup( { 'data-frmpreview-mode': 'attr:aria-label,bool-attr:readonly' } );
	input.value = 'Sign here';
	applyLivePreview( input, {} );
	assert.equal( targets[ 0 ].getAttribute( 'aria-label' ), 'Sign here' );
	assert.equal( targets[ 0 ].hasAttribute( 'readonly' ), true );
	input.value = '';
	applyLivePreview( input, {} );
	assert.equal( targets[ 0 ].hasAttribute( 'aria-label' ), false );
	assert.equal( targets[ 0 ].hasAttribute( 'readonly' ), false );
} );

test( 'style mode appends the unit to bare numbers, supports custom properties, and clamps', () => {
	const { input, targets } = setup( { 'data-frmpreview-mode': 'style:width:px,style:--frm-size', min: '10', max: '400' } );
	input.value = '300';
	applyLivePreview( input, {} );
	assert.equal( targets[ 0 ].style.width, '300px' );
	assert.equal( targets[ 0 ].styleValues[ '--frm-size' ], '300' );
	input.value = '900';
	applyLivePreview( input, {} );
	assert.equal( targets[ 0 ].style.width, '400px', 'clamped to max' );
	input.value = '';
	applyLivePreview( input, {} );
	assert.equal( targets[ 0 ].style.width, '' );
	assert.equal( targets[ 0 ].styleValues[ '--frm-size' ], undefined );
} );

test( 'style mode reads a unit input from its controls', () => {
	const document = installFakeDocument();
	installFakeHooks();
	const textarea = el( 'textarea', { class: 'frm-target' } );
	const hidden = el( 'input', { type: 'hidden', 'data-frmpreview': '.frm-target', 'data-frmpreview-mode': 'style:width:px' } );
	const control = el( 'input', { type: 'text', class: 'frm-unit-input-control', value: '50' } );
	const unit = el( 'select', { value: '%' } );
	document.root.append( el( 'span', { class: 'frm-unit-input' }, [ hidden, control, unit ] ), textarea );
	applyLivePreview( hidden, {} );
	assert.equal( textarea.style.width, '50%' );
	unit.value = '';
	applyLivePreview( hidden, {} );
	assert.equal( textarea.style.width, '50px', 'a number without a unit gets px' );
} );

test( 'toggle, toggle-inverse and hide-empty modes follow checkboxes and values', () => {
	const { input, targets } = setup( { type: 'checkbox', 'data-frmpreview-mode': 'toggle:frm-on,toggle-inverse:frm-off,hide-empty' } );
	input.checked = true;
	applyLivePreview( input, {} );
	assert.equal( targets[ 0 ].classList.contains( 'frm-on' ), true );
	assert.equal( targets[ 0 ].classList.contains( 'frm-off' ), false );
	assert.equal( targets[ 0 ].classList.contains( 'frm_hidden' ), false );
	input.checked = false;
	applyLivePreview( input, {} );
	assert.equal( targets[ 0 ].classList.contains( 'frm-on' ), false );
	assert.equal( targets[ 0 ].classList.contains( 'frm-off' ), true );
	assert.equal( targets[ 0 ].classList.contains( 'frm_hidden' ), true );

	const text = setup( { 'data-frmpreview-mode': 'hide-empty' } );
	text.input.value = '0';
	applyLivePreview( text.input, {} );
	assert.equal( text.targets[ 0 ].classList.contains( 'frm_hidden' ), true, '0 counts as empty' );
} );

test( 'class-swap mode swaps known classes with a prefix', () => {
	const { input, targets } = setup( { 'data-frmpreview-mode': 'class-swap', 'data-frmpreview-classes': 'frm-size-s frm-size-m frm-size-l', 'data-frmpreview-prefix': 'frm-size-' } );
	targets[ 0 ].classList.add( 'frm-size-s', 'keep' );
	input.value = 'l';
	applyLivePreview( input, {} );
	assert.deepEqual( targets[ 0 ].classes().toSorted(), [ 'frm-size-l', 'frm-target', 'keep' ] );
	input.value = 'xl';
	applyLivePreview( input, {} );
	assert.deepEqual( targets[ 0 ].classes().toSorted(), [ 'frm-target', 'keep' ], 'an unknown value adds nothing' );
} );

test( 'server mode refreshes the preview of the panel field', () => {
	const { input } = setup( { 'data-frmpreview': '', 'data-frmpreview-mode': 'server' } );
	const refreshed = [];
	applyLivePreview( input, { refreshFieldPreview: fieldId => refreshed.push( fieldId ) } );
	assert.deepEqual( refreshed, [ '7' ] );
} );

/**
 * Add a field preview and its settings panel.
 *
 * @param {Object} document The fake document.
 * @param {string} fieldId  The field ID.
 * @return {Object} The field list item and its panel.
 */
function addField( document, fieldId ) {
	const body = el( 'div', { class: 'frm_form_fields' }, [ el( 'input', { class: 'frm-widget' } ) ] );
	const field = el( 'li', { id: `frm_field_id_${ fieldId }`, class: 'form-field', 'data-fid': fieldId }, [
		el( 'div', { id: `field_${ fieldId }_inner_container`, class: 'frm_inner_field_container' }, [ body ] ),
	] );
	const panel = el( 'div', { id: `frm-single-settings-${ fieldId }`, class: 'frm-single-settings frm-type-widget', 'data-fid': fieldId } );
	document.root.append( field, panel );
	return { field, panel, body };
}

test( 'onFieldSettingsReady runs once per panel on every path', async () => {
	const document = installFakeDocument();
	const { callbacks } = installFakeHooks();
	const first = addField( document, '1' );
	const readiness = createFieldReadiness( { ensureFieldSettings: () => null } );
	const calls = [];
	readiness.onFieldSettingsReady( '.frm-type-widget', ( panel, fieldId ) => calls.push( fieldId ) );
	assert.deepEqual( calls, [ '1' ], 'the initial scan' );

	const added = addField( document, '2' );
	document.dispatchEvent( { type: 'frm_added_field', frmField: added.field } );
	callbacks.frmShowedFieldSettings.forEach( callback => callback( null, first.panel ) );
	await flushMicrotasks();
	assert.deepEqual( calls, [ '1', '2' ], 'frm_added_field, and a panel shown again is not repeated' );

	const loaded = addField( document, '3' );
	document.dispatchEvent( { type: 'frm_ajax_loaded_field', frmFields: [ { id: '3' } ] } );
	await flushMicrotasks();
	const deferred = addField( document, '4' );
	callbacks.frm_field_settings_loaded.forEach( callback => callback( deferred.panel ) );
	await flushMicrotasks();
	assert.deepEqual( calls, [ '1', '2', '3', '4' ], 'frm_ajax_loaded_field and frm_field_settings_loaded' );
	assert.ok( loaded.panel );

	const other = [];
	readiness.onFieldSettingsReady( '.frm-type-other', panel => other.push( panel ) );
	assert.deepEqual( other, [], 'panels that do not match are skipped' );
} );

test( 'onFieldPreviewReady runs again after the preview body is replaced', async () => {
	const document = installFakeDocument();
	const { callbacks } = installFakeHooks();
	const { field, body } = addField( document, '5' );
	const loading = el( 'li', { id: 'frm_field_id_6', class: 'form-field frm_field_loading', 'data-fid': '6' } );
	document.root.append( loading );
	const readiness = createFieldReadiness( { ensureFieldSettings: () => null } );
	const calls = [];
	readiness.onFieldPreviewReady( '.frm-widget', ( fieldLi, fieldId ) => calls.push( fieldId ) );
	assert.deepEqual( calls, [ '5' ], 'loading previews are skipped' );
	assert.equal( getFieldPreviewBody( '5' ), body );

	const newBody = body.cloneNode();
	newBody.append( el( 'input', { class: 'frm-widget' } ) );
	body.replaceWith( newBody );
	callbacks.frm_field_preview_refreshed.forEach( callback => callback( '5', field ) );
	callbacks.frm_field_preview_refreshed.forEach( callback => callback( '5', field ) );
	await flushMicrotasks();
	assert.deepEqual( calls, [ '5', '5' ] );
} );

test( 'whenFieldSettingsReady resolves with the panel once it loads', async () => {
	const document = installFakeDocument();
	const { callbacks } = installFakeHooks();
	const field = el( 'li', { id: 'frm_field_id_8', class: 'form-field', 'data-fid': '8' } );
	document.root.append( field );
	const readiness = createFieldReadiness( { ensureFieldSettings: () => document.getElementById( 'frm-single-settings-8' ) } );

	assert.equal( await readiness.whenFieldSettingsReady( '99' ), null, 'a missing field resolves with null' );

	const pending = readiness.whenFieldSettingsReady( '8' );
	const panel = el( 'div', { id: 'frm-single-settings-8', class: 'frm-single-settings', 'data-fid': '8' } );
	field.append( panel );
	callbacks.frm_field_settings_loaded.forEach( callback => callback( panel ) );
	assert.equal( await pending, panel );
	assert.equal( await readiness.whenFieldSettingsReady( '8' ), panel, 'a loaded panel resolves right away' );
} );

test( 'refreshFieldPreview debounces, aborts stale requests, and swaps the preview body', async () => {
	const document = installFakeDocument();
	const { recorded } = installFakeHooks();
	const { field, panel } = addField( document, '9' );
	panel.append(
		el( 'input', { name: 'field_options[size_9]', value: '40' } ),
		el( 'input', { type: 'checkbox', name: 'field_options[read_only_9]', value: '1' } ),
		el( 'input', { name: 'field_options[size_10]', value: 'other field' } )
	);
	globalThis.FormData = class {
		constructor() {
			this.entries = [];
		}
		append( name, value ) {
			this.entries.push( [ name, String( value ) ] );
		}
	};

	const requests = [];
	const post = ( action, data, signal ) => new Promise( ( resolve, reject ) => {
		requests.push( { action, data, signal } );
		signal.addEventListener( 'abort', () => reject( new Error( 'aborted' ) ) );
		setTimeout( () => resolve( { html: `<b>${ requests.length }</b>` } ), 20 );
	} );
	const refresh = createRefreshFieldPreview( { getFieldPreviewBody, post } );

	const first = refresh( '9', { delay: 5 } );
	const second = refresh( '9', { delay: 5, data: { extra: 'yes' } } );
	await new Promise( resolve => setTimeout( resolve, 10 ) );
	assert.equal( requests.length, 1, 'calls within the delay share one request' );
	const third = refresh( '9', { delay: 5 } );
	assert.equal( requests[ 0 ].signal.aborted, true, 'a new call aborts the request in flight' );

	const results = await Promise.all( [ first, second, third ] );
	assert.deepEqual( results, [ field, field, field ] );
	assert.equal( requests.length, 2 );
	assert.equal( requests[ 1 ].action, 'frm_refresh_field_preview' );
	assert.deepEqual( requests[ 0 ].data.entries, [
		[ 'field_id', '9' ],
		[ 'form_id', '' ],
		[ 'field_options[size_9]', '40' ],
		[ 'field_options[read_only_9]', '0' ],
		[ 'extra', 'yes' ],
	] );
	assert.equal( getFieldPreviewBody( '9' ).innerHTML, '<b>2</b>' );
	assert.deepEqual( recorded.filter( ( [ name ] ) => 'frm_field_preview_refreshed' === name ), [ [ 'frm_field_preview_refreshed', '9', field ] ] );

	field.remove();
	assert.equal( await refresh( '9', { delay: 0 } ), null, 'a removed field resolves with null' );
} );
