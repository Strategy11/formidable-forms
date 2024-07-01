/**
 * External dependencies
 */
import { getElements, addElements, PREFIX as SKELETON_PREFIX } from 'core/page-skeleton';

/**
 * Internal dependencies
 */
import { VIEWS } from '../constants';

const { bodyContent, sidebar } = getElements();

const categories = {
	availableCategory: sidebar.querySelector(
		`.${SKELETON_PREFIX}-cat[data-category="${VIEWS.AVAILABLE}"]`
	),
	activeCategory: sidebar.querySelector(
		`.${SKELETON_PREFIX}-cat[data-category="${VIEWS.ACTIVE}"]`,
	),
};

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
