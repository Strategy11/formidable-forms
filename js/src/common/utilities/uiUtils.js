/**
 * Internal dependencies
 */
import { hide, show } from '.';

/**
 * Displays form validation error messages.
 *
 * @param {string} inputId   The ID selector for the input field with the error.
 * @param {string} errorId   The ID selector for the error message display element.
 * @param {string} type      The categorization of the error (e.g., "invalid", "empty").
 * @param {string} [message] Optional. The specific error message to display.
 * @return {void}
 */
export const showFormError = ( inputId, errorId, type, message ) => {
	const inputElement = document.querySelector( inputId );
	const errorElement = document.querySelector( errorId );

	if ( ! inputElement || ! errorElement ) {
		console.warn( 'showFormError: Unable to find input or error element.' );
		return;
	}

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
