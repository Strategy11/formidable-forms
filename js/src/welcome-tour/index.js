/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { initializeModal, initializeSpotlight, initializeChecklist } from './ui';
import { addEventListeners } from './events';

domReady( () => {
	initializeModal();
	initializeSpotlight();
	initializeChecklist();
	addEventListeners();
} );
