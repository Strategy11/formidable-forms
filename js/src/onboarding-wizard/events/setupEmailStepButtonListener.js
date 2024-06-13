/**
 * External dependencies
 */
import { onClickPreventDefault } from 'core/utils';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { getState, setSingleState } from '../shared';
import { showEmailAddressError } from '../ui';
import { isValidEmail, navigateToNextStep } from '../utils';

/**
 * Manages event handling for the "Next Step" button in the "Default Email Address" step.
 *
 * @return {void}
 */
function addSetupEmailStepButtonEvents() {
	const { setupEmailStepButton } = getElements();

	// Attach click event listener
	onClickPreventDefault( setupEmailStepButton, onSetupEmailStepButtonClick );
}

/**
 * Handles the click event on the "Next Step" button in the "Default Email Address" step.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
const onSetupEmailStepButtonClick = async() => {
	const { defaultEmailField } = getElements();
	const email = defaultEmailField.value.trim();

	// Check if the email is valid
	if ( ! isValidEmail( email ) ) {
		showEmailAddressError( 'invalid' );
		return;
	}

	const { subscribeCheckbox, summaryEmailsCheckbox, allowTrackingCheckbox } = getElements();

	// Check if the 'subscribe' checkbox is selected. If so, proceed to add the user's email to the active campaign
	if ( subscribeCheckbox?.checked ) {
		// Assign default email to 'leave email' input if provided; otherwise, use administrator's email
		if ( email ) {
			const emailInput = document.getElementById( 'frm_leave_email' );
			emailInput.value = email;
		}

		frmAdminBuild.addMyEmailAddress();
		// Avoid replacing `#frm_leave_email_wrapper` content with a success message after email setup to prevent errors during modifications.
		wp.hooks.addFilter( 'frm_thank_you_on_signup', 'frmOnboardingWizard', () => false );
	}

	// Capture usage data
	const { emailStepData } = getState();
	emailStepData.default_email = email;
	emailStepData.allows_tracking = allowTrackingCheckbox.checked;
	emailStepData.summary_emails = summaryEmailsCheckbox.checked;
	if ( subscribeCheckbox ) {
		emailStepData.is_subscribed = subscribeCheckbox.checked;
	}
	setSingleState( 'emailStepData', emailStepData );

	// Prepare FormData for the POST request
	const formData = new FormData();
	formData.append( 'default_email', email );
	formData.append( 'allows_tracking', allowTrackingCheckbox.checked );
	formData.append( 'summary_emails', summaryEmailsCheckbox.checked );

	// Send the POST request
	const { doJsonPost } = frmDom.ajax;
	doJsonPost( 'onboarding_setup_email_step', formData ).then( navigateToNextStep );
};

export default addSetupEmailStepButtonEvents;
