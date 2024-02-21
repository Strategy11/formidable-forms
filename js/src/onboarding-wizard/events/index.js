/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { CURRENT_CLASS, PREFIX } from '../shared';
import { hide, frmAnimate, show } from '../utils';
import { addOptionBoxEvents } from '../../common/events';
import addProceedWithoutAccountButtonEvents from './proceedWithoutAccountButtonListener';
import addStepButtonsEvents from './skipStepButtonListener';
import addSetupEmailStepButtonEvents from './setupEmailStepButtonListener';
import addInstallAddonsButtonEvents from './installAddonsButtonListener';
import addCheckProInstallationButtonEvents from './checkProInstallationListener';

/**
 * Navigates to the next step in a sequence.
 *
 * Hiding the current step and displaying the next one.
 *
 * @param {Event} event The click event object.
 * @return {void}
 */
export const navigateToNextStep = () => {
	// Find and hide current step
	const currentStep = document.querySelector( `.${PREFIX}-step.${CURRENT_CLASS}` );
	currentStep.classList.remove( CURRENT_CLASS );
	hide( currentStep );

	// Display next step
	const nextStep = currentStep.nextElementSibling;
	nextStep.classList.add( CURRENT_CLASS );
	show( nextStep );
	new frmAnimate( nextStep ).fadeIn();

	// Update onboarding wizard's current step
	const { stepName } = nextStep.dataset;
	const { onboardingWizardPage } = getElements();
	onboardingWizardPage.setAttribute( 'data-current-step', stepName );
};

/**
 * Attaches event listeners for handling user interactions.
 *
 * @return {void}
 */
export function addEventListeners() {
	// Add event handling for the "Proceed without Account" button
	addProceedWithoutAccountButtonEvents();

	// Add event handling for the "Skip" step button
	addStepButtonsEvents();

	// Add event handling for the "Next Step" button in the "Default Email Address" step
	addSetupEmailStepButtonEvents();

	// Add event handling for the "Install & Finish Setup" button in the "Install Formidable Add-ons" step
	addInstallAddonsButtonEvents();
	// Add event handling for an option-box
	addOptionBoxEvents();

	// Add event handling for the "Continue" button in the "Install Formidable Pro" step
	addCheckProInstallationButtonEvents();
}
