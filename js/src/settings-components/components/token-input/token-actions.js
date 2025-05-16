/**
 * Token actions
 *
 * Functions for creating and managing tokens
 */

import { createToken } from './token-elements';

/**
 * Create tokens from a space-separated string value
 *
 * @param {string}      value         Space-separated values
 * @param {HTMLElement} tokensWrapper Wrapper element for tokens
 * @return {void}
 */
export function createTokensFromValue( value, tokensWrapper ) {
	if ( ! tokensWrapper ) {
		return;
	}

	// Clear existing tokens if any
	tokensWrapper.innerHTML = '';

	if ( ! value?.trim() ) {
		return;
	}

	// Create tokens from space-separated values
	value.trim()
		.split( /\s+/ )
		.filter( Boolean )
		.forEach( token => createToken( token, tokensWrapper ) );
}

/**
 * Create a token from the current input value
 *
 * @param {HTMLElement} field         The original hidden input field
 * @param {HTMLElement} displayInput  The display input field for interaction
 * @param {HTMLElement} tokensWrapper The wrapper for token display
 * @return {boolean}                  Whether a token was added
 */
export function addTokenFromInput( field, displayInput, tokensWrapper ) {
	const value = displayInput.value.trim();
	if ( ! value ) {
		return false;
	}

	// Update field value with the new token
	const currentValue = field.value ? field.value + ' ' : '';
	field.value = currentValue + value;

	// Trigger jQuery change event to detect changes and update the builder preview
	jQuery( field ).trigger( 'change' );

	displayInput.value = '';

	return true;
}
