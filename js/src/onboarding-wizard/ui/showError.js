/**
 * Internal dependencies
 */
import { PREFIX } from '../shared';
import { showFormError } from '../utils';

/**
 * Displays errors related to the email address field.
 *
 * @param {string} type The categorization of the error (e.g., "invalid", "empty").
 * @return {void}
 */
export const showEmailAddressError = ( type, input ) => {
	showFormError( '#' + input.id, '#' + input.nextElementSibling.id, type );
};
