/**
 * External dependencies
 */
import { nonce } from 'core/constants';
import { onClickPreventDefault, show } from 'core/utils';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { navigateToNextStep } from '../utils';

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

	let data;

	try {
		const response = await fetch( ajaxurl, {
			method: 'POST',
			body: formData
		});
		data = await response.json();
	} catch ( error ) {
		console.error( 'An error occurred:', error );
		return;
	}

	if ( data.success ) {
		navigateToNextStep();
		return;
	}

	const { checkProInstallationError } = getElements();
	show( checkProInstallationError );
};

export default addCheckProInstallationButtonEvents;
