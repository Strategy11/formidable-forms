/**
 * Internal dependencies
 */
import { getElements } from '../elements';

/**
 * Manages event handling for an application template.
 *
 * @return {void}
 */
export function addApplicationTemplateEvents() {
	const { applicationTemplateItems } = getElements();

	if ( undefined === applicationTemplateItems ) {
		return;
	}

	// Attach click event listener
	applicationTemplateItems.forEach( template => {
		template.addEventListener( 'click', onApplicationTemplateClick );
	} );
}

/**
 * Handles the click event on an application template.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
const onApplicationTemplateClick = event => {
	// Check if the clicked element is an anchor tag
	if ( event.target.closest( 'a' ) ) {
		return;
	}

	const applicationTemplate = event.currentTarget;
	window.location.href = applicationTemplate.dataset.href;
};
