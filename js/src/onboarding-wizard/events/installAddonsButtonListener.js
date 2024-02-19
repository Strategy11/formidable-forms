/**
 * Internal dependencies
 */
import { navigateToNextStep } from '.';
import { getElements } from '../elements';
import { nonce } from '../shared';
import { addToRequestQueue, onClickPreventDefault } from '../utils';

/**
 * Manages event handling for the "Install & Finish Setup" button.
 *
 * @return {void}
 */
function addInstallAddonsButtonEvents() {
	const { installAddonsButton } = getElements();

	// Attach click event listener
	onClickPreventDefault( installAddonsButton, onInstallAddonsButtonClick );
}

/**
 * Handles the click event on the "Install & Finish Setup" button.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
const onInstallAddonsButtonClick = async( event ) => {
	const addons = document.querySelectorAll( '.frm-option-box:not(.frm-disabled)' );

	const installAddonsButton = event.currentTarget;
	installAddonsButton.classList.add( 'frm_loading_button' );

	// Wait for all addon installations to complete
	await Promise.all( Array.from( addons ).map( addon =>
		addToRequestQueue( () => installAddon( addon.dataset ) )
	) );

	installAddonsButton.classList.remove( 'frm_loading_button' );
	navigateToNextStep();
};

/**
 * Install an add-on.
 *
 * Prepares and sends a POST request to install an add-on or plugin
 * based on the provided pluginSlug and isVendor flag
 *
 * @private
 * @param {Object} data An object containing the pluginSlug and isVendor flag.
 * @return {Promise<any>} A promise that resolves when the POST request is completed.
 */
async function installAddon({ pluginSlug, isVendor}) {
	// Prepare FormData for the POST request
	const formData = new FormData();
	formData.append( 'action', isVendor ? 'frm_install_plugin' : 'frm_install_addon' );
	formData.append( 'nonce', nonce );
	formData.append( 'plugin_slug', pluginSlug );

	try {
		// Perform the POST request
		const response = await fetch( ajaxurl, {
			method: 'POST',
			body: formData
		});

		// Parse the JSON response
		return await response.json();
	} catch ( error ) {
		console.error( 'An error occurred:', error );
	}
}

export default addInstallAddonsButtonEvents;
