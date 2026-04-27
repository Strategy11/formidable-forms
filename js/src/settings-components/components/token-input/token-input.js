/**
 * Token Input Component
 *
 * Transforms space-separated values in a text input into selectable tokens
 */

/**
 * Internal dependencies
 */
import { HOOKS } from 'core/constants';
import { CLASS_NAMES } from './constants';
import { createTokenContainerElement } from './token-elements';
import { synchronizeTokensDisplay } from './token-actions';
import { adjustAllProxyInputStyles } from './proxy-input-style';
import { addEventListeners } from './event-handlers';

/**
 * Initialize all token input fields on the page
 *
 * @return {void}
 */
function initTokenInputFields() {
	findAndInitializeTokenFields();

	/**
	 * Initialize for newly added fields
	 *
	 * @param {Event}       event          The frm_added_field event.
	 * @param {HTMLElement} event.frmField The added field object being destructured from the event.
	 */
	document.addEventListener( 'frm_added_field', ( { frmField } ) =>
		findAndInitializeTokenFields( frmField.dataset.fid )
	);

	/**
	 * Initialize for newly added fields via AJAX
	 *
	 * @param {Event}       event           The frm_ajax_loaded_field event.
	 * @param {HTMLElement} event.frmFields The added field objects being destructured from the event.
	 */
	document.addEventListener( 'frm_ajax_loaded_field', ( { frmFields } ) =>
		frmFields.forEach( field => findAndInitializeTokenFields( field.id ) )
	);

	// Adjust styling for all token inputs when field settings are shown
	wp.hooks.addAction( HOOKS.SHOW_FIELD_SETTINGS, 'formidable-token-input', adjustAllProxyInputStyles );
}

/**
 * Find all token input fields and initialize them
 *
 * @private
 * @param {string|number} fieldId The ID of the field to initialize
 * @return {void}
 */
function findAndInitializeTokenFields( fieldId ) {
	const container = fieldId ? document.getElementById( `frm-single-settings-${ fieldId }` ) : document.body;
	const tokenInputFields = container.querySelectorAll( `.${ CLASS_NAMES.TOKEN_INPUT_FIELD }` );
	if ( ! tokenInputFields.length ) {
		return;
	}

	// Track processed fields to prevent duplicate initialization
	const processedFields = new Set();
	tokenInputFields.forEach( field => {
		if ( ! processedFields.has( field.id ) ) {
			setupTokenInput( field );
			processedFields.add( field.id );
		}
	} );
}

/**
 * Set up a token input field with token container
 *
 * @private
 *
 * @param {HTMLElement} field Input field for tokenization
 */
function setupTokenInput( field ) {
	const container = createTokenContainerElement( field );
	if ( ! container ) {
		return;
	}

	const proxyInput = container.querySelector( `.${ CLASS_NAMES.TOKEN_PROXY_INPUT }` );
	const tokensWrapper = container.querySelector( `.${ CLASS_NAMES.TOKENS_WRAPPER }` );

	synchronizeTokensDisplay( field.value, proxyInput, tokensWrapper );
	addEventListeners( field, proxyInput, tokensWrapper );
}

export { initTokenInputFields };
