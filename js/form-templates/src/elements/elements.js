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
