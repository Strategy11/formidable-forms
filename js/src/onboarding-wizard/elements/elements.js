/**
 * Internal dependencies
 */
import getDOMElements from './getDOMElements';

let elements = null;

/**
 * Initialize the elements.
 *
 * @return {void}
 */
export function initializeElements() {
	elements = getDOMElements();
}

/**
 * Retrieve the initialized essential DOM elements.
 *
 * @return {Object|null} The initialized elements object or null.
 */
export function getElements() {
	return elements;
}

/**
 * Add new elements to the elements object.
 *
 * @param {Object} newElements An object containing new elements to be added.
 * @return {void} Updates the global `elements` object by merging the new elements into it.
 */
export function addElements( newElements ) {
	elements = { ...elements, ...newElements };
}
