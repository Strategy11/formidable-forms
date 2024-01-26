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
		skipButtons: document.querySelectorAll( `.${PREFIX}-skip-step` )
	};

	// Welcome Step Elements
	const welcomeStep = {};

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
