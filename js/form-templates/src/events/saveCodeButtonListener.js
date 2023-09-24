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
import { PREFIX, getAppState, hasQueryParam, removeQueryParam, nonce  } from '../shared';
import { showConfirmEmailAddressError } from '../ui';
import { onClickPreventDefault, show } from '../utils';

/**
 * Manages event handling for the "Save Code" button.
 *
 * @return {void}
 */
function addSaveCodeButtonEvents() {
	const saveCodeButton = document.querySelector( '#frm-confirm-email-address' );

	// Attach click event listener
	onClickPreventDefault( saveCodeButton, onSaveCodeButtonClick );
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
			show( document.querySelector( '#frm_code_from_email_options' ) );
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
		selectedTemplate.classList.remove( `${ PREFIX }-locked-item` );

		// Set the URL to the 'Use Template' button and trigger its click event
		const useTemplateButton = selectedTemplate.querySelector( '.frm-form-templates-use-template-button' );
		useTemplateButton.setAttribute( 'href', data.data.url );
		useTemplateButton.dispatchEvent( new Event( 'click', { bubbles: true }) );
	} catch ( error ) {
		console.error( 'An error occurred:', error );
	}
};

export default addSaveCodeButtonEvents;
