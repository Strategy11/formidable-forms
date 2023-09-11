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
import getElements from '../elements';
import { categorizedTemplates } from '../templates';
import {
	getAppState,
	PREFIX,
	ALL_TEMPLATES_SLUG,
	FAVORITES_SLUG,
	CUSTOM_SLUG
} from '../shared';
import {
	show,
	hide,
	showElements,
	hideElements,
	isFavoriteTemplate
} from '../utils';
import { updatePageTitle } from '.';

const {
	bodyContent,
	bodyContentChildren,
	pageTitle,
	templatesList,
	templateItems,
	twinFeaturedTemplateItems,
	customTemplatesSection,
	customTemplatesTitle,
	customTemplatesList,
	customTemplateItems
} = getElements();

/**
 * Show templates based on selected category.
 *
 * @param {string} selectedCategory The selected category to display templates for.
 * @return {void}
 */
export function showSelectedCategory( selectedCategory ) {
	updatePageTitle();
	show( pageTitle );

	switch ( selectedCategory ) {
		case ALL_TEMPLATES_SLUG:
			showAllTemplates();
			break;
		case FAVORITES_SLUG:
			showFavoriteTemplates();
			break;
		case CUSTOM_SLUG:
			showCustomTemplates();
			break;
		default:
			// Clear the stage for new content
			hideElements([ ...bodyContentChildren, ...templateItems ]);

			showElements([
				templatesList,
				...categorizedTemplates[ selectedCategory ]
			]);
			break;
	}
}

/**
 * Shows all templates when 'All Templates' is the selected category.
 *
 * @return {void}
 */
export function showAllTemplates() {
	showElements([ ...bodyContentChildren, ...templateItems ]);
	hideElements([ ...twinFeaturedTemplateItems, customTemplatesSection ]);
}

/**
 * Shows favorite templates.
 *
 * @return {void}
 */
export function showFavoriteTemplates() {
	// Clear the stage for new content
	hideElements([ ...bodyContentChildren, ...templateItems ]);

	const { favoritesCount } = getAppState();
	const elementsToShow = [];

	// Get all favorite items from the DOM and add the elements to show
	const favoriteItems = bodyContent.querySelectorAll(
		`.${ PREFIX }-favorite-item`
	);
	elementsToShow.push( ...favoriteItems );

	// Add default favorites if available
	if ( favoritesCount.default > 0 ) {
		elementsToShow.push( templatesList );
	}

	// Add custom favorites if available
	if ( favoritesCount.custom > 0 ) {
		const nonFavCustomTemplates = Array.from( customTemplateItems ).filter(
			( template ) => ! isFavoriteTemplate( template )
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
	// Clear the stage for new content
	hideElements( bodyContentChildren );

	showElements([
		customTemplatesSection,
		customTemplatesList,
		...customTemplateItems
	]);
}

export default showSelectedCategory;
