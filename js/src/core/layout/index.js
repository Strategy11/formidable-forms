/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { getState, setState } from './shared';
import initializePageSidebar from './initializePageSidebar';

domReady( () => {
	/**
	 * Entry point for pre-initialization adjustments to the application state.
	 *
	 * @param {Object} appState Current state of the application.
	 */
	wp.hooks.doAction( 'frmPageSidebar.beforeInitialize', {
		getState,
		setState,
	} );

	// Initialize the page sidebar
	initializePageSidebar();

	/**
	 * Entry point for post-initialization custom logic or adjustments to the application state.
	 *
	 * @param {Object} appState Current state of the application.
	 */
	wp.hooks.doAction( 'frmPageSidebar.afterInitialize', {
		getState,
		setState,
	} );
} );
