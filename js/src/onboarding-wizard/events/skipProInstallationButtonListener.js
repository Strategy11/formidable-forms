/**
 * External dependencies
 */
import { onClickPreventDefault } from 'core/utils';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { STEPS } from '../shared';
import { navigateToStep } from '../utils';

/**
 * Manages event handling for the "Skip" button in the "Install Formidable Pro" step.
 *
 * @return {void}
 */
function addSkipProInstallationButtonEvents() {
	const { skipProInstallationButton } = getElements();

	// Attach click event listener
	onClickPreventDefault( skipProInstallationButton, onSkipProInstallationButtonClick );
}

/**
 * Handles the click event on the "Skip" button in the "Install Formidable Pro" setup.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
const onSkipProInstallationButtonClick = async() => {
	navigateToStep( STEPS.DEFAULT_EMAIL_ADDRESS );
};

export default addSkipProInstallationButtonEvents;
