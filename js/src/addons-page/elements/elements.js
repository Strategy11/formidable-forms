/**
 * External dependencies
 */
import { getElements, addElements, PREFIX as SKELETON_PREFIX } from 'core/page-skeleton';

/**
 * Internal dependencies
 */
import { PLANS, VIEWS } from '../constants';

const { bodyContent, sidebar } = getElements();

const categories = {
	availableCategory: sidebar.querySelector(
		`.${SKELETON_PREFIX}-cat[data-category="${VIEWS.AVAILABLE}"]`
	),
	activeCategory: sidebar.querySelector(
		`.${SKELETON_PREFIX}-cat[data-category="${VIEWS.ACTIVE}"]`,
	),
	categoriesTopDivider: sidebar.querySelector( `.${SKELETON_PREFIX}-divider` ),
	basicPlanCategory: sidebar.querySelector(
		`.${SKELETON_PREFIX}-cat[data-category="${PLANS.BASIC}"]`,
	),
	plusPlanCategory: sidebar.querySelector(
		`.${SKELETON_PREFIX}-cat[data-category="${PLANS.PLUS}"]`,
	),
	businessPlanCategory: sidebar.querySelector(
		`.${SKELETON_PREFIX}-cat[data-category="${PLANS.BUSINESS}"]`,
	),
	elitePlanCategory: sidebar.querySelector(
		`.${SKELETON_PREFIX}-cat[data-category="${PLANS.ELITE}"]`,
	),
};

bodyContent.querySelectorAll( '.frm-card-item:not(.plugin-card-formidable-pro)' ).forEach(addon => {
	const categories = addon.dataset.categories;
	switch (true) {
		case categories.includes(PLANS.BUSINESS):
			addon.setAttribute('data-categories', `${categories},${PLANS.ELITE}`);
			break;
		case categories.includes(PLANS.PLUS):
			addon.setAttribute('data-categories', `${categories},${PLANS.BUSINESS},${PLANS.ELITE}`);
			break;
		case categories.includes(PLANS.BASIC):
			addon.setAttribute('data-categories', `${categories},${PLANS.PLUS},${PLANS.BUSINESS},${PLANS.ELITE}`);
			break;
	}
});

const cards = {
	addons: bodyContent.querySelectorAll( '.frm-card-item' ),
	availableAddons: bodyContent.querySelectorAll('.frm-card-item:not(.frm-locked-item)'),
	addonsToggle: bodyContent.querySelectorAll( '.frm_toggle_block' )
};

const upgradeBanner = document.getElementById( 'frm-upgrade-banner' );

// Add children of the bodyContent to the elements object.
const bodyContentChildren = bodyContent?.children;

addElements({
	...categories,
	...cards,
	upgradeBanner,
	bodyContentChildren
});

export { getElements, addElements };
