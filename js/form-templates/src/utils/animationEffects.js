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
import { PREFIX } from '../shared';

/**
 * Applies a fade-in animation to an element.
 *
 * @param {HTMLElement} element The element to apply the fade-in to.
 * @param {string} [fadingClass=`${PREFIX}-flex`] The CSS class to apply during the fading.
 * @return {void}
 */
export const fadeIn = ( element, fadingClass = `${ PREFIX }-flex` ) => {
	if ( ! element ) {
		return;
	}

	element.classList.add( `${ PREFIX }-fadein`, fadingClass );

	element.addEventListener( 'animationend', () => {
		element.classList.remove( `${ PREFIX }-fadein`, fadingClass );
	}, { once: true });
};
