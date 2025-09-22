/**
 * External dependencies
 */
import {
	getElements,
	addElements,
	PREFIX as SKELETON_PREFIX,
} from 'core/page-skeleton';

/**
 * Internal dependencies
 */
import { PLANS, PREFIX, VIEWS } from '../constants';

const { bodyContent, sidebar } = getElements();

bodyContent
	.querySelectorAll( '.frm-card-item:not(.plugin-card-formidable-pro)' )
	.forEach( addon => {
		const categories = addon.dataset.categories;
		switch ( true ) {
			case categories.includes( PLANS.BUSINESS ):
				addon.setAttribute(
					'data-categories',
					`${ categories },${ PLANS.ELITE }`
				);
				break;
			case categories.includes( PLANS.PLUS ):
				addon.setAttribute(
					'data-categories',
					`${ categories },${ PLANS.BUSINESS },${ PLANS.ELITE }`
				);
				break;
			case categories.includes( PLANS.BASIC ):
				addon.setAttribute(
					'data-categories',
					`${ categories },${ PLANS.PLUS },${ PLANS.BUSINESS },${ PLANS.ELITE }`
				);
				break;
		}
	} );

addElements( {
	// Body elements
	upgradeBanner: document.getElementById( 'frm-upgrade-banner' ),

	// Category elements
	availableCategory: sidebar.querySelector(
		`.${ SKELETON_PREFIX }-cat[data-category="${ VIEWS.AVAILABLE }"]`
	),
	activeCategory: sidebar.querySelector(
		`.${ SKELETON_PREFIX }-cat[data-category="${ VIEWS.ACTIVE }"]`
	),
	categoriesTopDivider: sidebar.querySelector(
		`.${ SKELETON_PREFIX }-divider`
	),
	basicPlanCategory: sidebar.querySelector(
		`.${ SKELETON_PREFIX }-cat[data-category="${ PLANS.BASIC }"]`
	),
	plusPlanCategory: sidebar.querySelector(
		`.${ SKELETON_PREFIX }-cat[data-category="${ PLANS.PLUS }"]`
	),
	businessPlanCategory: sidebar.querySelector(
		`.${ SKELETON_PREFIX }-cat[data-category="${ PLANS.BUSINESS }"]`
	),
	elitePlanCategory: sidebar.querySelector(
		`.${ SKELETON_PREFIX }-cat[data-category="${ PLANS.ELITE }"]`
	),

	// Card elements
	addonsList: document.getElementById( `${ PREFIX }-list` ),
	addons: bodyContent.querySelectorAll( '.frm-card-item' ),
	availableAddons: bodyContent.querySelectorAll(
		'.frm-card-item:not(.frm-locked-item)'
	),
	addonsToggle: bodyContent.querySelectorAll( '.frm_toggle_block' ),

	// Add children of the bodyContent to the elements object
	bodyContentChildren: bodyContent?.children,
} );

export { getElements, addElements };
