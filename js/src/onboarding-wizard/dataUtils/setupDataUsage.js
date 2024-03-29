import { getElements } from '../elements';
import { getAppState, STEPS } from '../shared';

const dataCaptureStepsSet = new Set([
	STEPS.DEFAULT_EMAIL_ADDRESS,
	STEPS.INSTALL_ADDONS,
	STEPS.SUCCESS
]);

/**
 * Set up usage data for the Onboarding Wizard.
 *
 * @param {String} processedStep The name of the step that has just been processed.
 * @return {void}
 */
function setupDataUsage( processedStep ) {
	if ( ! dataCaptureStepsSet.has( processedStep ) ) {
		return;
	}

	const formData = new FormData();

	if ( STEPS.DEFAULT_EMAIL_ADDRESS === processedStep ) {
		const { defaultEmailField, subscribeCheckbox, allowTrackingCheckbox } = getElements();

		// Prepare FormData for the POST request
		formData.append( 'default_email', defaultEmailField.value.trim() );
		formData.append( 'is_subscribed', subscribeCheckbox?.checked ?? false );
		formData.append( 'allows_tracking', allowTrackingCheckbox.checked );
	}

	if ( STEPS.INSTALL_ADDONS === processedStep ) {
		const {installedAddons} = getAppState();
		formData.append( 'installedAddons', installedAddons );
	}

	if ( STEPS.SUCCESS === processedStep ) {
		formData.append( 'completed_steps', true );
	}

	// Send the POST request
	const { doJsonPost } = frmDom.ajax;
	doJsonPost( 'onboarding_setup_usage_data', formData );
}

export default setupDataUsage;
