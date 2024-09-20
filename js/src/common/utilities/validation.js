/**
 * Validates an email address using a regular expression.
 *
 * @param {string} email The email address to validate.
 * @return {boolean} True if the email address is valid, otherwise false.
 */
export const isValidEmail = email =>
	typeof email === 'string' ? /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/i.test( email ) : false;

/**
 * Validates if the given element is an instance of HTMLElement.
 *
 * @param {any} element Element to be checked.
 * @return {boolean} True if it's an HTMLElement, otherwise false.
 */
export const isHTMLElement = element => element instanceof HTMLElement || console.warn( 'Invalid argument: Element must be an instance of HTMLElement' ) || false;

/**
 * Checks if the given object is empty.
 *
 * @param {Object} obj The object to check.
 * @return {boolean} True if the object is empty, otherwise false.
 */
export const isEmptyObject = obj => Object.keys( obj ).length === 0 && obj.constructor === Object;
