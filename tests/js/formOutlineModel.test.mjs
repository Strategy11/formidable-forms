/* oxlint-disable import/no-nodejs-modules -- Tests run with the Node test runner. */
import assert from 'node:assert/strict';
import { test } from 'node:test';
import { buildOutline, groupPages } from '../../js/src/admin/formOutlineModel.js';

const pageNumber = index => `Page ${ index + 1 }`;

/**
 * A field as formOutline.js describes it.
 *
 * @param {string} id    Field ID.
 * @param {string} type  Field type.
 * @param {string} label Field name.
 * @param {Object} props Other properties to set.
 * @return {Object} The field.
 */
function field( id, type, label = '', props = {} ) {
	return { id, type, heading: '', text: label, label: label || `Untitled field (${ id })`, sectionId: '', sectionIds: [], ...props };
}

function inSection( sectionId, item ) {
	return { ...item, sectionId, sectionIds: [ sectionId ] };
}

// Turns the outline into "kind label" lines, indented by depth, so tests can compare it at a glance.
function outline( fields, query = '' ) {
	const lines = [];
	const walk = ( items, depth ) => items.forEach( item => {
		lines.push( `${ '  '.repeat( depth ) }${ item.kind } ${ item.label }` );
		walk( item.children || [], depth + 1 );
	} );
	walk( buildOutline( groupPages( fields ), query, pageNumber ).items, 0 );
	return lines;
}

test( 'page breaks split the outline into pages titled by their first field', () => {
	const fields = [
		field( '1', 'text', 'Name' ),
		field( '2', 'email', 'Email' ),
		field( '3', 'break', 'Next' ),
		field( '4', 'text', 'Address' ),
		field( '5', 'submit', 'Submit' )
	];

	assert.deepEqual( outline( fields ), [
		'page Page 1: Name',
		'  field Name',
		'  field Email',
		'page Page 2: Address',
		'  field Address'
	] );
} );

test( 'sections group their fields and count them', () => {
	const fields = [
		field( '1', 'divider', 'Contact' ),
		inSection( '1', field( '2', 'text', 'Name' ) ),
		inSection( '1', field( '3', 'end_divider' ) ),
		field( '4', 'text', 'Notes' )
	];
	const { items } = buildOutline( groupPages( fields ), '', pageNumber );

	assert.deepEqual( outline( fields ), [ 'section Contact', '  field Name', 'field Notes' ] );
	assert.equal( items[ 0 ].fieldCount, 1 );
} );

test( 'search keeps the sections that hold a match', () => {
	const fields = [
		field( '1', 'divider', 'Contact' ),
		inSection( '1', field( '2', 'text', 'Name' ) ),
		field( '4', 'divider', 'Other' ),
		inSection( '4', field( '5', 'text', 'Notes' ) )
	];

	assert.deepEqual( outline( fields, 'name' ), [ 'section Contact', '  field Name' ] );
} );
