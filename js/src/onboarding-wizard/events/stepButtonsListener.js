/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { CURRENT_CLASS, PREFIX } from '../shared';
import { onClickPreventDefault, hide, frmAnimate, show } from '../utils';

/**
 * Manages event handling for the step buttons.
 *
 * @return {void}
 */
function addStepButtonsEvents() {
	const { skipButtons } = getElements();

	// Attach click event listeners to each skip buttons
	skipButtons.forEach( skipButton => {
		onClickPreventDefault( skipButton, onSkipButtonClick );
	});
}

/**
 * Handles the click event on a skip button.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
const onSkipButtonClick = () => {
	// Find and update current step
	const currentStep = document.querySelector( `.${PREFIX}-step.${CURRENT_CLASS}` );
	currentStep.classList.remove( CURRENT_CLASS );
	hide( currentStep );

	// Move to and display next step
	const nextStep = currentStep.nextElementSibling;
	nextStep.classList.add( CURRENT_CLASS );
	show( nextStep );
	new frmAnimate( nextStep ).fadeIn();

	// Update onboarding wizard's current step
	const { stepName } = nextStep.dataset;
	const { onboardingWizardPage } = getElements();
	onboardingWizardPage.setAttribute( 'data-current-step', stepName );
};

export default addStepButtonsEvents;
