/**
 * Internal dependencies
 */
import { documentOn } from 'core/utils';

/**
 * Setup custom toggle group handlers
 *
 * @return {void}
 */
export function setupCustomToggleGroupHandlers() {
	documentOn( 'change', '[id^="frm-default-type-calc-"]:not(:checked)', onCalculateValueSettingOff );
	documentOn( 'change', '[id^="frm-default-type-get_values_field-"]:not(:checked)', onLookupSettingOff );
	documentOn( 'change', '[id^="frm-enable-conditional-logic"]', onEnableConditionalLogicChange );
}

/**
 * Reset the "Calculate Value" toggle related fields when it's off
 *
 * @private
 * @param {Event} event The event object.
 * @return {void}
 */
function onCalculateValueSettingOff( event ) {
	const fieldId = event.target.closest( '[data-fid]' )?.dataset.fid;
	if ( ! fieldId ) {
		return;
	}

	const calcField = document.getElementById( `frm_calc_${ fieldId }` );
	if ( calcField ) {
		calcField.value = '';
	}

	const calcTypeField = document.querySelector( `[name^="field_options[calc_type_${ fieldId }]"]` );
	if ( calcTypeField ) {
		calcTypeField.checked = true;
	}
}

/**
 * Reset the "Lookup" toggle related fields when it's off
 *
 * @private
 * @param {Event} event The event object.
 * @return {void}
 */
function onLookupSettingOff( event ) {
	const fieldId = event.target.closest( '[data-fid]' )?.dataset.fid;
	if ( ! fieldId ) {
		return;
	}

	const valuesForm = document.getElementById( `get_values_form_${ fieldId }` );
	if ( valuesForm ) {
		valuesForm.value = '';
	}

	const valuesField = document.getElementById( `get_values_field_${ fieldId }` );
	if ( valuesField ) {
		valuesField.value = '';
	}

	const lookupLabel = document.getElementById( `frm_watch_lookup_label_${ fieldId }` );
	if ( lookupLabel ) {
		lookupLabel.classList.add( 'frm_hidden!' );
	}

	const lookupBlock = document.getElementById( `frm_watch_lookup_block_${ fieldId }` );
	if ( lookupBlock ) {
		lookupBlock.innerHTML = '';
	}

	const mostRecentValue = document.getElementById( `get_most_recent_value_${ fieldId }` );
	if ( mostRecentValue ) {
		mostRecentValue.checked = false;
	}
}

/**
 * Handle the change event for the "Enable Conditional Logic" toggle
 *
 * @private
 * @param {Event} event The event object.
 * @return {void}
 */
function onEnableConditionalLogicChange( event ) {
	const toggleButton = event.target;
	const fieldId = toggleButton.closest( '[data-fid]' )?.dataset.fid;
	if ( ! fieldId ) {
		return;
	}

	const enableConditionalLogicField = document.querySelector( `[name="field_options[enable_conditional_logic_${ fieldId }]"]` );
	if ( enableConditionalLogicField ) {
		enableConditionalLogicField.value = toggleButton.checked ? '1' : '0';
	}
}
