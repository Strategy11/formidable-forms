/**
 * External dependencies
 */
import { onClickPreventDefault } from 'core/utils';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { getState, setSingleState } from '../shared';
import { isCustomCategory } from '../utils';
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
const onEmptyStateButtonClick = () => {
	const { selectedCategory } = getState();
	if ( isCustomCategory( selectedCategory ) ) {
		return;
	}

	// Set selectedCategory to '' as search state flag that triggers 'allTemplates' category if search input is empty
	// @see searchListener.js: handleSearchResult method
	setSingleState( 'selectedCategory', '' );
	resetSearchInput();

	const { searchInput } = getElements();
	searchInput.focus();
};

export default addEmptyStateButtonEvents;
