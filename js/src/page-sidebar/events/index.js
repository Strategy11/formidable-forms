/**
 * Internal dependencies
 */
import addCategoryEvents from './categoryListener';
import addSearchEvents from './searchListener';
import addEmptyStateButtonEvents from './emptyStateButtonListener';

/**
 * Attaches event listeners for handling user interactions.
 *
 * @return {void}
 */
export function addEventListeners() {
	addCategoryEvents();
	addSearchEvents();
	addEmptyStateButtonEvents();
}

export { resetSearchInput } from './searchListener';
