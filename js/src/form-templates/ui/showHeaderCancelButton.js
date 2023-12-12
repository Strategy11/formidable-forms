/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { fadeIn } from '../utils';

/**
 * Display the "Cancel" button in the header.
 *
 * @return {void}
 */
export function showHeaderCancelButton() {
	const { headerCancelButton } = getElements();
	fadeIn( headerCancelButton );
};
