/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { setupInitialView } from './ui';
import { addEventListeners } from './events';

domReady( () => {
	setupInitialView();
	addEventListeners();
});
