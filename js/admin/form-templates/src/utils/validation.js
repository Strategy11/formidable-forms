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
import {
	PREFIX,
	ALL_TEMPLATES_SLUG,
	FAVORITES_SLUG,
	FEATURED_TEMPLATES_KEYS
} from '../shared';

/**
 * Checks if the category is "All Templates".
 *
 * @since x.x
 *
 * @param {string} category The category slug.
 * @returns {boolean} True if the category is "All Templates", otherwise false.
 */
export const isAllTemplatesCategory = ( category ) => ALL_TEMPLATES_SLUG === category;

/**
 * Checks if the category is "Favorites".
 *
 * @since x.x
 *
 * @param {string} category The category slug.
 * @returns {boolean} True if the category is "Favorites", otherwise false.
 */
export const isFavoritesCategory = ( category ) => FAVORITES_SLUG === category;

/**
 * Checks if a template is a favorite.
 *
 * @since x.x
 *
 * @param {HTMLElement} template The template element.
 * @returns {boolean} True if the template is a favorite, otherwise false.
 */
export const isFavoriteTemplate = ( template ) => template?.classList.contains( `${PREFIX}-favorite-item` );

/**
 * Checks if a template is custom.
 *
 * @since x.x
 *
 * @param {HTMLElement} template The template element.
 * @returns {boolean} True if the template is custom, otherwise false.
 */
export const isCustomTemplate = ( template ) => template?.classList.contains( `${PREFIX}-custom-item` );

/**
 * Checks if a template is featured.
 *
 * @since x.x
 *
 * @param {HTMLElement} template The template element.
 * @returns {boolean} True if the template is featured, otherwise false.
 */
export const isFeaturedTemplate = ( template ) => FEATURED_TEMPLATES_KEYS.includes( Number( template.dataset.id ) );
