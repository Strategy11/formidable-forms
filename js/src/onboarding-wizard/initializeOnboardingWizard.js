/**
 * Internal dependencies
 */
import { initializeElements } from './elements';
import { addEventListeners } from './events';
import { setupInitialView } from './ui';

/**
 * Initializes Onboarding Wizard.
 *
 * @return {void}
 */
function initializeOnboardingWizard() {
	// Initializes essential DOM elements
	initializeElements();

	// Set up the initial view, including any required DOM manipulations for proper presentation
	setupInitialView();

	addEventListeners();
}

export default initializeOnboardingWizard;
