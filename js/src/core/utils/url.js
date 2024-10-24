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
export const getQueryParam = paramName => urlParams.get( paramName );

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
 * Sets the value of a query parameter in the current URL and optionally updates the browser's history state.
 *
 * @param {string} paramName                  The name of the query parameter to set.
 * @param {string} paramValue                 The value to set for the query parameter.
 * @param {string} [updateMethod='pushState'] The method to use for updating the history state. Accepts 'pushState' or 'replaceState'.
 * @return {string} The updated URL string.
 */
export const setQueryParam = ( paramName, paramValue, updateMethod = 'pushState' ) => {
	urlParams.set( paramName, paramValue );
	url.search = urlParams.toString();

	if ([ 'pushState', 'replaceState' ].includes( updateMethod ) ) {
		const state = {[paramName]: paramValue};
		window.history[updateMethod]( state, '', url );
	}

	return url.toString();
};

/**
 * Checks if a query parameter exists in the current URL.
 *
 * @param {string} paramName The name of the query parameter to check.
 * @return {boolean} True if the query parameter exists, otherwise false.
 */
export const hasQueryParam = paramName => urlParams.has( paramName );
