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
import { hide, show } from '../utils';

/**
 * Displays form validation error messages.
 *
 * @param {string} inputId The ID selector for the input field with the error.
 * @param {string} errorId The ID selector for the error message display element.
 * @param {string} type The categorization of the error (e.g., "invalid", "empty").
 * @param {string} [message] Optional. The specific error message to display.
 * @return {void}
 */
export const showFormError = ( inputId, errorId, type, message ) => {
	const inputElement = document.querySelector( inputId );
	const errorElement = document.querySelector( errorId );

	// If a message is provided, update the span element's text that matches the error type
	if ( message ) {
		const span = errorElement.querySelector( `span[frm-error="${type}"]` );
		if ( span ) {
			span.textContent = message;
		}
	}

	// Assign the error type and make the error message visible
	errorElement.setAttribute( 'frm-error', type );
	show( errorElement );

	// Hide the error message when the user starts typing in the faulty input field
	inputElement.addEventListener( 'keyup', () => {
		hide( errorElement );
	}, { once: true });
};

/**
 * Displays errors related to the email address field.
 *
 * @param {string} type The categorization of the error (e.g., "invalid", "empty").
 * @return {void}
 */
export const showEmailAddressError = type => {
	showFormError( '#frm_leave_email', '#frm_leave_email_error', type );
};

/**
 * Displays errors related to the confirm email address field.
 *
 * @param {string} type The categorization of the error (e.g., "invalid", "empty").
 * @param {string} [message] Optional. The specific error message to display.
 * @return {void}
 */
export const showConfirmEmailAddressError = ( type, message ) => {
	showFormError( '#frm_code_from_email', '#frm_code_from_email_error', type, message );
};
