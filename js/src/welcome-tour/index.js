/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { initializeModal } from './ui';
import { div, svg } from 'core/utils';

let checklistElement;
let progressBar;

domReady( () => {
	if ( onDashboardPage() ) {
		initializeModal();
	}

	initalizeWelcomeTourChecklist();
} );

/**
 * Checks if the current page is the editor page.
 *
 * @return {boolean} True if the current page is the editor page, false otherwise.
 */
function onEditorPage() {
	const editorContainer = document.getElementById( 'frm_form_editor_container' );
	return editorContainer !== null;
}

function onStylerPage() {
	const stylerPreviewContainer = document.getElementById( 'frm_active_style_form' );
	return stylerPreviewContainer !== null;
}

/**
 * Checks if the current page is the form templates page.
 *
 * @return {boolean} True if the current page is the form templates page, false otherwise.
 */
function onFormTemplatesPage() {
	const urlObj = new URL( window.location.href );
	return urlObj.searchParams.get( 'page' ) === 'formidable-form-templates';
}

/**
 * Checks if the current page is the dashboard page.
 *
 * @return {boolean} True if the current page is the dashboard page, false otherwise.
 */
function onDashboardPage() {
	const urlObj = new URL( window.location.href );
	return urlObj.searchParams.get( 'page' ) === 'formidable-dashboard';
}

/**
 * Initializes the welcome tour checklist.
 *
 * @return {void}
 */
function initalizeWelcomeTourChecklist() {
	if ( ! shouldShowChecklist() ) {
		return;
	}

	// TODO: Add the new element to document.body.
	// This element will be used to display the checklist.
	// We'll hide the floating links elements.
	// The element will have a progress bar and a list of steps.
	// Each step will have an image that reflects the status.
	// The checklist needs to be collapsible.
	// The bottom should include a "Dismiss Checklist" button.

	document.body.appendChild( buildChecklistElement() );

	hideFloatingLinks();

	if ( onEditorPage() ) {
		document.addEventListener( 'frm_added_field', function() {
			markStepAsCompleted( 'add-fields' );
		} );
	}

	if ( onStylerPage() ) {
		const updatedMessage = document.querySelector( '.frm_updated_message' );
		if ( updatedMessage ) {
			markStepAsCompleted( 'style-form' );
		}
	}
}

/**
 * Marks a step as completed.
 *
 * @param {string} stepKey The step key.
 * @return {void}
 */
function markStepAsCompleted( stepKey ) {
	if ( ! checklistElement ) {
		return;
	}

	const stepElement = checklistElement.querySelector( `#frm-welcome-tour-checklist-step-${ stepKey }` );
	if ( ! stepElement ) {
		return;
	}

	if ( ! stepElement.classList.contains( 'frm-welcome-tour-active-step' ) ) {
		return;
	}

	stepElement.classList.remove( 'frm-welcome-tour-active-step' );
	stepElement.nextElementSibling?.classList.add( 'frm-welcome-tour-active-step' );

	const icon = stepElement.querySelector( 'svg' );
	if ( icon ) {
		icon.replaceWith( svg({ href: '#frm_complete_status_icon' }) );
	}
}

function shouldShowChecklist() {
	const activeStep = frmWelcomeTourVars.CHECKLIST_ACTIVE_STEP;

	switch ( activeStep ) {
		case 'create-first-form':
			return onFormTemplatesPage();
		case 'add-fields':
			return onEditorPage();
		case 'style-form':
			return onEditorPage() || onStylerPage();
		case 'embed-form':
			return onStylerPage() || onEditorPage();
		default:
			return false;
	}
}

/**
 * Build the checklist element.
 *
 * @return {HTMLElement} The checklist element.
 */
function buildChecklistElement() {
	checklistElement = div({ id: 'frm-welcome-tour-checklist' });

	const stepsWrapper = div({ className: 'frm-welcome-tour-checklist-steps' });

	checklistElement.appendChild( buildChecklistHeader( stepsWrapper ) );
	checklistElement.appendChild( buildChecklistProgressBar() );
	Object.entries( frmWelcomeTourVars.CHECKLIST_STEPS ).forEach( ( [ stepKey, stepValue ] ) => {
		stepsWrapper.appendChild( buildChecklistStep( stepKey, stepValue ) );
	} );
	checklistElement.appendChild( stepsWrapper );

	return checklistElement;
}

/**
 * Build the checklist header element.
 *
 * @param {HTMLElement} stepsWrapper The checklist steps wrapper element.
 * @return {HTMLElement} The checklist header element.
 */
function buildChecklistHeader( stepsWrapper ) {
	const header = div({
		className: 'frm-welcome-tour-checklist-header',
		text: frmWelcomeTourVars.i18n.CHECKLIST_HEADER_TITLE,
	});
	let icon = svg({ href: '#frm_arrowdown9_icon' });
	header.appendChild( icon );
	let open = true;
	header.addEventListener( 'click', () => {
		open = ! open;
		if ( open && progressBar ) {
			progressBar.style.borderRadius = 0;
		}
		jQuery( stepsWrapper ).slideToggle( 400, function() {
			if ( progressBar && ! open ) {
				progressBar.style.borderRadius = '0 0 8px 8px';
			}
		} );
		jQuery( icon ).animate({ rotate: open ? '0deg' : '-180deg' }, 400 );
	} );
	return header;
}

/**
 * Build the checklist progress bar element.
 *
 * @return {HTMLElement} The checklist progress bar element.
 */
function buildChecklistProgressBar() {
	progressBar = div({
		className: 'frm-welcome-tour-checklist-progress-bar'
	});
	progressBar.style.width = getProgressBarPercent() + '%';
	return div({
		className: 'frm-welcome-tour-checklist-progress-bar-wrapper',
		child: progressBar
	});
}

/**
 * Build a checklist step element.
 *
 * @param {string} stepKey The step key.
 * @param {Object} stepData The step data.
 * @return {HTMLElement} The checklist step element.
 */
function buildChecklistStep( stepKey, stepData ) {
	const status          = stepData.complete ? 'complete' : 'incomplete';
	const stepImage       = svg({ href: `#frm_${ status }_status_icon` });
	const stepTitle       = document.createTextNode( stepData.title );
	const stepMainContent = div({
		className: 'frm-welcome-tour-checklist-step-main-content',
		children: [ stepImage, stepTitle ]
	});
	const stepDescription = div({
		text: stepData.description,
		className: 'frm-welcome-tour-checklist-step-description',
	});
	const step            = div({
		id: `frm-welcome-tour-checklist-step-${ stepKey }`,
		className: 'frm-welcome-tour-checklist-step',
		children: [ stepMainContent, stepDescription ]
	});
	if ( isActiveStep( stepKey ) ) {
		step.classList.add( 'frm-welcome-tour-active-step' );
	}
	return step;
}

function isActiveStep( stepKey ) {
	return frmWelcomeTourVars.CHECKLIST_ACTIVE_STEP === stepKey;
}

/**
 * Hide the floating links elements.
 */
function hideFloatingLinks() {
	const floatingLinksElement = document.querySelector( '.s11-floating-links' );
	if ( floatingLinksElement ) {
		floatingLinksElement.style.display = 'none';
	}
}

function getProgressBarPercent() {
	return frmWelcomeTourVars.PROGRESS_BAR_PERCENT;
}
