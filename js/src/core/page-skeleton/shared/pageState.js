/**
 * External dependencies
 */
import { createPageState } from 'core/factory';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { VIEWS } from '../constants';

const { allItemsCategory } = getElements();

export const { getState, getSingleState, setState, setSingleState } =
	createPageState( {
		notEmptySearchText: false,
		selectedCategory: VIEWS.ALL_ITEMS,
		selectedCategoryEl: allItemsCategory,
	} );
