/**
 * Event handlers
 *
 * Functions for handling token input events
 */

import { CLASS_NAMES, KEYS } from './constants';
import { addToken, removeToken, synchronizeTokensDisplay } from './token-actions';
import { adjustProxyInputStyle } from './proxy-input-style';

/**
 * Add event listeners to token input components
 *
 * @param {HTMLElement} field         The original hidden input field
 * @param {HTMLElement} proxyInput    The proxy input field for interaction
 * @param {HTMLElement} tokensWrapper The wrapper for token display
 * @return {void}
 */
export function addEventListeners( field, proxyInput, tokensWrapper ) {
	// The jQuery change event is required to catch programmatic updates, as "Add Layout Classes" modifies the field value via jQuery
	jQuery( field ).on( 'change', () => synchronizeTokensDisplay( field.value, proxyInput, tokensWrapper ) );

	proxyInput.addEventListener( 'keydown', event => onProxyInputKeydown( event, field, proxyInput, tokensWrapper ) );
	proxyInput.addEventListener( 'blur', () => addToken( proxyInput.value.trim(), field, proxyInput ) );

	tokensWrapper.addEventListener( 'click', event => handleTokenRemoval( event, field, proxyInput ) );
}

/**
 * Handle keydown events on the proxy input field
 *
 * @private
 *
 * @param {Event}       event         Keydown event
 * @param {HTMLElement} field         The original hidden input field
 * @param {HTMLElement} proxyInput    The proxy input field for interaction
 * @param {HTMLElement} tokensWrapper The wrapper for token display
 * @return {void}
 */
function onProxyInputKeydown( event, field, proxyInput, tokensWrapper ) {
	const key = event.key;
	const value = proxyInput.value.trim();

	if ( key === KEYS.BACKSPACE && ! value ) { // Handle token removal on backspace
		event.preventDefault();
		const lastToken = tokensWrapper.querySelector( `.${ CLASS_NAMES.TOKEN }:last-child` );
		removeToken( lastToken, field, proxyInput )
	} else if ( [ KEYS.SPACE, KEYS.COMMA, KEYS.ENTER, KEYS.TAB ].includes( key ) ) { // Handle token creation keys
		event.preventDefault();
		addToken( value, field, proxyInput );
	}

	adjustProxyInputStyle( proxyInput, tokensWrapper );
}

/**
 * Handle token removal when clicking the remove button
 *
 * @private
 *
 * @param {Event}       event      Click event
 * @param {HTMLElement} field      The original hidden input field
 * @param {HTMLElement} proxyInput The proxy input field for interaction
 * @return {void}
 */
function handleTokenRemoval( event, field, proxyInput ) {
	const removeButton = event.target.closest( `.${ CLASS_NAMES.TOKEN_REMOVE }` );
	if ( ! removeButton ) {
		return;
	}

	const token = removeButton.closest( `.${ CLASS_NAMES.TOKEN }` );
	if ( ! token ) {
		return;
	}

	const tokensWrapper = token.parentElement;

	removeToken( token, field, proxyInput );
	adjustProxyInputStyle( proxyInput, tokensWrapper );
}
