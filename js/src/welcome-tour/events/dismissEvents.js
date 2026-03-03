/**
 * Internal dependencies
 */
import { doJsonPost } from 'core/utils';
import { getElements } from '../elements';

/**
 * Adds events to the dismiss button.
 *
 * @return {void}
 */
function addDismissEvents() {
	const { dismiss } = getElements();
	if ( ! dismiss ) {
		return;
	}

	dismiss.addEventListener( 'click', onDismissClick );
}

/**
 * Handles the dismiss button click event.
 *
 * @private
 * @return {void}
 */
function onDismissClick() {
	const { welcomeTour, spotlight } = getElements();

	welcomeTour?.remove();
	spotlight?.remove();

	doJsonPost( 'dismiss_welcome_tour', new FormData() );
}

export default addDismissEvents;
