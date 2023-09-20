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

export let categorizedTemplates = {};

/**
 * Builds a categorized list of templates.
 *
 * @return {void}
 */
export function buildCategorizedTemplates() {
	const { templateItems } = getElements();

	templateItems.forEach( template => {
		// Extract and split the categories from data attribute
		const categories = template.getAttribute( 'data-categories' ).split( ',' );

		categories.forEach( category => {
			// Initialize the category array if not already done
			if ( ! categorizedTemplates[ category ]) {
				categorizedTemplates[ category ] = [];
			}

			// Add the template to the appropriate category
			categorizedTemplates[ category ].push( template );
		});
	});
}
