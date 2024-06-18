/**
 * Internal dependencies
 */
import { setupInitialView } from './ui';
import { addEventListeners } from './events';

/**
 * Initializes the Page Sidebar.
 *
 * @return {void}
 */
function initializePageSidebar() {
	setupInitialView();
	addEventListeners();
}

export default initializePageSidebar;
