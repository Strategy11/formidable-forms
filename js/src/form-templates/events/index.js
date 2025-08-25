/**
 * External dependencies
 */
import { addCategoryEvents } from 'core/page-skeleton';

/**
 * Internal dependencies
 */
import addCreateFormButtonEvents from './createFormButtonListener';
import addFavoriteButtonEvents from './favoriteButtonListener';
import addUseTemplateButtonEvents from './useTemplateButtonListener';
import addSearchEvents from './searchListener';
import addCreateTemplateEvents from './createTemplateListeners';
import addGetFreeTemplatesEvents from './getFreeTemplatesListener';
import { showSelectedCategory } from '../ui';

/**
 * Attaches event listeners for handling user interactions.
 *
 * @return {void}
 */
export function addEventListeners() {
	addCategoryEvents();
	wp.hooks.addAction( 'frmPage.onCategoryClick', 'frmFormTemplates', selectedCategory => {
		// Display templates of the selected category
		showSelectedCategory( selectedCategory );
	});

	addCreateFormButtonEvents();
	addFavoriteButtonEvents();
	addUseTemplateButtonEvents();
	addSearchEvents();
	addCreateTemplateEvents();
	addGetFreeTemplatesEvents();
}

export { addApplicationTemplateEvents } from './applicationTemplateListener';
