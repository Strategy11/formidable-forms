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
		const { step, maxNum } = getRangeSettingsDefaults( singleSettings );
		const stepInputValue = parseFloat( stepInput.value || step );
		if ( stepInputValue <= 0 ) {
			return __( 'Step value must be greater than 0.', 'formidable' );
		}

		const maxValueInput = document.querySelector( `[name="field_options[maxnum_${ fieldId }]"]` );
		if ( ! maxValueInput ) {
			return '';
		}

		return stepInputValue > parseFloat( maxValueInput.value || maxNum )
			? __( 'Step value must be less than maximum value.', 'formidable' )
			: '';
	} );
}
