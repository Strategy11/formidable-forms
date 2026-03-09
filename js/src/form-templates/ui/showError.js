/**
 * External dependencies
 */
import { showFormError } from 'core/utils';

/**
 * Displays errors related to the email address field.
 *
 * @param {string} type The categorization of the error (e.g., "invalid", "empty").
 * @return {void}
 */
export const showEmailAddressError = type => {
	showFormError( '#frm_leave_email', '#frm_leave_email_error', type );
};
