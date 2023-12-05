/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { getAppState, setAppState } from './shared';
import initializeFormTemplates from './initializeFormTemplates';

domReady( () => {
	/**
	 * Entry point for pre-initialization adjustments to the application state.
	 *
	 * @param {Object} appState Current state of the application.
	 */
	wp.hooks.doAction( 'frmFormTemplates.beforeInitialize', {
		getAppState,
		setAppState
	});

	// Initialize the form templates
	initializeFormTemplates();

	/**
	 * Entry point for post-initialization custom logic or adjustments to the application state.
	 *
	 * @param {Object} appState Current state of the application.
	 */
	wp.hooks.doAction( 'frmFormTemplates.afterInitialize', {
		getAppState,
		setAppState
	});

	/**
	 * Trigger a specific action to interact with the hidden form '#frm-new-template',
	 * which is used for creating or using a form template.
	 *
	 * @param {jQuery} jQuery('#frm-new-template') The jQuery object containing the hidden form element.
	 */
	wp.hooks.doAction( 'frm_new_form_modal_form', jQuery( '#frm-new-template' ) );
});
