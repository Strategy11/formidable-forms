/**
 * Copyright (C) 2010 Formidable Forms
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

/**
 * Internal dependencies
 */
import { PREFIX } from '../shared';

/**
 * Gets DOM elements during initialization.
 *
 * @since x.x
 * @returns {Object} DOM elements gathered at initialization.
 */
function getElements() {
	// Body Elements
	const bodyContent = document.querySelector( '#post-body-content' );
	const bodyElements = {
		bodyContent,
		bodyContentChildren: Array.from( bodyContent?.children || []),
		createFormButton: document.querySelector( `#${PREFIX}-create-form` ),
		pageTitle: document.querySelector( `#${PREFIX}-page-title` ),
		upsellBanner: document.querySelector( `#${PREFIX}-upsell-banner` )
	};

	// Templates Elements
	const templatesList = document.querySelector( `#${PREFIX}-list` );
	const templates = {
		templatesList,
		featuredTemplatesList: document.querySelector( `#${PREFIX}-featured-list` ),
		templateItems: templatesList?.querySelectorAll( `.${PREFIX}-item` ),
		twinFeaturedTemplateItems: templatesList?.querySelectorAll( `.${PREFIX}-featured-item` )
	};

	// Custom Templates Section Element
	const customTemplatesSection = document.querySelector( `#${PREFIX}-custom-list-section` );
	const customTemplates = {
		customTemplatesSection,
		customTemplatesTitle: customTemplatesSection?.querySelector( `#${PREFIX}-title` ),
		customTemplatesList: customTemplatesSection?.querySelector( `#${PREFIX}-custom-list` ),
		customTemplateItems: customTemplatesSection?.querySelectorAll( `.${PREFIX}-item` )
	};

	// Sidebar Elements
	const searchInput = document.querySelector( '#template-search-input' );
	const selectedCategoryEl = document.querySelector( `.${PREFIX}-cat-item[data-category="all-templates"]` );
	const favoritesCategory = document.querySelector( `.${PREFIX}-cat-item[data-category="favorites"]` );
	const sidebar = {
		searchInput,
		selectedCategoryEl,
		favoritesCategory,
		favoritesCategoryCountEl: favoritesCategory?.querySelector( `.${PREFIX}-cat-count` ),
		allTemplatesCategory: selectedCategoryEl
	};

	// Empty State Elements
	const emptyState = document.querySelector( `#${PREFIX}-empty-state` );
	const emptyStateElements = {
		emptyState,
		emptyStateTitle: emptyState?.querySelector( `.${PREFIX}-title` ),
		emptyStateText: emptyState?.querySelector( `.${PREFIX}-text` ),
		emptyStateButton: emptyState?.querySelector( `.${PREFIX}-button` )
	};

	return {
		...bodyElements,
		...templates,
		...customTemplates,
		...sidebar,
		...emptyStateElements
	};
}

export default getElements;
