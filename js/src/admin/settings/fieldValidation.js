/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { getFieldId } from './utils';

/**
 * Filters the default values for range settings validation.
 *
 * @since x.x
 *
 * @param {Object} defaults        The default range settings.
 * @param {number} defaults.maxnum Maximum allowed value. Default 9999999.
 * @param {number} defaults.minnum Minimum allowed value. Default 0.
 * @param {number} defaults.step   Step increment value. Default 1.
 * @return {Object} Modified defaults object.
 */
const { maxnum, minnum, step } = wp.hooks.applyFilters(
	'frm_range_settings_defaults',
	{
		maxnum: 9999999,
		minnum: 0,
		step: 1
	}
);

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
		const minValueInput = document.querySelector( `[name="field_options[minnum_${ fieldId }]"]` );
		const maxValueInput = document.querySelector( `[name="field_options[maxnum_${ fieldId }]"]` );
		if ( ! minValueInput || ! maxValueInput ) {
			return '';
		}

		return parseFloat( minValueInput.value || minnum ) >= parseFloat( maxValueInput.value || maxnum )
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
		const stepInput = document.querySelector( `[name="field_options[step_${ fieldId }]"]` );
		if ( ! stepInput ) {
			return '';
		}

		const stepInputValue = parseFloat( stepInput.value || step );
		if ( stepInputValue <= 0 ) {
			return __( 'Step value must be greater than 0.', 'formidable' );
		}

		const maxValueInput = document.querySelector( `[name="field_options[maxnum_${ fieldId }]"]` );
		if ( ! maxValueInput ) {
			return '';
		}

		return stepInputValue > parseFloat( maxValueInput.value || maxnum )
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
