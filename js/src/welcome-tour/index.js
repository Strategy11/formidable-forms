/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { initializeModal } from './ui';

domReady( () => {
	initializeModal();
} );

function getProgressBarPercent() {
	return frmWelcomeTourVars.PROGRESS_BAR_PERCENT;
}
