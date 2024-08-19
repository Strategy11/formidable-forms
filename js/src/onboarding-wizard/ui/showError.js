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
export const showEmailAddressError = type => {
	showFormError( `#${PREFIX}-default-email-field`, `#${PREFIX}-email-step-error`, type );
};
