/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { navigateToStep } from '../events';
import { INITIAL_STEP } from '../shared';
import { frmAnimate, getQueryParam, hasQueryParam } from '../utils';

/**
 * Sets up the initial view, performing any required DOM manipulations for proper element presentation.
 *
 * @return {void}
 */
function setupInitialView() {
	// On initial page load, check if there's a 'step' query parameter and navigate to the corresponding step
	if ( hasQueryParam( 'step' ) ) {
		const initialStepName = getQueryParam( 'step' ) || INITIAL_STEP;
		// Navigate to the initial step without adding to browser history
		navigateToStep( initialStepName, 'replaceState' );
	}

	// Smoothly display the page
	const { pageBackground, container } = getElements();
	new frmAnimate( pageBackground ).fadeIn();
	new frmAnimate( container ).fadeIn();

	/**
	 * Initializes the "Default Email Address" step in the Onboarding Wizard.
	 * This function injects the API email form into the 'frmapi-email-form' element within 'leave-email-modal.php'.
	 * It utilizes 'FrmAppController::api_email_form' from 'default-email-step.php' to facilitate this injection.
	 */
	frmAdminBuild.showActiveCampaignForm();
}

export default setupInitialView;
