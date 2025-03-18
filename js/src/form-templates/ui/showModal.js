/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import { hideElements, show } from 'core/utils';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { MODAL_SIZES, PLANS, upgradeLink } from '../shared';
import { getModalWidget } from './';

/**
 * Display the locked template modal.
 *
 * @param {HTMLElement} template The template element.
 * @return {void}
 */
export function showLockedTemplateModal( template ) {
	const plan = template.dataset.requiredPlan;

	switch ( plan ) {
		case PLANS.BASIC:
		case PLANS.PLUS:
		case PLANS.BUSINESS:
		case PLANS.ELITE:
			showUpgradeModal( plan, template );
			break;
		case PLANS.RENEW:
			showRenewAccountModal();
			break;
	}
}

/**
 * Base function to show a modal dialog with a customizable pre-open execution step.
 *
 * @param {Function} executePreOpen The function to be executed before opening the modal dialog.
 * @return {Function} A higher-order function that can be invoked to display the modal dialog.
 */
const showModal = executePreOpen => async( ...params ) => {
	const dialogWidget = getModalWidget();
	if ( ! dialogWidget ) {
		return;
	}

	const { modalItems } = getElements();
	hideElements( modalItems );

	dialogWidget.dialog( 'option', 'width', MODAL_SIZES.GENERAL );
	await executePreOpen?.( ...params );
	dialogWidget.dialog( 'open' );
};

// Mapping each plan to the subsequent plans it can upgrade to
const upgradablePlans = {
	basic: [ 'basic', 'plus', 'business', 'elite' ],
	plus: [ 'plus', 'business', 'elite' ],
	business: [ 'business', 'elite' ],
	elite: [ 'elite' ]
};

/**
 * Display the modal dialog to prompt the user to upgrade their account.
 *
 * @param {string} plan Current plan name
 * @param {HTMLElement} template The template element
 * @return {void}
 */
export const showUpgradeModal = showModal( ( plan, template ) => {
	const templateName = template.querySelector( '.frm-form-template-name' ).textContent.trim();
	const { upgradeModal, upgradeModalTemplateNames, upgradeModalPlansIcons, upgradeModalLink } = getElements();

	// Update template names
	upgradeModalTemplateNames.forEach( element => element.textContent = templateName );

	// Update plan icons and their availability
	upgradeModalPlansIcons.forEach( icon => {
		const planType = icon.dataset.plan;
		const shouldDisplayCheck = upgradablePlans[plan].includes( planType );

		// Toggle icon class based on plan availability
		icon.classList.toggle( 'frm_green', shouldDisplayCheck );

		// Update SVG icon
		const svg = icon.querySelector( 'svg > use' );
		svg.setAttribute( 'xlink:href', shouldDisplayCheck ? '#frm_checkmark_icon' : '#frm_close_icon' );
	});

	// Append template slug to the upgrade modal link URL
	const templateSlug = template.dataset.slug ? `-${template.dataset.slug}` : '';
	upgradeModalLink.href = upgradeLink + templateSlug;

	show( upgradeModal );
});

/**
 * Display the modal dialog to prompt the user to renew their account.
 *
 * @return {void}
 */
export const showRenewAccountModal = showModal( () => {
	const { renewAccountModal } = getElements();
	show( renewAccountModal );
});

/**
 * Display the modal dialog to prompt the user to save the code sent to their email address.
 *
 * @return {void}
 */
export const showCodeFromEmailModal = showModal( () => {
	const { codeFromEmailModal } = getElements();
	show( codeFromEmailModal );
});

/**
 * Displays a modal dialog prompting the user to create a new template.
 *
 * @return {void}
 */
export const showCreateTemplateModal = showModal( () => {
	const dialogWidget = getModalWidget();
	dialogWidget.dialog( 'option', 'width', MODAL_SIZES.CREATE_TEMPLATE );

	const { createTemplateModal } = getElements();
	show( createTemplateModal );
});
