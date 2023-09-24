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
 * Initializes URL and URLSearchParams objects from the current window's location
 */
const url = new URL( window.location.href );
const urlParams = url.searchParams;

/**
 * Gets the value of a specified query parameter from the current URL.
 *
 * @param {string} paramName The name of the query parameter to retrieve.
 * @return {string|null} The value associated with the specified query parameter name, or null if not found.
 */
export const getQueryParam = ( paramName ) => {
	return urlParams.get( paramName );
};

/**
 * Removes a query parameter from the current URL and returns the updated URL string.
 *
 * @param {string} paramName The name of the query parameter to remove.
 * @return {string} The updated URL string.
 */
export const removeQueryParam = ( paramName ) => {
	urlParams.delete( paramName );
	url.search = urlParams.toString();
	return url.toString();
};

/**
 * Sets the value of a query parameter in the current URL and returns the updated URL string.
 *
 * @param {string} paramName The name of the query parameter to set.
 * @param {string} paramValue The value to set for the query parameter.
 * @return {string} The updated URL string.
 */
export const setQueryParam = ( paramName, paramValue ) => {
	urlParams.set( paramName, paramValue );
	url.search = urlParams.toString();
	return url.toString();
};

/**
 * Checks if a query parameter exists in the current URL.
 *
 * @param {string} paramName The name of the query parameter to check.
 * @return {boolean} True if the query parameter exists, otherwise false.
 */
export const hasQueryParam = ( paramName ) => {
	return urlParams.has( paramName );
};
