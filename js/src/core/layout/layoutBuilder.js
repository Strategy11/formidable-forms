/**
 * Internal dependencies
 */
import { initializePageElements } from './elements';
import { initializePageState } from './shared';
import { setupInitialView } from './ui';
import { addEventListeners } from './events';

/**
 * Initializes the Page Sidebar.
 *
 * @return {void}
 */
function initializePageSidebar() {
	initializePageElements();
	initializePageState();
	setupInitialView();
	addEventListeners();
}

export default initializePageSidebar;
