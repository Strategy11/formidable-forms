/**
 * Runs validation and handles UI feedback.
 *
 * @since 6.32
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
		focusFieldOnModalDismiss( field );
	} else {
		field.classList.remove( 'frm_invalid_field' );
	}

	return errorMessage;
}

/**
 * Returns focus to the invalid field once the info modal is dismissed.
 *
 * @since 6.32
 *
 * @param {HTMLElement} field The invalid field element.
 *
 * @return {void}
 */
function focusFieldOnModalDismiss( field ) {
	const dismissers = document.querySelectorAll(
		'#frm_info_modal .dismiss, #frm_info_modal #frm-info-click, .ui-widget-overlay.ui-front'
	);

	function onModalClose() {
		setTimeout( () => field.focus(), 0 );
		dismissers.forEach( el => el.removeEventListener( 'click', onModalClose ) );
	}

	dismissers.forEach( el => el.addEventListener( 'click', onModalClose ) );
}
