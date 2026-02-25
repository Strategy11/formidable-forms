/**
 * External dependencies
 */
import { nonce } from 'core/constants';
import { onClickPreventDefault, addToRequestQueue } from 'core/utils';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { getState, setSingleState } from '../shared';
import { navigateToNextStep } from '../utils';

/**
 * Manages event handling for the "Install & Finish Setup" button in the "Install Formidable Add-ons" step.
 *
 * @return {void}
 */
function addInstallAddonsButtonEvents() {
	const { installAddonsButton } = getElements();

	// Attach click event listener
	onClickPreventDefault( installAddonsButton, onInstallAddonsButtonClick );
}

/**
 * Handles the click event on the "Install & Finish Setup" button in the "Install Formidable Add-ons" step.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
const onInstallAddonsButtonClick = async event => {
	const addons = document.querySelectorAll( '.frm-option-box.frm-checked:not(.frm-disabled)' );
	const { installedAddons } = getState();
	const installAddonsButton = event.currentTarget;

	installAddonsButton.classList.add( 'frm_loading_button' );

	for ( const addon of addons ) {
		try {
			await addToRequestQueue( () => installAddon( addon.getAttribute( 'rel' ), addon.dataset ) );

			// Capture addon title
			const addonTitle = addon.dataset.title;
			if ( ! installedAddons.includes( addonTitle ) ) {
				installedAddons.push( addonTitle );
			}
		} catch ( error ) {
			console.error( 'An error occurred:', error );
		}
	}

	installAddonsButton.classList.remove( 'frm_loading_button' );

	setSingleState( 'installedAddons', installedAddons );
	navigateToNextStep();
};

/**
 * Installs an add-on or plugin based on the provided plugin name and vendor status.
 *
 * @private
 * @param {string}  plugin              The unique identifier or name of the plugin or add-on to be installed.
 * @param {Object}  options             An object containing additional options for the installation.
 * @param {boolean} options.isInstalled Indicates whether the plugin is already installed.
 * @param {boolean} options.isVendor    Indicates whether the plugin is a vendor plugin (true) or a regular add-on (false).
 * @return {Promise<any>} A promise that resolves with the JSON response from the server after the installation request is completed.
 */
async function installAddon( plugin, { isVendor, isInstalled } ) {
	// Prepare FormData for the POST request
	const formData = new FormData();
	formData.append( 'nonce', nonce );
	formData.append( 'plugin', plugin );

	const addonAction = isInstalled ? 'frm_activate_addon' : 'frm_install_addon';
	formData.append( 'action', isVendor ? 'frm_install_plugin' : addonAction );

	try {
		// Perform the POST request
		const response = await fetch( ajaxurl, {
			method: 'POST',
			body: formData
		} );

		if ( ! response.ok ) {
			throw new Error( `Server responded with status ${ response.status }` );
		}

		// Parse the JSON response
		return await response.json();
	} catch ( error ) {
		console.error( 'An error occurred:', error );
	}
}

export default addInstallAddonsButtonEvents;
