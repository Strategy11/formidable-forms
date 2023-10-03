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
		upsellBanner: document.getElementById( `${PREFIX}-upsell-banner` )
	};

	// Templates Elements
	const templatesList = document.getElementById( `${PREFIX}-list` );
	const templates = {
		templatesList,
		templateItems: templatesList.querySelectorAll( `.${PREFIX}-item` ),
		availableTemplateItems: templatesList.querySelectorAll( `.${PREFIX}-item:not(.${PREFIX}-locked-item)` ),
		freeTemplateItems: templatesList.querySelectorAll( `.${PREFIX}-item.frm-free-template` ),
		twinFeaturedTemplateItems: templatesList.querySelectorAll( `.${PREFIX}-featured-item` ),
		firstLockedFreeTemplate: templatesList.querySelector( '.frm-free-template' ),
		featuredTemplatesList: document.getElementById( `${PREFIX}-featured-list` )
	};

	// Custom Templates Section Element
	const customTemplatesSection = document.getElementById( `${PREFIX}-custom-list-section` );
	const customTemplates = {
		customTemplatesSection,
		customTemplateItems: customTemplatesSection.querySelectorAll( `.${PREFIX}-item` ),
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
		// Renew Account Modal
		renewAccountModal: document.getElementById( 'frm-renew-modal' ),
		// Leave Email Modal
		leaveEmailModal: document.getElementById( 'frm-leave-email-modal' ),
		leaveEmailModalInput: document.getElementById( 'frm_leave_email' ),
		leaveEmailModalApiEmailForm: document.getElementById( 'frmapi-email-form' ),
		// Code from Email Modal
		codeFromEmailModal: document.getElementById( 'frm-code-from-email-modal' ),
		codeFromEmailModalInput: document.getElementById( 'frm_code_from_email' ),
		// Upgrade Modal
		upgradeModal: document.getElementById( 'frm-upgrade-modal' ),
		upgradeModalTemplateNames: modal?.querySelectorAll( '.frm-upgrade-modal-template-name' ),
		upgradeModalPlansIcons: modal?.querySelectorAll( '.frm-upgrade-modal-plan-icon' )
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
