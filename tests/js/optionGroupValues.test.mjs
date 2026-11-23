/* oxlint-disable import/no-nodejs-modules -- Tests run with the Node test runner. */
/* eslint-disable sonarjs/code-eval -- The test runs a function from the checked-in builder in a fixture context. */
import assert from 'node:assert/strict';
import fs from 'node:fs';
import { test } from 'node:test';
import vm from 'node:vm';

const builder = fs.readFileSync( new URL( '../../js/src/admin/admin.js', import.meta.url ), 'utf8' );

test( 'duplicate-value checks exclude group headings and still catch repeated options', () => {
	const match = builder.match( /\tfunction checkUniqueOpt\([^\n]*\n[\s\S]*?\n\t}/ );
	assert.ok( match );

	const notices = [];
	const label = 'Fruit';
	const heading = { id: 'heading', name: 'group', value: label };
	const option = { id: 'option', name: 'option', value: label };
	const target = {
		...option,
		closest: () => ( { getAttribute: () => '1', querySelector: () => null } ),
	};
	const inputs = [ heading, option ];
	const document = {
		getElementById: () => ( {
			querySelectorAll: selector => selector.endsWith( ':not(.frm-option-group-name)' ) ? inputs.slice( 1 ) : inputs,
		} ),
	};
	const check = vm.runInNewContext( `${ match[ 0 ] }\ncheckUniqueOpt;`, {
		document,
		infoModal: message => notices.push( message ),
		sprintf: ( format, value ) => format.replace( '%s', value ),
		__: message => message,
		purifyHtml: value => value,
	} );

	check( target );
	assert.deepEqual( notices, [] );

	inputs.push( { ...option, id: 'duplicate' } );
	check( target );
	assert.deepEqual( notices, [ 'Duplicate option value "Fruit" detected' ] );
} );
