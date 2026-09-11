/**
 * Internal dependencies
 */
import { hideError, showError } from './utils';

const { doJsonPost } = frmDom.ajax;

const HIDDEN_CLASS = 'frm_hidden';
const CLASS_PREFIX = 'frm-plugin-feedback';
const LOADING_CLASS = 'frm_loading_button';

const pluginFeedback = document.getElementById( CLASS_PREFIX );
const form = document.getElementById( `${ CLASS_PREFIX }-form` );
const submitButton = form?.querySelector( 'button[type="submit"]' );
const npsStep = document.getElementById( `${ CLASS_PREFIX }-nps-step` );
const reasonsStep = document.getElementById( `${ CLASS_PREFIX }-reasons-step` );
const thankYouStep = document.getElementById( `${ CLASS_PREFIX }-thank-you-step` );

const submitAction = pluginFeedback?.dataset.submitAction;
const dismissAction = pluginFeedback?.dataset.dismissAction;

/**
 * Adds event listeners for submitting plugin feedback.
 *
 * @private
 * @return {void}
 */
function addSubmitFeedbackEventListeners() {
	if ( ! pluginFeedback || ! form ) {
		return;
	}

	form.addEventListener( 'submit', onSubmitFeedback );
	pluginFeedback.querySelector( '.dismiss' )?.addEventListener( 'click', onDismissFeedback );
}

/**
 * Handles form submission for plugin feedback.
 *
 * @private
 * @param {Event} event The form submit event.
 * @return {void}
 */
async function onSubmitFeedback( event ) {
	event.preventDefault();

	submitButton.classList.add( LOADING_CLASS );

	const step = pluginFeedback.dataset.step;
	const formData = new FormData();

	if ( 'nps' === step ) {
		const npsScore = form.querySelector( 'input[name="plugin-feedback-nps-score"]:checked' );
		formData.append( 'nps-score', npsScore?.value );
	} else {
		const reasons = form.querySelectorAll( 'input[name="plugin-feedback-reasons"]:checked' );
		formData.append( 'reasons', JSON.stringify( Array.from( reasons ).map( ( reason ) => reason.value ) ) );
		formData.append( 'details', form.querySelector( 'textarea[name="plugin-feedback-details"]' )?.value );
	}

	try {
		await doJsonPost( submitAction, formData );
	} catch ( error ) {
		showError( error.type );
		if ( error.message ) {
			console.error( 'Feedback submission error:', error.message );
		}
		return;
	} finally {
		submitButton.classList.remove( LOADING_CLASS );
	}

	hideError();
	updateFeedbackStep( step );
}

/**
 * Updates the feedback step and shows/hides appropriate step elements.
 *
 * @private
 * @param {string} step The current step ('nps' or 'reasons').
 * @return {void}
 */
function updateFeedbackStep( step ) {
	if ( 'nps' === step ) {
		pluginFeedback.dataset.step = 'reasons';
		npsStep.classList.add( HIDDEN_CLASS );
		reasonsStep.classList.remove( HIDDEN_CLASS );
	} else {
		pluginFeedback.dataset.step = 'thank-you';
		reasonsStep.classList.add( HIDDEN_CLASS );
		form.classList.add( HIDDEN_CLASS );
		thankYouStep.classList.remove( HIDDEN_CLASS );
	}
}

/**
 * Handles dismiss button click.
 *
 * @private
 * @param {Event} event The click event.
 * @return {void}
 */
async function onDismissFeedback( event ) {
	event.preventDefault();

	pluginFeedback.remove();

	if ( 'thank-you' === pluginFeedback.dataset.step ) {
		return;
	}

	const formData = new FormData();
	formData.append( 'dismissed', true );

	try {
		await doJsonPost( dismissAction, formData );
	} catch ( error ) {
		if ( error.message ) {
			console.error( 'Feedback submission error:', error.message );
		}
	}
}

export default addSubmitFeedbackEventListeners;
