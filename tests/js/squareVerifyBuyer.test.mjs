import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { test } from 'node:test';
import { runInNewContext } from 'node:vm';
import { parseSync } from '@babel/core';

const source = readFileSync( new URL( '../../square/js/frontend.js', import.meta.url ), 'utf8' );
const ast = parseSync( source, { configFile: false, babelrc: false } );
const wrapper = ast.program.body[ 0 ].expression.callee;
const names = [ 'getCardKey', 'verifyBuyer' ];
const functions = names.map( name => {
	const declaration = wrapper.body.body.find( node => node.id?.name === name );
	assert.ok( declaration, `${ name } must exist in the Square frontend` );
	return source.slice( declaration.start, declaration.end );
} ).join( '\n' );

/**
 * Build a Square token result for a card.
 *
 * @param {string} token The single use card token.
 * @param {string} last4 The last four digits of the card.
 * @return {Object} The token result.
 */
function tokenResult( token, last4 ) {
	return {
		status: 'OK',
		token,
		details: { card: { brand: 'VISA', last4, expMonth: 12, expYear: 2030 } }
	};
}

/**
 * Load the production functions with a mocked AJAX response and Square SDK.
 *
 * @return {Object} The loaded functions and the verifyBuyer calls made to the SDK.
 */
function setup() {
	const sdkCalls = [];
	const payments = {
		verifyBuyer: async token => {
			sdkCalls.push( token );
			return { token: `verification-for-${ token }` };
		}
	};
	const context = {
		thisForm: {},
		buyerTokens: {},
		frmSquareVars: { nonce: 'nonce', ajax: '/wp-admin/admin-ajax.php' },
		FormData: class {
			append() {}
			delete() {}
		},
		fetch: async () => ( {
			ok: true,
			json: async () => ( { success: true, data: { hash: 'same-amount-and-billing', verificationDetails: {} } } )
		} )
	};
	const functionsToLoad = `${ functions }\n({ getCardKey, verifyBuyer })`;
	const loaded = runInNewContext( functionsToLoad, context );
	return { ...loaded, payments, sdkCalls };
}

test( 'the same card reuses its buyer verification', async () => {
	const state = setup();
	const first = await state.verifyBuyer( state.payments, tokenResult( 'token-1', '1111' ) );
	const second = await state.verifyBuyer( state.payments, tokenResult( 'token-2', '1111' ) );
	assert.equal( second, first );
	assert.equal( state.sdkCalls.length, 1 );
} );

test( 'a different card after a decline is verified again', async () => {
	const state = setup();
	await state.verifyBuyer( state.payments, tokenResult( 'declined-card-token', '0002' ) );
	const retry = await state.verifyBuyer( state.payments, tokenResult( 'good-card-token', '1111' ) );
	assert.equal( retry, 'verification-for-good-card-token' );
	assert.deepEqual( state.sdkCalls, [ 'declined-card-token', 'good-card-token' ] );
} );

test( 'a token without card details is never reused', async () => {
	const state = setup();
	await state.verifyBuyer( state.payments, { status: 'OK', token: 'token-1' } );
	await state.verifyBuyer( state.payments, { status: 'OK', token: 'token-2' } );
	assert.equal( state.sdkCalls.length, 2 );
	assert.equal( state.getCardKey( { token: 'token-3', details: {} } ), 'token-3' );
} );
