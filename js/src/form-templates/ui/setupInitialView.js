/**
 * External dependencies
 */
import { HIDE_JS_CLASS } from 'core/constants';
import { frmAnimate, hasQueryParam, hideElements, show, removeParamFromHistory } from 'core/utils';
import { PREFIX as SKELETON_PREFIX } from 'core/page-skeleton';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { getSingleState } from '../shared';
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
		extraTemplateCountElements,
	} = getElements();

	const bodyContentAnimate = new frmAnimate( bodyContent );

	searchInput.value = '';

	// Hide the twin featured template items
	hideElements( twinFeaturedTemplateItems );

	// Set the 'Available Templates' count if it is present
	if ( availableTemplatesCategory ) {
		availableTemplatesCategory.querySelector( `.${SKELETON_PREFIX}-cat-count` ).textContent = getSingleState( 'availableTemplatesCount' );

		// Click the 'Available Templates' category if the 'registered-for-free-templates' query param is present
		if ( hasQueryParam( 'registered-for-free-templates' ) ) {
			removeParamFromHistory( 'registered-for-free-templates' );
			setTimeout( () => {
				availableTemplatesCategory.dispatchEvent( new Event( 'click', { bubbles: true } ) );
			}, 0 );
		}
	}

	// Update extra templates count
	extraTemplateCountElements.forEach( element => element.textContent = getSingleState( 'extraTemplatesCount' ) );

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
