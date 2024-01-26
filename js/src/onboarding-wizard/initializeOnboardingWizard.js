/**
 * Internal dependencies
 */
import { initializeAppState } from './shared';
import { initializeElements } from './elements';
import { addEventListeners } from './events';

/**
 * Initializes Onboarding Wizard.
 *
 * @return {void}
 */
function initializeOnboardingWizard() {
	initializeElements();
	initializeAppState();
	addEventListeners();
}

export default initializeOnboardingWizard;
