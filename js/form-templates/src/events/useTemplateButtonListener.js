/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { PREFIX, setAppStateProperty, installNewForm } from '../shared';
import { showLockedTemplateModal } from '../ui/';
import { isCustomTemplate, isLockedTemplate } from '../utils';

/**
 * Manages event handling for use template buttons.
 *
 * @return {void}
 */
function addUseTemplateButtonEvents() {
	const useTemplateButtons = document.querySelectorAll( `.${PREFIX}-use-template-button` );

	// Attach click event listeners to each use template button
	useTemplateButtons.forEach( useTemplateButton =>
		useTemplateButton.addEventListener( 'click', onUseTemplateButtonClick )
	);
}

/**
 * Handles the click event on the use template button.
 *
 * @private
 * @param {Event} event The click event object.
 * @return {void}
 */
const onUseTemplateButtonClick = ( event ) => {
	const useTemplateButton = event.currentTarget;

	const template = useTemplateButton.closest( `.${PREFIX}-item` );
	const isLocked = isLockedTemplate( template );
	const isTemplateCustom = isCustomTemplate( template );

	// Allow the default link behavior, if the template is custom and not locked
	if ( ! isLocked && isTemplateCustom ) {
		return;
	}

	// Prevent the default link behavior for non-custom or locked templates
	event.preventDefault();

	// Update app state with selected template
	setAppStateProperty( 'selectedTemplate', template );

	// Handle locked templates
	if ( isLocked ) {
		showLockedTemplateModal( template );
		return;
	}

	// Prepare for new template installation
	const { newTemplateForm, newTemplateNameInput, newTemplateLinkInput, newTemplateActionInput } = getElements();
	const templateName = template.querySelector( '.frm-form-template-name' ).textContent.trim();
	const actionName = 'frm_install_template';

	newTemplateNameInput.value = templateName;
	newTemplateActionInput.value = actionName;
	newTemplateLinkInput.value = useTemplateButton.href;

	// Install new form template
	installNewForm( newTemplateForm, actionName, useTemplateButton );
};

export default addUseTemplateButtonEvents;