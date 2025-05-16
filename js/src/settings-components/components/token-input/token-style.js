/**
 * Token style utilities
 *
 * Functions for adjusting token input styling
 */

import { CLASS_NAMES } from './constants';

/**
 * Adjust the padding-left of the display input based on the tokens wrapper width
 *
 * @param {HTMLElement} tokensWrapper The wrapper for token display
 * @return {void}
 */
export function adjustTokenInputPadding( tokensWrapper ) {
	if ( ! tokensWrapper ) {
		return;
	}

	// Get the display input using its specific class name
	const displayInput = tokensWrapper.closest( `.${ CLASS_NAMES.CONTAINER }` )?.querySelector( `.${ CLASS_NAMES.TOKEN_DISPLAY_INPUT }` );
	if ( ! displayInput ) {
		return;
	}

	// Set padding based on whether there are tokens
	const hasTokens = tokensWrapper.children.length > 0;
	displayInput.style.paddingLeft = hasTokens ? `${tokensWrapper.offsetWidth - 4}px` : '';
}

/**
 * Adjust padding for all token inputs on the page
 *
 * @return {void}
 */
export function adjustAllTokenInputPaddings() {
	const tokenContainers = document.querySelectorAll( `.${ CLASS_NAMES.CONTAINER }` );

	tokenContainers.forEach( container => {
		const tokensWrapper = container.querySelector( `.${ CLASS_NAMES.TOKENS_WRAPPER }` );
		if ( tokensWrapper ) {
			adjustTokenInputPadding( tokensWrapper );
		}
	});
}
