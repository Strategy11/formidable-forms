/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { STEPS } from '../shared';
import { onClickPreventDefault, setQueryParam } from '../utils';

/**
 * Manages event handling for the "Active & continue" button in the "License Management" step.
 *
 * @return {void}
 */
function addSaveLicenseButtonEvents() {
	const { saveLicenseButton } = getElements();

	// Attach click event listener
	onClickPreventDefault( saveLicenseButton, onSaveLicenseButtonClick );
}

/**
 * Handles the click event on the "Active & continue" button in the "License Management" setup.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
const onSaveLicenseButtonClick = async() => {
	wp.hooks.addAction( 'frm_after_authorize', 'frmOnboardingWizard', data => {
		// After authorization, update URL to "Default Email Address" step and reload page
		window.location.href = setQueryParam( 'step', STEPS.DEFAULT_EMAIL_ADDRESS, 'replaceState' );

		// Reset data.message to an empty string to avoid errors or undesired behavior
		data.message = '';
		return data;
	});
};

export default addSaveLicenseButtonEvents;
