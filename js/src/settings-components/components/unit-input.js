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
	documentOn( 'change', '.frm-unit-input input[type="number"]', onUnitInputChange );
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
	const numberInputValue = unitInput.querySelector( 'input[type="number"]' ).value.trim();

	// Update the actual field value
	unitInput.querySelector( 'input[type="hidden"]' ).value = numberInputValue !== ''
		? numberInputValue + unitInput.querySelector( 'select' ).value
		: '';
}

