/**
 * Internal dependencies
 */
import { documentOn } from 'core/utils';

/**
 * Setup field formula handlers
 *
 * @return {void}
 */
export function setupFieldFormulaHandlers() {
	documentOn( 'click', '.frm-field-formula-button.frm-math-button', onMathButtonClick );
}

/**
 * Handle the click event for the math buttons
 *
 * @private
 * @param {Event} event The event object.
 * @return {void}
 */
function onMathButtonClick( event ) {
	const mathButton = event.target.classList.contains( 'frm-math-button' ) ? event.target : event.target.closest( '.frm-field-formula-button.frm-math-button' );
	if ( ! mathButton ) {
		return;
	}

	const formulaEditor = mathButton.closest( '.frm-field-formula' )?.querySelector( '.frm-field-formula-editor' );
	if ( ! formulaEditor ) {
		return;
	}

	formulaEditor.value += mathButton.querySelector( '.frm-math-button-text' )?.textContent;
	formulaEditor.focus();
}

