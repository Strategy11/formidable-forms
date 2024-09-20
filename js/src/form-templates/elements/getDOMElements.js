/**
 * Internal dependencies
 */
import { PREFIX, VIEW_SLUGS } from '../shared';

/**
 * Return essential DOM elements.
 *
 * @return {Object} The DOM elements queried and constructed into an object.
 */
function getDOMElements() {
	// Body Elements
	const bodyContent = document.getElementById( 'post-body-content' );
	const bodyElements = {
		bodyContent,
		headerCancelButton: document.getElementById( 'frm-publishing' ).querySelector( 'a' ),
		createFormButton: document.getElementById( `${PREFIX}-create-form` ),
		pageTitle: document.getElementById( `${PREFIX}-page-title` ),
		pageTitleText: document.getElementById( `${PREFIX}-page-title-text` ),
		pageTitleDivider: document.getElementById( `${PREFIX}-page-title-divider` ),
		upsellBanner: document.getElementById( `${PREFIX}-upsell-banner` ),
		extraTemplateCountElements: document.querySelectorAll( `.${PREFIX}-extra-templates-count` )
	};

	// Templates Elements
	const templatesList = document.getElementById( `${PREFIX}-list` );
	const templates = {
		templatesList,
		templateItems: templatesList.querySelectorAll( '.frm-card-item' ),
		availableTemplateItems: templatesList.querySelectorAll( `.frm-card-item:not(.${PREFIX}-locked-item)` ),
		freeTemplateItems: templatesList.querySelectorAll( '.frm-card-item.frm-free-template' ),
		twinFeaturedTemplateItems: templatesList.querySelectorAll( `.${PREFIX}-featured-item` ),
		firstLockedFreeTemplate: templatesList.querySelector( '.frm-free-template' ),
		featuredTemplatesList: document.getElementById( `${PREFIX}-featured-list` )
	};

	// Custom Templates Section Element
	const customTemplatesSection = document.getElementById( `${PREFIX}-custom-list-section` );
	const customTemplates = {
		customTemplatesSection,
		customTemplateItems: customTemplatesSection.querySelectorAll( '.frm-card-item' ),
		customTemplatesTitle: document.getElementById( `${PREFIX}-custom-list-title` ),
		customTemplatesList: document.getElementById( `${PREFIX}-custom-list` )
	};

	// Sidebar Elements
	const sidebar = document.getElementById( `${PREFIX}-sidebar` );
	const favoritesCategory = document.querySelector(
		`.${PREFIX}-cat-item[data-category="${VIEW_SLUGS.FAVORITES}"]`
	);
	const sidebarElements = {
		sidebar,
		favoritesCategory,
		favoritesCategoryCountEl: favoritesCategory.querySelector(
			`.${PREFIX}-cat-count`
		),
		searchInput: document.getElementById( 'template-search-input' ),
		allTemplatesCategory: document.querySelector(
			`.${PREFIX}-cat-item[data-category="${VIEW_SLUGS.ALL_TEMPLATES}"]`
		),
		availableTemplatesCategory: document.querySelector(
			`.${PREFIX}-cat-item[data-category="${VIEW_SLUGS.AVAILABLE_TEMPLATES}"]`
		),
		freeTemplatesCategory: document.querySelector(
			`.${PREFIX}-cat-item[data-category="${VIEW_SLUGS.FREE_TEMPLATES}"]`
		)
	};

	// Modal Elements
	const modal = document.getElementById( `${PREFIX}-modal` );
	const modalElements = {
		modal,
		modalItems: modal?.querySelectorAll( `.${PREFIX}-modal-item` ),
		// Create New Template Modal
		showCreateTemplateModalButton: document.getElementById( 'frm-show-create-template-modal' ),
		createTemplateModal: document.getElementById( 'frm-create-template-modal' ),
		createTemplateFormsDropdown: document.getElementById( 'frm-create-template-modal-forms-select' ),
		createTemplateName: document.getElementById( 'frm_create_template_name' ),
		createTemplateDescription: document.getElementById( 'frm_create_template_description' ),
		createTemplateButton: document.getElementById( 'frm-create-template-button' ),
		// Renew Account Modal
		renewAccountModal: document.getElementById( 'frm-renew-modal' ),
		// Leave Email Modal
		leaveEmailModal: document.getElementById( 'frm-leave-email-modal' ),
		leaveEmailModalInput: document.getElementById( 'frm_leave_email' ),
		leaveEmailModalApiEmailForm: document.getElementById( 'frmapi-email-form' ),
		leaveEmailModalGetCodeButton: document.getElementById( 'frm-get-code-button' ),
		// Code from Email Modal
		codeFromEmailModal: document.getElementById( 'frm-code-from-email-modal' ),
		codeFromEmailModalInput: document.getElementById( 'frm_code_from_email' ),
		// Upgrade Modal
		upgradeModal: document.getElementById( 'frm-form-upgrade-modal' ),
		upgradeModalTemplateNames: modal?.querySelectorAll( '.frm-upgrade-modal-template-name' ),
		upgradeModalPlansIcons: modal?.querySelectorAll( '.frm-upgrade-modal-plan-icon' ),
		upgradeModalLink: document.getElementById( 'frm-upgrade-modal-link' )
	};

	// New Template Form Elements
	const newTemplateForm = document.getElementById( 'frm-new-template' );
	const newTemplateFormElements = {
		newTemplateForm,
		newTemplateNameInput: document.getElementById( 'frm_template_name' ),
		newTemplateDescriptionInput: document.getElementById( 'frm_template_desc' ),
		newTemplateLinkInput: document.getElementById( 'frm_link' ),
		newTemplateActionInput: document.getElementById( 'frm_action_type' )
	};

	return {
		...bodyElements,
		...templates,
		...customTemplates,
		...sidebarElements,
		...modalElements,
		...newTemplateFormElements
	};
}

export default getDOMElements;
