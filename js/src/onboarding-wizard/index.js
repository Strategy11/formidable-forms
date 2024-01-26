/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { getAppState, setAppState } from './shared';
import initializeOnboardingWizard from './initializeOnboardingWizard';

domReady( () => {
	/**
	 * Entry point for pre-initialization adjustments to the application state.
	 *
	 * @param {Object} appState Current state of the application.
	 */
	wp.hooks.doAction( 'frmOnboardingWizard.beforeInitialize', {
		getAppState,
		setAppState
	});

	// Initialize the Onboarding Wizard
	initializeOnboardingWizard();

	/**
	 * Entry point for post-initialization custom logic or adjustments to the application state.
	 *
	 * @param {Object} appState Current state of the application.
	 */
	wp.hooks.doAction( 'frmOnboardingWizard.afterInitialize', {
		getAppState,
		setAppState
	});
});
