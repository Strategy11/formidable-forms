/**
 * Internal dependencies
 */
import { getQueryParam, onClickPreventDefault } from 'core/utils';
import { markStepAsCompleted } from '../utils';

/**
 * Adds events to the styler update button.
 *
 * @returns {void}
 */
function addStylerUpdateEvents() {
	if ( 'formidable-styles' !== getQueryParam( 'page' ) ) {
		return;
	}

	onClickPreventDefault( document.getElementById( 'frm_submit_side_top' ), onStylerUpdateClick );
}

/**
 * Handles the styler update button click event.
 *
 * @private
 * @returns {void}
 */
async function onStylerUpdateClick() {
	await markStepAsCompleted( 'style-form' );
	window.location.reload();
}

export default addStylerUpdateEvents;
