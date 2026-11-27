import assert from 'node:assert/strict';
import { test } from 'node:test';
import { clearError, connectRenderedError, resetAfterError } from '../../js/src/altcha-errors.js';
import { FakeElement } from './altchaFakeDom.mjs';

/**
 * Build a captcha field with a rendered widget, optionally with a server error message.
 *
 * @param {string}  state     The widget state.
 * @param {boolean} withError Whether the field shows a server error.
 * @return {Object} The field parts.
 */
function captchaField( state = 'verified', withError = true ) {
	const container = new FakeElement( 'div', { id: 'frm_field_7_container', classes: [ 'frm_form_field', 'frm_blank_field' ] } );
	const widget = new FakeElement( 'altcha-widget', { classes: [ 'frm-altcha-widget' ] } );
	const checkbox = new FakeElement( 'input', { type: 'checkbox' } );
	const calls = { reset: 0, configure: [] };

	widget.state = state;
	widget.getState = () => widget.state;
	widget.reset = () => {
		calls.reset++;
		widget.state = 'unverified';
	};
	widget.configure = config => calls.configure.push( config );
	widget.append( checkbox );
	container.append( widget );

	let error = null;

	if ( withError ) {
		error = new FakeElement( 'div', { id: 'frm_error_field_7', classes: [ 'frm_error' ] } );
		container.append( error );
	}

	return { container, widget, checkbox, error, calls };
}

test( 'a server rejection resets a verified widget to unverified and stops it verifying by itself', () => {
	const { widget, error, calls } = captchaField( 'verified' );

	resetAfterError( widget, error );

	assert.equal( calls.reset, 1 );
	assert.equal( widget.getState(), 'unverified' );
	assert.equal( calls.configure.at( -1 ).auto, 'off', 'Focusing the invalid checkbox must not start a verification that shows Verified next to the error.' );
} );

test( 'the error is cleared once the visitor verifies again, so a verified box is not shown as invalid', () => {
	const { container, widget, checkbox, error } = captchaField( 'verified' );

	resetAfterError( widget, error );
	assert.equal( checkbox.getAttribute( 'aria-invalid' ), 'true' );

	widget.state = 'verifying';
	widget.dispatch( 'statechange' );
	assert.deepEqual( container.querySelectorAll( '.frm_error' ), [ error ], 'The error stays while verifying.' );

	widget.state = 'verified';
	widget.dispatch( 'statechange' );
	assert.equal( checkbox.getAttribute( 'aria-invalid' ), null );
	// The error was removed since the last query, so the result cannot be reused.
	assert.deepEqual( container.querySelectorAll( '.frm_error' ), [] ); // eslint-disable-line formidable/no-repeated-selector
	assert.equal( container.classList.contains( 'frm_blank_field' ), false );
} );

test( 'clearing removes only the error ID from the checkbox description', () => {
	const { widget, checkbox } = captchaField( 'verified' );

	checkbox.setAttribute( 'aria-describedby', 'frm_error_field_7 frm_desc_field_7' );
	clearError( widget );

	assert.equal( checkbox.getAttribute( 'aria-describedby' ), 'frm_desc_field_7' );
} );

test( 'a floating widget with an error is shown inline next to its error, not hidden', () => {
	const { widget, error, checkbox, calls } = captchaField( 'unverified' );

	resetAfterError( widget, error );

	assert.equal( calls.configure.at( -1 ).display, 'standard' );
	assert.equal( checkbox.getAttribute( 'aria-describedby' ), 'frm_error_field_7' );
} );

test( 'an error printed with the page is connected to the checkbox once the widget renders', () => {
	const { widget, checkbox, calls } = captchaField( 'unverified' );

	connectRenderedError( widget );

	assert.equal( checkbox.getAttribute( 'aria-invalid' ), 'true' );
	assert.equal( checkbox.getAttribute( 'aria-describedby' ), 'frm_error_field_7' );
	assert.equal( calls.configure.at( -1 ).auto, 'off', 'A widget set to verify on load must not show Verified next to the error.' );
} );

test( 'a widget without an error printed with the page is left alone', () => {
	const { widget, checkbox, calls } = captchaField( 'unverified', false );

	connectRenderedError( widget );

	assert.equal( checkbox.getAttribute( 'aria-invalid' ), null );
	assert.equal( calls.configure.length, 0 );
} );
