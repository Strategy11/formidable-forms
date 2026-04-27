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
import addConsentTrackingButtonEvents from './consentTrackingButtonListener';
import addInstallAddonsButtonEvents from './installAddonsButtonListener';
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

	addConsentTrackingButtonEvents();

	// Add event handling for the "Install & Finish Setup" button in the "Install Formidable Add-ons" step
	addInstallAddonsButtonEvents();
	// Add event handling for an option-box
	addOptionBoxEvents();
}

/**
 * Responds to browser navigation events (back/forward) by updating the UI to match the step indicated in the URL or history state.
 *
 * @param {PopStateEvent} event The event object associated with the navigation action.
 * @return {void}
 */
window.addEventListener( 'popstate', event => {
	const stepName = event.state?.step || getQueryParam( 'step' );
	// Navigate to the specified step without adding to browser history
	navigateToStep( stepName, 'replaceState' );
} );
