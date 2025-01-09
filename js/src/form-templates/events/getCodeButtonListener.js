/**
 * External dependencies
 */
import { onClickPreventDefault, isValidEmail } from 'core/utils';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { showCodeFromEmailModal, showEmailAddressError } from '../ui';

/**
 * Manages event handling for the "Get Code" button.
 *
 * @return {void}
 */
function addGetCodeButtonEvents() {
	const { leaveEmailModalGetCodeButton: getCodeButton } = getElements();

	// Attach click event listener
	onClickPreventDefault( getCodeButton, onGetCodeButtonClick );
}

/**
 * Handles the click event on the "Get Code" button.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
const onGetCodeButtonClick = async() => {
	const { leaveEmailModalInput } = getElements();
	const email = leaveEmailModalInput.value.trim();

	// Check if the email field is empty
	if ( ! email ) {
		showEmailAddressError( 'empty' );
		return;
	}

	// Check if the email is valid
	if ( ! isValidEmail( email ) ) {
		showEmailAddressError( 'invalid' );
		return;
	}

	const { leaveEmailModalHiddenForm, leaveEmailModalHiddenInput } = getElements();

	// Check if the hidden form exists
	if ( ! leaveEmailModalHiddenForm ) {
		return;
	}

	// Set the email value in the hidden input field
	leaveEmailModalHiddenInput.value = email;

	// Prepare FormData for the POST request
	const formData = new FormData( leaveEmailModalHiddenForm );
	formData.append( 'action', 'frm_forms_preview' );

	let doc;

	try {
		// Perform the POST request
		const response = await fetch( leaveEmailModalHiddenForm.getAttribute( 'action' ), {
			method: 'POST',
			body: formData
		});

		// Parse the response text to HTML
		const text = await response.text();
		const parser = new DOMParser();
		doc = parser.parseFromString( text, 'text/html' );
	} catch ( error ) {
		console.error( 'An error occurred:', error );
		return;
	}


	// Extract and trim the message from the HTML response
	const message = doc.querySelector( '.frm_message' )?.textContent.trim();

	// Check if the message indicates success ("Thanks!")
	if ( message && message.indexOf( 'Thanks!' ) >= 0 ) {
		showCodeFromEmailModal();
	} else {
		// Show an error if the email is invalid
		showEmailAddressError( 'invalid' );
	}
};

export default addGetCodeButtonEvents;
