/**
 * External dependencies
 */
import { showFormError } from 'core/utils';

/**
 * Displays errors related to the email address field.
 *
 * @since 6.15 Added the `input` param.
 *
 * @param {string}           type  The categorization of the error (e.g., "invalid", "empty").
 * @param {HTMLInputElement} input The input element to which the error is related.
 * @return {void}
 */
export const showEmailAddressError = ( type, input ) => {
	showFormError( `#${input.id}`, `#${input.nextElementSibling.id}`, type );
};
