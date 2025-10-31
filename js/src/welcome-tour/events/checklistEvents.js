/**
 * Internal dependencies
 */
import { getElements } from '../elements';

/**
 * Adds events to the checklist.
 *
 * @return {void}
 */
function addChecklistEvents() {
	const { checklist } = getElements();
	if ( ! checklist ) {
		return;
	}

	checklist.querySelector( '.frm-checklist__header' ).addEventListener( 'click', () => onChecklistHeaderClick( checklist ) );
}

/**
 * Handles the checklist header click event.
 *
 * @private
 * @param {HTMLElement} checklist The checklist element.
 * @return {void}
 */
function onChecklistHeaderClick( checklist ) {
	checklist.classList.toggle( 'frm-checklist--collapsed' );
}

export default addChecklistEvents;
