/**
 * Internal dependencies
 */
import { initializeElements } from './elements';
import { initializeAppState } from './shared';
import { setupInitialView, initializeModal } from './ui';
import { buildCategorizedTemplates, maybeAddApplicationTemplates } from './templates';
import { addEventListeners } from './events';

/**
 * Initializes form templates.
 *
 * @return {void}
 */
function initializeFormTemplates() {
	// Initializes essential DOM elements
	initializeElements();

	initializeAppState();

	maybeAddApplicationTemplates();

	// Set up the initial view, including any required DOM manipulations for proper presentation
	setupInitialView();

	initializeModal();

	// Generate a categorized list of templates
	buildCategorizedTemplates();

	addEventListeners();
}

export default initializeFormTemplates;
