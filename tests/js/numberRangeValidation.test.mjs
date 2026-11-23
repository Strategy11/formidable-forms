import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { test } from 'node:test';
import { runInNewContext } from 'node:vm';

const source = readFileSync( new URL( '../../js/formidable.js', import.meta.url ), 'utf8' );

/**
 * Run JavaScript validation for a form holding one number field.
 *
 * @param {string} value    The typed value.
 * @param {Object} validity The field's ValidityState flags.
 * @param {Object} attrs    The field's data attributes.
 * @return {string|undefined} The error for the field.
 */
function getNumberErrors( value, validity, attrs ) {
	const field = {
		type: 'number',
		name: 'item_meta[12]',
		value,
		validity: { valid: ! Object.values( validity ).includes( true ), ...validity },
		pattern: null,
		classList: { contains: () => false },
		getAttribute: name => attrs[ name ] ?? null
	};
	const form = {
		classList: { contains: () => false },
		querySelector: () => null,
		querySelectorAll: selector => selector === 'input,select,textarea' ? [ field ] : []
	};
	function jQuery() {
		return { ready() {}, hasClass: () => true, querySelectorAll: () => [] };
	}
	const context = {
		window: {},
		document: { body: { classList: { contains: () => false } }, dispatchEvent() {} },
		jQuery,
		CustomEvent() {}
	};

	runInNewContext( source, context );
	context.frmFrontForm = context.window.frmFrontForm;

	return context.window.frmFrontForm.getAjaxFormErrors( form )[ 12 ];
}

const attrs = {
	'data-invmsg': 'Enter a number',
	'data-minmsg': 'Please select a higher number',
	'data-maxmsg': 'Please select a lower number'
};

test( 'A number below the minimum shows the minimum message', () => {
	assert.equal( getNumberErrors( '1', { rangeUnderflow: true }, attrs ), 'Please select a higher number' );
} );

test( 'A number above the maximum shows the maximum message', () => {
	assert.equal( getNumberErrors( '5000', { rangeOverflow: true }, attrs ), 'Please select a lower number' );
} );

test( 'A number within range has no error', () => {
	assert.equal( getNumberErrors( '10', {}, attrs ), undefined );
} );

test( 'A range failure without range messages falls back to the invalid message', () => {
	assert.equal( getNumberErrors( '1', { rangeUnderflow: true }, { 'data-invmsg': 'Enter a number' } ), 'Enter a number' );
} );
