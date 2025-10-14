/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { initializeModal, initializeSpotlight, initializeChecklist } from './ui';
import { addChecklistEvents, addDismissEvents } from './events';

domReady( () => {
	initializeModal();
	initializeSpotlight();
	initializeChecklist();
	addChecklistEvents();
	addDismissEvents();
} );
