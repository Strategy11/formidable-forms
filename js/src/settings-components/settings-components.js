import { __ } from '@wordpress/i18n';
import frmRadioComponent from './components/radio-component.js';
import frmSliderComponent from './components/slider-component.js';
import frmTabsComponent from './components/tabs-component.js';
import frmDependentUpdaterComponent from './components/dependent-updater-component.js';

/**
 * Represents the frmSettingsComponents class.
 * @class
 */
class frmSettingsComponents  {

	constructor() {
		this.success = frmDom.success;
		this.init();
		this.initHover();
	}

	/**
	 * Initializes the components: frmRadioComponent, frmSliderComponent, and frmTabsComponent.
	 * Init the dependent
	 */
	init() {
		new frmRadioComponent();
		new frmSliderComponent();
		new frmTabsComponent();

		this.initColorPickerDependentUpdaterComponents();
		this.initStyleClassCopyToClipboard( __( 'The class name has been copied.', 'formidable' ) );
	}

	/**
	 * Initializes the color picker dependent updater components.
	 * Retrieves the components and elements, and adds them to the elements array.
	 * Adds an action hook for the frm_style_options_color_change event.
	 */
	initColorPickerDependentUpdaterComponents() {
		const components = document.querySelectorAll( '.frm-style-dependent-updater-component.frm-colorpicker' );
		const elements   = [];

		components.forEach( ( component ) => {
			const element = component.querySelector( 'input.hex' );
			const id      = 'undefined' !== typeof element ? element.getAttribute( 'id' ) : null;

			if ( null !== id ) {
				elements.push({
					id: id,
					dependentUpdaterClass: new frmDependentUpdaterComponent( component, 'colorpicker' )
				});
			}
		});

		wp.hooks.addAction( 'frm_style_options_color_change', 'formidable', ( { event, value } ) => {
			const container = event.target.closest( '.wp-picker-container' );
			const id        = event.target.getAttribute( 'id' );

			container.querySelector( '.wp-color-result-text' ).innerText = value;

			elements.forEach( ( element ) => {
				if ( element.id === id ) {
					element.dependentUpdaterClass.updateAllDependentElements( value );
				}
			});
		});
	}

	/**
	 * Initializes the hover functionality for the options.
	 * Creates a hover element and appends it to the settingsWrapper.
	 * Adds event listeners for mouseover and click events.
	 */
	initHover() {
		const settingsWrapper = document.querySelector( '.frm-right-panel .styling_settings .accordion-container' );
		if ( null === settingsWrapper ) {
			return;
		}
		const hoverElement = document.createElement( 'div' );
		hoverElement.classList.add( 'frm_hidden' );
		hoverElement.classList.add( 'frm-style-settings-hover' );
		settingsWrapper.appendChild( hoverElement );

		const styleOptionsMenu = settingsWrapper.querySelector( ':scope > ul' );

		styleOptionsMenu.querySelectorAll( ':scope > li' ).forEach( ( item ) => {
			item.querySelector('h3').addEventListener( 'mouseover', ( event ) => {
				hoverElement.style.transform = `translateY(${event.target.closest('li').offsetTop}px)`;
				hoverElement.classList.add( 'frm-animating' );
				hoverElement.classList.remove( 'frm_hidden' );
				setTimeout( () => { hoverElement.classList.remove( 'frm-animating' ); }, 250 );
			});
		});

		const accordionitems = document.querySelectorAll( '#frm_style_sidebar .accordion-section h3' );
		accordionitems.forEach( ( item ) => {
			item.addEventListener( 'click', () => {
				hoverElement.classList.add( 'frm_hidden' );
			});
		});
	}

	/**
	 * Initializes the copy to clipboard functionality for classes.
	 * Adds a click event listener to the copyLabel element.
	 * Copies the class name to the clipboard and displays a success message.
	 * @param {string} successMessage - The success message to display.
	 */
	initStyleClassCopyToClipboard( successMessage ) {
		const copyLabel = document.querySelector( '.frm-copy-text' );
		if ( ! copyLabel ) {
			return;
		}

		copyLabel.addEventListener( 'click', ( event ) => {
			const className = event.currentTarget.innerText;
			navigator.clipboard.writeText( className ).then( () => {
				this.success( successMessage );
			});
		})
	}
}

new frmSettingsComponents();
