/**
 * External dependencies
 */
import { createPageState } from 'core/factory';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { VIEW_SLUGS } from '.';

const { allItemsCategory } = getElements();

export const {
	initializePageState,
	getState,
	getSingleState,
	setState,
	setSingleState,
} = createPageState({
	hasSearchQuery: false,
	selectedCategory: VIEW_SLUGS.ALL_ITEMS,
	selectedCategoryEl: allItemsCategory,
});
