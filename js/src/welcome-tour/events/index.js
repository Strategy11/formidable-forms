import addChecklistEvents from './checklistEvents';
import addDismissEvents from './dismissEvents';
import addStylerUpdateEvents from './stylerUpdateButtonEvents';

/**
 * Attaches event listeners for handling user interactions.
 *
 * @returns {void}
 */
export function addEventListeners() {
	addChecklistEvents();
	addDismissEvents();
	addStylerUpdateEvents();
}
