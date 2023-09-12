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
 * Internal dependencies
 */
import { initializeElements } from './elements';
import { initializeAppState } from './shared';
import { setupInitialView } from './ui';
import { buildCategorizedTemplates } from './templates';
import { addEventListeners } from './events';

/**
 * Initializes form templates.
 *
 * @return {void}
 */
function initializeFormTemplates() {
	// Initializes essential DOM elements
	initializeElements();

	// Initialize application state
	initializeAppState();

	// Set up the initial view, including any required DOM manipulations for proper presentation
	setupInitialView();

	// Generate a categorized list of templates
	buildCategorizedTemplates();

	// Attach event listeners for user interactions
	addEventListeners();
}

export default initializeFormTemplates;
