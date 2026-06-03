import addChecklistEvents from './checklistEvents';
import addDismissEvents from './dismissEvents';
import addStylerUpdateEvents from './stylerUpdateButtonEvents';

/**
 * Attaches event listeners for handling user interactions.
 *
 * @return {void}
 */
export function addEventListeners() {
	addChecklistEvents();
	addDismissEvents();
	addStylerUpdateEvents();
}
