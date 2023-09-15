/**
 * Copyright (C) 2023 Formidable Forms
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
import { getElements } from '../elements';
import { PREFIX, VIEW_SLUGS, getAppState } from '../shared';
import { show, hide, showElements, hideElements, isFavoriteTemplate } from '../utils';
import { categorizedTemplates } from '../templates';
import { updatePageTitle, showFavoritesEmptyState, showCustomTemplatesEmptyState } from './';

/**
 * Show templates based on selected category.
 *
 * @param {string} selectedCategory The selected category to display templates for.
 * @return {void}
 */
export function showSelectedCategory( selectedCategory ) {
	const { bodyContentChildren, pageTitle, templatesList, templateItems } = getElements();

	if ( VIEW_SLUGS.ALL_TEMPLATES !== selectedCategory ) {
		hideElements( bodyContentChildren );
	}

	updatePageTitle();
	show( pageTitle );

	switch ( selectedCategory ) {
		case VIEW_SLUGS.ALL_TEMPLATES:
			showAllTemplates();
			break;
		case VIEW_SLUGS.FAVORITES:
			showFavoriteTemplates();
			break;
		case VIEW_SLUGS.CUSTOM:
			showCustomTemplates();
			break;
		default:
			hideElements( templateItems ); // Clear the view for new content
			showElements([ templatesList, ...categorizedTemplates[ selectedCategory ] ]);
			break;
	}
}

/**
 * Shows all templates when 'All Templates' is the selected category.
 *
 * @return {void}
 */
export function showAllTemplates() {
	const {
		bodyContentChildren,
		templateItems,
		twinFeaturedTemplateItems,
		customTemplatesSection,
		emptyState
	} = getElements();

	showElements([ ...bodyContentChildren, ...templateItems ]);
	hideElements([ ...twinFeaturedTemplateItems, customTemplatesSection, emptyState ]);
}

/**
 * Shows favorite templates.
 *
 * @return {void}
 */
export function showFavoriteTemplates() {
	const { favoritesCount } = getAppState();

	if ( 0 === favoritesCount.total ) {
		showFavoritesEmptyState();
	}

	const {
		bodyContent,
		templatesList,
		templateItems,
		customTemplatesSection,
		customTemplatesTitle,
		customTemplatesList,
		customTemplateItems
	} = getElements();

	// Clear the view for new content
	hideElements( templateItems );

	const elementsToShow = [];

	// Get all favorite items from the DOM and add the elements to show
	const favoriteItems = bodyContent.querySelectorAll( `.${ PREFIX }-favorite-item` );
	elementsToShow.push( ...favoriteItems );

	// Add default favorites if available
	if ( favoritesCount.default > 0 ) {
		elementsToShow.push( templatesList );
	}

	// Add custom favorites if available
	if ( favoritesCount.custom > 0 ) {
		const nonFavCustomTemplates = Array.from( customTemplateItems ).filter(
			template => ! isFavoriteTemplate( template )
		);

		hideElements( nonFavCustomTemplates );
		elementsToShow.push( customTemplatesSection );
		elementsToShow.push( customTemplatesList );

		0 === favoritesCount.default ?
			hide( customTemplatesTitle ) :
			elementsToShow.push( customTemplatesTitle );
	}

	// Show elements that were selected to be shown
	showElements( elementsToShow );
}

/**
 * Shows custom templates.
 *
 * @return {void}
 */
export function showCustomTemplates() {
	const { customCount } = getAppState();

	if ( 0 === customCount ) {
		showCustomTemplatesEmptyState();
	}

	const { customTemplatesSection, customTemplatesList, customTemplateItems } = getElements();
	showElements([ customTemplatesSection, customTemplatesList, ...customTemplateItems ]);
}

export default showSelectedCategory;
