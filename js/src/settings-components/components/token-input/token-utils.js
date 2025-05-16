/**
 * Token utilities
 *
 * Core functions for token operations and management
 */

/**
 * Internal dependencies
 */
import { createTokenElement } from './token-elements';

/**
 * Synchronize token display with the field value
 *
 * @param {string}      value         Space-separated values
 * @param {HTMLElement} tokensWrapper Wrapper element for tokens
 * @return {void}
 */
export function synchronizeTokensDisplay( value, tokensWrapper ) {
	if ( ! tokensWrapper ) {
		return;
	}

	// Get display input before clearing tokens to maintain focus after sync
	const container = tokensWrapper.closest( '.frm-token-container' );
	const displayInput = container?.querySelector( '.frm-token-display-input' );
	const hadFocus = displayInput?.matches( ':focus' );

	// Clear existing tokens display
	tokensWrapper.innerHTML = '';

	// Render each token in the wrapper
	parseTokens( value ).forEach( token => createTokenElement( token, tokensWrapper ) );

	// Restore focus if it was active before the operation
	if ( hadFocus && displayInput ) {
		displayInput.focus();
	}
}

/**
 * Add a new token to the field
 *
 * @param {HTMLElement} field        The original field
 * @param {HTMLElement} displayInput The display input
 * @param {string}      value        The token value to add
 * @return {boolean} Whether a token was added
 */
export function addToken( field, displayInput, value ) {
	if ( ! value ) {
		return false;
	}

	const tokens = parseTokens( field.value );

	// Skip duplicate tokens
	if ( tokens.includes( value ) ) {
		clearDisplayInput( displayInput );
		return false;
	}

	// Add new token
	tokens.push( value );
	updateFieldValue( field, tokens );
	clearDisplayInput( displayInput );
	return true;
}

/**
 * Remove a specific token from the field
 *
 * @param {HTMLElement} field         The original field
 * @param {HTMLElement} token         The token element to remove
 * @param {HTMLElement} tokensWrapper The wrapper containing tokens
 * @return {void}
 */
export function removeToken( field, token, tokensWrapper ) {
	// Get the token value from its content
	const value = token.querySelector( '.frm-token-value' ).textContent;

	// Filter out the token to remove
	const tokens = parseTokens( field.value ).filter( tokenValue => tokenValue !== value );

	// Update the field value
	updateFieldValue( field, tokens );

	// Remove the token element from DOM
	token.remove();

	// Focus the display input field after token removal
	const displayInput = tokensWrapper.closest( '.frm-token-container' )?.querySelector( '.frm-token-display-input' );
	displayInput?.focus();
}

/**
 * Parse string input into an array of tokens
 *
 * @param {string} value Space-separated string
 * @return {string[]} Array of tokens
 */
export function parseTokens( value ) {
	if ( ! value?.trim() ) {
		return [];
	}

	return value.trim().split( /\s+/ ).filter( Boolean );
}

/**
 * Update field value with tokens and trigger change event
 *
 * @param {HTMLElement} field  The field to update
 * @param {string[]}    tokens Array of token values
 * @return {void}
 */
export function updateFieldValue( field, tokens ) {
	field.value = tokens.join( ' ' );
	jQuery( field ).trigger( 'change' );
}

/**
 * Clear display input and maintain focus
 *
 * @param {HTMLElement} displayInput The display input field
 * @return {void}
 */
export function clearDisplayInput( displayInput ) {
	displayInput.value = '';
	displayInput.focus();
}

/**
 * Remove the last token from a field
 *
 * @param {HTMLElement} field        The original field
 * @param {HTMLElement} displayInput The display input
 * @return {boolean} Whether a token was removed
 */
export function removeLastToken( field, displayInput ) {
	const tokens = parseTokens( field.value );

	if ( ! tokens.length ) {
		return false;
	}

	// Remove last token
	tokens.pop();
	updateFieldValue( field, tokens );
	clearDisplayInput( displayInput );
	return true;
}
