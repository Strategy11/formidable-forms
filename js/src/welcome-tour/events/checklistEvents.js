/**
 * Internal dependencies
 */
import { getElements } from '../elements';

/**
 * Adds events to the checklist.
 *
 * @returns {void}
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
 * @returns {void}
 */
function onChecklistHeaderClick( checklist ) {
	checklist.classList.toggle( 'frm-checklist--collapsed' );
}

export default addChecklistEvents;
