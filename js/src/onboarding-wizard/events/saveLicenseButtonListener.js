/**
 * External dependencies
 */
import { onClickPreventDefault, setQueryParam } from 'core/utils';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { STEPS } from '../shared';

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
 * Handles the click event on the "Active & continue" button in the "License Management" step.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
const onSaveLicenseButtonClick = async() => {
	wp.hooks.addAction( 'frm_after_authorize', 'frmOnboardingWizard', data => {
		if ( true === data.success ) {
			// After authorization, update URL to "Default Email Address" step and reload the page
			window.location.href = setQueryParam( 'step', STEPS.DEFAULT_EMAIL_ADDRESS, 'replaceState' );
		}

		return data;
	});
};

export default addSaveLicenseButtonEvents;
