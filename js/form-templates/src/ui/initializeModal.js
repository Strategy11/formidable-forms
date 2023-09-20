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

let $modal = null;

/**
 * Initialize the modal dialog.
 *
 * @return {void}
 */
export function initializeModal() {
	$modal = initModal( '#frm-form-templates-modal', '440px' );
	setVerticalOffset( $modal, '103px' );
}

/**
 * Retrieve the the modal dialog element.
 *
 * @return {Object|false} The modal dialog element or false.
 */
export function getModal() {
	return $modal;
}

/**
 * Sets a vertical offset for the modal dialog.
 *
 * @private
 * @param {Object} $modal The modal dialog element.
 * @param {string} verticalOffset The vertical offset to apply.
 * @return {void}
 */
function setVerticalOffset( $modal, verticalOffset ) {
	if ( ! $modal ) {
		return;
	}

	const position = {
		my: 'top',
		at: 'top+' + verticalOffset,
		of: window
	};

	$modal.dialog( 'option', 'position', position );
}
