/**
 * External dependencies
 */
import { CURRENT_CLASS } from 'core/constants';

/**
 * Internal dependencies
 */
import { getElements } from "../elements";
import { STEPS } from '../shared';

const COMPLETED_STEP_CLASS = 'frm-completed-step';

/**
 * Updates the rootline to reflect the current and completed steps.
 *
 * - Applies COMPLETED_STEP_CLASS to steps before the current one.
 * - Applies CURRENT_CLASS to the current step, unless it is the success step.
 *
 * @param {string} currentStep The current step in the process.
 * @return {void}
 */
export function updateRootline( currentStep ) {
	if ( currentStep === STEPS.UNSUCCESSFUL ) {
		currentStep = STEPS.SUCCESS;
	}

	const { rootline } = getElements();
	const currentItem = rootline.querySelector( `.frm-rootline-item[data-step="${currentStep}"]` );

	rootline.querySelectorAll( '.frm-rootline-item' ).forEach( item => {
		item.classList.remove( COMPLETED_STEP_CLASS );
		item.classList.remove( CURRENT_CLASS );
	});

	let prevItem = currentItem.previousElementSibling;
	if ( prevItem ) {
		while ( prevItem ) {
			prevItem.classList.add( COMPLETED_STEP_CLASS );
			prevItem = prevItem.previousElementSibling; // move to the previous sibling
		}
	}

	if ( currentStep === STEPS.SUCCESS ) {
		currentItem.classList.add( COMPLETED_STEP_CLASS );
	} else {
		currentItem.classList.add( CURRENT_CLASS );
	}
}
