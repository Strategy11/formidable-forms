/**
 * Internal dependencies
 */
import { createPageElements } from 'core/factory';

/**
 * Internal dependencies
 */
import { PREFIX, VIEW_SLUGS } from '../constants';
import { createEmptyStateElement, getEmptyStateElements } from './emptyStateElement';

const bodyContent = document.getElementById( 'post-body-content' );

// Sidebar Elements
const sidebar = document.getElementById( `${PREFIX}-panel` );
const searchInput = sidebar.querySelector( '.frm-search-input' );
const categoryItems = sidebar.querySelectorAll( `.${PREFIX}-cat` );
const allItemsCategory = sidebar.querySelector(
	`.${PREFIX}-cat[data-category="${VIEW_SLUGS.ALL_ITEMS}"]`
);

// Empty State Elements
const emptyState = createEmptyStateElement();
bodyContent?.appendChild( emptyState );
const emptyStateElements = getEmptyStateElements();

export const { getElements, addElements } = createPageElements({
	bodyContent,
	sidebar,
	searchInput,
	categoryItems,
	allItemsCategory,
	...emptyStateElements,
});
