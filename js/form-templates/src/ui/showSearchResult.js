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
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { CURRENT_CLASS, getAppStateProperty } from '../shared';
import { showElements, hideElements, fadeIn } from '../utils';
import { updatePageTitle } from './';

/**
 * Updates the UI to display the search results.
 *
 * @return {void}
 */
export function showSearchResults() {
	const { bodyContent, bodyContentChildren, pageTitle, templatesList } = getElements();

	// Remove highlighting from the currently selected category
	getAppStateProperty( 'selectedCategoryEl' ).classList.remove( CURRENT_CLASS );

	// Hide non-relevant elements in the body content
	hideElements( bodyContentChildren );

	// Update the page title and display relevant elements
	updatePageTitle( __( 'Search Result', 'formidable' ) );
	showElements([ pageTitle, templatesList ]);

	// Smoothly display the updated UI elements
	fadeIn( bodyContent );
};
