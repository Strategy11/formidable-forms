import { __ } from '@wordpress/i18n';
import frmStyleDependentUpdaterComponent from './components/dependent-updater-component';

/**
 * Represents the frmStyleOptions class.
 *
 * @class
 */
class frmStyleOptions {
	constructor() {
		this.success = frmDom.success;
		this.cssEditorInstance = null;
		this.cssInlineStyleElement = null;
		this.cssEditorOptions = {
			retryLimit: 5, // Stop after 5 retries.
			retryInterval: 500, // Retry every 500ms.
			retryCount: 0, // Count the number of retries.
		};
		this.init();
		this.initHover();
		this.initCustomCSSEditorInstance();
	}

	/**
	 * Init the dependent
	 */
	init() {
		this.initColorPickerDependentUpdaterComponents();
		this.initStyleClassCopyToClipboard( __( 'The class name has been copied.', 'formidable' ) );
		this.toggleVisibilityOfCustomCSSEditor();
	}

	/**
	 * Initialize the custom CSS editor instance.
	 *
	 * @return {void}
	 */
	initCustomCSSEditorInstance() {
		if ( null !== this.cssEditorInstance || this.cssEditorOptions.retryCount >= this.cssEditorOptions.retryLimit ) {
			return;
		}

		if ( 'undefined' === typeof window.frm_single_style_custom_css_wp_editor || 'undefined' === typeof window.frm_single_style_custom_css_wp_editor.codemirror ) {
			setTimeout( () => {
				this.cssEditorOptions.retryCount++;
				this.initCustomCSSEditorInstance();
			}, 500 );
			return;
		}

		this.cssEditorInstance = window.frm_single_style_custom_css_wp_editor.codemirror;
		this.onCssEditorReady();
	}

	/**
	 * Get the inline style element.
	 *
	 * @return {HTMLElement}
	 */
	getInlineStyleElement() {
		if ( null !== this.cssInlineStyleElement ) {
			return this.cssInlineStyleElement;
		}

		this.cssInlineStyleElement = document.createElement( 'style' );
		document.head.appendChild( this.cssInlineStyleElement );
		return this.cssInlineStyleElement;
	}

	/**
	 * On the CSS editor ready, add an event listener to the editor to update the inline style element.
	 *
	 * @return {void}
	 */
	onCssEditorReady() {
		const cssScope = document.getElementById( 'frm_style_class_custom_css' )?.dataset?.cssScope;
		if ( null === cssScope ) {
			return;
		}

		this.cssEditorInstance.on( 'change', editor => this.getInlineStyleElement().textContent = `.${ cssScope } { ${ editor.getValue() } }` );
	}

	/**
	 * Initializes the color picker dependent updater components.
	 * Retrieves the components and elements, and adds them to the elements array.
	 * Adds an action hook for the frm_style_options_color_change event.
	 */
	initColorPickerDependentUpdaterComponents() {
		const components = document.querySelectorAll( '.frm-style-dependent-updater-component.frm-colorpicker' );
		const elements = [];

		components.forEach( component => {
			const element = component.querySelector( 'input.hex' );
			const id = 'undefined' !== typeof element ? element.getAttribute( 'id' ) : null;

			if ( null !== id ) {
				elements.push( {
					id: id,
					dependentUpdaterClass: new frmStyleDependentUpdaterComponent( component, 'colorpicker' )
				} );
			}
		} );

		wp.hooks.addAction( 'frm_style_options_color_change', 'formidable', ( { event, value } ) => {
			const container = event.target.closest( '.wp-picker-container' );
			const id = event.target.getAttribute( 'id' );

			container.querySelector( '.wp-color-result-text' ).innerText = value;

			elements.forEach( element => {
				if ( element.id === id ) {
					element.dependentUpdaterClass.updateAllDependentElements( value );
				}
			} );
		} );
	}

	/**
	 * Initializes the hover functionality for the style options.
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

		styleOptionsMenu.querySelectorAll( ':scope > li' ).forEach( item => {
			item.querySelector( 'h3' ).addEventListener( 'mouseover', event => {
				hoverElement.style.transform = `translateY(${ event.target.closest( 'li' ).offsetTop }px)`;
				hoverElement.classList.add( 'frm-animating' );
				hoverElement.classList.remove( 'frm_hidden' );
				setTimeout( () => {
					hoverElement.classList.remove( 'frm-animating' );
				}, 250 );
			} );
		} );

		const accordionitems = document.querySelectorAll( '#frm_style_sidebar .accordion-section h3' );
		accordionitems.forEach( item => {
			item.addEventListener( 'click', () => {
				hoverElement.classList.add( 'frm_hidden' );
			} );
		} );
	}

	/**
	 * Initializes the copy to clipboard functionality for style classes.
	 * Adds a click event listener to the copyLabel element.
	 * Copies the class name to the clipboard and displays a success message.
	 *
	 * @param {string} successMessage - The success message to display.
	 */
	initStyleClassCopyToClipboard( successMessage ) {
		if ( ! navigator.clipboard || ! navigator.clipboard.writeText ) {
			return;
		}

		const labels = document.querySelectorAll( '.frm-copy-text' );
		labels.forEach( label => {
			label.addEventListener( 'click', event => {
				const className = event.currentTarget.innerText;
				navigator.clipboard.writeText( className ).then( () => {
					this.success( successMessage );
				} );
			});
		});
	}

	toggleVisibilityOfCustomCSSEditor() {
		const toggle = document.querySelector( '#frm_enable_single_style_custom_css' );
		const editor = document.querySelector( '#frm_single_style_custom_css_editor' );
		if ( ! toggle || ! editor ) {
			return;
		}
		toggle.addEventListener( 'change', event => {
			editor.classList.toggle( 'frm_hidden', ! event.target.checked );
		} );
	}
}

new frmStyleOptions();
