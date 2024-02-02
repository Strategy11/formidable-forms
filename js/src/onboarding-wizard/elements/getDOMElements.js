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
		pageBackground: document.getElementById( `${PREFIX}-bg` ),
		returnToDashboard: document.getElementById( `${PREFIX}-return-dashboard` ),
		container: document.getElementById( `${PREFIX}-container` )
	};

	// Welcome Step Elements
	const welcomeStep = {
		welcomeStep: document.getElementById( `${PREFIX}-welcome-step` )
	};

	// Default Email Address Step Elements
	const emailStep = {
		getEmailButton: document.getElementById( `${PREFIX}-get-email-btn` )
	};

	// Success Step Elements
	const successStep = {
		successStep: document.getElementById( `${PREFIX}-success-step` )
	};

	return {
		...bodyElements,
		...welcomeStep,
		...emailStep,
		...successStep
	};
}

export default getDOMElements;
