/**
 * Internal dependencies
*/
import { getElements } from '../elements';
import { getAppState, setAppStateProperty } from '../shared';
import { isCustomCategory, onClickPreventDefault } from '../utils';
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
	const { selectedCategory } = getAppState();
	if ( isCustomCategory( selectedCategory ) ) {
		return;
	}

	// Set selectedCategory to '' as search state flag that triggers 'allTemplates' category if search input is empty
	// @see searchListener.js: handleSearchResult method
	setAppStateProperty( 'selectedCategory', '' );
	resetSearchInput();

	const { searchInput } = getElements();
	searchInput.focus();
};

export default addEmptyStateButtonEvents;
