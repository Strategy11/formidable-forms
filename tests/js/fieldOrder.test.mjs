/* oxlint-disable import/no-nodejs-modules -- Tests run with the Node test runner. */
import assert from 'node:assert/strict';
import { test } from 'node:test';
import { syncFieldOrder, confirmFieldOrderSaved } from '../../js/src/admin/fieldOrder.js';

let fields = [];
let orderInputs = [];

globalThis.document = {
	createElement: tagName => ( { tagName: tagName.toUpperCase() } ),
	querySelectorAll: selector => selector.startsWith( 'input' ) ? orderInputs : fields
};

function makeForm() {
	const children = [];
	return {
		children,
		querySelector: selector => {
			if ( '[name="frm_field_order"]' === selector ) {
				return children.find( child => 'frm_field_order' === child.name ) || null;
			}
			return null;
		},
		insertBefore: child => children.push( child )
	};
}

function setFields( ids ) {
	fields = ids.map( id => ( { dataset: { fid: String( id ) } } ) );
}

test( 'a reorder adds a named hidden input that is posted with the form', () => {
	orderInputs = [ 1, 2, 3 ].map( id => ( { name: `field_options[field_order_${ id }]`, value: String( id ) } ) );
	setFields( [ 1, 3, 2 ] );
	const form = makeForm();

	assert.equal( syncFieldOrder( form ), true );

	const input = form.querySelector( '[name="frm_field_order"]' );
	assert.equal( input.tagName, 'INPUT' );
	assert.equal( input.type, 'hidden' );
	assert.equal( input.name, 'frm_field_order' );
	assert.deepEqual( JSON.parse( input.value ), { 3: 2, 2: 3 } );
	assert.equal( orderInputs[ 2 ].value, 2 );

	// Only a confirmed save clears the pending orders.
	confirmFieldOrderSaved( form, input.value );
	assert.deepEqual( JSON.parse( input.value ), {} );
} );

test( 'unconfirmed orders are sent again on the next save', () => {
	orderInputs = [ 1, 2 ].map( id => ( { name: `field_options[field_order_${ id }]`, value: String( id ) } ) );
	setFields( [ 2, 1 ] );
	const form = makeForm();

	syncFieldOrder( form );
	const input = form.querySelector( '[name="frm_field_order"]' );
	const firstSave = input.value;

	// The save failed, so confirmFieldOrderSaved() is not called.
	assert.equal( syncFieldOrder( form ), false );
	assert.equal( input.value, firstSave );
	assert.deepEqual( JSON.parse( firstSave ), { 1: 2, 2: 1 } );

	confirmFieldOrderSaved( form, firstSave );
} );
