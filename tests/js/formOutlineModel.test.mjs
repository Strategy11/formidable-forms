/* oxlint-disable import/no-nodejs-modules -- Tests run with the Node test runner. */
import assert from 'node:assert/strict';
import { mock, test } from 'node:test';
import { buildOutline, getCurrentPageIndex, groupPages, throttle } from '../../js/src/admin/formOutlineModel.js';

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
	return { id, type, heading: '', text: label, label: label || `Untitled field (${ id })`, key: '', sectionId: '', sectionIds: [], ...props };
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

test( 'fields still loading keep the pages but get no rows until they arrive', () => {
	const loading = { label: '', text: '', placeholder: true };
	const fields = [
		field( '1', 'text', 'Name' ),
		field( '2', 'text', '', loading ),
		// A placeholder only knows its type from data-ftype, which still marks a page break.
		field( '3', 'break', '', loading ),
		field( '4', 'text', '', loading ),
		field( '5', 'text', 'Address' ),
		field( '6', 'submit', '', loading )
	];
	const pages = groupPages( fields );
	const { items } = buildOutline( pages, '', pageNumber );

	assert.deepEqual( outline( fields ), [
		'page Page 1: Name',
		'  field Name',
		'page Page 2: Address',
		'  field Address'
	] );
	assert.deepEqual( items.map( item => item.fieldCount ), [ 2, 2 ] );
	assert.deepEqual( outline( fields, 'untitled' ), [] );
} );

test( 'throttle runs during a steady stream of calls, not only after it stops', () => {
	mock.timers.enable( { apis: [ 'setTimeout' ] } );
	let runs = 0;
	const queue = throttle( () => runs++, 100 );

	// Field batches keep arriving every 50 ms for a second.
	for ( let elapsed = 0; elapsed < 1000; elapsed += 50 ) {
		queue();
		mock.timers.tick( 50 );
	}
	mock.timers.tick( 100 );
	mock.timers.reset();

	assert.ok( runs >= 9, `Ran ${ runs } times.` );
} );

test( 'the page at the top of the canvas is current', () => {
	const pills = [ { index: 0, top: -900 }, { index: 1, top: -20 }, { index: 2, top: 300 } ];

	assert.equal( getCurrentPageIndex( pills, { top: 0, height: 800, scrollTop: 900, scrollHeight: 4000 } ), 1 );
	// A form with nothing to scroll stays on page 1.
	assert.equal( getCurrentPageIndex( [ { index: 0, top: 10 }, { index: 1, top: 300 } ], { top: 0, height: 800, scrollTop: 0, scrollHeight: 800 } ), 0 );
} );

test( 'short last pages become current as the canvas scrolls to the bottom', () => {
	// Pages 3 and 4 are short, so their pills can never reach the top of the canvas.
	const view = { top: 0, height: 800, scrollHeight: 3000 };
	const pillsAt = scrollTop => [
		{ index: 0, top: -scrollTop },
		{ index: 1, top: 1500 - scrollTop },
		{ index: 2, top: 2400 - scrollTop },
		{ index: 3, top: 2700 - scrollTop }
	];
	const seen = new Set();
	for ( let scrollTop = 0; scrollTop <= 2200; scrollTop += 10 ) {
		seen.add( getCurrentPageIndex( pillsAt( scrollTop ), { ...view, scrollTop } ) );
	}

	assert.deepEqual( Array.from( seen ), [ 0, 1, 2, 3 ] );
	assert.equal( getCurrentPageIndex( pillsAt( 2200 ), { ...view, scrollTop: 2200 } ), 3 );
	assert.equal( getCurrentPageIndex( pillsAt( 1400 ), { ...view, scrollTop: 1400 } ), 0 );
} );

test( 'a page title match does not list the page\'s sections with nothing in them', () => {
	const fields = [
		field( '1', 'text', 'Name' ),
		field( '2', 'break', 'Next' ),
		field( '3', 'text', 'Delivery' ),
		field( '4', 'divider', 'Billing' ),
		inSection( '4', field( '5', 'text', 'Street' ) ),
		field( '6', 'divider', 'Shipping' ),
		inSection( '6', field( '7', 'text', 'Street' ) )
	];

	assert.deepEqual( outline( fields, 'page 2' ), [ 'page Page 2: Delivery' ] );
	// The page title comes from its first field, which still shows as a match.
	assert.deepEqual( outline( fields, 'delivery' ), [ 'page Page 2: Delivery', '  field Delivery' ] );
	assert.deepEqual( outline( fields, 'billing' ), [ 'page Page 2: Delivery', '  section Billing' ] );
} );

test( 'search matches a field ID exactly and a field key by its start', () => {
	const fields = [ [ '10', 'A' ], [ '100', 'B' ], [ '105', 'C' ], [ '110', 'D' ] ].map( ( [ id, name ] ) => field( id, 'text', `Question ${ name }`, { key: `qkey${ id }` } ) );
	fields.push( field( '7', 'text', 'Email', { key: 'contact_email' } ) );

	assert.deepEqual( outline( fields, '10' ), [ 'field Question A' ] );
	assert.deepEqual( outline( fields, 'contact_em' ), [ 'field Email' ] );
	assert.deepEqual( outline( fields, 'CONTACT_EMAIL' ), [ 'field Email' ] );
	// Keys are random letters and numbers, so a match in the middle of one is noise.
	assert.deepEqual( outline( fields, 'email' ), [ 'field Email' ] );
	assert.deepEqual( outline( fields, 'key10' ), [] );
} );

test( 'search lists every match', () => {
	const fields = Array.from( { length: 70 }, ( item, index ) => field( `${ index + 1 }`, 'text', `Item ${ index + 1 }` ) );
	const result = buildOutline( groupPages( fields ), 'item', pageNumber );

	assert.equal( result.items.length, 70 );
	assert.equal( result.resultCount, 70 );
} );
