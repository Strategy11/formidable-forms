/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { addOptionBoxEvents } from 'core/events';
import addSubmitFeedbackEventListeners from './submitFeedbackEvents';

domReady( () => {
	addSubmitFeedbackEventListeners();
	addOptionBoxEvents();
} );
