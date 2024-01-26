/**
 * Internal dependencies
 */
import addStepButtonsEvents from './stepButtonsListener';
import addGetEmailButtonEvents from './getEmailButtonListener';

/**
 * Attaches event listeners for handling user interactions.
 *
 * @return {void}
 */
export function addEventListeners() {
	addStepButtonsEvents();
	addGetEmailButtonEvents();
}

export { onSkipButtonClick } from './stepButtonsListener';
