/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import frmRadioComponent from './components/radio-component.js';
import frmSliderComponent from './components/slider-component.js';
import frmTabsComponent from './components/tabs-component.js';
import initAddFieldsButtonHandler from './handlers/add-fields-button-handler.js';
import { initTokenInputFields } from './token-input.js';

domReady( () => {
	new frmRadioComponent();
	new frmSliderComponent();
	new frmTabsComponent();
	initAddFieldsButtonHandler();
	initTokenInputFields();
} );
