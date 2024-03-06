/**
 * Internal dependencies
 */
import addSkipStepButtonEvents from './skipStepButtonListener';
import addSetupEmailStepButtonEvents from './setupEmailStepButtonListener';
import addInstallAddonsButtonEvents from './installAddonsButtonListener';
import addCheckProInstallationButtonEvents from './checkProInstallationButtonListener';
import addSkipProInstallationButtonEvents from './skipProInstallationButtonListener';
import addSaveLicenseButtonEvents from './saveLicenseButtonListener';
import { getElements } from '../elements';
import { CURRENT_CLASS, PREFIX } from '../shared';
import { hide, frmAnimate, show, setQueryParam, getQueryParam } from '../utils';
import { addOptionBoxEvents } from '../../common/events';

/**
 * Attaches event listeners for handling user interactions.
 *
 * @return {void}
 */
export function addEventListeners() {
	// Add event handling for the "Skip" step button
	addSkipStepButtonEvents();

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
 * Navigates to the given step in the onboarding sequence.
 * Optionally updates the browser's history state to include the current step.
 *
 * @param {string} stepName The name of the step to navigate to.
 * @param {string} [updateMethod='pushState'] Specifies the method to update the browser's history and URL. Accepts 'pushState' or 'replaceState'. If omitted, defaults to 'pushState'.
 * @return {void}
 */
export const navigateToStep = ( stepName, updateMethod = 'pushState' ) => {
	// Find the target step element
	const targetStep = document.querySelector( `.${PREFIX}-step[data-step-name="${stepName}"]` );
	if ( ! targetStep ) {
		return;
	}

	// Find and hide the current step element
	const currentStep = document.querySelector( `.${PREFIX}-step.${CURRENT_CLASS}` );
	if ( currentStep ) {
		currentStep.classList.remove( CURRENT_CLASS );
		hide( currentStep );
	}

	// Display the target step element
	targetStep.classList.add( CURRENT_CLASS );
	show( targetStep );
	new frmAnimate( targetStep ).fadeIn();

	// Update the onboarding wizard's current step attribute
	const { onboardingWizardPage } = getElements();
	onboardingWizardPage.setAttribute( 'data-current-step', stepName );

	// Update the URL query parameter, with control over history update method
	setQueryParam( 'step', stepName, updateMethod );
};

/**
 * Navigates to the next step in the sequence.
 *
 * The function assumes steps are sequentially ordered in the DOM.
 *
 * @return {void}
 */
export const navigateToNextStep = () => {
	const currentStep = document.querySelector( `.${PREFIX}-step.${CURRENT_CLASS}` );
	const nextStep = currentStep?.nextElementSibling;

	if ( ! nextStep ) {
		return;
	}

	const { stepName } = nextStep.dataset;
	navigateToStep( stepName );
};

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
