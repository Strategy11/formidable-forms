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
		onboardingWizardPage: document.getElementById( `${PREFIX}-wizard-page` ),
		pageBackground: document.getElementById( `${PREFIX}-bg` ),
		container: document.getElementById( `${PREFIX}-container` ),
		skipButtons: document.querySelectorAll( `.${PREFIX}-skip-step` )
	};

	// Welcome Step Elements
	const welcomeStep = {
		welcomeStep: document.getElementById( `${PREFIX}-welcome-step` ),
		skipConnectAccountButton: document.getElementById( `${PREFIX}-skip-connect-account` )
	};

	// License Management Step Elements
	const licenseManagementStep = {
		licenseManagementStep: document.getElementById( `${PREFIX}-license-management-step` )
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
		...licenseManagementStep,
		...welcomeStep,
		...emailStep,
		...successStep
	};
}

export default getDOMElements;
