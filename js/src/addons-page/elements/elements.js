/**
 * External dependencies
 */
import { getElements, addElements, PREFIX as SKELETON_PREFIX } from 'core/page-skeleton';

/**
 * Internal dependencies
 */
import { VIEWS } from '../constants';

const { bodyContent, sidebar } = getElements();

const addonsToggle = bodyContent.querySelectorAll( '.frm_toggle_block' );

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
	activeAddons: bodyContent.querySelectorAll('.frm-addon-active:not(.frm-locked-item)'),
	availableAddons: bodyContent.querySelectorAll('.frm-card-item:not(.frm-locked-item)')
};

// Add children of the bodyContent to the elements object.
const bodyContentChildren = bodyContent?.children;

addElements({
	addonsToggle,
	...categories,
	...cards,
	bodyContentChildren
});

export { getElements, addElements };
