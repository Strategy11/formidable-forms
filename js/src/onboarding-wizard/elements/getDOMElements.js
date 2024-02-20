/**
 * Internal dependencies
 */
import { PREFIX, WELCOME_STEP_ID } from '../shared';

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
		skipStepButtons: document.querySelectorAll( `.${PREFIX}-skip-step` )
	};

	// Welcome Step Elements
	const welcomeStep = {
		welcomeStep: document.getElementById( WELCOME_STEP_ID ),
		proceedWithoutAccountButton: document.getElementById( `${PREFIX}-proceed-without-account` )
	};

	// Install Formidable Pro Step Elements
	const installFormidableProStep = {
		installFormidableProStep: document.getElementById( `${PREFIX}-install-formidable-pro-step` )
	};

	// License Management Step Elements
	const licenseManagementStep = {
		licenseManagementStep: document.getElementById( `${PREFIX}-license-management-step` )
	};

	// Default Email Address Step Elements
	const emailStep = {
		setupEmailStepButton: document.getElementById( `${PREFIX}-setup-email-step-button` ),
		defaultEmailField: document.getElementById( `${PREFIX}-default-email-field` ),
		subscribeCheckbox: document.getElementById( `${PREFIX}-subscribe` ),
		allowTrackingCheckbox: document.getElementById( `${PREFIX}-allow-tracking` )
	};

	// Install Formidable Add-ons Step Elements
	const installAddonsStep = {
		installAddonsButton: document.getElementById( `${PREFIX}-install-addons-button` )
	};

	// Success Step Elements
	const successStep = {
		successStep: document.getElementById( `${PREFIX}-success-step` )
	};

	return {
		...bodyElements,
		...installFormidableProStep,
		...licenseManagementStep,
		...welcomeStep,
		...emailStep,
		...installAddonsStep,
		...successStep
	};
}

export default getDOMElements;
