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
 * Sets up the initial view, performing any necessary
 * DOM manipulations for proper element presentation.
 *
 * @return {void}
 */
function setupInitialView() {
	const { sidebar, searchInput, bodyContent } = getElements();

	// Clear the value in the search input
	searchInput.value = '';

	/**
	 * Action to set up the initial view.
	 *
	 * @return {void}
	 */
	wp.hooks.doAction( 'frmPageSkeleton.setupInitialView' );

	// Display the UI elements with smooth transitions
	bodyContent.classList.remove( HIDE_JS_CLASS );
	sidebar.classList.remove( HIDE_JS_CLASS );
	new frmAnimate( bodyContent ).fadeIn();
	show( sidebar );
}

export default setupInitialView;
