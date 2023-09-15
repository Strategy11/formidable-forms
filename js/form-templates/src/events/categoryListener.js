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
import { getElements } from '../elements';
import { PREFIX, CURRENT_CLASS, getAppState, setAppState } from '../shared';
import { showSelectedCategory } from '../ui';
import { fadeIn } from '../utils';
import { resetSearchInput } from './';

/**
 * Manages event handling for sidebar category links.
 *
 * @return {void}
 */
function addCategoryEvents() {
	const categoryItems = document.querySelectorAll( `.${ PREFIX }-cat-item` );

	// Attach click event listeners to each sidebar category
	categoryItems.forEach( category =>
		category.addEventListener( 'click', onCategoryClick )
	);
}

/**
 * Handles the click event on a category item.
 *
 * @private
 * @param {Event} event The click event object.
 */
const onCategoryClick = ( event ) => {
	const clickedCategory = event.currentTarget;
	const newSelectedCategory = clickedCategory.getAttribute( 'data-category' );
	let { selectedCategory, selectedCategoryEl, notEmptySearchText } = getAppState();

	// If the selected category hasn't changed, return early
	if ( selectedCategory === newSelectedCategory ) {
		return;
	}

	/**
	 * Filter hook to modify the selected category.
	 *
	 * @param {string} selectedCategory The selected category.
	 */
	selectedCategory = wp.hooks.applyFilters(
		'frmFormTemplates.selectedCategory',
		newSelectedCategory
	);

	// Highlight the newly clicked category and update the application state
	selectedCategoryEl.classList.remove( CURRENT_CLASS );
	selectedCategoryEl = clickedCategory;
	selectedCategoryEl.classList.add( CURRENT_CLASS );
	setAppState({ selectedCategory, selectedCategoryEl });

	// Reset the search input if it contains text
	if ( notEmptySearchText ) {
		resetSearchInput();
	}

	// Display templates of the selected category
	showSelectedCategory( selectedCategory );

	// Smoothly display the updated UI elements
	const { bodyContent } = getElements();
	fadeIn( bodyContent );
};

export default addCategoryEvents;
