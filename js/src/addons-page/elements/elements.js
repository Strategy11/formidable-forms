/**
 * External dependencies
 */
import { getElements, addElements } from 'core/page-skeleton';

const { bodyContent } = getElements();

// Add children of the bodyContent to the elements object.
const bodyContentChildren = bodyContent?.children;

addElements({
	bodyContentChildren
});

export { getElements, addElements };
