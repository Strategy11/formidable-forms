/**
 * Event handlers
 *
 * Functions for handling token input events
 */

import { CLASS_NAMES, KEYS } from './constants';
import { addToken, removeLastToken, removeToken, synchronizeTokensDisplay } from './token-utils';
import { adjustTokenInputStyle } from './token-style';

/**
 * Add event listeners to token input components
 *
 * @param {HTMLElement} field         The original hidden input field
 * @param {HTMLElement} displayInput  The display input field for interaction
 * @param {HTMLElement} tokensWrapper The wrapper for token display
 * @return {void}
 */
export function addEventListeners( field, displayInput, tokensWrapper ) {
	// Use jQuery change event to catch programmatic updates, as "Add Layout Classes" triggers value changes via jQuery
	jQuery( field ).on( 'change', () => synchronizeTokensDisplay( field.value, tokensWrapper ) );

	// Handle display input keydown and blur events
	displayInput.addEventListener( 'keydown', event => onDisplayInputKeydown( event, field, displayInput ) );
	displayInput.addEventListener( 'blur', () => addToken( field, displayInput, displayInput.value.trim() ) );

	// Handle token removal
	tokensWrapper.addEventListener( 'click', event => handleTokenRemoval( event, field ) );
}

/**
 * Handle keydown events on the display input field
 *
 * @private
 *
 * @param {Event}       event        Keydown event
 * @param {HTMLElement} field        The original hidden input field
 * @param {HTMLElement} displayInput The display input field for interaction
 * @return {void}
 */
function onDisplayInputKeydown( event, field, displayInput ) {
	const key = event.key;
	const value = displayInput.value.trim();

	// Handle backspace key separately
	if ( key === KEYS.BACKSPACE && ! value ) {
		event.preventDefault();
		removeLastToken( field, displayInput );
		adjustTokenInputStyle( tokensWrapper );
		return;
	}

	// Handle token creation keys
	if ( [ KEYS.SPACE, KEYS.COMMA, KEYS.ENTER, KEYS.TAB ].includes( key ) ) {
		event.preventDefault();
		addToken( field, displayInput, value );
		adjustTokenInputStyle( tokensWrapper );
	}
}

/**
 * Handle token removal when clicking the remove button
 *
 * @private
 *
 * @param {Event}       event Click event
 * @param {HTMLElement} field The original hidden input field
 * @return {void}
 */
function handleTokenRemoval( event, field ) {
	const removeButton = event.target.closest( `.${ CLASS_NAMES.TOKEN_REMOVE }` );
	if ( ! removeButton ) {
		return;
	}

	const token = removeButton.closest( `.${ CLASS_NAMES.TOKEN }` );
	if ( ! token ) {
		return;
	}

	const tokensWrapper = token.parentElement;

	removeToken( field, token, tokensWrapper );
	adjustTokenInputStyle( tokensWrapper );
}
