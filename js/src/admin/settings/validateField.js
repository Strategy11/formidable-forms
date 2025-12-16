/**
 * Runs validation and handles UI feedback.
 *
 * @since x.x
 *
 * @param {HTMLElement} field    The field element being validated.
 * @param {Function}    getError Function that returns error message or empty string.
 *
 * @return {string} The error message or empty string.
 */
export function validateField( field, getError ) {
	const errorMessage = getError();
	if ( errorMessage ) {
		frmAdminBuild.infoModal( errorMessage );
		field.classList.add( 'frm_invalid_field' );
	} else {
		field.classList.remove( 'frm_invalid_field' );
	}

	return errorMessage;
}
