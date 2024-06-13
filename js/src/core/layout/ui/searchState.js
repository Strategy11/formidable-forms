/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import { frmAnimate } from 'core/utils';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { CURRENT_CLASS, getSingleState } from '../shared';
import { hideElements } from '../utils';

/**
 * Updates the UI to display the search state.
 *
 * @param {boolean} hasSearchQuery True if search input is not empty.
 * @return {void}
 */
export function showSearchState( hasSearchQuery ) {
	// Remove highlighting from the currently selected category if the search text is not empty
	if ( hasSearchQuery ) {
		getSingleState( 'selectedCategoryEl' ).classList.remove( CURRENT_CLASS );
	}

	const { bodyContent, bodyContentChildren } = getElements();

	// Hide non-relevant elements in the body content
	hideElements( bodyContentChildren );

	/**
	 * Action to update the UI to display the search state.
	 *
	 * @param {boolean} hasSearchQuery True if search input is not empty.
	 */
	wp.hooks.doAction( 'frmPageSidebar.updateSearchUI', { hasSearchQuery } );

	// Smoothly display the updated UI elements
	new frmAnimate( bodyContent ).fadeIn();
}
