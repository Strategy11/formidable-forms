/**
 * External dependencies
 */
import { frmAnimate } from 'core/utils';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';

/**
 * Display the "Cancel" button in the header.
 *
 * @return {void}
 */
export function showHeaderCancelButton() {
	const { headerCancelButton } = getElements();
	new frmAnimate( headerCancelButton ).fadeIn();
}
