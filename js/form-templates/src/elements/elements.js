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

let elements = getDOMElements();

/**
 * Initialize the elements.
 *
 * @return {void}
 */
export function initializeElements() {
	addEmptyStateElements();
}

export function addEmptyStateElements() {
	if ( elements.emptyState ) {
		return;
	}

	const emptyState = createEmptyStateElement();
	elements.bodyContent?.appendChild( emptyState );

	const emptyStateElements = getEmptyStateElements();
	elements = { ...elements, ...emptyStateElements };
}

/**
 * Gets essential DOM elements.
 *
 * @return {Object} DOM elements.
 */
export function getElements() {
	if ( null !== elements ) {
		return elements;
	}

	initializeElements();
}
