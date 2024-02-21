/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { CURRENT_CLASS, PREFIX } from '../shared';
import { frmAnimate, getQueryParam, hasQueryParam, hide, show } from '../utils';

/**
 * Sets up the initial view, performing any required
 * DOM manipulations for proper element presentation.
 *
 * @return {void}
 */
function setupInitialView() {
	// Display a specific step based on the 'step' query parameter, if it exists
	if ( hasQueryParam( 'step' ) ) {
		const stepElement = document.querySelector( `.${PREFIX}-step[data-step-name="${getQueryParam( 'step' )}"]` );
		// Proceed only if the step element is found
		if ( stepElement ) {
			const { welcomeStep, onboardingWizardPage } = getElements();

			// Transition from the "Welcome" step to the targeted step
			welcomeStep.classList.remove( CURRENT_CLASS );
			hide( welcomeStep );
			stepElement.classList.add( CURRENT_CLASS );
			show( stepElement );

			// Update the onboarding wizard's current step attribute
			onboardingWizardPage.setAttribute( 'data-current-step', getQueryParam( 'step' ) );
		}
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
