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
import { ALL_TEMPLATES_SLUG } from './';
import { allTemplatesCategory } from '../elements';

// Extract initial data from the global frmFormTemplatesVars variable
const { favoritesCount, FEATURED_TEMPLATES_KEYS } = window.frmFormTemplatesVars;

let appState = {
	selectedCategory: ALL_TEMPLATES_SLUG,
	selectedCategoryEl: allTemplatesCategory,
	notEmptySearchText: false,
	FEATURED_TEMPLATES_KEYS,
	favoritesCount
};

/**
 * Returns the current application state.
 *
 * @since x.x
 *
 * @returns {Object} The current state of the application.
 */
export const getAppState = () => appState;

/**
 * Updates the application state with new values.
 *
 * @since x.x
 *
 * @param {Object} newState The new values to update the state.
 */
export const setAppState = newState => {
	appState = { ...appState, ...newState };
};

/**
 * Returns a specific property from the current application state.
 *
 * @since x.x
 *
 * @param {string} propertyName The property name to retrieve from the state.
 * @returns {*} The value of the specified property, or null if it doesn't exist.
 */
export const getAppStateProperty = propertyName => Reflect.get( appState, propertyName ) ?? null;

/**
 * Updates a specific property in the application state with a new value.
 *
 * @since x.x
 *
 * @param {string} propertyName The property name to update.
 * @param {*} value The new value to set.
 */
export const setAppStateProperty = ( propertyName, value ) => {
	if ( Reflect.has( appState, propertyName ) ) {
		Reflect.set( appState, propertyName, value );
	} else {
		console.warn( `Property "${propertyName}" does not exist in the application state.` );
	}
};
