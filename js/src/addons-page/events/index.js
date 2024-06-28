/**
 * External dependencies
 */
import { addCategoryEvents } from 'core/page-skeleton';

/**
 * Internal dependencies
 */
import addAddonToggle from './addonToggleListener';
import { showSelectedCategory } from '../ui';

/**
 * Attaches event listeners for handling user interactions.
 *
 * @return {void}
 */
export function addEventListeners() {
	addCategoryEvents();
	wp.hooks.addAction( 'frmPageSkeleton.onCategoryClick', 'frmAddonsPage', selectedCategory => {
		showSelectedCategory( selectedCategory );
	});

	addAddonToggle();
}
