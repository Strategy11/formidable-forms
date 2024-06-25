/**
 * External dependencies
 */
import { HIDE_JS_CLASS } from 'core/constants';
import { frmAnimate, show } from 'core/utils';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';

/**
 * Sets up the initial view, performing any required
 * DOM manipulations for proper element presentation.
 *
 * @return {void}
 */
function setupInitialView() {
	const { sidebar, searchInput, bodyContent } = getElements();

	const bodyContentAnimate = new frmAnimate( bodyContent );

	// Clear the value in the search input
	searchInput.value = '';

	// Smoothly display the updated UI elements
	bodyContent.classList.remove( HIDE_JS_CLASS );
	sidebar.classList.remove( HIDE_JS_CLASS );
	bodyContentAnimate.fadeIn();
	show( sidebar );
}

export default setupInitialView;
