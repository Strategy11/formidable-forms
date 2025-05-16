/**
 * Event handlers
 *
 * Functions for handling token input events
 */

import { CLASS_NAMES, KEYS } from './constants';
import { addTokenFromInput, createTokensFromValue } from './token-actions';
import { adjustTokenInputPadding } from './token-style';

/**
 * Handle keydown events on the display input field
 *
 * @param {Event}       event         Keydown event
 * @param {HTMLElement} field         The original hidden input field
 * @param {HTMLElement} displayInput  The display input field for interaction
 * @param {HTMLElement} tokensWrapper The wrapper for token display
 * @return {void}
 */
export function handleTokenInputKeydown( event, field, displayInput, tokensWrapper ) {
	if ( ! [ KEYS.SPACE, KEYS.COMMA, KEYS.ENTER, KEYS.TAB ].includes( event.key ) ) {
		return;
	}

	event.preventDefault();
	addTokenFromInput( field, displayInput, tokensWrapper );
}

/**
 * Handle token removal when clicking the remove button
 *
 * @param {Event}       event Click event
 * @param {HTMLElement} field The original hidden input field
 * @return {void}
 */
export function handleTokenRemoval( event, field ) {
	const removeButton = event.target.closest( `.${ CLASS_NAMES.TOKEN_REMOVE }` );
	if ( ! removeButton ) {
		return;
	}

	const token = removeButton.closest( `.${ CLASS_NAMES.TOKEN }` );
	if ( ! token ) {
		return;
	}

	const tokensWrapper = token.parentElement;
	const value = token.querySelector( `.${ CLASS_NAMES.TOKEN_VALUE }` ).textContent;

	field.value = field.value
		.split( /\s+/ )
		.filter( tokenValue => tokenValue && tokenValue !== value )
		.join( ' ' );

	// Must trigger jQuery change event to detect changes and update the builder preview
	jQuery( field ).trigger( 'change' );

	token.remove();
	adjustTokenInputPadding( tokensWrapper );

	// Focus the input field after token removal
	const displayInput = tokensWrapper.closest( `.${ CLASS_NAMES.CONTAINER }` )?.querySelector( `.${ CLASS_NAMES.TOKEN_DISPLAY_INPUT }` );
	displayInput?.focus();
}

/**
 * Add event listeners to token input components
 *
 * @param {HTMLElement} field         The original hidden input field
 * @param {HTMLElement} displayInput  The display input field for interaction
 * @param {HTMLElement} tokensWrapper The wrapper for token display
 * @return {void}
 */
export function addEventListeners( field, displayInput, tokensWrapper ) {
	displayInput.addEventListener( 'keydown', event => handleTokenInputKeydown( event, field, displayInput, tokensWrapper ) );
	tokensWrapper.addEventListener( 'click', event => handleTokenRemoval( event, field ) );
	displayInput.addEventListener( 'blur', () => addTokenFromInput( field, displayInput, tokensWrapper ) );

	// Use jQuery change event to catch programmatic updates, as "Add Layout Classes" triggers value changes via jQuery
	jQuery( field ).on( 'change', () => createTokensFromValue( field.value, tokensWrapper ) );
}
