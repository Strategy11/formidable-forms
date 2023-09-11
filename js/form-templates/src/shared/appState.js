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
import { ALL_TEMPLATES } from './';

const { favoritesCount, FEATURED_TEMPLATES_KEYS } = window.frmFormTemplatesVars;
const { allTemplatesCategory } = getElements();

let appState = {
	selectedCategory: ALL_TEMPLATES,
	selectedCategoryEl: allTemplatesCategory,
	notEmptySearchText: false,
	FEATURED_TEMPLATES_KEYS,
	favoritesCount
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
 */
export const setAppState = ( newState ) => {
	appState = { ...appState, ...newState };
};

/**
 * Returns a specific property from the current application state.
 *
 * @param {string} propertyName The property name to retrieve from the state.
 * @return {*} The value of the specified property, or null if it doesn't exist.
 */
export const getAppStateProperty = ( propertyName ) =>
	Reflect.get( appState, propertyName ) ?? null;

/**
 * Updates a specific property in the application state with a new value.
 *
 * @param {string} propertyName The property name to update.
 * @param {*} value The new value to set.
 */
export const setAppStateProperty = ( propertyName, value ) => {
	if ( Reflect.has( appState, propertyName ) ) {
		Reflect.set( appState, propertyName, value );
	}
};