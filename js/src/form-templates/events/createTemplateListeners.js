/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import { onClickPreventDefault } from 'core/utils';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { getState } from '../shared';
import { showCreateTemplateModal } from '../ui';
import { isCustomCategory } from '../utils';

/**
 * Manages event handling for the 'Create New Template' modal.
 *
 * @return {void}
 */
function addCreateTemplateEvents() {
	const {
		createTemplateFormsDropdown,
		createTemplateButton,
		showCreateTemplateModalButton,
		emptyStateButton
	} = getElements();

	// Show the 'Create New Template' modal when either empty state or show modal button is clicked
	onClickPreventDefault( showCreateTemplateModalButton, onShowCreateTemplateModalButtonClick );
	onClickPreventDefault( emptyStateButton, onShowCreateTemplateModalButtonClick );

	// Handle changes in the forms selection dropdown for creating a new template
	createTemplateFormsDropdown.addEventListener( 'change', onFormsSelectChange );

	// Create a new template when the create button inside the modal is clicked
	onClickPreventDefault( createTemplateButton, onCreateTemplateButtonClick );
}

/**
 * Handles the click event on the 'Create Template' button, showing the 'Create New Template' modal.
 *
 * @private
 * @return {void}
 */
const onShowCreateTemplateModalButtonClick = () => {
	const { selectedCategory } = getState();
	if ( ! isCustomCategory( selectedCategory ) ) {
		return;
	}

	showCreateTemplateModal();
};

/**
 * Handles changes in the forms selection dropdown for creating a new template.
 *
 * @private
 * @return {void}
 */
const onFormsSelectChange = () => {
	const { createTemplateFormsDropdown: formsSelect } = getElements();
	const formId = formsSelect.value;

	if ( ! formId || formId === 'no-forms' ) {
		toggleDisableModalElements( true );
		return;
	}

	toggleDisableModalElements( false );

	const selectedOption = formsSelect.options[ formsSelect.selectedIndex ];
	const formDescription = selectedOption.dataset.description.trim();

	let formName = selectedOption.dataset.name.trim();
	const templateString = ` ${ __( 'Template', 'formidable' ) }`;
	if ( ! formName.endsWith( templateString ) ) {
		formName += templateString;
	}

	const { createTemplateName, createTemplateDescription } = getElements();
	createTemplateName.value = formName;
	createTemplateDescription.value = formDescription;
};

/**
 * Toggles the disabled state of elements in the 'Create Template' modal.
 *
 * @private
 * @param {boolean} shouldDisable True to disable, false to enable.
 * @return {void}
 */
const toggleDisableModalElements = shouldDisable => {
	const { createTemplateName, createTemplateDescription, createTemplateButton } = getElements();

	// Toggle the disabled attribute for input and textarea
	[ createTemplateName, createTemplateDescription ].forEach( element => {
		element.disabled = shouldDisable;
		if ( shouldDisable ) {
			element.value = ''; // Clear the content for input and textarea
		}
	} );

	// Toggle the disabled class for the button
	createTemplateButton.classList.toggle( 'disabled', shouldDisable );
};

/**
 * Handles the click event on the 'Create Template' button to create a new template.
 *
 * @private
 * @return {void}
 */
const onCreateTemplateButtonClick = () => {
	const { installNewForm } = window.frmAdminBuild;
	const actionName = 'frm_create_template';
	const {
		newTemplateForm,
		newTemplateActionInput,
		newTemplateNameInput,
		newTemplateDescriptionInput,
		newTemplateLinkInput,
		createTemplateName,
		createTemplateDescription,
		createTemplateFormsDropdown,
		createTemplateButton
	} = getElements();

	newTemplateActionInput.value = actionName;
	newTemplateNameInput.value = createTemplateName.value.trim();
	newTemplateDescriptionInput.value = createTemplateDescription.value.trim();
	newTemplateLinkInput.value = createTemplateFormsDropdown.value;

	// Install new form template
	installNewForm( newTemplateForm, actionName, createTemplateButton );
};

export default addCreateTemplateEvents;
