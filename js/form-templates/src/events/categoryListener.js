/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { PREFIX, CURRENT_CLASS, getAppState, setAppState } from '../shared';
import { showSelectedCategory } from '../ui';
import { fadeIn, onClickPreventDefault } from '../utils';
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
		onClickPreventDefault( category, onCategoryClick )
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
