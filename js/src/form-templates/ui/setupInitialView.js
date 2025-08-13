/**
 * External dependencies
 */
import { HIDE_JS_CLASS } from 'core/constants';
import { frmAnimate, hasQueryParam, hideElements, removeParamFromHistory } from 'core/utils';
import { counter } from 'core/ui';
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
		extraTemplateCountElements
	} = getElements();

	const bodyContentAnimate = new frmAnimate( bodyContent );

	searchInput.value = '';

	// Hide the twin featured template items
	hideElements( twinFeaturedTemplateItems );

	setupAvailableTemplatesCategory( availableTemplatesCategory );

	// Update extra templates count
	extraTemplateCountElements.forEach( element => element.textContent = getSingleState( 'extraTemplatesCount' ) );

	// Smoothly display the updated UI elements
	bodyContent.classList.remove( HIDE_JS_CLASS );
	sidebar.classList.remove( HIDE_JS_CLASS );
	bodyContentAnimate.fadeIn();

	// Show the "Cancel" button in the header if the 'return_page' query param is present
	if ( hasQueryParam( 'return_page' ) ) {
		showHeaderCancelButton();
	}
}

/**
 * Sets up the 'Available Templates' category with proper count display
 *
 * @param {Element} availableTemplatesCategory The Available Templates category element
 * @return {void}
 */
function setupAvailableTemplatesCategory( availableTemplatesCategory ) {
	if ( ! availableTemplatesCategory ) {
		return;
	}

	const availableTemplatesCount = getSingleState( 'availableTemplatesCount' );
	if ( ! hasQueryParam( 'registered-for-free-templates' ) ) {
		availableTemplatesCategory.querySelector( `.${SKELETON_PREFIX}-cat-count` ).textContent = availableTemplatesCount;
		return;
	}

	removeParamFromHistory( 'registered-for-free-templates' );
	runAvailableTemplatesEffects( availableTemplatesCategory, availableTemplatesCount );
}

/**
 * Runs effects for the Available Templates category when the
 * 'registered-for-free-templates' query parameter is present.
 *
 * @param {Element} element The Available Templates category element
 * @param {number}  count   The count of available templates
 * @return {void}
 */
function runAvailableTemplatesEffects( element, count ) {
	setTimeout( () => {
		element.dispatchEvent( new Event( 'click', { bubbles: true } ) );
	}, 0 );

	setTimeout( () => {
		counter( element.querySelector( `.${SKELETON_PREFIX}-cat-count` ), count );
	}, 150 );

	setTimeout( () => {
		const { availableTemplateItems } = getElements();
		availableTemplateItems.forEach( item => {
			item.classList.add( 'frm-background-highlight' );

			// Remove class after animation completes to prevent restart
			item.addEventListener( 'animationend', function handleAnimationEnd( event ) {
				if ( event.animationName === 'backgroundHighlight' ) {
					this.classList.remove( 'frm-background-highlight' );
					this.removeEventListener( 'animationend', handleAnimationEnd );
				}
			});
		});
	}, 750 );
}

export default setupInitialView;
