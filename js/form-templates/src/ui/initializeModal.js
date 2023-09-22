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
import { initModal } from '../shared';

let modalWidget = null;

/**
 * Initialize the modal widget.
 *
 * @return {void}
 */
export function initializeModal() {
	modalWidget = initModal( '#frm-form-templates-modal', '440px' );
	setVerticalOffset( modalWidget, '103px' );
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
 * Sets a vertical offset for the modal widget.
 *
 * @private
 * @param {Object} modalWidget The modal widget.
 * @param {string} verticalOffset The vertical offset to apply.
 * @return {void}
 */
function setVerticalOffset( modalWidget, verticalOffset ) {
	if ( ! modalWidget ) {
		return;
	}

	const position = {
		my: 'top',
		at: 'top+' + verticalOffset,
		of: window
	};

	modalWidget.dialog( 'option', 'position', position );
}
