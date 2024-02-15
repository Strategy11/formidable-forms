/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { ADDONS_NAMES } from '../shared';
import { onClickPreventDefault } from '../utils';

/**
 * Manages event handling for the "Next Step" button in the email setup step.
 *
 * @return {void}
 */
function addInstallAddonsButtonEvents() {
	const { installAddonsButton } = getElements();

	// Attach click event listener
	onClickPreventDefault( installAddonsButton, onInstallAddonsButtonClick );
}

/**
 * Handles the click event on the "Next Step" button during email setup.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
const onInstallAddonsButtonClick = async() => {
	const addons = document.querySelectorAll( '.frm-option-box:not(.frm-disabled)' );

	addons.forEach( addon => {
		const addonName = addon.dataset.addon;

		if ( addonName === ADDONS_NAMES.SMTP ) {
			return;
		}
	});
};

export default addInstallAddonsButtonEvents;
