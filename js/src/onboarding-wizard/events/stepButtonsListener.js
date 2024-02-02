/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { PREFIX } from '../shared';
import { onClickPreventDefault, hide, frmAnimate, show } from '../utils';

/**
 * Manages event handling for the step buttons.
 *
 * @return {void}
 */
function addStepButtonsEvents() {
	const { skipButtons } = getElements();

	// Attach click event listeners to each skip buttons
	skipButtons.forEach( skipButton => {
		onClickPreventDefault( skipButton, onSkipButtonClick );
	});
}

/**
 * Handles the click event on a skip button.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
const onSkipButtonClick = () => {
	const currentStep = document.querySelector( '[data-current-step]' );
	const nextStep = currentStep.nextElementSibling;

	currentStep.removeAttribute( 'data-current-step' );
	nextStep.setAttribute( 'data-current-step', '' );

	hide( currentStep );
	show( nextStep );

	if ( nextStep.id === `${PREFIX}-success-step` ) {
		const { returnToDashboard } = getElements();
		hide( returnToDashboard );
	}

	new frmAnimate( nextStep ).fadeIn();
};

export default addStepButtonsEvents;
