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
import addGetCodeButtonEvents from './getCodeButtonListener';
import addSaveCodeButtonEvents from './saveCodeButtonListener';
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
	addGetCodeButtonEvents();
	addSaveCodeButtonEvents();
}

export { addApplicationTemplateEvents } from './applicationTemplateListener';
