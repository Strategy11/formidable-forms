/**
 * External dependencies
 */
import { onClickPreventDefault } from 'core/utils';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { navigateToNextStep } from '../utils';

/**
 * Manages event handling for the "Allow & Continue" button in the "Never miss an important update" step.
 *
 * @return {void}
 */
function addConsentTrackingButtonEvents() {
	const { consentTrackingButton } = getElements();

	// Attach click event listener
	onClickPreventDefault( consentTrackingButton, onConsentTrackingButtonClick );
}

/**
 * Handles the click event on the "Allow & Continue" button in the "Never miss an important update" step.
 *
 * @private
 * @return {void}
 */
const onConsentTrackingButtonClick = async() => {
	const { doJsonPost } = frmDom.ajax;
	doJsonPost( 'onboarding_consent_tracking', new FormData() ).then( navigateToNextStep );
};

export default addConsentTrackingButtonEvents;
