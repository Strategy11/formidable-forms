import addChecklistEvents from './checklistEvents';
import addDismissEvents from './dismissEvents';

/**
 * Attaches event listeners for handling user interactions.
 *
 * @return {void}
 */
export function addEventListeners() {
	addChecklistEvents();
	addDismissEvents();
}
