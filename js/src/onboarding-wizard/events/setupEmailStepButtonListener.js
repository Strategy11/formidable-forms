/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { showEmailAddressError } from '../ui';
import { isValidEmail, onClickPreventDefault } from '../utils';

/**
 * Manages event handling for the "Next Step" button in the email setup step.
 *
 * @return {void}
 */
function addSetupEmailStepButtonEvents() {
	const { setupEmailStepButton } = getElements();

	// Attach click event listener
	onClickPreventDefault( setupEmailStepButton, onSetupEmailStepButtonClick );
}

/**
 * Handles the click event on the "Next Step" button during email setup.
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

	// Prepare FormData for the POST request
	const formData = new FormData();
	const { subscribeCheckbox, allowTrackingCheckbox } = getElements();
	formData.append( 'default_email', email );
	formData.append( 'is_subscribed', subscribeCheckbox.checked );
	formData.append( 'is_allowed_tracking', allowTrackingCheckbox.checked );

	// Send the POST request
	const { doJsonPost } = frmDom.ajax;
	return doJsonPost( 'onboarding_setup_email_step', formData );
};

export default addSetupEmailStepButtonEvents;
