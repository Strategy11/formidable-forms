/**
 * External dependencies
 */
import { showElements, hideElements, show, hide } from 'core/utils';
import { VIEWS as SKELETON_VIEWS } from 'core/page-skeleton';

/**
 * Internal dependencies
 */
import { getElements } from "../elements";
import { categorizedAddons } from '../addons';
import { showEmptyState } from '.';

/**
 * Show addons based on selected category.
 *
 * @param {string} selectedCategory The selected category to display addons for.
 * @return {void}
 */
export function showSelectedCategory( selectedCategory ) {
	const { addons, emptyState, upgradeBanner } = getElements();

	hide( emptyState );
	show( upgradeBanner )

	switch (selectedCategory) {
		case SKELETON_VIEWS.ALL_ITEMS:
			showElements( addons );
			break;
		default:
			hideElements( addons );
			if ( categorizedAddons[ selectedCategory ].length === 0 ) {
				showEmptyState();
				hide( upgradeBanner );
			} else {
				showElements( categorizedAddons[ selectedCategory ] );
			}
			break;
	}
}

export default showSelectedCategory;
