/**
 * External dependencies
 */
import { onClickPreventDefault } from 'core/utils';
import { resetSearchInput } from 'core/page-skeleton';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { getState, setSingleState, VIEW_SLUGS } from '../shared';
import { showSearchState, displaySearchElements } from '../ui';

const { init: initSearch } = window.frmDom.search;

/**
 * Adds search-related event listeners by calling the 'initSearch' function.
 *
 * @see frmDom.search method
 * @return {void}
 */
function addSearchEvents() {
	const { searchInput, emptyStateButton } = getElements();

	initSearch( searchInput, 'frm-card-item', { handleSearchResult } );
	onClickPreventDefault( emptyStateButton, onEmptyStateButtonClick );
}

/**
 * Manages UI state based on search results and input value.
 *
 * @private
 * @param {Object}  args                    Contains flags for search status.
 * @param {boolean} args.foundSomething     True if search yielded results.
 * @param {boolean} args.notEmptySearchText True if search input is not empty.
 * @param {Event}   event                   The event object (input, search, or change event).
 * @return {void}
 */
function handleSearchResult({ foundSomething, notEmptySearchText }, event ) {
	// Prevent double calls as window.frmDom.search.init attaches both 'input' and 'search' events,
	// triggering this method twice on 'x' button click.
	if ( event && event.type === 'search' && event.target.value === '' ) {
		return;
	}

	const state = getState();
	const { allItemsCategory } = getElements();

	setSingleState( 'notEmptySearchText', notEmptySearchText );

	// Revert to 'All Templates' if search and selected category are both empty
	if ( ! state.notEmptySearchText && ! state.selectedCategory ) {
		allItemsCategory.dispatchEvent(
			new Event( 'click', { bubbles: true })
		);

		return;
	}

	// Display search state if a category is selected
	if ( state.selectedCategory ) {
		showSearchState( notEmptySearchText );

		// Setting "selectedCategory" to an empty string as a flag for search state
		if ( notEmptySearchText ) {
			setSingleState( 'selectedCategory', '' );
		}
	}

	displaySearchElements( foundSomething, notEmptySearchText );
}

/**
 * Handles the click event on the empty state button.
 *
 * @private
 * @return {void}
 */
const onEmptyStateButtonClick = () => {
	const { emptyState } = getElements();
	if ( VIEW_SLUGS.SEARCH !== emptyState.dataset?.view ) {
		return;
	}

	// Set selectedCategory to '' as search state flag that triggers ALL_ITEMS category if search input is empty
	// @see handleSearchResult()
	setSingleState( 'selectedCategory', '' );
	resetSearchInput();

	const { searchInput } = getElements();
	searchInput.focus();
};

export default addSearchEvents;
