/**
 * External dependencies
 */
import { HIDE_JS_CLASS } from 'core/constants';
import { frmAnimate, hasQueryParam, hideElements, show, hide } from 'core/utils';
import { PREFIX as SKELETON_PREFIX } from 'core/page-skeleton';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { getState } from '../shared';
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
		availableTemplatesCategory
	} = getElements();

	const bodyContentAnimate = new frmAnimate( bodyContent );

	searchInput.value = '';

	// Hide the twin featured template items
	hideElements( twinFeaturedTemplateItems );

	// Set the 'Available Templates' count if it is present
	if ( availableTemplatesCategory ) {
		const { availableTemplatesCount } = getState();
		availableTemplatesCategory.querySelector( `.${SKELETON_PREFIX}-cat-count` ).textContent = availableTemplatesCount;
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
