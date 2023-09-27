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
import { PREFIX, VIEW_SLUGS } from '../shared';

/**
 * Return essential DOM elements.
 *
 * @return {Object} The DOM elements queried and constructed into an object.
 */
function getDOMElements() {
	// Body Elements
	const bodyContent = document.querySelector( '#post-body-content' );
	const bodyElements = {
		bodyContent,
		headerCancelButton: document.querySelector( '#frm-publishing a' ),
		createFormButton: document.querySelector( `#${ PREFIX }-create-form` ),
		pageTitle: document.querySelector( `#${ PREFIX }-page-title` ),
		upsellBanner: document.querySelector( `#${ PREFIX }-upsell-banner` )
	};

	// Templates Elements
	const templatesList = document.querySelector( `#${ PREFIX }-list` );
	const templates = {
		templatesList,
		featuredTemplatesList: document.querySelector(
			`#${ PREFIX }-featured-list`
		),
		templateItems: templatesList?.querySelectorAll( `.${ PREFIX }-item` ),
		availableTemplateItems: templatesList?.querySelectorAll( `.${ PREFIX }-item:not(.${ PREFIX }-locked-item)` ),
		freeTemplateItems: templatesList?.querySelectorAll( `.${ PREFIX }-item.frm-free-template` ),
		twinFeaturedTemplateItems: templatesList?.querySelectorAll(
			`.${ PREFIX }-featured-item`
		),
		firstLockedFreeTemplate: templatesList?.querySelector( '.frm-free-template' )
	};

	// Custom Templates Section Element
	const customTemplatesSection = document.querySelector(
		`#${ PREFIX }-custom-list-section`
	);
	const customTemplates = {
		customTemplatesSection,
		customTemplatesTitle: customTemplatesSection?.querySelector(
			`#${ PREFIX }-custom-list-title`
		),
		customTemplatesList: customTemplatesSection?.querySelector(
			`#${ PREFIX }-custom-list`
		),
		customTemplateItems: customTemplatesSection?.querySelectorAll(
			`.${ PREFIX }-item`
		)
	};

	// Sidebar Elements
	const sidebar = document.querySelector( `#${PREFIX}-sidebar` );
	const searchInput = document.querySelector( '#template-search-input' );
	const allTemplatesCategory = document.querySelector(
		`.${ PREFIX }-cat-item[data-category="${VIEW_SLUGS.ALL_TEMPLATES}"]`
	);
	const favoritesCategory = document.querySelector(
		`.${ PREFIX }-cat-item[data-category="${VIEW_SLUGS.FAVORITES}"]`
	);
	const availableTemplatesCategory = document.querySelector(
		`.${ PREFIX }-cat-item[data-category="${VIEW_SLUGS.AVAILABLE_TEMPLATES}"]`
	);
	const freeTemplatesCategory = document.querySelector(
		`.${ PREFIX }-cat-item[data-category="${VIEW_SLUGS.FREE_TEMPLATES}"]`
	);
	const sidebarElements = {
		sidebar,
		searchInput,
		allTemplatesCategory,
		favoritesCategory,
		favoritesCategoryCountEl: favoritesCategory?.querySelector(
			`.${ PREFIX }-cat-count`
		),
		availableTemplatesCategory,
		freeTemplatesCategory
	};

	// Modal Elements
	const modal = document.querySelector( `#${PREFIX}-modal` );
	const modalElements = {
		modal,
		modalItems: modal?.querySelectorAll( `.${PREFIX}-modal-item` ),
		renewAccountModal: modal?.querySelector( '#frm-renew-modal' ),
		// Leave Email Modal
		leaveEmailModal: modal?.querySelector( '#frm-leave-email-modal' ),
		leaveEmailModalInput: modal?.querySelector( '#frm_leave_email' ),
		leaveEmailModalApiEmailForm: modal?.querySelector( '#frmapi-email-form' ),
		// Code from Email Modal
		codeFromEmailModal: modal?.querySelector( '#frm-code-from-email-modal' ),
		codeFromEmailModalInput: modal?.querySelector( '#frm_code_from_email' ),
		// Upgrade Modal
		upgradeModal: modal?.querySelector( '#frm-upgrade-modal' ),
		upgradeModalTemplateNames: modal?.querySelectorAll( '.frm-upgrade-modal-template-name' ),
		upgradeModalPlansIcons: modal?.querySelectorAll( '.frm-upgrade-modal-plan-icon' )
	};

	// New Template Form Elements
	const newTemplateForm = document.querySelector( '#frm-new-template' );
	const newTemplateFormElements = {
		newTemplateForm,
		newTemplateNameInput: newTemplateForm?.querySelector( '#frm_template_name' ),
		newTemplateDescriptionInput: newTemplateForm?.querySelector( '#frm_template_desc' ),
		newTemplateLinkInput: newTemplateForm?.querySelector( '#frm_link' ),
		newTemplateActionInput: newTemplateForm?.querySelector( '#frm_action_type' )
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
