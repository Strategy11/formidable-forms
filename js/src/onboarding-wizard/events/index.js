/**
 * External dependencies
 */
import { addOptionBoxEvents } from 'core/events';
import { getQueryParam } from 'core/utils';

/**
 * Internal dependencies
 */
import addSkipStepButtonEvents from './skipStepButtonListener';
import addBackButtonEvents from './backButtonListener';
import addSetupEmailStepButtonEvents from './setupEmailStepButtonListener';
import addInstallAddonsButtonEvents from './installAddonsButtonListener';
import addCheckProInstallationButtonEvents from './checkProInstallationButtonListener';
import addSkipProInstallationButtonEvents from './skipProInstallationButtonListener';
import addSaveLicenseButtonEvents from './saveLicenseButtonListener';
import { navigateToStep } from '../utils';

/**
 * Attaches event listeners for handling user interactions.
 *
 * @return {void}
 */
export function addEventListeners() {
	// Add event handling for the "Skip" and "Back" buttons
	addSkipStepButtonEvents();
	addBackButtonEvents();

	// Add event handling for the "Next Step" button in the "Default Email Address" step
	addSetupEmailStepButtonEvents();

	// Add event handling for the "Active & continue" button in the "License Management" step.
	addSaveLicenseButtonEvents();

	// Add event handling for the "Install & Finish Setup" button in the "Install Formidable Add-ons" step
	addInstallAddonsButtonEvents();
	// Add event handling for an option-box
	addOptionBoxEvents();

	// Add event handling for the "Continue" and "Skip" buttons in the "Install Formidable Pro" step
	addCheckProInstallationButtonEvents();
	addSkipProInstallationButtonEvents();
}

/**
 * Responds to browser navigation events (back/forward) by updating the UI to match the step indicated in the URL or history state.
 *
 * @param {PopStateEvent} event The event object associated with the navigation action.
 * @return {void}
 */
window.addEventListener( 'popstate', ( event ) => {
	const stepName = event.state?.step || getQueryParam( 'step' );
	// Navigate to the specified step without adding to browser history
	navigateToStep( stepName, 'replaceState' );
});
