/**
 * Copyright (C) 2023 Formidable Forms
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { showCodeFromEmailModal, showEmailAddressError } from '../ui';
import { isValidEmail, onClickPreventDefault } from '../utils';

/**
 * Manages event handling for the "Get Code" button.
 *
 * @return {void}
 */
function addGetCodeButtonEvents() {
	const getCodeButton = document.querySelector( '#frm-add-my-email-address' );

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
const onGetCodeButtonClick = async( event ) => {
	event.preventDefault();

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

	try {
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

		// Perform the POST request
		const response = await fetch( leaveEmailModalHiddenForm.getAttribute( 'action' ), {
			method: 'POST',
			body: formData
		});

		// Parse the response text to HTML
		const text = await response.text();
		const parser = new DOMParser();
		const doc = parser.parseFromString( text, 'text/html' );

		// Extract and trim the message from the HTML response
		const message = doc.querySelector( '.frm_message' )?.textContent.trim();

		// Check if the message indicates success ("Thanks!")
		if ( message && message.indexOf( 'Thanks!' ) >= 0 ) {
			showCodeFromEmailModal();
		} else {
			// Show an error if the email is invalid
			showEmailAddressError( 'invalid' );
		}
	} catch ( error ) {
		console.error( 'An error occurred:', error );
	}
};

export default addGetCodeButtonEvents;
