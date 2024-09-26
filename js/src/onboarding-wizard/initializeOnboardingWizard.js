/**
 * Internal dependencies
 */
import { setupInitialView } from './ui';
import { addEventListeners } from './events';

/**
 * Initializes Onboarding Wizard.
 *
 * @return {void}
 */
function initializeOnboardingWizard() {
	setupInitialView();
	addEventListeners();
}

export default initializeOnboardingWizard;
