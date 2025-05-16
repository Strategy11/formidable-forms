/**
 * Token style utilities
 *
 * Functions for adjusting token input styling
 */

import { CLASS_NAMES, DISPLAY_INPUT_HEIGHT, TOKEN_GAP } from './constants';

/**
 * Adjust the styling of the display input based on tokens wrapper dimensions
 *
 * @param {HTMLElement} tokensWrapper The wrapper for token display
 * @return {void}
 */
export function adjustTokenInputStyle( tokensWrapper ) {
	if ( ! tokensWrapper ) {
		return;
	}

	// Get the display input using its specific class name
	const displayInput = tokensWrapper.closest( `.${ CLASS_NAMES.CONTAINER }` )?.querySelector( `.${ CLASS_NAMES.TOKEN_DISPLAY_INPUT }` );
	if ( ! displayInput ) {
		return;
	}

	// Check if there are any tokens
	const tokens = tokensWrapper.querySelectorAll( `.${ CLASS_NAMES.TOKEN }` );
	const hasTokens = tokens.length > 0;

	// Reset all styles if no tokens
	if ( ! hasTokens ) {
		displayInput.style.paddingLeft = '';
		displayInput.style.paddingTop = '';
		displayInput.style.height = '';
		return;
	}

	// Get dimensions of tokens and wrapper
	const tokensWrapperHeight = tokensWrapper.offsetHeight;
	const tokensWrapperWidth = tokensWrapper.offsetWidth;

	// Calculate number of rows based on wrapper height
	const numRows = Math.max( 1, Math.ceil( tokensWrapperHeight / DISPLAY_INPUT_HEIGHT ) );

	// Handle multi-row tokens
	if ( numRows > 1 ) {
		// For multiple rows, calculate the width of tokens in the last row
		const lastRowTokens = getLastRowTokens( tokens );
		const lastRowWidth = calculateLastRowWidth( lastRowTokens );

		displayInput.style.paddingLeft = lastRowWidth ? `${lastRowWidth + TOKEN_GAP * 2}px` : '';
		displayInput.style.paddingTop = `${tokensWrapperHeight - DISPLAY_INPUT_HEIGHT + TOKEN_GAP}px`;
		displayInput.style.height = `${tokensWrapperHeight}px`;
	} else {
		// For single row, use the full width of tokens
		displayInput.style.paddingLeft = `${tokensWrapperWidth - TOKEN_GAP}px`;
		displayInput.style.paddingTop = '';
		displayInput.style.height = '';
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

/**
 * Adjust styling for all token inputs on the page
 *
 * @return {void}
 */
export function adjustAllTokenInputStyles() {
	const tokenContainers = document.querySelectorAll( `.${ CLASS_NAMES.CONTAINER }` );

	tokenContainers.forEach( container => {
		const tokensWrapper = container.querySelector( `.${ CLASS_NAMES.TOKENS_WRAPPER }` );
		if ( tokensWrapper ) {
			adjustTokenInputStyle( tokensWrapper );
		}
	});
}
