/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { frmAnimate } from '../utils';

/**
 * Sets up the initial view, performing any required
 * DOM manipulations for proper element presentation.
 *
 * @return {void}
 */
function setupInitialView() {
	// Smoothly display the page
	const { pageBackground, container } = getElements();
	new frmAnimate( pageBackground ).fadeIn();
	new frmAnimate( container ).fadeIn();
}

export default setupInitialView;
