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
