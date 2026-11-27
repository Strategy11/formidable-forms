/**
 * Finish ALTCHA verification before a Formidable form is sent.
 */
import { skipsVerification } from './altcha-skip-verification.js';
import { getCheckbox, resetAfterError, showError } from './altcha-errors.js';

export const WIDGET_SELECTOR = 'altcha-widget.frm-altcha-widget';

/**
 * Forms waiting for verification to finish, so repeated submit clicks only submit once.
 *
 * @type {WeakSet<HTMLFormElement>}
 */
const waitingForms = new WeakSet();

/**
 * Whether a widget has finished its proof of work.
 *
 * @param {HTMLElement} widget The altcha-widget element.
 * @return {boolean} True when verified, or when the widget has not loaded its methods yet.
 */
const isVerified = widget => typeof widget.getState !== 'function' || widget.getState() === 'verified';

/**
 * The "Start verifying" setting the widget was printed with.
 *
 * The widget's own setting can change later, for example floating widgets verify on submit, so
 * the captcha element's data attribute is read instead.
 *
 * @param {HTMLElement} widget The altcha-widget element.
 * @return {string} onfocus, onload, onsubmit or off.
 */
const getStartMode = widget => widget.closest( '.frm-altcha' )?.dataset.auto || '';

/**
 * Whether the visitor still has to click the checkbox before the form can be submitted.
 *
 * In the "Only when the visitor clicks the checkbox" mode, submitting does not verify for the
 * visitor. A checkbox that was clicked and is still verifying is waited for.
 *
 * @param {HTMLElement} widget The altcha-widget element.
 * @return {boolean} True when the submit must be refused.
 */
const needsClick = widget => getStartMode( widget ) === 'off' && ! isVerified( widget ) && widget.getState() !== 'verifying';

/**
 * The message the widget was printed with for an unverified submit.
 *
 * @param {HTMLElement} widget The altcha-widget element.
 * @return {string} The message.
 */
const getRequiredMessage = widget => {
	try {
		return JSON.parse( widget.closest( '.frm-altcha' )?.dataset.configuration || '{}' ).validationMessage || '';
	} catch {
		return '';
	}
};

/**
 * Refuse a submit until the visitor clicks the checkbox, with an error in the field that is
 * connected to the checkbox for screen readers.
 *
 * @param {HTMLElement[]} widgets Widgets the visitor has not verified.
 * @return {void}
 */
const askForClick = widgets => {
	widgets.forEach( widget => {
		resetAfterError( widget, showError( widget, getRequiredMessage( widget ) ) );
	} );

	getCheckbox( widgets[ 0 ] )?.focus?.();
};

/**
 * Resolve once a widget has finished verifying, starting verification if nothing is running.
 *
 * A second verify() call aborts one already in progress and the first call resolves to null,
 * for example when focusing the submit button starts the widget's onfocus verification. So the
 * result is read from the widget's state, not from the verify() return value.
 *
 * @param {HTMLElement} widget The altcha-widget element.
 * @return {Promise<boolean>} Whether the widget ended up verified.
 */
const finishVerification = widget => new Promise( resolve => {
	const settle = () => {
		const state = widget.getState();

		if ( state === 'verifying' ) {
			return;
		}

		widget.removeEventListener( 'statechange', settle );
		resolve( state === 'verified' );
	};

	widget.addEventListener( 'statechange', settle );

	if ( widget.getState() !== 'verifying' ) {
		widget.verify().then( settle );
	}
} );

/**
 * Verify the widgets, then submit the form again once, if every widget verified.
 *
 * @param {HTMLFormElement}       form      The form.
 * @param {HTMLElement[]}         widgets   Widgets that still need to verify.
 * @param {HTMLElement|undefined} submitter The button that submitted the form, if known.
 * @return {void}
 */
const verifyThenSubmit = ( form, widgets, submitter ) => {
	if ( waitingForms.has( form ) ) {
		return;
	}

	waitingForms.add( form );
	// Formidable's loading state, so the visitor sees the submit is in progress while a slow proof finishes.
	form.classList.add( 'frm_loading_form' );

	Promise.all( widgets.map( finishVerification ) ).then( results => {
		waitingForms.delete( form );
		// Removed before submitting again, so Formidable starts its own loading state and events.
		form.classList.remove( 'frm_loading_form' );

		if ( results.every( Boolean ) ) {
			form.requestSubmit( submitter );
		}
	} );
};

/**
 * Finish verification before the form is sent.
 *
 * Runs in the capture phase on the document, so it stops the submit before Formidable's own
 * handler sees it. Previous page and Save Draft submits go through without verifying. Once every
 * widget is verified the form is submitted again, and that submit goes through to Formidable,
 * including its AJAX submit. In the "Only when the visitor clicks the checkbox" mode the submit
 * is refused instead, until the visitor clicks the checkbox.
 *
 * @param {SubmitEvent} event Submit event.
 * @return {void}
 */
export const verifyBeforeSubmit = event => {
	const form = event.target;

	if ( ! ( form instanceof HTMLFormElement ) || skipsVerification( form, event.submitter ) ) {
		return;
	}

	const pending = Array.from( form.querySelectorAll( WIDGET_SELECTOR ) ).filter( widget => ! isVerified( widget ) );

	if ( ! pending.length ) {
		return;
	}

	event.preventDefault();
	event.stopImmediatePropagation();

	const unclicked = pending.filter( needsClick );

	if ( unclicked.length ) {
		askForClick( unclicked );
		return;
	}

	verifyThenSubmit( form, pending, event.submitter && form.contains( event.submitter ) ? event.submitter : undefined );
};

/**
 * Handle the widget's required checkbox blocking a submit in forms without novalidate.
 *
 * The browser fires invalid instead of submit, so verifyBeforeSubmit never runs. Verify in
 * the background instead of showing the browser's "check this box" message, then submit. In the
 * "Only when the visitor clicks the checkbox" mode, show the field error instead.
 *
 * @param {Event} event Invalid event.
 * @return {void}
 */
export const verifyInvalidWidget = event => {
	const widget = event.target instanceof Element ? event.target.closest( WIDGET_SELECTOR ) : null;
	const form = widget?.closest( 'form' );

	if ( ! widget || ! form || isVerified( widget ) ) {
		return;
	}

	event.preventDefault();

	if ( needsClick( widget ) ) {
		askForClick( [ widget ] );
		return;
	}

	verifyThenSubmit( form, [ widget ] );
};
