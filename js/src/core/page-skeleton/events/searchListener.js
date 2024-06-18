/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { SEARCH_RESULT_ITEM, getState, setSingleState } from '../shared';
import { showSearchState } from '../ui';

const { init: initSearch } = window.frmDom.search;

/**
 * Adds search-related event listeners by calling the 'initSearch' function.
 *
 * @see frmDom.search method
 * @return {void}
 */
function addSearchEvents() {
	const { searchInput } = getElements();

	initSearch( searchInput, SEARCH_RESULT_ITEM, { handleSearchResult });
}

/**
 * Manages UI state based on search results and input value.
 *
 * @private
 * @param {Object}  args                    Contains flags for search status.
 * @param {boolean} args.foundSomething     True if search yielded results.
 * @param {boolean} args.notEmptySearchText True if search input is not empty.
 * @param {Event}   event                   The event object from input, search, or change events.
 * @return {void}
 */
function handleSearchResult( { foundSomething, notEmptySearchText }, event ) {
	// Avoid double calls from 'input' and 'search' events triggered by the 'clear' button.
	if ( event && event.type === 'search' && event.target.value === '' ) {
		return;
	}

	const appState = getState();
	setSingleState( 'hasSearchQuery', notEmptySearchText );

	// Show ALL_ITEMS if both the search query and the selected category are empty
	if ( ! appState.hasSearchQuery && ! appState.selectedCategory ) {
		const { allItemsCategory } = getElements();
		allItemsCategory.dispatchEvent( new Event( 'click', { bubbles: true }) );

		return;
	}

	// Display the search state if a category is selected
	if ( appState.selectedCategory ) {
		showSearchState( notEmptySearchText );

		// Setting "selectedCategory" to an empty string as a flag for search state
		if ( notEmptySearchText ) {
			setSingleState( 'selectedCategory', '' );
		}
	}

	/**
	 * Action to update the UI elements based on search results.
	 *
	 * @param {boolean} foundSomething     True if search yielded results.
	 * @param {boolean} notEmptySearchText True if search input is not empty.
	 */
	wp.hooks.doAction( 'frmPageSidebar.displaySearchElements', {
		foundSomething,
		notEmptySearchText,
	});
}

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
