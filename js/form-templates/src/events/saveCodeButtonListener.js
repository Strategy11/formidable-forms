/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { PREFIX, getAppState, hasQueryParam, removeQueryParam, nonce  } from '../shared';
import { showConfirmEmailAddressError } from '../ui';
import { show, hide, hideElements, onClickPreventDefault } from '../utils';

/**
 * Manages event handling for the "Save Code" button.
 *
 * @return {void}
 */
function addSaveCodeButtonEvents() {
	const saveCodeButton = document.getElementById( 'frm-confirm-email-address' );
	const backButton = document.getElementById( 'frm-code-modal-back-button' );
	const changeEmailButton = document.getElementById( 'frm-change-email-address' );
	const resendCode = document.getElementById( 'frm-resend-code' );

	// Attach click event to the "Save Code" button
	onClickPreventDefault( saveCodeButton, onSaveCodeButtonClick );

	// Attach click events to the "Back" and "Change email address" buttons
	onClickPreventDefault( backButton, onBackButton );
	onClickPreventDefault( changeEmailButton, onBackButton );

	// Attach click event to the "Resend code" button
	onClickPreventDefault( resendCode, onResendCode );
}

/**
 * Handles the click event on the "Save Code" button.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
const onSaveCodeButtonClick = async( event ) => {
	const { codeFromEmailModalInput } = getElements();
	const code = codeFromEmailModalInput.value.trim();

	// Check if the code field is empty
	if ( ! code ) {
		showConfirmEmailAddressError( 'empty' );
		return;
	}

	const { selectedTemplate } = getAppState();

	// Prepare FormData for the POST request
	const formData = new FormData();
	formData.append( 'action', 'template_api_signup' );
	formData.append( 'nonce', nonce );
	formData.append( 'code', code );
	formData.append( 'key', selectedTemplate.dataset.key );

	try {
		// Perform the POST request
		const response = await fetch( ajaxurl, {
			method: 'POST',
			body: formData
		});

		// Parse the JSON response
		const data = await response.json();

		// Handle unsuccessful request
		if ( ! data.success ) {
			const { message: errorMessage } = data?.data?.[0] || {};
			const errorType = errorMessage ? 'custom' : 'wrong-code';
			showConfirmEmailAddressError( errorType, errorMessage );
			show( document.getElementById( 'frm_code_from_email_options' ) );
			return;
		}

		// If the 'free-templates' query parameter is set, remove it and reload the page
		if ( hasQueryParam( 'free-templates' ) ) {
			window.location.href = removeQueryParam( 'free-templates' );
			return;
		}

		// Check if data and URL are present
		if ( ! data.data || ! data.data.url ) {
			return;
		}

		// Remove the 'locked' status from the selected template
		selectedTemplate.classList.remove( `${PREFIX}-locked-item` );

		// Set the URL to the 'Use Template' button and trigger its click event
		const useTemplateButton = selectedTemplate.querySelector( '.frm-form-templates-use-template-button' );
		useTemplateButton.setAttribute( 'href', data.data.url );
		useTemplateButton.dispatchEvent( new Event( 'click', { bubbles: true }) );
	} catch ( error ) {
		console.error( 'An error occurred:', error );
	}
};

/**
 * Handles the click event on the "Back" or "Change email address" buttons.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
const onBackButton = ( event ) => {
	const { leaveEmailModal, codeFromEmailModal } = getElements();
	hide( codeFromEmailModal );
	show( leaveEmailModal );
};

/**
 * Handles the click event on the "Resend code" button.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
const onResendCode = ( event ) => {
	const { codeFromEmailModalInput, leaveEmailModalGetCodeButton: getCodeButton } = getElements();
	codeFromEmailModalInput.value = '';
	hideElements( document.querySelectorAll( '#frm_code_from_email_options, #frm_code_from_email_error' ) );
	getCodeButton.dispatchEvent( new Event( 'click', { bubbles: true }) );
};

export default addSaveCodeButtonEvents;
