/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { FrmAnimate } from '../../common/utilities/animation';

/**
 * Display the "Cancel" button in the header.
 *
 * @return {void}
 */
export function showHeaderCancelButton() {
	const { headerCancelButton } = getElements();
	new FrmAnimate( headerCancelButton ).fadeIn();
};
