import assert from 'node:assert/strict';
import { test } from 'node:test';
import { allowContainerDrop } from '../../js/src/admin/fieldPlacement.js';

function field( type, restricted = false ) {
	return {
		id: type,
		dataset: { ftype: type },
		classList: { contains: name => restricted && name === 'frm-no-repeater' },
		matches: () => true
	};
}

function target( repeater = false, section = true, panels = false ) {
	return {
		closest: selector => ( selector === '.repeat_section' ? repeater : section ) ? {} : null,
		classList: { contains: name => panels && name === 'frm-layout-panels' }
	};
}

function row( ...fields ) {
	return { matches: () => false, querySelectorAll: () => fields };
}

globalThis.wp = { hooks: { applyFilters: ( hook, fallback, item ) => item.dataset.ftype === 'legacy_addon' || fallback } };

test( 'repeater restrictions apply to individual fields and whole rows', () => {
	for ( const type of [ 'captcha', 'submit', 'gdpr', 'summary' ] ) {
		const item = field( type, true );
		assert.equal( allowContainerDrop( item, target( true ) ), false, type );
		assert.equal( allowContainerDrop( row( field( 'email' ), item ), target( true ) ), false, type );
	}
} );

test( 'page breaks, submit buttons, embeds, and nested sections cannot enter sections', () => {
	for ( const type of [ 'break', 'submit', 'form', 'divider' ] ) {
		assert.equal( allowContainerDrop( field( type ), target() ), false, type );
		assert.equal( allowContainerDrop( row( field( type ) ), target() ), false, type );
	}
} );

test( 'add-on hooks inspect each field inside a row', () => {
	assert.equal( allowContainerDrop( row( field( 'email' ), field( 'legacy_addon' ) ), target( true ) ), false );
} );

test( 'normal rows and top level placements remain available', () => {
	assert.equal( allowContainerDrop( row( field( 'email' ), field( 'text' ) ), target( true ) ), true );
	assert.equal( allowContainerDrop( field( 'captcha', true ), target( false, false ) ), true );
	assert.equal( allowContainerDrop( field( 'captcha', true ), target() ), true );
	assert.equal( allowContainerDrop( field( 'divider', true ), target( false, true, true ) ), true );
} );
