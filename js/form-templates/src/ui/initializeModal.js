/**
 * Internal dependencies
 */
import { addElements, getElements } from '../elements';
import { initModal, offsetModalY, hasQueryParam } from '../shared';
import { showLeaveEmailModal } from './';

let modalWidget = null;

/**
 * Initialize the modal widget.
 *
 * @return {void}
 */
export async function initializeModal() {
	modalWidget = initModal( '#frm-form-templates-modal', '440px' );

	// Set the vertical offset for the modal
	if ( modalWidget ) {
		offsetModalY( modalWidget, '103px' );
	}

	// Show the email modal if the 'free-templates' query param is present
	if ( hasQueryParam( 'free-templates' ) ) {
		const { leaveEmailModal } = getElements();
		if ( leaveEmailModal ) {
			showLeaveEmailModal();
		}
	}

	// Maybe fetch and inject the API email form into the modal
	maybeFetchInjectForm();
}

/**
 * Retrieve the modal widget.
 *
 * @return {Object|false} The modal widget or false.
 */
export function getModalWidget() {
	return modalWidget;
}

/**
 * Maybe fetch and inject the API email form into the "Leave your email address" modal.
 *
 * If the "Leave your email address" modal is present for capturing the user's email
 * and sending a code to unlock free templates, this function may fetch and
 * inject the API email form.
 *
 * @private
 * @return {void}
 */
async function maybeFetchInjectForm() {
	const { leaveEmailModalApiEmailForm } = getElements();

	// Check if the element is present
	if ( ! leaveEmailModalApiEmailForm ) {
		return;
	}

	// Get the URL to fetch the form HTML from
	const url = leaveEmailModalApiEmailForm.getAttribute( 'data-url' );

	try {
		// Fetch form HTML
		const response = await fetch( url );
		const json = await response.json();
		if ( ! json.renderedHtml ) {
			console.warn( 'renderedHtml is not available.' );
			return;
		}
		let formHtml = json.renderedHtml;

		// Remove unnecessary link tags from the HTML
		const regex = /<link\b[^>]*(formidableforms.css|action=frmpro_css)[^>]*>/gi;
		formHtml = formHtml.replace( regex, '' );

		// Inject form HTML into the email form container
		leaveEmailModalApiEmailForm.innerHTML = formHtml;

		// Add the fetched form and email input to the initialized elements list for later use
		const leaveEmailModalHiddenForm = leaveEmailModalApiEmailForm.querySelector( 'form' );
		const leaveEmailModalHiddenInput = leaveEmailModalHiddenForm.querySelector( '[type="email"]:not(.frm_verify)' );
		addElements({ leaveEmailModalHiddenForm, leaveEmailModalHiddenInput });
	} catch ( error ) {
		console.error( 'An error occurred:', error );
	}
}
