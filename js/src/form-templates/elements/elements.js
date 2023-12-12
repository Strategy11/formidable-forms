/**
 * Internal dependencies
 */
import { createEmptyStateElement, getEmptyStateElements } from './emptyStateElement';
import getDOMElements from './getDOMElements';

let elements = null;

/**
 * Initialize the elements.
 *
 * @return {void}
 */
export function initializeElements() {
	elements = getDOMElements();
	addEmptyStateElements();
	addBodyContentChildren();
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

/**
 * Inject empty state elements into the DOM and the elements object.
 *
 * @private
 * @return {void}
 */
function addEmptyStateElements() {
	if ( elements.emptyState ) {
		return;
	}

	const emptyState = createEmptyStateElement();
	elements.bodyContent?.appendChild( emptyState );

	const emptyStateElements = getEmptyStateElements();
	elements = { ...elements, ...emptyStateElements };
}

/**
 * Add children of the bodyContent to the elements object.
 *
 * @private
 * @return {void}
 */
function addBodyContentChildren() {
	const bodyContentChildren = elements.bodyContent?.children;
	elements = { ...elements, bodyContentChildren };
}
