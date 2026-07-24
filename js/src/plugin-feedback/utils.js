const error = document.getElementById( 'frm-plugin-feedback-error' );

/**
 * Shows an error message for a form field.
 *
 * @param {string} type The categorization of the error (e.g., "invalid", "empty").
 * @return {void}
 */
export function showError( type ) {
	error.setAttribute( 'frm-error', type );
	error.classList.remove( 'frm_hidden' );
}

/**
 * Hides the error message.
 *
 * @return {void}
 */
export function hideError() {
	error.classList.add( 'frm_hidden' );
}
