/**
 * Copyright (C) 2023 Formidable Forms
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { getAppState, setAppState } from './src/shared';
import initializeFormTemplates from './src/initializeFormTemplates';

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
});
