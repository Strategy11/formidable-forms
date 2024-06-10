/**
 * Internal dependencies
 */
import { createAppState } from '../../common/createAppState';
import { getElements } from '../elements';
import { VIEW_SLUGS } from '.';

/**
 * Creates an instance of application state management for the Form Templates.
 *
 * @return {Object} The initial state of the application.
 */
const formTemplatesAppState = createAppState( () => {
	const { templatesCount, favoritesCount, customCount } = window.frmFormTemplatesVars;
	const { allItemsCategory, availableTemplateItems, freeTemplateItems, firstLockedFreeTemplate } = getElements();

	const availableTemplatesCount = availableTemplateItems.length;
	const extraTemplatesCount = templatesCount - availableTemplatesCount;

	return {
		hasSearchQuery: false,
		selectedCategory: VIEW_SLUGS.ALL_ITEMS,
		selectedCategoryEl: allItemsCategory,
		selectedTemplate: firstLockedFreeTemplate,
		favoritesCount,
		customCount: Number( customCount ),
		availableTemplatesCount,
		freeTemplatesCount: freeTemplateItems.length,
		extraTemplatesCount
	};
});

export const {
	initializeAppState,
	getAppState,
	setAppState,
	getAppStateProperty,
	setAppStateProperty
} = formTemplatesAppState;
