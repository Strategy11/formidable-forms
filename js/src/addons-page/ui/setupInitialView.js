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
import { categorizedAddons } from '../addons';
import { VIEWS } from '../constants';

/**
 * Sets up the initial view, performing any required
 * DOM manipulations for proper element presentation.
 *
 * @return {void}
 */
export function setupInitialView() {
	const { sidebar, searchInput, bodyContent } = getElements();

	const bodyContentAnimate = new frmAnimate( bodyContent );

	// Clear the value in the search input
	searchInput.value = '';

	setupAvailableCategory();
	setupActiveCategory();
	setupAllAddonsCategory();

	// Smoothly display the updated UI elements
	bodyContent.classList.remove( HIDE_JS_CLASS );
	sidebar.classList.remove( HIDE_JS_CLASS );
	bodyContentAnimate.fadeIn();
	show( sidebar );
}

/**
 * Sets up the "Available" category, updating the
 * categorizedAddons object and the category count.
 *
 * @private
 * @return {void}
 */
function setupAvailableCategory() {
	const { availableCategory, availableAddons } = getElements();

	categorizedAddons[ VIEWS.AVAILABLE ] = availableAddons;

	// Set "Available" category count
	if ( availableCategory ) {
		availableCategory.querySelector(
			`.${ SKELETON_PREFIX }-cat-count`
		).textContent = availableAddons.length;
	}
}

/**
 * Sets up the "Active" category, updating the
 * categorizedAddons object and the category count.
 *
 * @return {void}
 */
export function setupActiveCategory() {
	const { activeCategory } = getElements();
	const activeAddons = document.querySelectorAll('.frm-addon-active:not(.frm-locked-item)');

	categorizedAddons[ VIEWS.ACTIVE ] = activeAddons;

	// Set "Active" category count
	activeCategory.querySelector( `.${ SKELETON_PREFIX }-cat-count` ).textContent = activeAddons.length;
}

/**
 * Sets up the "All Add-Ons" category, updating the
 * category count.
 *
 * @private
 * @return {void}
 */
function setupAllAddonsCategory() {
	const { allItemsCategory, addons } = getElements();

	// Set "All Add-Ons" category count
	allItemsCategory.querySelector(
		`.${ SKELETON_PREFIX }-cat-count`
	).textContent = addons.length;
}
