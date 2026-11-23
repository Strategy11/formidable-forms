import assert from 'node:assert/strict';
import fs from 'node:fs';
import { test } from 'node:test';
import vm from 'node:vm';

const builder = fs.readFileSync( new URL( '../../js/src/admin/admin.js', import.meta.url ), 'utf8' );

test( 'the builder reserves distinct orders and follows the server response', () => {
	const functions = [ 'reserveFieldOrder', 'syncFieldOrderWithResponse' ].map( name => {
		const match = builder.match( new RegExp( `\\tfunction ${ name }\\([^\\n]*\\n[\\s\\S]*?\\n\\t}` ) );
		assert.ok( match, name );
		return match[ 0 ];
	} );
	const document = {
		querySelectorAll( selector ) {
			assert.equal( selector, '#frm-show-fields li.edit_form_item.frm_field_box' );
			return Array( 4 );
		},
	};
	const div = () => ( {
		querySelectorAll( selector ) {
			assert.equal( selector, 'input[name^="field_options[field_order_"]' );
			return [ { value: '3' } ];
		},
		querySelector( selector ) {
			assert.equal( selector, '#frm-last-row-fields-order' );
			return { value: '{"9":7}' };
		},
	} );
	const api = vm.runInNewContext(
		`let nextFieldOrder = 0;\n${ functions.join( '\n' ) }\n({ reserveFieldOrder, syncFieldOrderWithResponse });`,
		{ document, div }
	);

	assert.equal( api.reserveFieldOrder( 1 ), 0 );
	api.syncFieldOrderWithResponse( '' );
	assert.equal( api.reserveFieldOrder( 1 ), 8 );
	assert.equal( api.reserveFieldOrder( 1 ), 10 );
	api.syncFieldOrderWithResponse( '' );
	assert.equal( api.reserveFieldOrder( 0 ), 12 );
} );
