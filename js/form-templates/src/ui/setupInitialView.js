/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { getAppState, hasQueryParam } from '../shared';
import { show, hide, hideElements, fadeIn } from '../utils';
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

	// Clear the value in the search input
	searchInput.value = '';

	// Hide the twin featured template items
	hideElements( twinFeaturedTemplateItems );

	// Set the 'Available Templates' count if it is present
	if ( availableTemplatesCategory ) {
		const { availableTemplatesCount } = getAppState();
		availableTemplatesCategory.querySelector( '.frm-form-templates-cat-count' ).textContent = availableTemplatesCount;
	}

	// Update the 'Free Templates' count and hide the category if count is zero
	const { freeTemplatesCount } = getAppState();
	freeTemplatesCategory.querySelector( '.frm-form-templates-cat-count' ).textContent = freeTemplatesCount;
	if ( 0 === freeTemplatesCount ) {
		hide( freeTemplatesCategory );
	}

	// Smoothly display the updated UI elements
	fadeIn( bodyContent );
	show( sidebar );

	// Show the "Cancel" button in the header if the 'return_page' query param is present
	if ( hasQueryParam( 'return_page' ) ) {
		showHeaderCancelButton();
	}
}

export default setupInitialView;
