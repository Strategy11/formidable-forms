/**
 * External dependencies
 */
import { getElements, addElements } from 'core/page-skeleton';

const { bodyContent } = getElements();

const addonsToggle = bodyContent.querySelectorAll( '.frm_toggle_block' );

// Add children of the bodyContent to the elements object.
const bodyContentChildren = bodyContent?.children;

addElements({
	addonsToggle,
	bodyContentChildren
});

export { getElements, addElements };
