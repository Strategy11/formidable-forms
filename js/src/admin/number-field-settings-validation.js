/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Validates number field settings (min/max/step).
 *
 * @since x.x
 *
 * @param {HTMLElement} target  The changed input element.
 * @param {string}      fieldId The field ID.
 *
 * @return {string} Error message or empty string if valid.
 */
function getNumberFieldSettingsValidationMessage( target, fieldId ) {
	let errorMessage = '';
	const minNumInput = document.querySelector( `[name="field_options[minnum_${ fieldId }]"]` );
	const maxNumInput = document.querySelector( `[name="field_options[maxnum_${ fieldId }]"]` );

	if ( ! minNumInput || ! maxNumInput ) {
		return errorMessage;
	}

	const minNum = parseFloat( minNumInput.value || 0 );
	const maxNum = parseFloat( maxNumInput.value || 9999999 );

	// Validate number range (frm-number-range)
	if ( target.closest( '.frm-number-range' ) ) {
		if ( minNum >= maxNum ) {
			errorMessage = __( 'Minimum value cannot be greater than or equal to maximum value.', 'formidable' );
		}
	}

	// Validate step (frm-step)
	if ( target.closest( '.frm-step' ) ) {
		const step = parseFloat( target.value );
		if ( step <= 0 ) {
			errorMessage = __( 'Step value must be greater than 0.', 'formidable' );
		} else if ( step > maxNum ) {
			errorMessage = __( 'Step value must be less than maximum value.', 'formidable' );
		}
	}

	// Allow Pro and other plugins to extend validation
	return frmAdminBuild.hooks.applyFilters(
		'frm_number_field_settings_validation_message',
		errorMessage,
		{ target, fieldId, minNum, maxNum }
	);
}
