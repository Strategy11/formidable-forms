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

	initSearch( searchInput, `${PREFIX}-item`, {
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
function handleSearchResult({ foundSomething, notEmptySearchText }, event ) {
	// Prevent double calls as window.frmDom.search.init attaches both 'input' and 'search' events,
	// triggering this method twice on 'x' button click.
	if ( event && event.type === 'search' && event.target.value === '' ) {
		return;
	}

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

	// Switch to displaying search results
	if ( appState.selectedCategory ) {
		showSearchResults( notEmptySearchText );

		// Setting "selectedCategory" to an empty string as a flag for search state
		if ( notEmptySearchText ) {
			setAppStateProperty( 'selectedCategory', '' );
		}
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
