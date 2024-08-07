/**
 * External dependencies
 */
import { createPageElements } from 'core/factory';

/**
 * Internal dependencies
 */
import { PREFIX, VIEWS } from '../constants';
import {
	createEmptyStateElement,
	getEmptyStateElements,
} from './emptyStateElement';

const bodyContent = document.getElementById( 'post-body-content' );
const sidebar = document.getElementById( `${ PREFIX }-sidebar` );

// Append empty state elements to body content
const emptyState = createEmptyStateElement();
bodyContent?.appendChild( emptyState );
const emptyStateElements = getEmptyStateElements();

export const { getElements, addElements } = createPageElements( {
	bodyContent,

	// Sidebar elements
	sidebar,
	searchInput: sidebar.querySelector( '.frm-search-input' ),
	categoryItems: sidebar.querySelectorAll( `.${ PREFIX }-cat` ),
	allItemsCategory: sidebar.querySelector(
		`.${ PREFIX }-cat[data-category="${ VIEWS.ALL_ITEMS }"]`
	),

	// Empty State elements
	...emptyStateElements,
} );
