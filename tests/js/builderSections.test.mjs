import assert from 'node:assert/strict';
import { test } from 'node:test';

const { countSectionFields, isCountedField } = await import( '../../js/src/admin/builderSections.js' );

/**
 * A field preview list item, as the builder renders it.
 *
 * @param {string}    type    The field type.
 * @param {...string} classes Extra classes on the list item.
 * @return {Object} An element-like object.
 */
function field( type, ...classes ) {
	const classList = new Set( classes );
	return {
		dataset: { type },
		classList: { contains: name => classList.has( name ) },
	};
}

const section = type => field( type, 'edit_field_type_divider' );
const endMarker = type => field( type, 'edit_field_type_end_divider' );

test( 'counts regular fields', () => {
	assert.equal( isCountedField( field( 'text' ) ), true );
	assert.equal( isCountedField( field( 'name' ) ), true );
} );

test( 'skips sections, containers and their end markers', () => {
	assert.equal( isCountedField( section( 'divider' ) ), false );
	assert.equal( isCountedField( section( 'tabs' ) ), false );
	assert.equal( isCountedField( endMarker( 'end_divider' ) ), false );
	assert.equal( isCountedField( endMarker( 'end_tabs' ) ), false );
} );

test( 'skips the submit button and page breaks', () => {
	assert.equal( isCountedField( field( 'submit' ) ), false );
	assert.equal( isCountedField( field( 'break' ) ), false );
} );

test( 'a tabs container counts only the fields inside its panels', () => {
	// Tabs > [ Attendee: 5 fields ], [ Sessions: 3 fields ], [ VIP extras: 3 fields ], end of tabs.
	const panel = count => [ section( 'divider' ), ...Array.from( { length: count }, () => field( 'text' ) ), endMarker( 'end_divider' ) ];
	const descendants = [ ...panel( 5 ), ...panel( 3 ), ...panel( 3 ), endMarker( 'end_tabs' ) ];

	assert.equal( countSectionFields( descendants ), 11 );
} );

test( 'an empty section counts zero', () => {
	assert.equal( countSectionFields( [] ), 0 );
} );
