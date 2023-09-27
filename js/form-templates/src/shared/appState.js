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
import { getElements } from '../elements';
import { VIEW_SLUGS } from './';

let appState = null;

/**
 * Initialize the application state.
 *
 * @return {void}
*/
export const initializeAppState = () => {
	const { favoritesCount, customCount } = window.frmFormTemplatesVars;
	const { allTemplatesCategory, availableTemplateItems, freeTemplateItems, firstLockedFreeTemplate } = getElements();

	appState = {
		selectedCategory: VIEW_SLUGS.ALL_TEMPLATES,
		selectedCategoryEl: allTemplatesCategory,
		selectedTemplate: firstLockedFreeTemplate,
		notEmptySearchText: false,
		favoritesCount,
		customCount: Number( customCount ),
		availableTemplatesCount: availableTemplateItems.length,
		freeTemplatesCount: freeTemplateItems.length
	};
};

/**
 * Returns the current application state.
 *
 * @return {Object} The current state of the application.
 */
export const getAppState = () => appState;

/**
 * Updates the application state with new values.
 *
 * @param {Object} newState The new values to update the state.
 * @return {void}
 */
export const setAppState = newState => {
	appState = { ...appState, ...newState };
};

/**
 * Returns a specific property from the current application state.
 *
 * @param {string} propertyName The property name to retrieve from the state.
 * @return {*} The value of the specified property, or null if it doesn't exist.
 */
export const getAppStateProperty = propertyName =>
	Reflect.get( appState, propertyName ) ?? null;

/**
 * Updates a specific property in the application state with a new value.
 *
 * @param {string} propertyName The property name to update.
 * @param {*} value The new value to set.
 * @return {void}
 */
export const setAppStateProperty = ( propertyName, value ) => {
	if ( Reflect.has( appState, propertyName ) ) {
		Reflect.set( appState, propertyName, value );
	}
};
