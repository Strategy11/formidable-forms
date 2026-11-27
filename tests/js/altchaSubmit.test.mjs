import assert from 'node:assert/strict';
import { test } from 'node:test';
import { FakeElement } from './altchaFakeDom.mjs';

globalThis.Element = FakeElement;
globalThis.HTMLFormElement = class extends FakeElement {};

const { verifyBeforeSubmit, verifyInvalidWidget } = await import( '../../js/src/altcha-submit.js' );

const MESSAGE = 'Please verify that you are not a robot.';

/**
 * Build a form with one captcha field and a rendered widget.
 *
 * @param {string} auto  The "Start verifying" setting.
 * @param {string} state The widget state.
 * @return {Object} The form parts.
 */
function captchaForm( auto, state = 'unverified' ) {
	const form = new globalThis.HTMLFormElement( 'form' );
	const container = new FakeElement( 'div', { id: 'frm_field_7_container', classes: [ 'frm_form_field' ] } );
	const captcha = new FakeElement( 'div', { classes: [ 'frm-altcha' ] } );
	const widget = new FakeElement( 'altcha-widget', { classes: [ 'frm-altcha-widget' ] } );
	const checkbox = new FakeElement( 'input', { type: 'checkbox' } );
	const calls = { verify: 0, submit: [], configure: [] };

	captcha.dataset.auto = auto;
	captcha.dataset.configuration = JSON.stringify( { display: 'standard', validationMessage: MESSAGE } );
	form.contains = el => form.descendants().includes( el );
	form.requestSubmit = submitter => calls.submit.push( submitter );

	widget.state = state;
	widget.getState = () => widget.state;
	widget.reset = () => {
		widget.state = 'unverified';
	};
	widget.configure = config => calls.configure.push( config );
	widget.verify = () => {
		calls.verify++;
		widget.state = 'verifying';
		return new Promise( () => {} );
	};

	widget.append( checkbox );
	captcha.append( widget );
	container.append( captcha );
	form.append( container );

	return { form, container, widget, checkbox, calls };
}

/**
 * A submit event stand-in.
 *
 * @param {HTMLFormElement} form The submitted form.
 * @return {Object} The event, with flags for what was called.
 */
function submitEvent( form ) {
	const event = { target: form, submitter: null, prevented: false, stopped: false };
	event.preventDefault = () => event.prevented = true;
	event.stopImmediatePropagation = () => event.stopped = true;
	return event;
}

test( 'in click-only mode, submitting before the checkbox is verified is refused without verifying', () => {
	const { form, container, widget, checkbox, calls } = captchaForm( 'off' );
	const event = submitEvent( form );

	verifyBeforeSubmit( event );

	assert.ok( event.prevented && event.stopped, 'The submit must not reach Formidable.' );
	assert.equal( calls.verify, 0, 'Submit must not verify for the visitor.' );
	assert.equal( calls.submit.length, 0 );
	assert.equal( widget.getState(), 'unverified' );

	const error = container.querySelector( '.frm_error' );
	assert.equal( error?.textContent, MESSAGE );
	assert.equal( error.getAttribute( 'role' ), 'alert' );
	assert.equal( checkbox.getAttribute( 'aria-describedby' ), error.id );
	assert.equal( checkbox.getAttribute( 'aria-invalid' ), 'true' );
} );

test( 'in click-only mode, a checkbox the visitor already clicked is waited for, then the form is submitted', async () => {
	const { form, widget, calls } = captchaForm( 'off', 'verifying' );
	const event = submitEvent( form );

	verifyBeforeSubmit( event );
	assert.ok( event.prevented );
	assert.equal( calls.verify, 0 );

	widget.state = 'verified';
	widget.dispatch( 'statechange' );
	await new Promise( resolve => setTimeout( resolve, 0 ) );

	assert.equal( calls.submit.length, 1 );
} );

test( 'in click-only mode, a verified widget lets the submit through', () => {
	const { form, calls } = captchaForm( 'off', 'verified' );
	const event = submitEvent( form );

	verifyBeforeSubmit( event );

	assert.equal( event.prevented, false );
	assert.equal( calls.verify, 0 );
} );

test( 'other modes still finish verification on submit, then submit once', async () => {
	const { form, widget, calls } = captchaForm( 'onfocus' );
	const event = submitEvent( form );

	verifyBeforeSubmit( event );
	assert.ok( event.prevented );
	assert.equal( calls.verify, 1 );

	widget.state = 'verified';
	widget.dispatch( 'statechange' );
	await new Promise( resolve => setTimeout( resolve, 0 ) );

	assert.equal( calls.submit.length, 1 );
} );

test( 'in click-only mode, the browser\'s required checkbox check shows the error instead of verifying', () => {
	const { form, container, checkbox, calls } = captchaForm( 'off' );
	const event = { target: checkbox, prevented: false };
	event.preventDefault = () => event.prevented = true;

	verifyInvalidWidget( event );

	assert.ok( event.prevented );
	assert.equal( calls.verify, 0 );
	assert.equal( calls.submit.length, 0 );
	assert.equal( container.querySelector( '.frm_error' )?.textContent, MESSAGE );
	assert.ok( form );
} );

test( 'the form shows Formidable\'s loading state while the widget finishes verifying', async () => {
	const { form, widget, calls } = captchaForm( 'onsubmit' );
	let loadingWhenSubmitted = null;

	form.requestSubmit = () => {
		loadingWhenSubmitted = form.classList.contains( 'frm_loading_form' );
		calls.submit.push( true );
	};

	verifyBeforeSubmit( submitEvent( form ) );
	assert.equal( form.classList.contains( 'frm_loading_form' ), true, 'A slow High difficulty proof should show the submit button as loading.' );

	widget.state = 'verified';
	widget.dispatch( 'statechange' );
	await new Promise( resolve => setTimeout( resolve, 0 ) );

	assert.equal( calls.submit.length, 1 );
	assert.equal( loadingWhenSubmitted, false, 'Formidable adds its own loading state when the form is submitted again.' );
} );

test( 'the loading state is removed when verification fails', async () => {
	const { form, widget, calls } = captchaForm( 'onsubmit' );

	verifyBeforeSubmit( submitEvent( form ) );
	widget.state = 'error';
	widget.dispatch( 'statechange' );
	await new Promise( resolve => setTimeout( resolve, 0 ) );

	assert.equal( calls.submit.length, 0 );
	assert.equal( form.classList.contains( 'frm_loading_form' ), false );
} );
