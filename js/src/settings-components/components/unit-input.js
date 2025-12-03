/**
 * Internal dependencies
 */
import { documentOn } from 'core/utils';

/**
 * Setup unit input handlers
 *
 * @return {void}
 */
export function setupUnitInputHandlers() {
	documentOn( 'change', '.frm-unit-input .frm-unit-input-control', onUnitInputChange );
	documentOn( 'change', '.frm-unit-input select', onUnitInputChange );
}

/**
 * Handle the change event for the unit input
 *
 * @private
 * @param {Event} event The event object.
 * @return {void}
 */
function onUnitInputChange( event ) {
	const unitInput = event.target.closest( '.frm-unit-input' );
	const control = unitInput.querySelector( '.frm-unit-input-control' );
	const unit = unitInput.querySelector( 'select' ).value;

	// Update input type when unit changes
	if ( event.target.matches( 'select' ) ) {
		control.type = '' === unit ? 'text' : 'number';
	}

	// Update the actual field value
	const inputValue = control.value.trim();
	unitInput.querySelector( 'input[type="hidden"]' ).value = '' !== inputValue ? inputValue + unit : '';
}

