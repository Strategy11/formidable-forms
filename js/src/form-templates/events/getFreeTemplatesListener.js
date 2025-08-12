/**
 * External dependencies
 */
import { onClickPreventDefault, isValidEmail, setQueryParam, hasQueryParam, removeQueryParam } from 'core/utils';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { showEmailAddressError, showLeaveEmailModal } from '../ui';

/**
 * Manages event handling for the "Get Templates" button.
 *
 * @return {void}
 */
function addGetFreeTemplatesEvents() {
	const { leaveEmailModalButton, getFreeTemplatesBannerButton } = getElements();

	onClickPreventDefault( leaveEmailModalButton, onGetTemplatesButtonClick );
	onClickPreventDefault( getFreeTemplatesBannerButton, showLeaveEmailModal );
}

/**
 * Handles the click event on the "Get Templates" button.
 *
 * @private
 * @return {void}
 */
const onGetTemplatesButtonClick = async() => {
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

	const formData = new FormData();
	formData.append( 'email', email );

	let data;
	const { doJsonPost } = frmDom.ajax;

	try {
		data = await doJsonPost( 'get_free_templates', formData );
	} catch ( error ) {
		console.error( 'An error occurred:', error );
		return;
	}

	if ( ! data.success ) {
		const { leaveEmailModal } = getElements();
		leaveEmailModal.querySelector( '.inside' ).innerHTML = `<p>${ __( 'Failed to get templates, please try again later.', 'formidable' ) }</p>`;
		return;
	}

	if ( hasQueryParam( 'free-templates' ) ) {
		removeQueryParam( 'free-templates' );
	}

	setQueryParam( 'registered_for_free_templates', '1' );

	window.location.reload();
};

export default addGetFreeTemplatesEvents;
