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
import { hasQueryParam } from '../shared';
import { hideElements, fadeIn } from '../utils';
import { showHeaderCancelButton } from './';

/**
 * Sets up the initial view, performing any required
 * DOM manipulations for proper element presentation.
 *
 * @return {void}
 */
function setupInitialView() {
	const { searchInput, bodyContent, twinFeaturedTemplateItems } = getElements();

	// Clear the value in the search input
	searchInput.value = '';

	// Hide the twin featured template items
	hideElements( twinFeaturedTemplateItems );

	// Smoothly display the updated UI elements
	fadeIn( bodyContent );

	// Show the "Cancel" button in the header if the 'return_page' query param is present
	if ( hasQueryParam( 'return_page' ) ) {
		showHeaderCancelButton();
	}
}

export default setupInitialView;
