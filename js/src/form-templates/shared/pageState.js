/**
 * External dependencies
 */
import { getState, getSingleState, setState, setSingleState } from 'core/page-skeleton';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';

const { templatesCount, favoritesCount, customCount } = window.frmFormTemplatesVars;
const { availableTemplateItems, freeTemplateItems, firstLockedFreeTemplate } = getElements();
const availableTemplatesCount = availableTemplateItems.length;

setState({
	availableTemplatesCount,
	customCount: Number( customCount ),
	extraTemplatesCount: templatesCount - availableTemplatesCount,
	favoritesCount,
	freeTemplatesCount: freeTemplateItems.length,
	selectedTemplate: firstLockedFreeTemplate,
});

export { getState, getSingleState, setState, setSingleState };
