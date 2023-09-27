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
import { setAppStateProperty } from '../shared';
import { onClickPreventDefault } from '../utils';
import { resetSearchInput } from './';

/**
 * Manages event handling for the empty state button.
 *
 * @return {void}
 */
function addEmptyStateButtonEvents() {
	const { emptyStateButton } = getElements();

	// Attach click event listener to the button
	onClickPreventDefault( emptyStateButton, onEmptyStateButtonClick );
}

/**
 * Handles the click event on the empty state button.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
const onEmptyStateButtonClick = ( event ) => {
	const { searchInput } = getElements();

	// Set selectedCategory to '' as search state flag that triggers 'allTemplates' category if search input is empty
	// @see searchListener.js: handleSearchResult method
	setAppStateProperty( 'selectedCategory', '' );
	resetSearchInput();
	searchInput.focus();
};

export default addEmptyStateButtonEvents;