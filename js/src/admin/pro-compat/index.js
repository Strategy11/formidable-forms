/* global frmAdminBuild */

/**
 * Builder features that moved from Lite to Pro.
 *
 * Lite only loads this bundle when Pro is active and does not list every feature in
 * frm_pro_builder_features. That happens with Pro versions released before the move.
 * For each feature Pro does not handle, this registers Lite's original code.
 *
 * @since x.x
 */

import { registerCalcBox } from './calcBox';
import { registerPageSectionChrome } from './pageSectionChrome';
import { registerLookupSettings } from './lookupSettings';
import { registerRepeaterSettings } from './repeaterSettings';
import { registerConfirmationField } from './confirmationField';
import { registerFieldLogicRows } from './fieldLogicRows';
import { registerLogicOptionSync } from './logicOptionSync';

const COMPAT_FEATURES = {
	calc_box: registerCalcBox,
	page_section_chrome: registerPageSectionChrome,
	lookup_settings: registerLookupSettings,
	repeater_settings: registerRepeaterSettings,
	confirmation_field: registerConfirmationField,
	field_logic_rows: registerFieldLogicRows,
	logic_option_sync: registerLogicOptionSync,
};

if ( 'function' === typeof window.frmAdminBuild?.registerBuilderFeature ) {
	const proFeatures = new Set( window.frmGlobal?.proBuilderFeatures );

	Object.entries( COMPAT_FEATURES ).forEach( ( [ key, register ] ) => {
		if ( ! proFeatures.has( key ) && ! frmAdminBuild.hasBuilderFeature( key ) ) {
			register();
		}
	} );
}
