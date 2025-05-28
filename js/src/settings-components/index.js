/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { frmRadioComponent, frmSliderComponent, frmTabsComponent, initTokenInputFields, initToggleGroupComponents } from './components';
import { initAddFieldsButtonHandler } from './handlers';

domReady( () => {
	new frmRadioComponent();
	new frmSliderComponent();
	new frmTabsComponent();
	initAddFieldsButtonHandler();
	initTokenInputFields();
	initToggleGroupComponents();
} );
