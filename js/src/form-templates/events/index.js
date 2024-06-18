/**
 * External dependencies
 */
import { addEventListeners as addSkeletonEventListeners } from 'core/page-skeleton';

/**
 * Internal dependencies
 */
import addCreateFormButtonEvents from './createFormButtonListener';
import addFavoriteButtonEvents from './favoriteButtonListener';
import addUseTemplateButtonEvents from './useTemplateButtonListener';
import addSearchEvents from './searchListener';
import addEmptyStateButtonEvents from './emptyStateButtonListener';
import addCreateTemplateEvents from './createTemplateListeners';
import addGetCodeButtonEvents from './getCodeButtonListener';
import addSaveCodeButtonEvents from './saveCodeButtonListener';
import { showSelectedCategory } from '../ui';

/**
 * Attaches event listeners for handling user interactions.
 *
 * @return {void}
 */
export function addEventListeners() {
	addSkeletonEventListeners();
	wp.hooks.addAction( 'frmPageSkeleton.onCategoryClick', 'frmFormTemplates', selectedCategory => {
		// Display templates of the selected category
		showSelectedCategory( selectedCategory );
	});

	addCreateFormButtonEvents();
	addFavoriteButtonEvents();
	addUseTemplateButtonEvents();
	addSearchEvents();
	addEmptyStateButtonEvents();
	addCreateTemplateEvents();
	addGetCodeButtonEvents();
	addSaveCodeButtonEvents();
}

export { resetSearchInput } from './searchListener';
export { addApplicationTemplateEvents } from './applicationTemplateListener';
