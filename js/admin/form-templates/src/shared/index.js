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

export * from './constants';

/**
 * Exports commonly used global variables.
 *
 * @typedef {Object} GlobalVars
 * @property {string} pluginURL The URL of the plugin.
 * @property {number} favoritesCount The count of favorite templates.
 * @property {string[]} FEATURED_TEMPLATES_KEYS The keys of the featured templates.
 * @property {Function} tag Function for creating HTML tags.
 * @property {Function} div Function for creating HTML div.
 * @property {Function} span Function for creating HTML span.
 * @property {Function} a Function for creating HTML anchor tags.
 * @property {Function} img Function for creating HTML image tags.
 * @property {Function} doJsonPost Function for making JSON POST requests.
 * @property {Function} onClickPreventDefault Click handler that cancels default and runs callback.
 * @property {Function} initSearch Function to initialize search functionality.
 */
export const { url: PLUGIN_URL } = window.frmGlobal;
export const { favoritesCount, FEATURED_TEMPLATES_KEYS } = window.frmFormTemplatesVars;
export const { tag, div, span, a, img } = window.frmDom;
export const { doJsonPost } = window.frmDom?.ajax;
export const { onClickPreventDefault } = window.frmDom?.util;
export const initSearch = window.frmDom?.search?.init;
