/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { PREFIX, WELCOME_STEP_ID } from '../shared';
import { onClickPreventDefault } from '../utils';

/**
 * Manages event handling for the "Proceed without Account" button.
 *
 * @return {void}
 */
function addSkipConnectAccountButtonEvents() {
	const { skipConnectAccountButton } = getElements();

	// Attach click event listeners to each skip buttons
	onClickPreventDefault( skipConnectAccountButton, onSkipConnectAccountButtonClick );
}

/**
 * Handles the click event on the "Proceed without Account" button.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
const onSkipConnectAccountButtonClick = () => {
	// Remove the "License Management" step by clicking on the "Proceed without Account" button
	const { licenseManagementStep } = getElements();
	licenseManagementStep.remove();

	// Calculate and set the width for each step's progress bar
	const steps = Array.from( document.querySelectorAll( `.${PREFIX}-step` ) ).filter( step => step.id !== WELCOME_STEP_ID );
	steps.forEach( ( step, index ) => {
		// Calculate width percentage based on the current step index (add 1 since index is 0-based) and total steps length
		const widthPercentage = ( ( index + 1 ) / steps.length ) * 100;

		// Find the progress bar within the current step and set its width
		const progressBar = step.querySelector( '.frm-card-box-progress-bar > span' );
		if ( progressBar ) {
			progressBar.style.width = `${widthPercentage}%`;
		}
	});
};

export default addSkipConnectAccountButtonEvents;
