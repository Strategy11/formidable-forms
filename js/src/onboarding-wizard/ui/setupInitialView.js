/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { getAppState } from '../shared';
import { show, hide, hideElements, frmAnimate } from '../utils';

/**
 * Sets up the initial view, performing any required
 * DOM manipulations for proper element presentation.
 *
 * @return {void}
 */
function setupInitialView() {
	const { pageBackground, welcomeStep } = getElements();

	const pageBackgroundAnimate = new frmAnimate( pageBackground );
	const welcomeStepAnimate = new frmAnimate( welcomeStep );

	pageBackgroundAnimate.fadeIn();
	welcomeStepAnimate.fadeIn();
}

export default setupInitialView;
