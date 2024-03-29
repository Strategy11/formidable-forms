/**
 * Internal dependencies
 */
import { createAppState } from '../../common/createAppState';

/**
 * Creates an instance of application state management for the Onboarding Wizard.
 *
 * @return {Object} The initial state of the application.
 */
const onboardingWizardAppState = createAppState( () => {
	return {
		processedSteps: [],
		installedAddons: []
	};
});

export const {
	initializeAppState,
	getAppState,
	setAppState,
	getAppStateProperty,
	setAppStateProperty
} = onboardingWizardAppState;
