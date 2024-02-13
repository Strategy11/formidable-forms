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

	/**
	 * Initializes the "Default Email Address" step in the Onboarding Wizard.
	 * This function injects the API email form into the 'frmapi-email-form' element within 'leave-email-modal.php'.
	 * It utilizes 'FrmAppController::api_email_form' from 'default-email-step.php' to facilitate this injection.
	 */
	frmAdminBuild.showActiveCampaignForm();
}

export default setupInitialView;
