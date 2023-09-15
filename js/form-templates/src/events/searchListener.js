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
import { PREFIX, getAppState, setAppStateProperty, initSearch } from '../shared';
import { showSearchEmptyState, showSearchResults } from '../ui';
import { isVisible, show, hide } from '../utils';

/**
 * Adds search-related event listeners by calling the 'initSearch' function.
 *
 * @see frmDom.search method
 * @return {void}
 */
function addSearchEvents() {
	const { searchInput } = getElements();

	initSearch( searchInput, `${ PREFIX }-item`, {
		handleSearchResult
	});
}

/**
 * Manages UI state based on search results and input value.
 *
 * @private
 * @param {Object} args Contains flags for search status.
 * @param {boolean} args.foundSomething True if search yielded results.
 * @param {boolean} args.notEmptySearchText True if search input is not empty.
 * @return {void}
 */
function handleSearchResult({ foundSomething, notEmptySearchText }) {
	const appState = getAppState();
	const { allTemplatesCategory, emptyState } = getElements();

	setAppStateProperty( 'notEmptySearchText', notEmptySearchText );

	// Revert to 'All Templates' if search and selected category are both empty
	if ( ! appState.notEmptySearchText && ! appState.selectedCategory ) {
		allTemplatesCategory.dispatchEvent(
			new Event( 'click', { bubbles: true })
		);

		return;
	}

	// Show empty state if no templates found
	if ( ! foundSomething ) {
		showSearchEmptyState();
		return;
	}

	// Hide empty state if currently displayed
	if ( isVisible( emptyState ) ) {
		hide( emptyState );

		const { pageTitle } = getElements();
		show( pageTitle );
	}

	// Switch to displaying search results if a category is selected
	if ( appState.selectedCategory ) {
		showSearchResults();

		// Clear selected category, acting as a flag to indicate search result state
		setAppStateProperty( 'selectedCategory', '' );
	}
}

/**
 * Handles the click event on the empty state button.
 *
 * @param {Event} event The click event object.
 * @return {void}
 */
export const onEmptyStateButtonClick = ( event ) => {
	const { searchInput } = getElements();
	resetSearchInput();
	searchInput.focus();
};

/**
 * Resets the value of the search input and triggers an input event.
 *
 * @return {void}
 */
export function resetSearchInput() {
	const { searchInput } = getElements();

	searchInput.value = '';
	searchInput.dispatchEvent( new Event( 'input', { bubbles: true }) );
}

export default addSearchEvents;
