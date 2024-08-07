/**
 * Internal dependencies
 */
import { setupInitialView, initializeModal } from './ui';
import { buildCategorizedTemplates, maybeAddApplicationTemplates } from './templates';
import { addEventListeners } from './events';

/**
 * Initializes form templates.
 *
 * @return {void}
 */
function initializeFormTemplates() {
	maybeAddApplicationTemplates();
	initializeModal();
	buildCategorizedTemplates();
	setupInitialView();
	addEventListeners();
}

export default initializeFormTemplates;
