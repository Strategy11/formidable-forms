/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { VIEWS } from '../constants';

export const categorizedAddons = {};

categorizedAddons[ VIEWS.AVAILABLE ] = [];
categorizedAddons[ VIEWS.ACTIVE ] = [];

/**
 * Builds a categorized list of addons.
 *
 * @return {void}
 */
export function buildCategorizedAddons() {
	const { addons } = getElements();

	addons.forEach( ( addon ) => {
		// Extract and split the categories from data attribute
		const dataCategories = addon.getAttribute( 'data-categories' );
		if ( ! dataCategories ) {
			return;
		}

		const categories = dataCategories.split( ',' );

		categories.forEach( ( category ) => {
			// Initialize the category array if not already done
			if ( ! categorizedAddons[ category ] ) {
				categorizedAddons[ category ] = [];
			}

			// Add the addon to the appropriate category
			categorizedAddons[ category ].push( addon );
		} );
	} );
}
