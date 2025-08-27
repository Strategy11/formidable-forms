/**
 * External dependencies
 */
import { getState, getSingleState, setState, setSingleState } from 'core/page-skeleton';

/**
 * Internal dependencies
 */
import { getElements } from '../elements';

const { templatesCount, favoritesCount, customCount } = window.frmFormTemplatesVars;
const { availableTemplateItems } = getElements();
const availableTemplatesCount = availableTemplateItems.length;

setState( {
	availableTemplatesCount,
	customCount: Number( customCount ),
	extraTemplatesCount: templatesCount - availableTemplatesCount,
	favoritesCount,
	selectedTemplate: false,
} );

export { getState, getSingleState, setState, setSingleState };
