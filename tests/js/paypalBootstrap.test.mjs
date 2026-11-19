/* oxlint-disable import/no-nodejs-modules -- Tests run with the Node test runner. */
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { test } from 'node:test';
import { runInNewContext } from 'node:vm';

const source = readFileSync( new URL( '../../paypal/js/frontend.js', import.meta.url ), 'utf8' );

/**
 * Run the real PayPal frontend script with a fake document.
 *
 * Reading the card element is the first thing paypalInit() does.
 *
 * @param {string} readyState The document's readyState when the script runs.
 *
 * @return {Object} The DOMContentLoaded listeners and the number of init runs.
 */
function runScript( readyState ) {
	const state = { listeners: [], initRuns: 0 };
	const document = {
		readyState,
		addEventListener: ( type, callback ) => state.listeners.push( { type, callback } ),
		querySelector: () => {
			state.initRuns++;
			return null;
		}
	};
	const jQuery = () => ( { on: () => {} } );

	runInNewContext( source, {
		window: { frmPayPalVars: {}, paypal: {} },
		document,
		jQuery,
		setInterval,
		clearInterval
	} );

	return state;
}

test( 'PayPal initializes right away when the script runs after DOMContentLoaded', () => {
	const state = runScript( 'complete' );

	assert.equal( state.initRuns, 1 );
} );

test( 'PayPal initializes once on DOMContentLoaded when the script runs while loading', () => {
	const state = runScript( 'loading' );

	assert.equal( state.initRuns, 0 );

	const listeners = state.listeners.filter( listener => 'DOMContentLoaded' === listener.type );
	assert.equal( listeners.length, 1 );

	listeners[ 0 ].callback();
	assert.equal( state.initRuns, 1 );
} );

/**
 * Let pending microtasks run until a condition holds, giving up after a few ticks.
 *
 * @param {Function} isDone Returns true once the awaited state is reached.
 * @return {Promise<void>} Resolves when the condition holds or the tick limit is reached.
 */
async function flushMicrotasksUntil( isDone ) {
	for ( let tick = 0; tick < 10 && ! isDone(); tick++ ) {
		await Promise.resolve();
	}
}

/**
 * Exercise the real initializer while wallet discovery is still waiting.
 *
 * @return {Promise<void>} Resolves after both competing initialization runs finish.
 */
async function checkOverlappingInitialization() {
	const start = source.indexOf( '\tasync function paypalInit()' );
	const end = source.indexOf( '\n\t// ---- Discovery ----', start );
	assert.ok( start >= 0 && end > start, 'PayPal initializer boundaries must exist.' );
	let resume;
	const gate = new Promise( resolve => {
		resume = resolve;
	} );
	let waiting = 0;
	let selectorCount = 0;
	const form = { addEventListener() {} };
	const element = { innerHTML: '', closest: () => form, append() {}, classList: { add() {} } };
	const methods = new Map();
	const context = {
		window: { paypal: { Buttons() {} } },
		document: {
			querySelector: () => element,
			createElement: () => ( { classList: { add() {} }, append() {} } ),
		},
		getPayPalSettings: () => [ { one: 'single', paypalLayout: 'checkout_only' } ],
		waitForVisiblePaymentField: async () => {},
		listenForFieldMutations() {},
		listenForSubmitButtonMutations() {},
		paymentMethods: methods,
		discoverPaymentMethods: async () => methods.set( 'paypal', { eligible: true, render: async () => {} } ),
		discoverDeferredPaymentMethods: () => {
			waiting++; return gate;
		},
		displayPaymentFailure() {},
		buildRadioGroup: () => {
			selectorCount++; return { style: {} };
		},
		renderMarks() {},
		PRE_RENDER_METHODS: new Set( [ 'paypal' ] ),
		selectPaymentMethod: async () => {},
		handleFormSubmission() {},
		actionsUseConditionalLogic: () => false,
	};
	runInNewContext( `let thisForm, cardElementContainer, isRecurring, selectedMethod; let initRunId = 0;${ source.slice( start, end ) }`, context );
	const first = context.paypalInit();
	await flushMicrotasksUntil( () => waiting >= 1 );
	assert.equal( waiting, 1 );
	const second = context.paypalInit();
	await flushMicrotasksUntil( () => waiting >= 2 );
	assert.equal( waiting, 2 );
	resume();
	await Promise.all( [ first, second ] );
	assert.equal( selectorCount, 1, 'Only the latest run should render the payment selector.' );
}

test( 'PayPal renders only the latest initialization after asynchronous discovery', checkOverlappingInitialization );

test( 'superseded wallet discovery does not register methods in the current run', async () => {
	const start = source.indexOf( '\tasync function discoverDeferredPaymentMethods(' );
	const end = source.indexOf( '\n\t/**', start );
	assert.ok( start >= 0 && end > start, 'Wallet discovery boundaries must exist.' );
	const registered = [];
	const context = {
		frmPayPalVars: { includeGooglePay: true, includeApplePay: false },
		resolveGooglePayEligibility: async () => true,
		registerMethod: key => registered.push( key ),
		renderGooglePayButton() {},
	};
	runInNewContext( `let initRunId = 2;${ source.slice( start, end ) }`, context );
	await context.discoverDeferredPaymentMethods( { buttonsAreEnabled: true, isRecurring: false, runId: 1 } );
	assert.deepEqual( registered, [] );
	await context.discoverDeferredPaymentMethods( { buttonsAreEnabled: true, isRecurring: false, runId: 2 } );
	assert.deepEqual( registered, [ 'google_pay' ] );
} );

test( 'a page change before the PayPal SDK loads leaves initialization to the bootstrap poll', async () => {
	const start = source.indexOf( '\tasync function paypalInit()' );
	const end = source.indexOf( '\n\t// ---- Discovery ----', start );
	assert.ok( start >= 0 && end > start );
	const context = {
		window: {},
		document: { querySelector: () => ( { closest: () => ( {} ) } ) },
		getPayPalSettings: () => [ { one: 'single' } ],
	};
	runInNewContext( `let thisForm, cardElementContainer, isRecurring;${ source.slice( start, end ) }`, context );
	await context.paypalInit();
} );

/**
 * Run the real createPayPalButton() and return the config passed to paypal.Buttons().
 *
 * @param {string} fundingSource The PayPal FUNDING constant.
 * @param {string} color         The saved button color.
 *
 * @return {Object} The button config.
 */
function createButtonConfig( fundingSource, color ) {
	const start = source.indexOf( '\tfunction createPayPalButton(' );
	const end = source.indexOf( '\n\t// ---- Google Pay ----', start );
	assert.ok( start >= 0 && end > start, 'Button creation boundaries must exist.' );
	let config;
	const context = {
		frmPayPalVars: { buttonStyle: { color, label: 'paypal' } },
		paypal: {
			Buttons: buttonConfig => {
				config = buttonConfig;
			}
		},
		onApprove() {},
		onError() {},
		onCancel() {},
		createOrder() {},
		createSubscription() {},
	};
	runInNewContext( `${ source.slice( start, end ) }\ncreatePayPalButton( fundingSource, false );`, { ...context, fundingSource } );
	return config;
}

test( 'the PayPal button keeps every color PayPal supports for it', () => {
	[ 'gold', 'blue', 'silver', 'white', 'black' ].forEach( color => {
		assert.equal( createButtonConfig( 'paypal', color ).style.color, color );
	} );
} );

test( 'a funding source drops a color it does not support', () => {
	assert.equal( 'color' in createButtonConfig( 'venmo', 'gold' ).style, false );
	assert.equal( 'color' in createButtonConfig( 'paypal', 'purple' ).style, false );
} );
