/**
 * Internal dependencies
 */
import addSkipConnectAccountButtonEvents from './skipConnectAccountButtonListener';
import addStepButtonsEvents from './stepButtonsListener';
import addGetEmailButtonEvents from './getEmailButtonListener';

/**
 * Attaches event listeners for handling user interactions.
 *
 * @return {void}
 */
export function addEventListeners() {
	addSkipConnectAccountButtonEvents();
	addStepButtonsEvents();
	addGetEmailButtonEvents();
}
