/**
 * External dependencies
 */
import { createPageElements } from 'core/factory';

/**
 * Internal dependencies
 */
import { PREFIX, WELCOME_STEP_ID } from '../shared';

export const { getElements, addElements } = createPageElements({
	// Body Elements
	onboardingWizardPage: document.getElementById( `${PREFIX}-wizard-page` ),
	container: document.getElementById( `${PREFIX}-container` ),
	steps: document.querySelectorAll( `.${PREFIX}-step` ),
	skipStepButtons: document.querySelectorAll( `.${PREFIX}-skip-step` ),
	backButtons: document.querySelectorAll( `.${PREFIX}-back-button` ),
	hiddenLicenseKeyInput: document.getElementById( 'frm-license-key' ),

	// Install Formidable Pro Step Elements
	installFormidableProStep: document.getElementById( `${PREFIX}-install-formidable-pro-step` ),
	checkProInstallationButton: document.getElementById( `${PREFIX}-check-pro-installation-button` ),
	skipProInstallationButton: document.getElementById( `${PREFIX}-skip-pro-installation-button` ),
	checkProInstallationError: document.getElementById( `${PREFIX}-check-pro-installation-error` ),

	// License Management Step Elements
	licenseManagementStep: document.getElementById( `${PREFIX}-license-management-step` ),
	licenseKeyInput: document.getElementById( 'edd_formidable_pro_license_key' ),
	saveLicenseButton: document.getElementById( `${PREFIX}-save-license-button` ),

	// Welcome Step Elements
	welcomeStep: document.getElementById( WELCOME_STEP_ID ),

	// Default Email Address Step Elements
	setupEmailStepButton: document.getElementById( `${PREFIX}-setup-email-step-button` ),
	defaultEmailField: document.getElementById( `${PREFIX}-default-email-field` ),
	defaultFromEmailField: document.getElementById( `${PREFIX}-from-email` ),
	subscribeCheckbox: document.getElementById( `${PREFIX}-subscribe` ),
	summaryEmailsCheckbox: document.getElementById( `${PREFIX}-summary-emails` ),
	allowTrackingCheckbox: document.getElementById( `${PREFIX}-allow-tracking` ),

	// Install Formidable Add-ons Step Elements
	installAddonsButton: document.getElementById( `${PREFIX}-install-addons-button` ),

	// Success Step Elements
	successStep: document.getElementById( `${PREFIX}-success-step` )
});
