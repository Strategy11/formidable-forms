/**
 * Internal dependencies
 */
import { PROGRESS_BAR_PERCENT } from '../shared';
import { markStepAsCompleted } from '../utils';

/**
 * Initializes the checklist.
 *
 * @return {void}
 */
function initializeChecklist() {
	setProgressBarPercent();

	document.addEventListener( 'frm_added_field', () => markStepAsCompleted( 'add-fields' ) );
}

/**
 * Sets the progress bar percent.
 *
 * @private
 * @return {void}
 */
function setProgressBarPercent() {
	const progressFill = document.querySelector( '.frm-welcome-tour .frm-checklist__progress-fill' );
	if ( ! progressFill ) {
		return;
	}

	progressFill.style.width = `${ PROGRESS_BAR_PERCENT }%`;
}

export default initializeChecklist;
