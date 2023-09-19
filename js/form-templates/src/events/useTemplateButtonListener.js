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
import { PREFIX, getAppState, initModal, setAppState } from '../shared';
import { $modal } from '../ui';
import { isLockedTemplate, onClickPreventDefault } from '../utils';

/**
 * Manages event handling for use template buttons.
 *
 * @return {void}
 */
function addUseTemplateButtonEvents() {
	const useTemplateButtons = document.querySelectorAll( `.${PREFIX}-use-template-button` );

	// Attach click event listeners to each use template button
	useTemplateButtons.forEach( useTemplateButton =>
		onClickPreventDefault( useTemplateButton, onUseTemplateClick )
	);
}

/**
 * Handles the click event on the use template button.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
const onUseTemplateClick = ( event ) => {
	const useTemplateButton = event.currentTarget;

	/**
	 * Get necessary template information
	 */
	const template = useTemplateButton.closest( `.${ PREFIX }-item` );
	const isLocked = isLockedTemplate( template );
};

export default addUseTemplateButtonEvents;
