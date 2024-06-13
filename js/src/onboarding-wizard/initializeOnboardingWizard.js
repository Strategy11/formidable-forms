/**
 * Internal dependencies
 */
import { initializePageElements } from './elements';
import { initializePageState } from './shared';
import { setupInitialView } from './ui';
import { addEventListeners } from './events';

/**
 * Initializes Onboarding Wizard.
 *
 * @return {void}
 */
function initializeOnboardingWizard() {
	initializePageElements();
	initializePageState();
	setupInitialView();
	addEventListeners();
}

export default initializeOnboardingWizard;
