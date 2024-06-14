/**
 * External dependencies
 */
import { frmAnimate, hasQueryParam } from 'core/utils';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { HIDE_JS_CLASS, getState } from '../shared';
import { show, hide, hideElements } from '../utils';
import { showHeaderCancelButton } from './';

/**
 * Sets up the initial view, performing any required
 * DOM manipulations for proper element presentation.
 *
 * @return {void}
 */
function setupInitialView() {
	const {
		sidebar,
		searchInput,
		bodyContent,
		twinFeaturedTemplateItems,
		availableTemplatesCategory,
		freeTemplatesCategory
	} = getElements();

	const bodyContentAnimate = new frmAnimate( bodyContent );

	// Clear the value in the search input
	searchInput.value = '';

	// Hide the twin featured template items
	hideElements( twinFeaturedTemplateItems );

	// Set the 'Available Templates' count if it is present
	if ( availableTemplatesCategory ) {
		const { availableTemplatesCount } = getState();
		availableTemplatesCategory.querySelector( '.frm-form-templates-cat-count' ).textContent = availableTemplatesCount;
	}

	// Update the 'Free Templates' count and hide the category if count is zero
	const { freeTemplatesCount } = getState();
	freeTemplatesCategory.querySelector( '.frm-form-templates-cat-count' ).textContent = freeTemplatesCount;
	if ( 0 === freeTemplatesCount ) {
		hide( freeTemplatesCategory );
	}

	// Update extra templates count
	const { extraTemplateCountElements } = getElements();
	const { extraTemplatesCount } = getState();
	extraTemplateCountElements.forEach( element => element.textContent = extraTemplatesCount );

	// Smoothly display the updated UI elements
	bodyContent.classList.remove( HIDE_JS_CLASS );
	sidebar.classList.remove( HIDE_JS_CLASS );
	bodyContentAnimate.fadeIn();
	show( sidebar );

	// Show the "Cancel" button in the header if the 'return_page' query param is present
	if ( hasQueryParam( 'return_page' ) ) {
		showHeaderCancelButton();
	}
}

export default setupInitialView;
