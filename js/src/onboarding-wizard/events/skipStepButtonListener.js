/**
 * Internal dependencies
 */
import { navigateToNextStep } from '.';
import { getElements } from '../elements';
import { onClickPreventDefault } from '../utils';

/**
 * Manages event handling for the step buttons.
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
 * Handles the click event on a skip button.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
const onSkipStepButtonClick = () => {
	navigateToNextStep();
};

export default addSkipStepButtonEvents;
