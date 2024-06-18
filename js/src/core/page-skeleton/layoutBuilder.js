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
function initializePageSkeleton() {
	setupInitialView();
	addEventListeners();
}

export default initializePageSkeleton;
