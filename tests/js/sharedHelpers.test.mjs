import assert from 'node:assert/strict';
import { test } from 'node:test';
import { createRequire } from 'node:module';
import { installFakeDocument, el, flushMicrotasks } from './helpers/fakeDom.mjs';

const require = createRequire( import.meta.url );

const DOM_SCRIPT = require.resolve( '../../js/admin/dom.js' );
const FRONT_SCRIPT = require.resolve( '../../js/formidable.js' );

/**
 * Set the fake globals and forget a plain browser script, so the next require runs it again.
 *
 * @param {string} resolved The resolved script path.
 * @param {Object} globals  Extra globals for the script.
 * @return {void}
 */
function prepareScript( resolved, globals = {} ) {
	globalThis.window = { ...globals };
	Object.assign( globalThis, globals );
	delete require.cache[ resolved ];
}

/**
 * @return {Object} frmDom from a fresh run of dom.js.
 */
function loadDom() {
	prepareScript( DOM_SCRIPT );
	require( '../../js/admin/dom.js' );
	return globalThis.window.frmDom;
}

function createJQueryStub( handlers ) {
	const api = {
		on( events, selectorOrHandler, maybeHandler ) {
			const handler = 'function' === typeof selectorOrHandler ? selectorOrHandler : maybeHandler;
			events.split( ' ' ).forEach( eventName => {
				( handlers[ eventName ] = handlers[ eventName ] || [] ).push( handler );
			} );
			return api;
		},
		off: () => api,
		ready: () => api,
		trigger: () => api,
	};
	return () => api;
}

function loadFrontForm() {
	const handlers = {};
	prepareScript( FRONT_SCRIPT, { jQuery: createJQueryStub( handlers ) } );
	require( '../../js/formidable.js' );
	return { frmFrontForm: globalThis.window.frmFrontForm, jQueryHandlers: handlers };
}

const wait = ms => new Promise( resolve => setTimeout( resolve, ms ) );

for ( const [ label, getUtil ] of [
	[ 'frmDom.util', () => loadDom().util ],
	[ 'frmFrontForm', () => {
		installFakeDocument();
		return loadFrontForm().frmFrontForm;
	} ],
] ) {
	test( `${ label }.debounceByKey keeps a timer per key, with cancel and flush`, async () => {
		installFakeDocument();
		const { debounceByKey } = getUtil();
		const calls = [];
		const run = debounceByKey( ( ...args ) => calls.push( args ), 20 );

		run( 'a', 1 );
		run( 'a', 2 );
		run( 'b', 3 );
		run( 'c' );
		run.cancel( 'b' );
		await wait( 40 );
		assert.deepEqual( calls, [ [ 2 ], [ 'c' ] ], 'only the last call per key runs, and a lone key is passed on' );

		run( 'd', 4 );
		run.flush( 'd' );
		assert.deepEqual( calls.at( -1 ), [ 4 ], 'flush runs the pending call now' );
		await wait( 40 );
		assert.equal( calls.length, 3, 'a flushed call does not run again' );
		run.flush( 'missing' );
		assert.equal( calls.length, 3, 'flushing a key with nothing pending does nothing' );
	} );

	test( `${ label }.oncePerFrame runs once per frame with the latest arguments`, async () => {
		installFakeDocument();
		const { oncePerFrame } = getUtil();
		const calls = [];
		const throttled = oncePerFrame( value => calls.push( value ) );
		throttled( 1 );
		throttled( 2 );
		throttled( 3 );
		assert.deepEqual( calls, [] );
		await wait( 40 );
		assert.deepEqual( calls, [ 3 ] );
		throttled( 4 );
		await wait( 40 );
		assert.deepEqual( calls, [ 3, 4 ] );
	} );
}

test( 'frmFrontForm.onFieldsReady initializes each element once, however it arrived', async () => {
	const document = installFakeDocument();
	const { frmFrontForm, jQueryHandlers } = loadFrontForm();
	const first = el( 'div', { class: 'frm-widget' } );
	document.root.append( first );

	const initialized = [];
	const unsubscribe = frmFrontForm.onFieldsReady( '.frm-widget', element => initialized.push( element ) );
	assert.deepEqual( initialized, [ first ], 'scans right away when the page is ready' );

	const second = el( 'div', { class: 'frm-widget' } );
	const form = el( 'form', {}, [ second ] );
	document.root.append( form );
	document.dispatchEvent( { type: 'frmFieldsInserted', detail: { container: form } } );
	jQueryHandlers.frmPageChanged.forEach( handler => handler() );
	jQueryHandlers.frmAfterAddRow.forEach( handler => handler() );
	await flushMicrotasks();
	assert.deepEqual( initialized, [ first, second ], 'events in the same tick share one idempotent scan' );

	const container = el( 'div', { class: 'frm-widget' } );
	document.root.append( container );
	document.dispatchEvent( { type: 'frmFieldsInserted', detail: { container } } );
	await flushMicrotasks();
	assert.equal( initialized.at( -1 ), container, 'the container itself is included when it matches' );

	unsubscribe();
	document.root.append( el( 'div', { class: 'frm-widget' } ) );
	jQueryHandlers.frmFormComplete.forEach( handler => handler() );
	await flushMicrotasks();
	assert.equal( initialized.length, 3, 'nothing runs after unsubscribe' );
} );

test( 'frmFrontForm.onFieldsReady tears down detached elements and isolates init errors', async () => {
	const document = installFakeDocument();
	const { frmFrontForm, jQueryHandlers } = loadFrontForm();
	const widget = el( 'div', { class: 'frm-widget' } );
	document.root.append( widget );

	const tornDown = [];
	const errors = [];
	const originalError = console.error;
	console.error = ( ...args ) => errors.push( args );
	try {
		frmFrontForm.onFieldsReady( '.frm-widget', () => {
			throw new Error( 'broken add-on' );
		} );
		const initialized = [];
		frmFrontForm.onFieldsReady( '.frm-widget', element => initialized.push( element ), { teardown: element => tornDown.push( element ) } );
		assert.deepEqual( initialized, [ widget ], 'a failing init does not stop other registrations' );
		assert.equal( errors.length, 1 );

		widget.remove();
		jQueryHandlers.frm_after_start_over.forEach( handler => handler() );
		await flushMicrotasks();
		assert.deepEqual( tornDown, [ widget ], 'a detached element is torn down before the next scan' );

		jQueryHandlers.frmInPlaceEdit.forEach( handler => handler() );
		await flushMicrotasks();
		assert.deepEqual( tornDown, [ widget ], 'and only once' );
	} finally {
		console.error = originalError;
	}
} );

test( 'frmFrontForm.onFieldsReady waits for DOMContentLoaded while the page loads', () => {
	const document = installFakeDocument();
	document.readyState = 'loading';
	const { frmFrontForm } = loadFrontForm();
	document.root.append( el( 'div', { class: 'frm-widget' } ) );
	const initialized = [];
	frmFrontForm.onFieldsReady( '.frm-widget', element => initialized.push( element ) );
	assert.equal( initialized.length, 0 );
	document.dispatchEvent( { type: 'DOMContentLoaded' } );
	assert.equal( initialized.length, 1 );
} );
