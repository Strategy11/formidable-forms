/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { frmAnimate } from '../utils';

/**
 * Display the "Cancel" button in the header.
 *
 * @return {void}
 */
export function showHeaderCancelButton() {
	const { headerCancelButton } = getElements();
	new frmAnimate( headerCancelButton ).fadeIn();
};
