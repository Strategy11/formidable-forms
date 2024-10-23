/**
 * External dependencies
 */
import { onClickPreventDefault } from 'core/utils';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { navigateToPrevStep } from '../utils';

/**
 * Manages event handling for the "Back" button.
 *
 * @return {void}
 */
function addBackButtonEvents() {
	const { backButtons } = getElements();

	// Attach click event listeners to each back buttons
	backButtons.forEach( backButton => {
		onClickPreventDefault( backButton, onBackButtonClick );
	});
}

/**
 * Handles the click event on a "Back" button.
 *
 * @private
 * @return {void}
 */
const onBackButtonClick = () => {
	navigateToPrevStep();
};

export default addBackButtonEvents;
