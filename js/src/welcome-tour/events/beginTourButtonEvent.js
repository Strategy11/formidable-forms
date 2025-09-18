/**
 * Internal dependencies
 */
import { navigate } from 'core/utils';
import { getElements } from '../elements';
import { TOUR_URL } from '../shared';

/**
 * Adds event listeners to the begin tour button.
 *
 * @return {void}
 */
function addBeginTourButtonEvents() {
	const { beginTourButton } = getElements();
	if ( ! beginTourButton ) {
		return;
	}

	beginTourButton.addEventListener( 'click', onBeginTourButtonClick );
}

/**
 * Handles the click event on the begin tour button.
 *
 * @private
 * @return {void}
 */
function onBeginTourButtonClick() {
	const { beginTourModal } = getElements();

	jQuery( beginTourModal ).dialog( 'close' );
	startTour();
}

/**
 * Redirects the user to the tour URL.
 *
 * @private
 * @return {void}
 */
function startTour() {
	navigate( TOUR_URL );
}

export default addBeginTourButtonEvents;
