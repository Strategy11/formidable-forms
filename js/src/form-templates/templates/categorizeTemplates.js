/**
 * Internal dependencies
 */
import { getElements } from '../elements';

export const categorizedTemplates = {};

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
