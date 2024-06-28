/**
 * External dependencies
 */
import { HIDE_JS_CLASS } from 'core/constants';
import { frmAnimate, show } from 'core/utils';
import { PREFIX as SKELETON_PREFIX } from 'core/page-skeleton';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';

/**
 * Sets up the initial view, performing any required
 * DOM manipulations for proper element presentation.
 *
 * @return {void}
 */
function setupInitialView() {
	const {
		sidebar,
		searchInput,
		bodyContent,
		availableCategory,
		availableAddons,
		activeCategory,
		activeAddons,
		allItemsCategory,
		addons,
	} = getElements();

	const bodyContentAnimate = new frmAnimate( bodyContent );

	// Clear the value in the search input
	searchInput.value = '';

	// Set count of categories
	if ( availableCategory ) {
		availableCategory.querySelector(
			`.${ SKELETON_PREFIX }-cat-count`
		).textContent = availableAddons.length;
	}
	activeCategory.querySelector(
		`.${ SKELETON_PREFIX }-cat-count`
	).textContent = activeAddons.length;
	allItemsCategory.querySelector(
		`.${ SKELETON_PREFIX }-cat-count`
	).textContent = addons.length;


	// Smoothly display the updated UI elements
	bodyContent.classList.remove( HIDE_JS_CLASS );
	sidebar.classList.remove( HIDE_JS_CLASS );
	bodyContentAnimate.fadeIn();
	show( sidebar );
}

export default setupInitialView;
