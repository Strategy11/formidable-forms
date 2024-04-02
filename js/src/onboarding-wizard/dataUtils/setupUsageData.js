/**
 * Internal Dependencies
 */
import { isEmptyObject } from '../utils';
import { getAppState, STEPS } from '../shared';

/**
 * Processes and submits usage data for the specified onboarding step.
 *
 * @param {String} processedStep The name of the step that has just been processed.
 * @param {String} nextStepName The name of the next step in the onboarding process.
 * @return {void}
 */
function setupUsageData( processedStep, nextStepName ) {
	const formData = processDataForStep( processedStep, nextStepName );
	if ( ! formData ) {
		return;
	}

	// Send the POST request
	const { doJsonPost } = frmDom.ajax;
	doJsonPost( 'onboarding_setup_usage_data', formData );
}

/**
 * Processes onboarding step data and returns the corresponding FormData.
 *
 * @private
 * @param {String} processedStep The name of the step that has just been processed.
 * @param {String} nextStepName The name of the next step in the onboarding process.
 * @returns {FormData|null} The FormData to be submitted for the step, or null if there's no data.
 */
function processDataForStep( processedStep, nextStepName ) {
	let formData;

	// Append completed steps if moving to the success step
	if ( STEPS.SUCCESS === nextStepName ) {
		const { processedSteps } = getAppState();
		if ( processedSteps.length > 1 ) {
			formData = new FormData();
			formData.append( 'processed_steps', processedSteps.join( ',' ) );
			formData.append( 'completed_steps', true );
		}
	}

	// Append email step data for the email step
	if ( STEPS.DEFAULT_EMAIL_ADDRESS === processedStep ) {
		const { emailStepData } = getAppState();
		if ( ! isEmptyObject( emailStepData ) ) {
			formData = formData ?? new FormData();
			for ( const [ key, value ] of Object.entries( emailStepData ) ) {
				formData.append( key, value );
			}
		}
	}

	// Append installed addons for the addon installation step
	if ( STEPS.INSTALL_ADDONS === processedStep ) {
		const { installedAddons } = getAppState();
		if ( installedAddons.length > 0 ) {
			formData = formData ?? new FormData();
			formData.append( 'installed_addons', installedAddons.join( ',' ) );
		}
	}

	return formData;
}

export default setupUsageData;
