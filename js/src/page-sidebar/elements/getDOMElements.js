/**
 * Internal dependencies
 */
import { PREFIX, VIEW_SLUGS } from '../shared';

/**
 * Return essential DOM elements.
 *
 * @return {Object} The DOM elements queried and constructed into an object.
 */
function getDOMElements() {
	// Body Elements
	const bodyContent = document.getElementById( 'post-body-content' );

	// Sidebar Elements
	const sidebar = document.getElementById( `${PREFIX}-sidebar` );
	const sidebarElements = {
		searchInput: sidebar.querySelector( '.frm-search-input' ),
		categoryItems: sidebar.querySelectorAll( `.${PREFIX}-cat-item` ),
		allItemsCategory: document.querySelector(
			`.${PREFIX}-cat-item[data-category="${VIEW_SLUGS.ALL_ITEMS}"]`
		),
	};

	return {
		bodyContent,
		...sidebarElements,
	};
}

export default getDOMElements;
