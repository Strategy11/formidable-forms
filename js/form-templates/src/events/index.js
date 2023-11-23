/**
 * Internal dependencies
 */
import addCategoryEvents from './categoryListener';
import addCreateFormButtonEvents from './createFormButtonListener';
import addFavoriteButtonEvents from './favoriteButtonListener';
import addUseTemplateButtonEvents from './useTemplateButtonListener';
import addSearchEvents from './searchListener';
import addEmptyStateButtonEvents from './emptyStateButtonListener';
import addCreateTemplateEvents from './createTemplateListeners';
import addGetCodeButtonEvents from './getCodeButtonListener';
import addSaveCodeButtonEvents from './saveCodeButtonListener';

/**
 * Attaches event listeners for handling user interactions.
 *
 * @return {void}
 */
export function addEventListeners() {
	addCategoryEvents();
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
