/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { validateField } from './validateField';
import { getFieldId, getFieldType } from './utils';

/**
 * Gets the default values for range settings validation.
 *
 * @since x.x
 *
 * @param {HTMLElement} singleSettings The single settings element.
 *
 * @return {Object} The defaults object with maxNum, minNum, and step.
 */
export function getRangeSettingsDefaults( singleSettings ) {
	const fieldType = getFieldType( singleSettings ) || 'number';
	const defaultSettings = {
		maxNum: 9999999,
		minNum: 0,
		step: 1
	};

	/**
	 * Filters the default values for range settings validation.
	 *
	 * @since x.x
	 *
	 * @param {Object}      defaultSettings        The default settings.
	 * @param {Object}      context                Additional context.
	 * @param {HTMLElement} context.singleSettings The single settings element.
	 * @param {string}      context.fieldType      The field type.
	 *
	 * @return {Object} The filtered default settings.
	 */
	return wp.hooks.applyFilters( 'frm_range_settings_defaults', defaultSettings, { singleSettings, fieldType } );
}

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

	const singleSettings = field.closest( '.frm-single-settings' );
	const fieldId = getFieldId( singleSettings );
	if ( ! fieldId ) {
		return;
	}

	const minValueInput = document.querySelector( `[name="field_options[minnum_${ fieldId }]"]` );
	if ( ! minValueInput ) {
		return;
	}

	const maxValueInput = document.querySelector( `[name="field_options[maxnum_${ fieldId }]"]` );
	if ( ! maxValueInput ) {
		return;
	}

	return validateField( field, () => {
		const { minNum, maxNum } = getRangeSettingsDefaults( singleSettings );

		return parseFloat( minValueInput.value || minNum ) >= parseFloat( maxValueInput.value || maxNum )
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

	const singleSettings = field.closest( '.frm-single-settings' );
	const fieldId = getFieldId( singleSettings );
	if ( ! fieldId ) {
		return;
	}

	const stepInput = document.querySelector( `[name="field_options[step_${ fieldId }]"]` );
	if ( ! stepInput ) {
		return;
	}

	return validateField( field, () => {
		const { step, minNum, maxNum } = getRangeSettingsDefaults( singleSettings );
		const stepInputValue = parseFloat( stepInput.value || step );
		if ( stepInputValue <= 0 ) {
			return __( 'Step value must be greater than 0.', 'formidable' );
		}

		const maxValueInput = document.querySelector( `[name="field_options[maxnum_${ fieldId }]"]` );
		if ( ! maxValueInput ) {
			return '';
		}

		const minValueInput = document.querySelector( `[name="field_options[minnum_${ fieldId }]"]` );
		const minValue = parseFloat( minValueInput?.value || minNum );
		const maxValue = parseFloat( maxValueInput.value || maxNum );

		return stepInputValue > maxValue - minValue
			? __( 'Step value cannot be greater than the difference between the minimum and maximum values.', 'formidable' )
			: '';
	} );
}

/**
 * Validates all range related settings for a field and lets add-ons extend the validation.
 *
 * This is the single entry point used by the builder change handler. It runs
 * the number range and step checks, then exposes the `frm_validate_range_settings`
 * filter so add-ons (like Pro) can contribute additional checks (e.g. gap range)
 * without the core needing to know anything about them.
 *
 * @since x.x
 *
 * @param {HTMLElement} field The field element being validated.
 *
 * @return {string} The error message, or empty string when valid.
 */
export function validateRangeSettings( field ) {
	let errorMessage = validateNumberRangeSetting( field );
	if ( ! errorMessage ) {
		errorMessage = validateStepSetting( field );
	}

	/**
	 * Filters the range settings validation result so add-ons can add their own checks.
	 *
	 * @since x.x
	 *
	 * @param {string}      errorMessage  The current error message, or empty string when valid.
	 * @param {Object}      context       Additional context.
	 * @param {HTMLElement} context.field The field element being validated.
	 *
	 * @return {string} The (possibly updated) error message.
	 */
	return wp.hooks.applyFilters( 'frm_validate_range_settings', errorMessage || '', { field } );
}
