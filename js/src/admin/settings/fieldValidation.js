/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { getFieldId } from './utils';

/**
 * Validates number range setting.
 *
 * @since x.x
 *
 * @param {HTMLElement} field The field element being validated.
 */
export function validateNumberRangeSetting( field ) {
	if ( ! field.closest( '.frm-number-range' ) ) {
		return;
	}

	const fieldId = getFieldId( field );
	if ( ! fieldId ) {
		return;
	}

	validateField( field, () => {
		const minNum = document.querySelector( `[name="field_options[minnum_${ fieldId }]"]` )?.value;
		const maxNum = document.querySelector( `[name="field_options[maxnum_${ fieldId }]"]` )?.value;
		if ( ! minNum || ! maxNum ) {
			return '';
		}

		return parseFloat( minNum ) >= parseFloat( maxNum )
			? __( 'Minimum value cannot be greater than or equal to maximum value.', 'formidable' )
			: '';
	} );
}

/**
 * Validates step setting.
 *
 * @since x.x
 *
 * @param {HTMLElement} field The field element being validated.
 */
export function validateStepSetting( field ) {
	if ( ! field.closest( '.frm-step' ) ) {
		return;
	}

	const fieldId = getFieldId( field );
	if ( ! fieldId ) {
		return;
	}

	validateField( field, () => {
		let step = field.value;
		if ( ! step ) {
			return '';
		}

		step = parseFloat( step );
		if ( step <= 0 ) {
			return __( 'Step value must be greater than 0.', 'formidable' );
		}

		const maxNum = document.querySelector( `[name="field_options[maxnum_${ fieldId }]"]` )?.value;
		return step > parseFloat( maxNum )
			? __( 'Step value must be less than maximum value.', 'formidable' )
			: '';
	} );
}

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
