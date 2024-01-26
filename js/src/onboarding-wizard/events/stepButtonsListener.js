/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { onClickPreventDefault, fadeIn, hide } from '../utils';

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
	const currentStep = document.querySelector( '[data-current-step]' );

	hide( currentStep );
	fadeIn( currentStep.nextElementSibling );
};

export default addStepButtonsEvents;
