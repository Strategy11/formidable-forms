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
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { onClickPreventDefault } from '../utils';
import { installNewForm } from '../shared';

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
const onCreateFormButtonClick = ( event ) => {
	const { createFormButton, newTemplateForm, newTemplateNameInput, newTemplateActionInput } = getElements();

	newTemplateNameInput.value = __( 'No name', 'formidable' );
	newTemplateActionInput.value = 'frm_install_form';
	installNewForm( newTemplateForm, 'frm_install_form', createFormButton );
};

export default addCreateFormButtonEvents;
