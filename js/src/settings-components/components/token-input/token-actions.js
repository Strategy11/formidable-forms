/**
 * Token actions
 *
 * Core functions for token operations and management
 */

/**
 * Internal dependencies
 */
import { CLASS_NAMES } from './constants';
import { adjustProxyInputStyle } from './proxy-input-style';
import { createTokenElement } from './token-elements';

/**
 * Synchronize token display with the field value
 *
 * @param {string}      value         The field value
 * @param {HTMLElement} proxyInput    The proxy input field
 * @param {HTMLElement} tokensWrapper Wrapper element for tokens
 * @return {void}
 */
export function synchronizeTokensDisplay( value, proxyInput, tokensWrapper ) {
	if ( ! value || ! tokensWrapper || ! proxyInput ) {
		return;
	}

	// Clear existing tokens display and render new tokens
	tokensWrapper.innerHTML = '';
	parseTokens( value ).forEach( token => createTokenElement( token, tokensWrapper ) );

	adjustProxyInputStyle( proxyInput, tokensWrapper );

	proxyInput.focus();
}

/**
 * Add a new token to the field
 *
 * @param {string}      tokenValue The token value to add
 * @param {HTMLElement} field      The original field
 * @param {HTMLElement} proxyInput The proxy input
 * @return {boolean} Whether a token was added
 */
export function addToken( tokenValue, field, proxyInput ) {
	if ( ! tokenValue || ! field || ! proxyInput ) {
		return false;
	}

	// Get current tokens from field value
	const tokens = parseTokens( field.value );

	// Skip duplicate tokens
	if ( tokens.includes( tokenValue ) ) {
		clearProxyInput( proxyInput );
		return false;
	}

	// Add new token
	tokens.push( tokenValue );
	updateFieldValue( field, tokens );
	clearProxyInput( proxyInput );
	return true;
}

/**
 * Remove a specific token from the field
 *
 * @param {HTMLElement} token      The token element to remove
 * @param {HTMLElement} field      The original field
 * @param {HTMLElement} proxyInput The proxy input
 * @return {void}
 */
export function removeToken( token, field, proxyInput ) {
	if ( ! token || ! field || ! proxyInput ) {
		return;
	}

	const value = token.querySelector( `.${ CLASS_NAMES.TOKEN_VALUE }` ).textContent;

	// Filter out the token to remove
	const tokens = parseTokens( field.value ).filter( tokenValue => tokenValue !== value );
	updateFieldValue( field, tokens );

	// Remove the token element from DOM
	token.remove();

	proxyInput.focus();
}

/**
 * Parse string input into an array of tokens
 *
 * @param {string} value Space-separated string
 * @return {string[]} Array of tokens
 */
export function parseTokens( value = '' ) {
	value = value.trim();

	if ( ! value ) {
		return [];
	}

	return value.split( /\s+/ ).filter( Boolean );
}

/**
 * Update field value with tokens and trigger change event
 *
 * @param {HTMLElement} field  The field to update
 * @param {string[]}    tokens Array of token values
 * @return {void}
 */
export function updateFieldValue( field, tokens = [] ) {
	if ( ! field ) {
		return;
	}

	field.value = tokens.join( ' ' );
	jQuery( field ).trigger( 'change' );
}

/**
 * Clear proxy input and maintain focus
 *
 * @param {HTMLElement} proxyInput The proxy input field
 * @return {void}
 */
export function clearProxyInput( proxyInput ) {
	if ( ! proxyInput ) {
		return;
	}

	proxyInput.value = '';
	proxyInput.focus();
}
