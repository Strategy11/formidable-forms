/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { onClickPreventDefault } from '../utils';

/**
 * Manages event handling for the "Create a blank form" button.
 *
 * @return {void}
 */
function addCreateFormButtonEvents() {
	const { createFormButton } = getElements();

	// Attach click event listener
	onClickPreventDefault( createFormButton, onCreateFormButtonClick );
}

/**
 * Handles the click event on the "Create a blank form" button.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
const onCreateFormButtonClick = () => {
	const { createFormButton, newTemplateForm, newTemplateNameInput, newTemplateActionInput } = getElements();
	const { installNewForm } = window.frmAdminBuild;

	newTemplateNameInput.value = '';
	newTemplateActionInput.value = 'frm_install_form';
	installNewForm( newTemplateForm, 'frm_install_form', createFormButton );
};

export default addCreateFormButtonEvents;
