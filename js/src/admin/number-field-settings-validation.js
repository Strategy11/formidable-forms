/**
 * WordPress dependencies
 */
import { _x } from '@wordpress/i18n';

/**
 * Handles number field settings validation on change.
 *
 * @since x.x
 *
 * @param {HTMLElement} target The changed input element.
 *
 * @return {void}
 */
export function handleNumberFieldSettingsChange( target ) {
	if ( ! target.closest( '.frm-number-range' ) && ! target.closest( '.frm-step' ) ) {
		return;
	}

	const fieldId = target.closest( '.frm-single-settings' )?.dataset.fid;
	if ( ! fieldId ) {
		return;
	}

	const errorMessage = getNumberFieldSettingsValidationMessage( target, fieldId );
	if ( errorMessage ) {
		frmAdminBuild.infoModal( errorMessage );
		target.classList.add( 'frm_invalid_field' );
	} else {
		target.classList.remove( 'frm_invalid_field' );
	}
}

/**
 * Validates number field settings (min/max/step).
 *
 * @since x.x
 *
 * @private
 * @param {HTMLElement} target  The changed input element.
 * @param {string}      fieldId The field ID.
 *
 * @return {string|void} Error message or empty string if valid.
 */
function getNumberFieldSettingsValidationMessage( target, fieldId ) {
	let errorMessage = '';
	const minNum = parseFloat( document.querySelector( `[name="field_options[minnum_${ fieldId }]"]` ).value || 0 );
	const maxNum = parseFloat( document.querySelector( `[name="field_options[maxnum_${ fieldId }]"]` ).value || 9999999 );

	if ( target.closest( '.frm-number-range' ) ) {
		if ( minNum >= maxNum ) {
			errorMessage = _x( 'Minimum value cannot be greater than or equal to maximum value.', 'the min and max values of a number field', 'formidable' );
		}
	}

	// Validate the "Step" setting
	if ( target.closest( '.frm-step' ) ) {
		const step = parseFloat( target.value );
		if ( step <= 0 ) {
			errorMessage = _x( 'Step value must be greater than 0.', 'the step value of a number field', 'formidable' );
		} else if ( step > maxNum ) {
			errorMessage = _x( 'Step value must be less than maximum value.', 'the step value of a number field', 'formidable' );
		}
	}

	/**
	 * Filters the number field settings validation message.
	 *
	 * @since x.x
	 *
	 * @param {string} errorMessage The error message.
	 * @param {Object} args         The arguments object.
	 */
	return frmAdminBuild.hooks.applyFilters(
		'frm_number_field_settings_validation_message',
		errorMessage,
		{ target, fieldId, minNum, maxNum }
	);
}

