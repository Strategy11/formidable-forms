/**
 * Copyright (C) 2010 Formidable Forms
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
import { ALL_TEMPLATES_SLUG } from './src/shared';
import initializeFormTemplates from './src/initializeFormTemplates';

domReady( () => {
	/**
	 * State object containing key configurations, passed to hooks and template initializer.
	 *
	 * @since x.x
	 *
	 * @var {Object} formTemplatesConfig
	 * @property {string} selectedCategory The currently selected template category.
	 */
	const formTemplatesConfig = {
		selectedCategory: ALL_TEMPLATES_SLUG
	};

	/**
	 * Provides an entry point for custom logic or modification to `formTemplatesConfig`
	 * before form templates initialization.
	 *
	 * @since x.x
	 *
	 * @hook frmFormTemplates.beforeInitialize
	 * @param {Object} formTemplatesConfig - Template initialization settings.
	 */
	wp.hooks.doAction( 'frmFormTemplates.beforeInitialize', formTemplatesConfig );

	// Initialize the form templates
	initializeFormTemplates( formTemplatesConfig );

	/**
	 * Provides an entry point for custom logic or modification to `formTemplatesConfig`
	 * after form templates initialization.
	 *
	 * @since x.x
	 * @hook frmFormTemplates.afterInitialize
	 * @param {Object} formTemplatesConfig - Template initialization settings.
	 */
	wp.hooks.doAction( 'frmFormTemplates.afterInitialize', formTemplatesConfig );
});
