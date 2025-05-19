/**
 * Proxy input style
 *
 * Functions for adjusting proxy input styling
 */

import { CLASS_NAMES, PROXY_INPUT_HEIGHT, TOKEN_GAP } from './constants';

/**
 * Adjust styling for all proxy inputs on the current settings
 *
 * @return {void}
 */
export function adjustAllProxyInputStyles() {
	document.querySelectorAll( `.${ CLASS_NAMES.CONTAINER }` ).forEach( container =>
		adjustProxyInputStyle(
			container.querySelector( `.${ CLASS_NAMES.TOKEN_PROXY_INPUT }` ),
			container.querySelector( `.${ CLASS_NAMES.TOKENS_WRAPPER }` )
		)
	);
}

/**
 * Adjust the styling of the proxy input based on tokens wrapper dimensions
 *
 * @param {HTMLElement} proxyInput    The proxy input field
 * @param {HTMLElement} tokensWrapper The wrapper for token display
 * @return {void}
 */
export function adjustProxyInputStyle( proxyInput, tokensWrapper ) {
	if ( ! proxyInput || ! tokensWrapper ) {
		return;
	}

	const tokens = tokensWrapper.querySelectorAll( `.${ CLASS_NAMES.TOKEN }` );
	const hasTokens = tokens.length > 0;

	// Reset all styles if no tokens
	if ( ! hasTokens ) {
		proxyInput.style.paddingLeft = '';
		proxyInput.style.paddingTop = '';
		proxyInput.style.height = '';
		return;
	}

	const tokensWrapperHeight = tokensWrapper.offsetHeight;

	// Calculate number of rows based on wrapper height
	const numRows = Math.max( 1, Math.ceil( tokensWrapperHeight / PROXY_INPUT_HEIGHT ) );

	if ( numRows > 1 ) {
		// For multiple rows, calculate the width of tokens in the last row
		const lastRowWidth = calculateLastRowWidth( getLastRowTokens( tokens ) );

		proxyInput.style.height = `${tokensWrapperHeight}px`;
		proxyInput.style.paddingTop = `${tokensWrapperHeight - PROXY_INPUT_HEIGHT + TOKEN_GAP}px`;
		proxyInput.style.paddingLeft = lastRowWidth ? `${lastRowWidth + TOKEN_GAP * 2}px` : '';
	} else {
		// For single row, use the full width of tokens
		proxyInput.style.height = '';
		proxyInput.style.paddingTop = '';
		proxyInput.style.paddingLeft = `${tokensWrapper.offsetWidth - TOKEN_GAP}px`;
	}
}

/**
 * Identify tokens in the last row of a multi-row token layout
 *
 * @param {NodeList} tokens All token elements
 * @return {Array} Array of tokens in the last row
 */
function getLastRowTokens( tokens ) {
	if ( ! tokens.length ) {
		return [];
	}

	const tokensArray = Array.from( tokens );
	let lastRowY = -1;

	tokensArray.forEach( token => {
		const tokenRect = token.getBoundingClientRect();
		const tokenBottom = tokenRect.bottom;

		if ( tokenBottom > lastRowY ) {
			lastRowY = tokenBottom;
		}
	});

	const threshold = TOKEN_GAP / 2;
	return tokensArray.filter( token => {
		const tokenRect = token.getBoundingClientRect();
		return Math.abs( tokenRect.bottom - lastRowY ) <= threshold;
	});
}

/**
 * Calculate the total width of tokens in the last row
 *
 * @param {Array} lastRowTokens Array of token elements in the last row
 * @return {number} Total width of tokens in the last row
 */
function calculateLastRowWidth( lastRowTokens ) {
	if ( ! lastRowTokens.length ) {
		return 0;
	}

	let totalWidth = 0;

	lastRowTokens.forEach( token => {
		totalWidth += token.offsetWidth;
	});

	totalWidth += ( lastRowTokens.length - 1 ) * TOKEN_GAP;

	return totalWidth + TOKEN_GAP;
}
