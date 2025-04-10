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

domReady( () => {
	new frmRadioComponent();
	new frmSliderComponent();
	new frmTabsComponent();
} );

