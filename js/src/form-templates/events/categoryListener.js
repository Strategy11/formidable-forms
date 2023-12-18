/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { PREFIX, CURRENT_CLASS, getAppState, setAppState } from '../shared';
import { showSelectedCategory } from '../ui';
import { onClickPreventDefault } from '../utils';
import { resetSearchInput } from './';
import { FrmAnimate } from '../../common/utilities/animation';

/**
 * Manages event handling for sidebar category links.
 *
 * @return {void}
 */
function addCategoryEvents() {
	const categoryItems = document.querySelectorAll( `.${PREFIX}-cat-item` );

	// Attach click and keyboard event listeners to each sidebar category
	categoryItems.forEach( category => {
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
	const clickedCategory     = event.currentTarget;
	const newSelectedCategory = clickedCategory.getAttribute( 'data-category' );
	const { bodyContent }     = getElements();
	const bodyContentAnimate  = new FrmAnimate( bodyContent );
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
	bodyContentAnimate.fadeIn();
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
        event.preventDefault(); // Prevent default action
        onCategoryClick( event );
    }
}

export default addCategoryEvents;
