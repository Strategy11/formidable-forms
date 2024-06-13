/**
 * External dependencies
 */
import { CURRENT_CLASS } from 'core/constants';
import { onClickPreventDefault, frmAnimate } from 'core/utils';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { getState, setState } from '../shared';
import { resetSearchInput } from '.';

/**
 * Manages event handling for sidebar category links.
 *
 * @return {void}
 */
function addCategoryEvents() {
	const { categoryItems } = getElements();

	// Attach click and keyboard event listeners to each sidebar category
	categoryItems.forEach( ( category ) => {
		onClickPreventDefault( category, onCategoryClick );
		category.addEventListener( 'keydown', onCategoryKeydown );
	});
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
	let { selectedCategory, selectedCategoryEl, hasSearchQuery } = getState();

	// If the selected category hasn't changed, return early
	if ( selectedCategory === newSelectedCategory ) {
		return;
	}

	/**
	 * Filter hook to modify the selected category
	 *
	 * @param {string} selectedCategory The selected category
	 */
	selectedCategory = wp.hooks.applyFilters(
		'frmPageSidebar.selectedCategory',
		newSelectedCategory
	);

	// Highlight the newly clicked category and update the application state
	selectedCategoryEl.classList.remove( CURRENT_CLASS );
	selectedCategoryEl = clickedCategory;
	selectedCategoryEl.classList.add( CURRENT_CLASS );
	setState( { selectedCategory, selectedCategoryEl });

	// Reset the search input if it contains text
	if ( hasSearchQuery ) {
		resetSearchInput();
	}

	/**
	 * Trigger custom event to update category content in the sidebar.
	 *
	 * @param {Object} detail                  The detail object to pass with the event.
	 * @param {string} detail.selectedCategory The selected category.
	 */
	dispatchCustomEvent( 'layout:sidebar:updateCategoryContent', {
		selectedCategory,
	});

	// Smoothly display the updated UI elements
	const { bodyContent } = getElements();
	new frmAnimate( bodyContent ).fadeIn();
};

/**
 * Handles the keyboard event on a category item.
 *
 * @param {KeyboardEvent} event The keyboard event object.
 * @return {void}
 */
function onCategoryKeydown( event ) {
	// Only respond to 'Enter' or 'Space' key presses
	if ( event.key === 'Enter' || event.key === ' ' ) {
		event.preventDefault();
		onCategoryClick( event );
	}
}

export default addCategoryEvents;
