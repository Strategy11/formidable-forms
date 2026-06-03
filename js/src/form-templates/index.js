/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { getState, setState } from './shared';
import initializeFormTemplates from './initializeFormTemplates';

domReady( () => {
	/**
	 * Entry point for pre-initialization adjustments to the page state.
	 *
	 * @param {Object} state Current state of the page.
	 */
	wp.hooks.doAction( 'frmFormTemplates.beforeInitialize', {
		getState,
		setState
	} );

	// Initialize the form templates
	initializeFormTemplates();

	/**
	 * Entry point for post-initialization custom logic or adjustments to the page state.
	 *
	 * @param {Object} state Current state of the page.
	 */
	wp.hooks.doAction( 'frmFormTemplates.afterInitialize', {
		getState,
		setState
	} );

	/**
	 * Trigger a specific action to interact with the hidden form '#frm-new-template',
	 * which is used for creating or using a form template.
	 *
	 * @param {HTMLElement} $form The jQuery object containing the hidden form element.
	 */
	wp.hooks.doAction( 'frm_new_form_modal_form', jQuery( '#frm-new-template' ) );
} );
