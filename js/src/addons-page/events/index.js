/**
 * External dependencies
 */
import { addCategoryEvents } from 'core/page-skeleton';

/**
 * Internal dependencies
 */
import addAddonToggle from './addonToggleListener';
import { showSelectedCategory } from '../ui';
import addSearchEvents from './searchListener';

/**
 * Attaches event listeners for handling user interactions.
 *
 * @return {void}
 */
export function addEventListeners() {
	addCategoryEvents();
	wp.hooks.addAction( 'frmPage.onCategoryClick', 'frmAddonsPage', selectedCategory => {
		showSelectedCategory( selectedCategory );
	});

	addAddonToggle();
	addSearchEvents();
}
