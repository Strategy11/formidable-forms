/**
 * Internal dependencies
 */
import { CHECKED_CLASS } from '../constants';

/**
 * Manages event handling for an option-box.
 *
 * @return {void}
 */
function addOptionBoxEvents() {
	const optionBoxes = document.querySelectorAll( '.frm-option-box' );

	// Attach click event listeners to each option-boxes.
	optionBoxes.forEach( optionBox => {
		optionBox.addEventListener( 'click', onOptionBoxClick );
	});
}

function onOptionBoxClick( event )  {
	if ( event.target.tagName.toLowerCase() !== 'input' ) {
		return;
	}

	let optionBox = event.currentTarget.closest( '.frm-option-box' );
	optionBox.classList.toggle( CHECKED_CLASS );
}

export default addOptionBoxEvents;
