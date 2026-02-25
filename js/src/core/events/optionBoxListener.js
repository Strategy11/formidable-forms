/**
 * Internal dependencies
 */
import { CHECKED_CLASS } from 'core/constants';

const OPTION_BOX_CLASS = '.frm-option-box';

/**
 * Manages event handling for an option-box.
 *
 * @return {void}
 */
export function addOptionBoxEvents() {
	const optionBoxes = document.querySelectorAll( OPTION_BOX_CLASS );

	optionBoxes.forEach( optionBox => {
		optionBox.addEventListener( 'click', onOptionBoxClick );
	} );
}

/**
 * Handles the click event on a option box item.
 *
 * @private
 * @param {Event} event The click event object.
 */
function onOptionBoxClick( event ) {
	if ( event.target.tagName.toLowerCase() !== 'input' ) {
		return;
	}

	const optionBox = event.currentTarget.closest( OPTION_BOX_CLASS );
	optionBox.classList.toggle( CHECKED_CLASS );
}
