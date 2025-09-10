/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { initializeModal } from './ui';

domReady( () => {
	if ( onDashboardPage() ) {
		initializeModal();
	}

	if ( onFormTemplatesPage() ) {
		initalizeWelcomeTourChecklist();
	}
} );

function getProgressBarPercent() {
	return frmWelcomeTourVars.PROGRESS_BAR_PERCENT;
}

function onFormTemplatesPage() {
	const urlObj = new URL( window.location.href );
	return urlObj.searchParams.get( 'page' ) === 'formidable-form-templates';
}

function onDashboardPage() {
	const urlObj = new URL( window.location.href );
	return urlObj.searchParams.get( 'page' ) === 'formidable-dashboard';
}

function initalizeWelcomeTourChecklist() {
	// TODO: Add the new element to document.body.
	// This element will be used to display the checklist.
	// We'll hide the floating links elements.
	// The element will have a progress bar and a list of steps.
	// Each step will have an image that reflects the status.
	// The checklist needs to be collapsible.
	// The bottom should include a "Dismiss Checklist" button.

	const checklistElement = document.createElement( 'div' );
	checklistElement.id = 'frm-welcome-tour-checklist';

	const checklistHeader = document.createElement( 'div' );
	checklistHeader.className = 'frm-welcome-tour-checklist-header';
	checklistHeader.textContent = frmWelcomeTourVars.i18n.CHECKLIST_HEADER_TITLE;
	checklistElement.appendChild( checklistHeader );

	document.body.appendChild( checklistElement );

	// Hide the floating links elements.
	const floatingLinksElement = document.querySelector( '.s11-floating-links' );
	if ( floatingLinksElement ) {
		floatingLinksElement.style.display = 'none';
	}

}
