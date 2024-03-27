/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import initializeOnboardingWizard from './initializeOnboardingWizard';

domReady( () => {
	// Initialize the Onboarding Wizard
	initializeOnboardingWizard();
});
