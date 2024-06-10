/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { CURRENT_CLASS, getAppStateProperty } from '../shared';
import { hideElements, frmAnimate } from '../utils';

/**
 * Updates the UI to display the search state.
 *
 * @param {boolean} notEmptySearchText True if search input is not empty.
 * @return {void}
 */
export function showSearchState( notEmptySearchText ) {
	// Remove highlighting from the currently selected category if the search text is not empty
	if ( notEmptySearchText ) {
		getAppStateProperty( 'selectedCategoryEl' ).classList.remove( CURRENT_CLASS );
	}

	const { bodyContent, bodyContentChildren } = getElements();

	// Hide non-relevant elements in the body content
	hideElements( bodyContentChildren );

	/**
	 * Action to update the UI to display the search state.
	 *
	 * @param {boolean} notEmptySearchText True if search input is not empty.
	 */
    wp.hooks.doAction( 'frmPageSidebar.updateSearchUI', { notEmptySearchText } );

	// Smoothly display the updated UI elements
	new frmAnimate( bodyContent ).fadeIn();
};
