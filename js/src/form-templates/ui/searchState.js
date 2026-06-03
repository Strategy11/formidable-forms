/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import { CURRENT_CLASS } from 'core/constants';
import { frmAnimate, showElements, hideElements, show, hide, isVisible } from 'core/utils';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { getSingleState } from '../shared';
import { showSearchEmptyState, updatePageTitle } from '.';

/**
 * Updates the UI to display the search state.
 *
 * @param {boolean} notEmptySearchText True if search input is not empty.
 * @return {void}
 */
export function showSearchState( notEmptySearchText ) {
	const { bodyContent, bodyContentChildren, pageTitle, templatesList, applicationTemplates } = getElements();
	const bodyContentAnimate = new frmAnimate( bodyContent );

	// Remove highlighting from the currently selected category if the search text is not empty
	if ( notEmptySearchText ) {
		getSingleState( 'selectedCategoryEl' ).classList.remove( CURRENT_CLASS );
	}

	// Hide non-relevant elements in the body content
	hideElements( bodyContentChildren );

	// Update the page title and display relevant elements
	updatePageTitle( __( 'Search Result', 'formidable' ) );
	showElements( [ pageTitle, templatesList, applicationTemplates ] );

	// Smoothly display the updated UI elements
	bodyContentAnimate.fadeIn();
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
		showSearchEmptyState();
		return;
	}

	// Hide empty state if currently displayed
	const { emptyState } = getElements();
	if ( isVisible( emptyState ) ) {
		const { pageTitle } = getElements();
		hide( emptyState );
		show( pageTitle );
	}

	const { templatesList, applicationTemplates, applicationTemplatesTitle, applicationTemplatesList } = getElements();

	showElements( [ templatesList, applicationTemplates, applicationTemplatesTitle ] );

	if ( templatesList.offsetHeight === 0 ) {
		hideElements( [ templatesList, applicationTemplatesTitle ] );
	}

	if ( applicationTemplatesList?.offsetHeight === 0 ) {
		hide( applicationTemplates );
	}
}
