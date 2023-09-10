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
import { searchInput, bodyContent, twinFeaturedTemplateItems } from '../elements';
import { show, fadeIn, hideElements } from '../utils';

/**
 * Sets up the initial view, performing any required
 * DOM manipulations for proper element presentation.
 *
 * @since x.x
 */
function setupInitialView() {
	// Clear the value in the search input
	searchInput.value = '';

	// Hide the twin featured template items
	hideElements( twinFeaturedTemplateItems );

	// Display the main body content and gradually reveal the updated UI elements
	show( bodyContent );
	fadeIn( bodyContent );
};

export default setupInitialView;
