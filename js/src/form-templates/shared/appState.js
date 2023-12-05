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
	const { templatesCount, favoritesCount, customCount } = window.frmFormTemplatesVars;
	const { allTemplatesCategory, availableTemplateItems, freeTemplateItems, firstLockedFreeTemplate } = getElements();

	const availableTemplatesCount = availableTemplateItems.length;
	const extraTemplatesCount = templatesCount - availableTemplatesCount;

	appState = {
		selectedCategory: VIEW_SLUGS.ALL_TEMPLATES,
		selectedCategoryEl: allTemplatesCategory,
		selectedTemplate: firstLockedFreeTemplate,
		notEmptySearchText: false,
		favoritesCount,
		customCount: Number( customCount ),
		availableTemplatesCount,
		freeTemplatesCount: freeTemplateItems.length,
		extraTemplatesCount
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
