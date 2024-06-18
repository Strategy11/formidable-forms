/**
 * Internal dependencies
 */
import { createPageElements } from 'core/factory';

/**
 * Internal dependencies
 */
import { PREFIX } from '../constants';
import { createEmptyStateElement, getEmptyStateElements } from './emptyStateElement';

const bodyContent = document.getElementById( 'post-body-content' );

// Sidebar Elements
const sidebar = document.getElementById( `${PREFIX}-sidebar` );
const searchInput = sidebar.querySelector( '.frm-search-input' );
const categoryItems = sidebar.querySelectorAll( `.${PREFIX}-cat` );

// Empty State Elements
const emptyState = createEmptyStateElement();
bodyContent?.appendChild( emptyState );
const emptyStateElements = getEmptyStateElements();

export const { getElements, addElements } = createPageElements({
	bodyContent,
	sidebar,
	searchInput,
	categoryItems,
	...emptyStateElements,
});
