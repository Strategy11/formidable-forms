/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { initAddFieldsButtonHandler } from './events';
import {
	frmRadioComponent,
	frmSliderComponent,
	frmTabsComponent,
	initTokenInputFields,
	initToggleGroupComponents,
	setupUnitInputHandlers
} from './components';
import { initFormActionsSearch } from './components/formActionsSearch';

domReady( () => {
	new frmRadioComponent();
	new frmSliderComponent();
	new frmTabsComponent();
	initAddFieldsButtonHandler();
	initTokenInputFields();
	initToggleGroupComponents();
	setupUnitInputHandlers();
	initFormActionsSearch();
} );
