/**
 * Loads the ALTCHA widget and connects it to Formidable forms.
 *
 * Lite's Captcha field prints an empty .frm-altcha element with the widget settings in data
 * attributes, as it does for the other captcha types. This script puts the widget inside it,
 * including for form pages that load later over AJAX.
 */
import 'altcha';

const WIDGET_SELECTOR = 'altcha-widget.frm-altcha-widget';
const CAPTCHA_SELECTOR = '.frm-altcha';

/**
 * Widget attributes read from the captcha element's data attributes.
 *
 * @type {string[]}
 */
const WIDGET_ATTRIBUTES = [ 'name', 'challenge', 'auto', 'language', 'configuration' ];

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

	Promise.all( widgets.map( finishVerification ) ).then( results => {
		waitingForms.delete( form );

		if ( results.every( Boolean ) ) {
			form.requestSubmit( submitter );
		}
	} );
};

/**
 * Finish verification before the form is sent, whatever the widget's automatic mode.
 *
 * Runs in the capture phase on the document, so it stops the submit before Formidable's own
 * handler sees it. Once every widget is verified the form is submitted again, and that
 * submit goes through to Formidable, including its AJAX submit.
 *
 * @param {SubmitEvent} event Submit event.
 * @return {void}
 */
const verifyBeforeSubmit = event => {
	const form = event.target;

	if ( ! ( form instanceof HTMLFormElement ) ) {
		return;
	}

	const pending = Array.from( form.querySelectorAll( WIDGET_SELECTOR ) ).filter( widget => ! isVerified( widget ) );

	if ( ! pending.length ) {
		return;
	}

	event.preventDefault();
	event.stopImmediatePropagation();

	verifyThenSubmit( form, pending, event.submitter && form.contains( event.submitter ) ? event.submitter : undefined );
};

/**
 * Handle the widget's required checkbox blocking a submit in forms without novalidate.
 *
 * The browser fires invalid instead of submit, so verifyBeforeSubmit never runs. Verify in
 * the background instead of showing the browser's "check this box" message, then submit.
 *
 * @param {Event} event Invalid event.
 * @return {void}
 */
const verifyInvalidWidget = event => {
	const widget = event.target instanceof Element ? event.target.closest( WIDGET_SELECTOR ) : null;
	const form = widget?.closest( 'form' );

	if ( ! widget || ! form || isVerified( widget ) ) {
		return;
	}

	event.preventDefault();
	verifyThenSubmit( form, [ widget ] );
};

/**
 * Show verification feedback for layouts that start hidden, including automatic verification.
 *
 * @since x.x
 *
 * @param {CustomEvent} event Widget state change.
 * @return {void}
 */
const syncWidgetDisplay = event => {
	const widget = event.currentTarget;

	if ( typeof widget.getConfiguration !== 'function' || widget.getConfiguration().display === 'standard' ) {
		return;
	}

	if ( [ 'verified', 'unverified' ].includes( event.detail.state ) ) {
		widget.hide();
		return;
	}

	widget.show();
};

/**
 * Put a widget inside each captcha element that does not have one yet.
 *
 * @param {Document|Element} root Element or document to search.
 * @return {void}
 */
const addWidgets = root => {
	const captchas = root instanceof Element && root.matches( CAPTCHA_SELECTOR ) ? [ root ] : Array.from( root.querySelectorAll( CAPTCHA_SELECTOR ) );

	captchas.forEach( captcha => {
		if ( captcha.querySelector( WIDGET_SELECTOR ) ) {
			return;
		}

		const widget = document.createElement( 'altcha-widget' );
		widget.className = 'frm-altcha-widget';
		widget.addEventListener( 'statechange', syncWidgetDisplay );

		WIDGET_ATTRIBUTES.forEach( attribute => {
			const value = captcha.dataset[ attribute ];

			if ( value ) {
				widget.setAttribute( attribute, value );
			}
		} );

		// Each widget goes into a different captcha element, so there is nothing to batch.
		captcha.append( widget ); // eslint-disable-line formidable/prefer-document-fragment
	} );
};

/**
 * Mark the widget's checkbox invalid after the server rejects it, as Formidable does for
 * other inputs, until the visitor verifies again.
 *
 * @param {HTMLElement} widget The altcha-widget element.
 * @return {void}
 */
const markInvalid = widget => {
	const checkbox = widget.querySelector( 'input[type="checkbox"]' );

	if ( ! checkbox ) {
		return;
	}

	checkbox.setAttribute( 'aria-invalid', 'true' );

	const clear = () => {
		if ( widget.getState() === 'verified' ) {
			checkbox.removeAttribute( 'aria-invalid' );
			widget.removeEventListener( 'statechange', clear );
		}
	};

	widget.addEventListener( 'statechange', clear );
};

/**
 * Add widgets to new form pages, and reset a widget when the server rejects its solution,
 * so the visitor gets a fresh challenge.
 *
 * Formidable adds a .frm_error element to the field container for each server-side error.
 *
 * @param {MutationRecord[]} records DOM changes.
 * @return {void}
 */
const watchChanges = records => {
	records.forEach( record => {
		record.addedNodes.forEach( node => {
			if ( ! ( node instanceof HTMLElement ) ) {
				return;
			}

			if ( ! node.classList.contains( 'frm_error' ) ) {
				addWidgets( node );
				return;
			}

			const widget = node.closest( '.frm_form_field' )?.querySelector( WIDGET_SELECTOR );

			if ( widget && typeof widget.reset === 'function' ) {
				widget.reset();
				markInvalid( widget );
			}
		} );
	} );
};

/**
 * Start once, even if a theme or plugin prints the script twice.
 *
 * @return {void}
 */
const init = () => {
	if ( document.documentElement.dataset.frmAltcha ) {
		return;
	}

	document.documentElement.dataset.frmAltcha = '1';
	addWidgets( document );
	document.addEventListener( 'submit', verifyBeforeSubmit, true );
	document.addEventListener( 'invalid', verifyInvalidWidget, true );
	new MutationObserver( watchChanges ).observe( document.documentElement, { childList: true, subtree: true } );
};

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', init );
} else {
	init();
}
