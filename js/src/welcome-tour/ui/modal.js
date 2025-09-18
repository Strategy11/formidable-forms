/**
 * Internal dependencies
 */
import { buildBeginTourModalElement } from '../elements';
import { addBeginTourButtonEvents } from '../events';
import { IS_WELCOME_TOUR_SEEN } from '../shared';
import { onDashboardPage } from '../utils';

/**
 * Initialize the modal widget.
 *
 * @return {void}
 */
function initializeModal() {
	if ( IS_WELCOME_TOUR_SEEN || ! onDashboardPage() ) {
		return;
	}

	buildBeginTourModalElement();
	addBeginTourButtonEvents();
}

export default initializeModal;
