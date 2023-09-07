/**
 * Copyright (C) 2010 Formidable Forms
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
import { HIDDEN_CLASS } from '../shared';

/**
 * Shows specified elements by removing the hidden class.
 *
 * @since x.x
 *
 * @param {Array<Element>} elements An array of elements to show.
 */
export const showElements = ( elements ) => elements?.forEach( element => show( element ) );

/**
 * Hides specified elements by adding the hidden class.
 *
 * @since x.x
 *
 * @param {Array<Element>} elements An array of elements to hide.
 */
export const hideElements = ( elements ) => elements?.forEach( element => hide( element ) );

/**
 * Removes the hidden class to show the element.
 *
 * @since x.x
 *
 * @param {Element} element The element to show.
 */
export const show = ( element ) => element?.classList.remove( HIDDEN_CLASS );

/**
 * Adds the hidden class to hide the element.
 *
 * @since x.x
 *
 * @param {Element} element The element to hide.
 */
export const hide = ( element ) => element?.classList.add( HIDDEN_CLASS );

/**
 * Checks if an element is visible.
 *
 * @since x.x
 *
 * @param {HTMLElement} element The HTML element to check for visibility.
 * @returns {boolean} Returns true if the element is visible, otherwise false.
 */
export const isVisible = ( element ) => element?.classList.contains( HIDDEN_CLASS );
