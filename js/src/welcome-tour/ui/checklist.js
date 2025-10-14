/**
 * Internal dependencies
 */
import { doJsonPost } from 'core/utils';
import { getElements } from '../elements';
import { PROGRESS_BAR_PERCENT } from '../shared';

const STEP_PREFIX = 'frm-checklist__step';

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
 * @return {void}
 */
function setProgressBarPercent() {
	const progressFill = document.querySelector( '.frm-welcome-tour .frm-checklist__progress-fill' );
	if ( ! progressFill ) {
		return;
	}

	progressFill.style.width = `${ PROGRESS_BAR_PERCENT }%`;
}

/**
 * Marks a step as completed.
 *
 * @param {string} stepKey The step key.
 * @return {void}
 */
function markStepAsCompleted( stepKey ) {
	const { checklist } = getElements();
	if ( ! checklist ) {
		return;
	}

	const stepElement = document.getElementById( `${ STEP_PREFIX }-${ stepKey }` );
	if ( ! stepElement ) {
		return;
	}

	if ( ! stepElement.classList.contains( `${ STEP_PREFIX }--active` ) ) {
		return;
	}

	stepElement.classList.remove( `${ STEP_PREFIX }--active` );
	stepElement.classList.add( `${ STEP_PREFIX }--completed` );
	stepElement.nextElementSibling?.classList.add( `${ STEP_PREFIX }--active` );

	const formData = new FormData();
	formData.append( 'step_key', stepKey );
	doJsonPost( 'mark_checklist_step_as_completed', formData );
}

export default initializeChecklist;
