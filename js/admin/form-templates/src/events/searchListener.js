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
import { initSearch, getAppState, setAppStateProperty, PREFIX } from '../shared';
import { searchInput, allTemplatesCategory, emptyState } from '../elements';
import { showSearchEmptyState, showSearchResults } from '../ui';
import { hide } from '../utils';

/**
 * Adds search-related event listeners by calling the 'initSearch' function.
 *
 * @since x.x
 *
 * @see frmDom.search method
 */
function addSearchEvents() {
	initSearch( searchInput, `${PREFIX}-item`, { handleSearchResult });
};

/**
 * Manages UI state based on search results and input value.
 *
 * @since x.x
 *
 * @param {Object} args Contains flags for search status.
 * @param {boolean} args.foundSomething True if search yielded results.
 * @param {boolean} args.notEmptySearchText True if search input is not empty.
 */
function handleSearchResult({ foundSomething, notEmptySearchText }) {
	const appState = getAppState();

	setAppStateProperty( 'notEmptySearchText', notEmptySearchText );

	// Revert to 'All Templates' if search and selected category are both empty
	if ( ! appState.notEmptySearchText && ! appState.selectedCategory ) {
		allTemplatesCategory.dispatchEvent( new Event( 'click', { 'bubbles': true }) );
		return;
	}

	// Show empty state if no templates found
	if ( ! foundSomething ) {
		showSearchEmptyState();
		return;
	}

	// Hide empty state if currently displayed
	hide( emptyState );

	// Switch to displaying search results if a category is selected
	if ( appState.selectedCategory ) {
		showSearchResults();

		// Clear selected category, acting as a flag to indicate search result state
		setAppStateProperty( 'selectedCategory', '' );
	}
};

/**
 * Resets the value of the search input and triggers an input event.
 *
 * @since x.x
 */
export function resetSearchInput() {
	searchInput.value = '';
	searchInput.dispatchEvent( new Event( 'input', { 'bubbles': true }) );
}

export default addSearchEvents;
