import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { test } from 'node:test';
import { runInNewContext } from 'node:vm';

const source = readFileSync( new URL( '../../js/formidable.js', import.meta.url ), 'utf8' );

/**
 * Submit a form with an invisible reCAPTCHA and record what it does.
 *
 * @param {string}      widgetClass The widget's rendering class.
 * @param {string|void} widgetId    The ID assigned by Formidable's explicit renderer.
 * @param {Function}    getResponse Returns the current response from reCAPTCHA.
 * @param {Object}      proForm     Optional Pro form methods.
 * @return {Array} Calls made to reCAPTCHA and the immediate submission handler.
 */
function submitForm( widgetClass, widgetId, getResponse, proForm ) {
	const calls = [];
	const widget = {
		classList: { contains: name => name === widgetClass },
		dataset: { rid: widgetId }
	};
	const form = {
		classList: { contains: () => false },
		querySelector: selector => selector.includes( `.${ widgetClass }[data-size="invisible"]` ) ? widget : null
	};
	function jQuery() {
		return { ready() {}, hasClass: () => true, querySelectorAll: () => [] };
	}
	const context = {
		window: {},
		document: { body: { classList: { contains: () => false } } },
		jQuery,
		grecaptcha: getResponse && {
			getResponse: id => {
				calls.push( [ 'getResponse', id ] );
				return getResponse( id );
			},
			reset: id => calls.push( [ 'reset', id ] ),
			execute: id => calls.push( [ 'execute', id ] )
		},
		frmProForm: proForm
	};

	runInNewContext( source, context );
	const { frmFrontForm } = context.window;
	context.frmFrontForm = frmFrontForm;
	frmFrontForm.submitFormNow = () => calls.push( [ 'submit' ] );
	frmFrontForm.submitFormManual( { preventDefault() {} }, form );

	return calls;
}

test( 'Invisible reCAPTCHA executes the auto-rendered widget without an ID', () => {
	const calls = submitForm( 'g-recaptcha', undefined, () => '' );

	assert.deepEqual( calls, [ [ 'getResponse', undefined ], [ 'reset', undefined ], [ 'execute', undefined ] ] );
} );

test( 'Invisible reCAPTCHA executes the explicitly rendered widget by ID with Pro', () => {
	const calls = submitForm( 'frm-g-recaptcha', '0', () => '', { goingToPreviousPage: () => false } );

	assert.deepEqual( calls, [ [ 'getResponse', '0' ], [ 'reset', '0' ], [ 'execute', '0' ] ] );
} );

test( 'An explicit invisible widget without an ID cannot run the default widget', () => {
	const calls = submitForm( 'frm-g-recaptcha', undefined, () => '' );

	assert.deepEqual( calls, [ [ 'submit' ] ] );
} );

test( 'An unavailable reCAPTCHA API allows the submission to reach server validation', () => {
	assert.deepEqual( submitForm( 'g-recaptcha' ), [ [ 'submit' ] ] );
} );

test( 'An auto-rendered widget that is not ready allows the submission to reach server validation', () => {
	const calls = submitForm( 'g-recaptcha', undefined, () => {
		throw new Error( 'No reCAPTCHA clients exist.' );
	} );

	assert.deepEqual( calls, [ [ 'getResponse', undefined ], [ 'submit' ] ] );
} );
