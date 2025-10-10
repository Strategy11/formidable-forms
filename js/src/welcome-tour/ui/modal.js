/**
 * Internal dependencies
 */
import { buildBeginTourModalElement } from '../elements';
import { addBeginTourButtonEvents } from '../events';
import { IS_WELCOME_TOUR_SEEN, IS_DASHBOARD_PAGE } from '../shared';

/**
 * Initialize the modal widget.
 *
 * @return {void}
 */
function initializeModal() {
	if ( IS_WELCOME_TOUR_SEEN || ! IS_DASHBOARD_PAGE ) {
		return;
	}

	buildBeginTourModalElement();
	addBeginTourButtonEvents();
}

export default initializeModal;
