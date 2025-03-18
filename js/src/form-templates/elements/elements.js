/**
 * External dependencies
 */
import { getElements, addElements, PREFIX as SKELETON_PREFIX } from 'core/page-skeleton';

/**
 * Internal dependencies
 */
import { PREFIX, VIEW_SLUGS } from '../shared';

const { bodyContent } = getElements();
const templatesList = document.getElementById( `${PREFIX}-list` );
const customTemplatesSection = document.getElementById( `${PREFIX}-custom-list-section` );
const favoritesCategory = document.querySelector(
	`.${SKELETON_PREFIX}-cat[data-category="${VIEW_SLUGS.FAVORITES}"]`
);
const modal = document.getElementById( `${PREFIX}-modal` );

addElements({
	// Body elements
	headerCancelButton: document.getElementById( 'frm-publishing' )?.querySelector( 'a' ),
	createFormButton: document.getElementById( `${PREFIX}-create-form` ),
	pageTitle: document.getElementById( `${PREFIX}-page-title` ),
	pageTitleText: document.getElementById( `${PREFIX}-page-title-text` ),
	pageTitleDivider: document.getElementById( `${PREFIX}-page-title-divider` ),
	upsellBanner: document.getElementById( `${PREFIX}-upsell-banner` ),
	extraTemplateCountElements: document.querySelectorAll( `.${PREFIX}-extra-templates-count` ),

	// Templates elements
	templatesList,
	templateItems: templatesList.querySelectorAll( '.frm-card-item' ),
	availableTemplateItems: templatesList.querySelectorAll( `.frm-card-item:not(.${PREFIX}-locked-item)` ),
	freeTemplateItems: templatesList.querySelectorAll( '.frm-card-item.frm-free-template' ),
	twinFeaturedTemplateItems: templatesList.querySelectorAll( `.${PREFIX}-featured-item` ),
	firstLockedFreeTemplate: templatesList.querySelector( '.frm-free-template' ),
	featuredTemplatesList: document.getElementById( `${PREFIX}-featured-list` ),

	// Custom Templates Section elements
	customTemplatesSection,
	customTemplateItems: customTemplatesSection.querySelectorAll( '.frm-card-item' ),
	customTemplatesTitle: document.getElementById( `${PREFIX}-custom-list-title` ),
	customTemplatesList: document.getElementById( `${PREFIX}-custom-list` ),

	// Sidebar elements
	favoritesCategory,
	favoritesCategoryCountEl: favoritesCategory?.querySelector(
		`.${SKELETON_PREFIX}-cat-count`
	),
	availableTemplatesCategory: document.querySelector(
		`.${SKELETON_PREFIX}-cat[data-category="${VIEW_SLUGS.AVAILABLE_TEMPLATES}"]`
	),
	freeTemplatesCategory: document.querySelector(
		`.${SKELETON_PREFIX}-cat[data-category="${VIEW_SLUGS.FREE_TEMPLATES}"]`
	),

	// Modal elements
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
	// Code from Email Modal
	codeFromEmailModal: document.getElementById( 'frm-code-from-email-modal' ),
	codeFromEmailModalInput: document.getElementById( 'frm_code_from_email' ),
	// Upgrade Modal
	upgradeModal: document.getElementById( 'frm-form-upgrade-modal' ),
	upgradeModalTemplateNames: modal?.querySelectorAll( '.frm-upgrade-modal-template-name' ),
	upgradeModalPlansIcons: modal?.querySelectorAll( '.frm-upgrade-modal-plan-icon' ),
	upgradeModalLink: document.getElementById( 'frm-upgrade-modal-link' ),

	// New Template Form elements
	newTemplateForm: document.getElementById( 'frm-new-template' ),
	newTemplateNameInput: document.getElementById( 'frm_template_name' ),
	newTemplateDescriptionInput: document.getElementById( 'frm_template_desc' ),
	newTemplateLinkInput: document.getElementById( 'frm_link' ),
	newTemplateActionInput: document.getElementById( 'frm_action_type' ),

	// Add children of the bodyContent to the elements object.
	bodyContentChildren: bodyContent?.children
});

export { getElements, addElements };
