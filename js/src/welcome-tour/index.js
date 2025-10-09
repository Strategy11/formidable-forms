/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { initializeModal, initializeSpotlight } from './ui';

domReady( () => {
	initializeModal();
	initializeSpotlight();
} );
