/**
 * Loads the ALTCHA widget and connects it to Formidable forms.
 *
 * Lite's Captcha field prints an empty .frm-altcha element with the widget settings in data
 * attributes, as it does for the other captcha types. This script puts the widget inside it,
 * including for form pages that load later over AJAX.
 */
import 'altcha';
import { connectRenderedError, resetAfterError } from './altcha-errors';
import { WIDGET_SELECTOR, verifyBeforeSubmit, verifyInvalidWidget } from './altcha-submit';

const CAPTCHA_SELECTOR = '.frm-altcha';

/**
 * Widget attributes read from the captcha element's data attributes.
 *
 * @type {string[]}
 */
const WIDGET_ATTRIBUTES = [ 'name', 'challenge', 'auto', 'language', 'configuration' ];

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
		widget.addEventListener( 'load', () => connectRenderedError( widget ), { once: true } );

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
 * Add widgets to new form pages, and reset a widget when the server rejects its solution,
 * so the visitor gets a fresh challenge and the widget no longer says Verified.
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

			if ( widget ) {
				resetAfterError( widget, node );
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
