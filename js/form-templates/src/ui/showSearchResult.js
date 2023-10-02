/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { CURRENT_CLASS, getAppStateProperty } from '../shared';
import { showElements, hideElements, fadeIn } from '../utils';
import { updatePageTitle } from './';

/**
 * Updates the UI to display the search results.
 *
 * @param {boolean} notEmptySearchText True if search input is not empty.
 * @return {void}
 */
export function showSearchResults( notEmptySearchText ) {
	const { bodyContent, bodyContentChildren, pageTitle, templatesList } = getElements();

	// Remove highlighting from the currently selected category if the search text is not empty
	if ( notEmptySearchText ) {
		getAppStateProperty( 'selectedCategoryEl' ).classList.remove( CURRENT_CLASS );
	}

	// Hide non-relevant elements in the body content
	hideElements( bodyContentChildren );

	// Update the page title and display relevant elements
	updatePageTitle( __( 'Search Result', 'formidable' ) );
	showElements([ pageTitle, templatesList ]);

	// Smoothly display the updated UI elements
	fadeIn( bodyContent );
};
