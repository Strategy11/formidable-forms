/**
 * Internal dependencies
 */
import { buildBeginTourModalElement } from '../elements';
import { IS_WELCOME_TOUR_SEEN, IS_DASHBOARD_PAGE } from '../shared';

/**
 * Initialize the modal widget.
 *
 * @return {void}
 */
function initializeModal() {
	if ( IS_DASHBOARD_PAGE && ! IS_WELCOME_TOUR_SEEN ) {
		buildBeginTourModalElement();
	}
}

export default initializeModal;
