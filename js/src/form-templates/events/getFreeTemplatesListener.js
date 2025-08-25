/**
 * External dependencies
 */
import { onClickPreventDefault, isValidEmail, setQueryParam, hasQueryParam, removeQueryParam } from 'core/utils';

const { tag } = window.frmDom;

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

	// Disable the button
	const { leaveEmailModalButton } = getElements();
	leaveEmailModalButton.style.setProperty( 'cursor', 'not-allowed' );
	leaveEmailModalButton.classList.add( 'frm_loading_button' );

	const formData = new FormData();
	formData.append( 'email', email );

	let data;
	const { doJsonPost } = frmDom.ajax;

	try {
		data = await doJsonPost( 'get_free_templates', formData );
	} catch ( error ) {
		console.error( 'An error occurred:', error );
		showFailedToGetTemplates();
		return;
	}

	if ( ! data.success ) {
		showFailedToGetTemplates();
		return;
	}

	if ( hasQueryParam( 'free-templates' ) ) {
		removeQueryParam( 'free-templates' );
	}

	setQueryParam( 'registered-for-free-templates', '1' );

	window.location.reload();
};

/**
 * Shows a message indicating that templates could not be retrieved.
 *
 * @private
 * @return {void}
 */
function showFailedToGetTemplates() {
	const { leaveEmailModal } = getElements();

	leaveEmailModal.querySelector( '.inside' ).replaceChildren(
		tag( 'p', __( 'Failed to get templates, please try again later.', 'formidable' ) )
	);

	leaveEmailModal.querySelector( '.frm_modal_footer' ).classList.add( 'frm_hidden' );
}

export default addGetFreeTemplatesEvents;
