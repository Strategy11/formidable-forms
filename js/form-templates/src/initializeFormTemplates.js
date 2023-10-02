/**
 * Internal dependencies
 */
import { initializeElements } from './elements';
import { initializeAppState } from './shared';
import { setupInitialView, initializeModal } from './ui';
import { buildCategorizedTemplates } from './templates';
import { addEventListeners } from './events';

/**
 * Initializes form templates.
 *
 * @return {void}
 */
function initializeFormTemplates() {
	// Initializes essential DOM elements
	initializeElements();

	// Initialize application state
	initializeAppState();

	// Set up the initial view, including any required DOM manipulations for proper presentation
	setupInitialView();

	// Initialize the modal dialog
	initializeModal();

	// Generate a categorized list of templates
	buildCategorizedTemplates();

	// Attach event listeners for user interactions
	addEventListeners();
}

export default initializeFormTemplates;
