/**
 * Copyright (C) 2023 Formidable Forms
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

/**
 * Internal dependencies
 */
import addCategoryEvents from './categoryListener';
import addCreateFormButtonEvents from './createFormButtonListener';
import addFavoriteButtonEvents from './favoriteButtonListener';
import addUseTemplateButtonEvents from './useTemplateButtonListener';
import addSearchEvents from './searchListener';
import addEmptyStateButtonEvents from './emptyStateButtonListener';
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
	addGetCodeButtonEvents();
	addSaveCodeButtonEvents();
}

export { resetSearchInput } from './searchListener';