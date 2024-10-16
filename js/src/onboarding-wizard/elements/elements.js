/**
 * External dependencies
 */
import { createPageElements } from 'core/factory';

/**
 * Internal dependencies
 */
import { PREFIX } from '../shared';

export const { getElements, addElements } = createPageElements({
	onboardingWizardPage: document.getElementById( `${PREFIX}-wizard-page` ),
	container: document.getElementById( `${PREFIX}-container` ),
	rootline: document.getElementById( `${PREFIX}-rootline` ),
	steps: document.querySelectorAll( `.${PREFIX}-step` ),
	skipStepButtons: document.querySelectorAll( `.${PREFIX}-skip-step` ),
	backButtons: document.querySelectorAll( `.${PREFIX}-back-button` ),
	installAddonsButton: document.getElementById( `${PREFIX}-install-addons-button` ),
	hiddenLicenseKeyInput: document.getElementById( 'frm-license-key' ),
});
