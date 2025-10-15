/**
 * Internal dependencies
 */
import { doJsonPost, a } from 'core/utils';
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

/**
 * Marks a step as completed.
 *
 * @private
 * @param {string} stepKey The step key.
 * @return {Promise<void>}
 */
async function markStepAsCompleted( stepKey ) {
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

	const formData = new FormData();
	formData.append( 'step_key', stepKey );

	try {
		await doJsonPost( 'mark_checklist_step_as_completed', formData );

		stepElement.classList.remove( `${ STEP_PREFIX }--active` );
		stepElement.classList.add( `${ STEP_PREFIX }--completed` );

		const nextStep = stepElement.nextElementSibling;
		if ( ! nextStep ) {
			return;
		}

		nextStep.classList.add( `${ STEP_PREFIX }--active` );

		if ( nextStep.dataset.link ) {
			wrapStepTitleWithLink( nextStep );
		}
	} catch ( error ) {
		console.error( 'Failed to mark step as completed:', error );
	}
}

/**
 * Wraps step title with anchor link from dataset.
 *
 * @private
 * @param {HTMLElement} stepElement The step element to wrap.
 * @return {void}
 */
function wrapStepTitleWithLink( stepElement ) {
	const stepTitle = stepElement.querySelector( '.frm-checklist__step-title' );
	if ( stepTitle?.querySelector( 'a' ) ) {
		return;
	}

	const anchor = a( {
		href: stepElement.dataset.link,
		className: 'frm-h-stack-xs',
		children: Array.from( stepTitle.childNodes )
	} );

	stepTitle.replaceChildren( anchor );
}

export default initializeChecklist;
