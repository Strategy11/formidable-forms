/**
 * Internal dependencies
 */
import { initializeElements } from './elements';
import { initializeAppState } from './shared';
import { addEventListeners } from './events';
import { setupInitialView } from './ui';

/**
 * Initializes Onboarding Wizard.
 *
 * @return {void}
 */
function initializeOnboardingWizard() {
	initializeElements();
	initializeAppState();
	setupInitialView();
	addEventListeners();
}

export default initializeOnboardingWizard;
