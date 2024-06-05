/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { navigateToNextStep, onClickPreventDefault } from '../utils';

/**
 * Manages event handling for the "Skip" step button.
 *
 * @return {void}
 */
function addSkipStepButtonEvents() {
	const { skipStepButtons } = getElements();

	// Attach click event listeners to each skip buttons
	skipStepButtons.forEach( skipButton => {
		onClickPreventDefault( skipButton, onSkipStepButtonClick );
	});
}

/**
 * Handles the click event on a "Skip" step button.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
const onSkipStepButtonClick = () => {
	navigateToNextStep();
};

export default addSkipStepButtonEvents;
