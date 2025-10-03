/**
 * External dependencies
 */
import { showElements, hideElements, show, hide } from 'core/utils';
import { VIEWS as SKELETON_VIEWS } from 'core/page-skeleton';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { PREFIX, VIEW_SLUGS, getState } from '../shared';
import { isFavoriteTemplate } from '../utils';
import { categorizedTemplates } from '../templates';
import { updatePageTitle, showFavoritesEmptyState, showCustomTemplatesEmptyState, showAvailableTemplatesEmptyState } from './';

/**
 * Show templates based on selected category.
 *
 * @param {string} selectedCategory The selected category to display templates for.
 * @return {void}
 */
export function showSelectedCategory( selectedCategory ) {
	const { bodyContentChildren, pageTitle, showCreateTemplateModalButton, templatesList, templateItems, upsellBanner } = getElements();

	if ( SKELETON_VIEWS.ALL_ITEMS !== selectedCategory ) {
		hideElements( bodyContentChildren );
	}

	updatePageTitle();
	hide( showCreateTemplateModalButton );
	show( pageTitle );

	switch ( selectedCategory ) {
		case SKELETON_VIEWS.ALL_ITEMS:
			showAllTemplates();
			break;
		case VIEW_SLUGS.AVAILABLE_TEMPLATES:
			showAvailableTemplates();
			break;
		case VIEW_SLUGS.FAVORITES:
			showFavoriteTemplates();
			break;
		case VIEW_SLUGS.CUSTOM:
			showCustomTemplates();
			break;
		default:
			hideElements( templateItems ); // Clear the view for new content
			showElements( [ upsellBanner, templatesList, ...categorizedTemplates[ selectedCategory ] ] );
			break;
	}
}

/**
 * Shows all templates when 'All Templates' is the selected category.
 *
 * @return {void}
 */
export function showAllTemplates() {
	const {
		bodyContentChildren,
		pageTitleDivider,
		templateItems,
		twinFeaturedTemplateItems,
		customTemplatesSection,
		emptyState,
		applicationTemplates
	} = getElements();

	showElements( [ ...bodyContentChildren, ...templateItems ] );
	hideElements( [ pageTitleDivider, ...twinFeaturedTemplateItems, customTemplatesSection, emptyState, applicationTemplates ] );
}

/**
 * Shows favorite templates.
 *
 * @return {void}
 */
export function showFavoriteTemplates() {
	const { favoritesCount } = getState();

	if ( 0 === favoritesCount.total ) {
		showFavoritesEmptyState();
		return;
	}

	const {
		bodyContent,
		templatesList,
		templateItems,
		customTemplatesSection,
		customTemplatesTitle,
		customTemplatesList,
		customTemplateItems
	} = getElements();

	// Clear the view for new content
	hideElements( templateItems );

	const elementsToShow = [];

	// Get all favorite items from the DOM and add the elements to show
	const favoriteItems = bodyContent.querySelectorAll( `.${ PREFIX }-favorite-item` );
	elementsToShow.push( ...favoriteItems );

	// Add default favorites if available
	if ( favoritesCount.default > 0 ) {
		elementsToShow.push( templatesList );
	}

	// Add custom favorites if available
	if ( favoritesCount.custom > 0 ) {
		const nonFavCustomTemplates = Array.from( customTemplateItems ).filter(
			template => ! isFavoriteTemplate( template )
		);

		hideElements( nonFavCustomTemplates );
		elementsToShow.push( customTemplatesSection );
		elementsToShow.push( customTemplatesList );

		if ( 0 === favoritesCount.default ) {
			hide( customTemplatesTitle );
		} else {
			elementsToShow.push( customTemplatesTitle );
		}
	}

	// Show elements that were selected to be shown
	showElements( elementsToShow );
}

/**
 * Shows custom templates.
 *
 * @return {void}
 */
export function showCustomTemplates() {
	const { customCount } = getState();

	if ( 0 === customCount ) {
		showCustomTemplatesEmptyState();
		return;
	}

	const {
		showCreateTemplateModalButton,
		pageTitleDivider,
		customTemplatesSection,
		customTemplatesList,
		customTemplatesTitle,
		customTemplateItems
	} = getElements();

	hide( customTemplatesTitle );
	showElements( [ showCreateTemplateModalButton, pageTitleDivider, customTemplatesSection, customTemplatesList, ...customTemplateItems ] );
}

/**
 * Shows available templates.
 *
 * @return {void}
 */
export function showAvailableTemplates() {
	const { availableTemplatesCount } = getState();

	if ( 0 === availableTemplatesCount ) {
		showAvailableTemplatesEmptyState();
		return;
	}

	const { templatesList, templateItems, availableTemplateItems, upsellBanner } = getElements();

	hideElements( templateItems ); // Clear the view for new content
	showElements( [ upsellBanner, templatesList, ...availableTemplateItems ] );
}

export default showSelectedCategory;
