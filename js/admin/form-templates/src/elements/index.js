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
import getElements from './getElements';
import createEmptyStateElement from './emptyStateElement';

let elements = {};

/**
 * Initialize the elements.
 *
 * @since x.x
 */
export const initializeElements = () => {
	elements = getElements();

	const emptyState = createEmptyStateElement();
	elements.bodyContent.appendChild( emptyState );
};

export default elements;
