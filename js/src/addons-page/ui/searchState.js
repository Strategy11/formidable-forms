/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import { CURRENT_CLASS } from 'core/constants';
import { frmAnimate, hideElements, hide, show, isVisible } from 'core/utils';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { getSingleState } from '../shared';
import { showEmptyState } from '.';

/**
 * Updates the UI to display the search state.
 *
 * @param {boolean} notEmptySearchText True if search input is not empty.
 * @return {void}
 */
export function showSearchState( notEmptySearchText ) {
	const { bodyContent, bodyContentChildren, addonsList } = getElements();

	// Remove highlighting from the currently selected category if the search text is not empty
	if ( notEmptySearchText ) {
		getSingleState( 'selectedCategoryEl' ).classList.remove(
			CURRENT_CLASS
		);
	}

	// Hide non-relevant elements in the body content
	hideElements( bodyContentChildren );

	show( addonsList );

	// Smoothly display the updated UI elements
	new frmAnimate( bodyContent ).fadeIn();
}

/**
 * Displays search results based on search outcome.
 *
 * @param {boolean} foundSomething True if search yielded results.
 * @return {void}
 */
export function displaySearchElements( foundSomething ) {
	// Show empty state if no templates found
	if ( ! foundSomething ) {
		showEmptyState();
		return;
	}

	// Hide empty state if currently displayed
	const { emptyState } = getElements();
	if ( isVisible( emptyState ) ) {
		hide( emptyState );
	}
}
