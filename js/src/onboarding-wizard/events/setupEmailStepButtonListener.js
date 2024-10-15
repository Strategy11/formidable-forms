/**
 * External dependencies
 */
import { onClickPreventDefault, isValidEmail } from 'core/utils';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { getState, setSingleState } from '../shared';
import { showEmailAddressError } from '../ui';
import { navigateToNextStep } from '../utils';

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

const validateEmails = emailInputs => {
	let isValid = true;
	emailInputs.forEach( input => {
		const emailAddress = input.value.trim();
		if ( ! isValidEmail( emailAddress ) ) {
			showEmailAddressError( 'invalid', input );
			isValid = false;
		}
	});
	return isValid;
};

/**
 * Handles the click event on the "Next Step" button in the "Default Email Address" step.
 *
 * @private
 *
 * @return {void}
 */
const onSetupEmailStepButtonClick = async() => {
	const { defaultEmailField, defaultFromEmailField } = getElements();

	// Check if the emails are valid
	if ( ! validateEmails( [ defaultFromEmailField, defaultEmailField ] ) ) {
		return;
	}

	const { subscribeCheckbox, summaryEmailsCheckbox, allowTrackingCheckbox } = getElements();
	const email = defaultEmailField.value.trim();

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
	emailStepData.from_email = defaultFromEmailField.value.trim();
	emailStepData.allows_tracking = allowTrackingCheckbox.checked;
	emailStepData.summary_emails = summaryEmailsCheckbox.checked;
	if ( subscribeCheckbox ) {
		emailStepData.is_subscribed = subscribeCheckbox.checked;
	}
	setSingleState( 'emailStepData', emailStepData );

	// Prepare FormData for the POST request
	const formData = new FormData();
	formData.append( 'default_email', email );
	formData.append( 'from_email', defaultFromEmailField.value.trim() );
	formData.append( 'allows_tracking', allowTrackingCheckbox.checked );
	formData.append( 'summary_emails', summaryEmailsCheckbox.checked );

	// Send the POST request
	const { doJsonPost } = frmDom.ajax;
	doJsonPost( 'onboarding_setup_email_step', formData ).then( navigateToNextStep );
};

export default addSetupEmailStepButtonEvents;
