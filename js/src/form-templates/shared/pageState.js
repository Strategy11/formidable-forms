/**
 * External dependencies
 */
import { createPageState } from 'core/factory';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';
import { VIEW_SLUGS } from '.';

const { templatesCount, favoritesCount, customCount } =
	window.frmFormTemplatesVars;

const {
	allTemplatesCategory,
	availableTemplateItems,
	freeTemplateItems,
	firstLockedFreeTemplate,
} = getElements();

const availableTemplatesCount = availableTemplateItems.length;

export const { getState, getSingleState, setState, setSingleState } = createPageState({
	selectedCategory: VIEW_SLUGS.ALL_TEMPLATES,
	selectedCategoryEl: allTemplatesCategory,
	selectedTemplate: firstLockedFreeTemplate,
	notEmptySearchText: false,
	favoritesCount,
	customCount: Number( customCount ),
	availableTemplatesCount,
	freeTemplatesCount: freeTemplateItems.length,
	extraTemplatesCount: templatesCount - availableTemplatesCount,
});
