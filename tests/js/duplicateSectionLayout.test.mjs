/* oxlint-disable import/no-nodejs-modules -- Tests run with the Node test runner. */
import assert from 'node:assert/strict';
import { test } from 'node:test';
import { groupCopiesBySourceRow, copiedRowsNeedRestore } from '../../js/src/admin/duplicateSectionLayout.js';

function makeRow() {
	return { parentElement: { isRow: true } };
}

function makeCopy( section ) {
	return { parentElement: { closest: () => section } };
}

const isFieldGroup = element => true === element.isRow;

test( 'copies are grouped by the source row their original sat in, in source order', () => {
	const section = { name: 'copied section' };
	const topRow = makeRow();
	const bottomRow = makeRow();
	const copyA = makeCopy( section );
	const copyB = makeCopy( section );
	const copyC = makeCopy( section );
	// Field C was moved above A and B in the builder before the Section was duplicated.
	const layouts = new Map( [
		[ '3', { row: topRow } ],
		[ '1', { row: bottomRow } ],
		[ '2', { row: bottomRow } ],
	] );
	const copies = new Map( [ [ '1', copyA ], [ '2', copyB ], [ '3', copyC ] ] );

	const sections = groupCopiesBySourceRow( layouts, copies, isFieldGroup );

	assert.deepEqual( [ ...sections.keys() ], [ section ] );
	assert.deepEqual( [ ...sections.get( section ).values() ], [ [ copyC ], [ copyA, copyB ] ] );
} );

test( 'fields without a copy or a source row are left out', () => {
	const section = {};
	const layouts = new Map( [
		[ '1', { row: makeRow() } ],
		[ '2', { row: makeRow() } ],
		[ '3', { row: { parentElement: { isRow: false } } } ],
		[ '4', { row: undefined } ],
	] );
	const copies = new Map( [ [ '1', makeCopy( section ) ], [ '3', makeCopy( section ) ], [ '4', makeCopy( section ) ] ] );

	const rows = groupCopiesBySourceRow( layouts, copies, isFieldGroup ).get( section );

	assert.equal( rows.size, 1 );
} );

test( 'rows are rebuilt only when the same fields are laid out differently', () => {
	const [ a, b, c ] = [ { id: 'a' }, { id: 'b' }, { id: 'c' } ];

	assert.equal( copiedRowsNeedRestore( [ [ a, b ], [ c ] ], [ [ c ], [ a, b ] ] ), true, 'A moved row should be restored.' );
	assert.equal( copiedRowsNeedRestore( [ [ a, b, c ] ], [ [ a, b ], [ c ] ] ), true, 'A split row should be restored.' );
	assert.equal( copiedRowsNeedRestore( [ [ b, a ], [ c ] ], [ [ a, b ], [ c ] ] ), true, 'A reordered row should be restored.' );
	assert.equal( copiedRowsNeedRestore( [ [ c ], [ a, b ] ], [ [ c ], [ a, b ] ] ), false, 'A matching layout should be left alone.' );
} );

test( 'rows are left alone when the copied fields are not the expected ones', () => {
	const [ a, b, c, d ] = [ { id: 'a' }, { id: 'b' }, { id: 'c' }, { id: 'd' } ];

	assert.equal( copiedRowsNeedRestore( [], [ [ a ] ] ), false, 'No current rows.' );
	assert.equal( copiedRowsNeedRestore( [ [ a, b ], [ c, d ] ], [ [ c ], [ a, b ] ] ), false, 'An extra field.' );
	assert.equal( copiedRowsNeedRestore( [ [ a ], [ d ] ], [ [ a ], [ c ] ] ), false, 'A different field.' );
	assert.equal( copiedRowsNeedRestore( [ [ a ] ], [ [ a ], [ c ] ] ), false, 'A missing field.' );
} );
