/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { setupInitialView } from './ui';
import { addEventListeners } from './events';
import { buildCategorizedAddons } from './addons';

domReady(() => {
	setupInitialView();
	buildCategorizedAddons();
	addEventListeners();

	console.log( 'A test change to see if GitHub Actions caching is working' );
});
