/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { setupInitialView } from './ui';

domReady( () => {
	setupInitialView();
});
