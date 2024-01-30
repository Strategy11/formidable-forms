/**
 * Internal dependencies
 */
import { PREFIX } from '../shared';

/**
 * Return essential DOM elements.
 *
 * @return {Object} The DOM elements queried and constructed into an object.
 */
function getDOMElements() {
	// Body Elements
	const bodyElements = {
		skipButtons: document.querySelectorAll( `.${PREFIX}-skip-step` ),
		pageBackground: document.getElementById( `${PREFIX}-bg` )
	};

	// Welcome Step Elements
	const welcomeStep = {
		welcomeStep: document.getElementById( `${PREFIX}-welcome-step` )
	};

	// Default Email Address Step Elements
	const emailStep = {
		getEmailButton: document.getElementById( `${PREFIX}-get-email-btn` )
	};

	return {
		...bodyElements,
		...welcomeStep,
		...emailStep
	};
}

export default getDOMElements;
