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

	let hasData = false;
	const formData = new FormData();

	if ( STEPS.DEFAULT_EMAIL_ADDRESS === processedStep ) {
		const { emailStepData } = getAppState();
		if ( emailStepData ) {
			formData.append( 'default_email', emailStepData.default_email );
			formData.append( 'is_subscribed', emailStepData.is_subscribed );
			formData.append( 'allows_tracking', emailStepData.allows_tracking );
			hasData = true;
		}
	}

	if ( STEPS.INSTALL_ADDONS === processedStep ) {
		const { installedAddons } = getAppState();
		if ( installedAddons.length > 0 ) {
			formData.append( 'installed_addons', installedAddons );
			hasData = true;
		}
	}

	if ( STEPS.SUCCESS === processedStep ) {
		const { processedSteps } = getAppState();
		if ( processedSteps.length > 1 ) {
			formData.append( 'processed_steps', processedSteps );
			formData.append( 'completed_steps', true );
			hasData = true;
		}
	}

	if ( hasData ) {
		// Send the POST request
		const { doJsonPost } = frmDom.ajax;
		doJsonPost( 'onboarding_setup_usage_data', formData );
	}
}

export default setupDataUsage;
