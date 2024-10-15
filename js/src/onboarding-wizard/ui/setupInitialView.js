/**
 * External dependencies
 */
import { frmAnimate, getQueryParam, removeQueryParam, hasQueryParam } from 'core/utils';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { STEPS, proIsIncluded } from '../shared';
import { navigateToStep } from '../utils';

/**
 * Initializes the onboarding wizard's UI, sets up the initial step based on certain conditions,
 * and applies necessary UI enhancements for a smoother user experience.
 *
 * @return {void}
 */
export default function setupInitialView() {
	navigateToInitialStep();
	fadeInPageElements();
}

/**
 * Determines the initial step in the onboarding process and navigates to it, considering the installation
 * status of Formidable Pro and specific query parameters.
 *
 * @private
 * @return {void}
 */
function navigateToInitialStep() {
	const initialStepName = determineInitialStep();
	clearOnboardingQueryParams();
	navigateToStep( initialStepName, 'replaceState' );
}

/**
 * Determines the initial step based on the current state, such as whether Formidable Pro is installed
 * and the presence of specific query parameters. Also handles the removal of unnecessary steps.
 *
 * @private
 * @return {string} The name of the initial step to navigate to.
 */
function determineInitialStep() {
	const { hiddenLicenseKeyInput, licenseManagementStep } = getElements();

	if ( hiddenLicenseKeyInput ) {
		return handleLicenseKeyInput( hiddenLicenseKeyInput, licenseManagementStep ); // Steps are conditionally removed inside handleLicenseKeyInput based on proIsIncluded
	}

	if ( hasQueryParam( 'success' ) ) {
		// Handle the case where 'success' query parameter is present
		licenseManagementStep.remove();
		// return STEPS.DEFAULT_EMAIL_ADDRESS;
	}

	const stepQueryParam = getQueryParam( 'step' ) || STEPS.INITIAL;

	switch ( stepQueryParam ) {
		case STEPS.LICENSE_MANAGEMENT:
			break;
		default:
			// Remove both steps as they are not needed
			licenseManagementStep.remove();
	}

	return stepQueryParam;
}

/**
 * Handles the presence of a hidden license key input, determining the next step based on whether Formidable Pro is installed.
 * Removes unnecessary steps based on the determined next step.
 *
 * @private
 * @param {HTMLElement} hiddenLicenseKeyInput The hidden input element containing the license key.
 * @param {HTMLElement} licenseManagementStep The step element for license management.
 * @return {string} The name of the next step to navigate to.
 */
function handleLicenseKeyInput( hiddenLicenseKeyInput, licenseManagementStep ) {
	const { licenseKeyInput } = getElements();
	licenseKeyInput.value = hiddenLicenseKeyInput.value;

	return STEPS.LICENSE_MANAGEMENT;
}

/**
 * Clears specific query parameters related to the onboarding process.
 *
 * @private
 * @return {void}
 */
function clearOnboardingQueryParams() {
	removeQueryParam( 'key' );
	removeQueryParam( 'success' );
}

/**
 * Smoothly fades in the background and container elements of the page for a more pleasant user experience.
 *
 * @private
 * @return {void}
 */
function fadeInPageElements() {
	const { container } = getElements();
	new frmAnimate( container ).fadeIn();
}
