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

	// Update the field value
	unitInput.querySelector( 'input[type="hidden"]' ).value =
		unitInput.querySelector( 'input[type="number"]' ).value + unitInput.querySelector( 'select' ).value;
}

