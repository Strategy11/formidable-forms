/**
 * External dependencies
 */
import { frmAnimate, getQueryParam, removeQueryParam, hasQueryParam } from 'core/utils';
import { addProgressToCardBoxes } from 'core/ui';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { STEPS, WELCOME_STEP_ID, proIsIncluded } from '../shared';
import { navigateToStep } from '../utils';

/**
 * Initializes the onboarding wizard's UI, sets up the initial step based on certain conditions,
 * and applies necessary UI enhancements for a smoother user experience.
 *
 * @return {void}
 */
export default function setupInitialView() {
	navigateToInitialStep();
	enhanceStepsWithProgress();
	fadeInPageElements();

	// Load the email form from the server for the "Default Email Address" step
	loadApiEmailForm();
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
	const { hiddenLicenseKeyInput, installFormidableProStep, licenseManagementStep } = getElements();

	if ( hiddenLicenseKeyInput ) {
		return handleLicenseKeyInput( hiddenLicenseKeyInput, installFormidableProStep, licenseManagementStep ); // Steps are conditionally removed inside handleLicenseKeyInput based on proIsIncluded
	}

	if ( hasQueryParam( 'success' ) ) {
		// Handle the case where 'success' query parameter is present
		installFormidableProStep.remove();
		licenseManagementStep.remove();
		return STEPS.DEFAULT_EMAIL_ADDRESS;
	}

	const stepQueryParam = getQueryParam( 'step' ) || STEPS.INITIAL;

	switch ( stepQueryParam ) {
		case STEPS.LICENSE_MANAGEMENT:
			installFormidableProStep.remove();
			break;
		case STEPS.INSTALL_FORMIDABLE_PRO:
			break;
		default:
			// Remove both steps as they are not needed
			installFormidableProStep.remove();
			licenseManagementStep.remove();
	}

	return stepQueryParam;
}

/**
 * Handles the presence of a hidden license key input, determining the next step based on whether Formidable Pro is installed.
 * Removes unnecessary steps based on the determined next step.
 *
 * @private
 * @param {HTMLElement} hiddenLicenseKeyInput    The hidden input element containing the license key.
 * @param {HTMLElement} installFormidableProStep The step element for installing Formidable Pro.
 * @param {HTMLElement} licenseManagementStep    The step element for license management.
 * @return {string} The name of the next step to navigate to.
 */
function handleLicenseKeyInput( hiddenLicenseKeyInput, installFormidableProStep, licenseManagementStep ) {
	const { licenseKeyInput } = getElements();
	licenseKeyInput.value = hiddenLicenseKeyInput.value;

	if ( proIsIncluded ) {
		installFormidableProStep.remove(); // Remove install Pro step if Pro is installed
		return STEPS.LICENSE_MANAGEMENT;
	}

	return STEPS.INSTALL_FORMIDABLE_PRO;
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
 * Adds a progress bar to each step except the welcome step.
 *
 * @private
 * @return {void}
 */
function enhanceStepsWithProgress() {
	const { steps } = getElements();
	addProgressToCardBoxes([ ...steps ].filter( step => step.id !== WELCOME_STEP_ID ) );
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

/**
 * Loads the email form from the server for the "Default Email Address" step in the Onboarding Wizard.
 * This involves making a server request to fetch the form and then injecting it into the appropriate part of the DOM.
 *
 * @private
 * @return {void}
 */
function loadApiEmailForm() {
	frmAdminBuild.loadApiEmailForm();
}
