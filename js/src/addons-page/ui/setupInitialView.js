/**
 * External dependencies
 */
import { HIDE_JS_CLASS } from 'core/constants';
import { frmAnimate, hideElements, show } from 'core/utils';
import { PREFIX as SKELETON_PREFIX } from 'core/page-skeleton';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { categorizedAddons } from '../addons';
import { PREFIX, VIEWS } from '../constants';

const CAT_COUNT_CLASS = `.${ SKELETON_PREFIX }-cat-count`;

/**
 * Sets up the initial view, performing any required
 * DOM manipulations for proper element presentation.
 *
 * @return {void}
 */
export function setupInitialView() {
	const { sidebar, searchInput, bodyContent } = getElements();

	const bodyContentAnimate = new frmAnimate( bodyContent );

	searchInput.value = '';

	setupActiveCategory();
	setupAvailableCategory();
	setupAllAddonsCategory();
	setupPlansCategory();

	// Smoothly display the updated UI elements
	bodyContent.classList.remove( HIDE_JS_CLASS );
	sidebar.classList.remove( HIDE_JS_CLASS );
	bodyContentAnimate.fadeIn();
	show( sidebar );
}

/**
 * Sets up the "Active" category, updating the
 * categorizedAddons object and the category count.
 *
 * @return {void}
 */
export function setupActiveCategory() {
	const { activeCategory, availableCategory, categoriesTopDivider } =
		getElements();
	const activeAddons = document.querySelectorAll(
		'.frm-addon-active:not(.frm-locked-item)'
	);

	if ( activeAddons.length === 0 ) {
		hideElements( [
			activeCategory,
			availableCategory,
			categoriesTopDivider,
		] );
		return;
	}

	categorizedAddons[ VIEWS.ACTIVE ] = activeAddons;

	// Set "Active" category count
	activeCategory.querySelector( CAT_COUNT_CLASS ).textContent = activeAddons.length;
}

/**
 * Sets up the "Available" category, updating the
 * categorizedAddons object and the category count.
 *
 * @private
 * @return {void}
 */
function setupAvailableCategory() {
	const { bodyContent, availableCategory, availableAddons } = getElements();

	categorizedAddons[ VIEWS.AVAILABLE ] = availableAddons;

	// Set "Available" category count
	if ( availableCategory ) {
		availableCategory.querySelector( CAT_COUNT_CLASS ).textContent = availableAddons.length;

		const upgradeBannerAvailableCount = bodyContent.querySelector(
			`.${ PREFIX }-available-count`
		);
		if ( upgradeBannerAvailableCount ) {
			upgradeBannerAvailableCount.textContent = availableAddons.length;
		}
	}
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
	allItemsCategory.querySelector( CAT_COUNT_CLASS ).textContent = addons.length;
}

/**
 * Sets up the "All Add-Ons" category, updating the
 * category count.
 *
 * @private
 * @return {void}
 */
function setupPlansCategory() {
	const {
		basicPlanCategory,
		plusPlanCategory,
		businessPlanCategory,
		elitePlanCategory,
	} = getElements();

	const getCount = category => parseInt( category.querySelector( CAT_COUNT_CLASS ).textContent, 10 ) || 0;

	// The "Formidable Pro" add-on is included in all plans, so we just consider that in the basicCount
	const basicCount = getCount( basicPlanCategory );
	const plusCount = getCount( plusPlanCategory ) - 1;
	const businessCount = getCount( businessPlanCategory ) - 1;
	const eliteCount = getCount( elitePlanCategory ) - 1;

	// Update the text content for each category
	plusPlanCategory.querySelector( CAT_COUNT_CLASS ).textContent = basicCount + plusCount;
	businessPlanCategory.querySelector( CAT_COUNT_CLASS ).textContent = basicCount + plusCount + businessCount;
	elitePlanCategory.querySelector( CAT_COUNT_CLASS ).textContent = basicCount + plusCount + businessCount + eliteCount;
}
