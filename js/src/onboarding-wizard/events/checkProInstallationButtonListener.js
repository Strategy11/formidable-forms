/**
 * Internal dependencies
 */
import { navigateToNextStep } from '.';
import { getElements } from '../elements';
import { nonce } from '../shared';
import { onClickPreventDefault, show } from '../utils';

/**
 * Manages event handling for the "Continue" button in the "Install Formidable Pro" step.
 *
 * @return {void}
 */
function addCheckProInstallationButtonEvents() {
	const { checkProInstallationButton } = getElements();

	// Attach click event listener
	onClickPreventDefault( checkProInstallationButton, onCheckProInstallationButtonClick );
}

/**
 * Handles the click event on the "Continue" button in the "Install Formidable Pro" setup.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
const onCheckProInstallationButtonClick = async() => {
	// Prepare FormData for the POST request
	const formData = new FormData();
	formData.append( 'action', 'frm_check_plugin_activation' );
	formData.append( 'nonce', nonce );
	formData.append( 'plugin_path', 'formidable-pro/formidable-pro.php' );

	try {
		// Perform the POST request
		const response = await fetch( ajaxurl, {
			method: 'POST',
			body: formData
		});

		// Parse the JSON response
		const { success } = await response.json();

		if ( success ) {
			navigateToNextStep();
		} else {
			const { checkProInstallationError } = getElements();
			show( checkProInstallationError );
		}
	} catch ( error ) {
		console.error( 'An error occurred:', error );
	}
};

export default addCheckProInstallationButtonEvents;
