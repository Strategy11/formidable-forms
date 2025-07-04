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
	setupCustomToggleGroupHandlers
} from './components';

domReady( () => {
	new frmRadioComponent();
	new frmSliderComponent();
	new frmTabsComponent();
	initAddFieldsButtonHandler();
	initTokenInputFields();
	initToggleGroupComponents();
	setupCustomToggleGroupHandlers();
});
