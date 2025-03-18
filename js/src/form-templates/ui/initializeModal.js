/**
 * External dependencies
 */
import { hasQueryParam } from 'core/utils';


/**
 * Internal dependencies
 */
import { addElements, getElements } from '../elements';
import { MODAL_SIZES } from '../shared';

let modalWidget = null;

/**
 * Initialize the modal widget.
 *
 * @return {void}
 */
export async function initializeModal() {
	const { initModal, offsetModalY } = window.frmAdminBuild;

	modalWidget = initModal( '#frm-form-templates-modal', MODAL_SIZES.GENERAL );

	// Set the vertical offset for the modal
	if ( modalWidget ) {
		offsetModalY( modalWidget, '103px' );
	}

	// Maybe fetch and inject the API email form into the modal
	maybeFetchInjectForm();

	// Customize the confirm modal appearance: adjusting its width and vertical position
	wp.hooks.addAction( 'frmAdmin.beforeOpenConfirmModal', 'frmFormTemplates', ( options ) => {
		const { $info: confirmModal } = options;

		confirmModal.dialog( 'option', 'width', MODAL_SIZES.CREATE_TEMPLATE );
		offsetModalY( confirmModal, '103px' );
	});
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

	let json;

	try {
		const response = await fetch( url );
		json = await response.json();
	} catch ( error ) {
		console.error( 'An error occurred:', error );
		return;
	}

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
}
