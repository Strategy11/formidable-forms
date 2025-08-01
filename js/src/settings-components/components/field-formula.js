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
	documentOn( 'click', '.frm-field-formula-insert-field', onInsertFieldButtonClick );
	documentOn( 'click', '.frm-field-formula-button.frm-math-button', onMathButtonClick );
	documentOn( 'input', '.frm-field-formula-editor', handleShowFieldsModalShortcut );
	documentOn( 'focusin', '.frm-field-formula-editor', onFormulaEditorFocus );
}

/**
 * Handle the click event for the insert field button
 *
 * @private
 * @param {Event}       event        The click event
 * @param {HTMLElement} event.target The click target
 */
function onInsertFieldButtonClick( { target } ) {
	// Focus on the Fields modal search input
	const searchInput = target.closest( '.frm-field-formula' )?.querySelector( 'input[id^="frm_calc_"]' );
	if ( ! searchInput ) {
		return;
	}

	searchInput.value = '';
	searchInput.focus();
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

/**
 * Handle `#` character in formula editor and trigger field insertion
 *
 * @private
 * @param {Event}       event        The input event
 * @param {HTMLElement} event.target The input target
 */
function handleShowFieldsModalShortcut( event ) {
	// Return early if characters are being deleted rather than added
	if ( ! event.inputType.startsWith( 'insert' ) ) {
		return;
	}

	const formulaEditor = event.target;
	const fieldFormula = formulaEditor.closest( '.frm-field-formula' );
	if ( ! fieldFormula ) {
		return;
	}

	const cursorPosition = formulaEditor.selectionStart;
	// Check if character before the previous one is '#' (e.g., typing 'N' after '#')
	if ( cursorPosition < 2 || formulaEditor.value[cursorPosition - 2] !== '#' ) {
		return;
	}

	const searchChar = formulaEditor.value[cursorPosition - 1];
	formulaEditor.dataset.searchChar = searchChar;
	fieldFormula.querySelector( '.frm-field-formula-insert-field' )?.click();

	// Set the search input to the character typed after '#'
	const searchInput = document.getElementById( `frm_calc_${fieldFormula.dataset.fieldId}-search-input` );
	if ( searchInput ) {
		searchInput.value = searchChar;
		searchInput.dispatchEvent( new Event( 'input', { bubbles: true } ) );
	}
}

/**
 * Handle focus on formula editor
 *
 * @private
 * @param {Event}       event        The focus event
 * @param {HTMLElement} event.target The focus target
 */
function onFormulaEditorFocus( { target: formulaEditor } ) {
	const { searchChar } = formulaEditor.dataset;
	if ( ! searchChar ) {
		return;
	}

	formulaEditor.dataset.searchChar = '';

	// Find and remove '#' + searchChar before cursor position
	const pattern = `#${searchChar}`;
	const cursorPosition = formulaEditor.selectionStart;
	const textBeforeCursor = formulaEditor.value.slice( 0, cursorPosition );
	const lastIndex = textBeforeCursor.lastIndexOf( pattern );

	if ( lastIndex === -1 ) {
		return;
	}

	formulaEditor.value = `${formulaEditor.value.slice( 0, lastIndex )}${formulaEditor.value.slice( lastIndex + pattern.length )}`;

	// Set cursor position to the end of the content
	const endPosition = formulaEditor.value.length;
	formulaEditor.setSelectionRange( endPosition, endPosition );

	// Close the calculation insert field modal
	formulaEditor.closest( '.frm-field-formula' ).querySelector( '.frm-field-formula-insert-field' )?.click();
}
