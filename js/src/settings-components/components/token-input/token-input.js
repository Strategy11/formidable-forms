/**
 * Token Input Component
 *
 * Transforms space-separated values in a text input into selectable tokens
 */

/**
 * Internal dependencies
 */
import { HOOKS, CLASS_NAMES } from './constants';
import { createTokenContainer } from './token-elements';
import { createTokensFromValue } from './token-actions';
import { adjustAllTokenInputStyles } from './token-style';
import { addEventListeners } from './event-handlers';

/**
 * Initialize all token input fields on the page
 *
 * @return {void}
 */
function initTokenInputFields() {
	findAndInitializeTokenFields();

	// Adjust styling for all token inputs when field settings are shown
	wp.hooks.addAction( HOOKS.SHOW_FIELD_SETTINGS, 'formidable-token-input', adjustAllTokenInputStyles );
}

/**
 * Find all token input fields and initialize them
 *
 * @private
 *
 * @return {void}
 */
function findAndInitializeTokenFields() {
	const tokenInputFields = document.querySelectorAll( `.${ CLASS_NAMES.TOKEN_INPUT_FIELD }` );

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
	});
}

/**
 * Set up a token input field with token container
 *
 * @private
 *
 * @param {HTMLElement} field Input field for tokenization
 */
function setupTokenInput( field ) {
	const container = createTokenContainer( field );

	if ( ! container ) {
		return;
	}

	const tokensWrapper = container.querySelector( `.${ CLASS_NAMES.TOKENS_WRAPPER }` );
	const displayInput = container.querySelector( `.${ CLASS_NAMES.TOKEN_DISPLAY_INPUT }` );

	createTokensFromValue( field.value, tokensWrapper );
	addEventListeners( field, displayInput, tokensWrapper );
}

export { initTokenInputFields };
