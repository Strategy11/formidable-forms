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
		const copiedMessage = __( 'The class name has been copied.', 'formidable' );

		this.initColorPickerDependentUpdaterComponents();
		this.initStyleClassCopyToClipboard( copiedMessage );
		this.initStyleClassRename( copiedMessage );
		this.toggleVisibilityOfCustomCSSEditor();
	}

	/**
	 * Initializes renaming of the style class.
	 * The name is edited in place, and mirrored into the read only label in the advanced
	 * settings while it is typed. The warning only appears once the name actually changes.
	 *
	 * @param {string} successMessage The message to show once the class name is copied.
	 * @return {void}
	 */
	initStyleClassRename( successMessage ) {
		const component = document.querySelector( '.frm-style-class-component' );
		if ( ! component ) {
			return;
		}

		const copyButton = component.querySelector( '.frm-style-class-copy' );
		const description = component.querySelector( '.frm-style-class-description' );
		const input = component.querySelector( '#frm_style_class' );

		if ( ! input ) {
			return;
		}

		const originalName = input.value;

		input.addEventListener( 'input', () => {
			input.value = this.sanitizeStyleClassName( input.value );
			this.updateStyleClassLabels( input.value );
			description?.classList.toggle( 'frm_hidden', input.value === originalName );
		} );

		input.addEventListener( 'keydown', event => {
			if ( 'Escape' === event.key ) {
				input.value = originalName;
				this.updateStyleClassLabels( originalName );
				description?.classList.add( 'frm_hidden' );
				return;
			}

			if ( 'Enter' === event.key ) {
				// Don't submit the whole style form from this input.
				event.preventDefault();
			}
		} );

		copyButton?.addEventListener( 'click', () => {
			this.copyToClipboard( `.frm_style_${ input.value }`, copyButton, successMessage );
		} );
	}

	/**
	 * Reduces a typed value to the characters WordPress keeps in a post slug.
	 *
	 * @param {string} name The typed class name.
	 * @return {string} The sanitized class name.
	 */
	sanitizeStyleClassName( name ) {
		return name.toLowerCase().replace( /[^a-z0-9_-]+/g, '-' );
	}

	/**
	 * Mirrors the class name into every label that shows it.
	 * Both the quick settings and the advanced settings render one.
	 *
	 * @param {string} name The class name to show.
	 * @return {void}
	 */
	updateStyleClassLabels( name ) {
		document.querySelectorAll( '.frm-style-class-name' ).forEach( label => {
			label.textContent = name;
		} );
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

		if ( window.frm_single_style_custom_css_wp_editor === undefined || window.frm_single_style_custom_css_wp_editor.codemirror === undefined ) {
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
	 * @return {HTMLElement} The inline style element.
	 */
	getInlineStyleElement() {
		if ( null !== this.cssInlineStyleElement ) {
			return this.cssInlineStyleElement;
		}

		this.cssInlineStyleElement = document.createElement( 'style' );
		document.head.append( this.cssInlineStyleElement );
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
		const sanitizedCssScope = CSS.escape( cssScope );

		this.cssEditorInstance.on( 'change', editor => {
			// eslint-disable-next-line sonarjs/super-linear-regex -- regex kept as-is, not refactored
			const value = editor.getValue().replace( /<[^>]*>/g, '' ).trim();
			this.getInlineStyleElement().textContent = `.${ sanitizedCssScope } { ${ value } }`;
		} );
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
			const id = element !== undefined ? element.getAttribute( 'id' ) : null;

			if ( null !== id ) {
				elements.push( {
					id,
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
		if ( ! settingsWrapper ) {
			return;
		}
		const hoverElement = document.createElement( 'div' );
		hoverElement.classList.add( 'frm_hidden' );
		hoverElement.classList.add( 'frm-style-settings-hover' );
		settingsWrapper.append( hoverElement );

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
	 * @param {string} successMessage The success message to display.
	 * @return {void} Initializes the copy to clipboard functionality for style classes.
	 */
	initStyleClassCopyToClipboard( successMessage ) {
		const labels = document.querySelectorAll( '.frm-copy-text' );
		labels.forEach( label => {
			label.addEventListener( 'click', event => {
				this.copyToClipboard( event.currentTarget.innerText, event.currentTarget, successMessage );
			} );
		} );
	}

	/**
	 * Copies text to the clipboard and reports it, falling back when the Clipboard API is missing.
	 *
	 * @param {string}      text           The text to copy.
	 * @param {HTMLElement} element        Used to position the fallback input element.
	 * @param {string}      successMessage The message to show once the text is copied.
	 * @return {void}
	 */
	copyToClipboard( text, element, successMessage ) {
		if ( ! navigator.clipboard || ! navigator.clipboard.writeText ) {
			if ( true === this.fallbackCopyToClipboard( text, element ) ) {
				this.success( successMessage );
			}
			return;
		}

		navigator.clipboard.writeText( text ).then( () => {
			this.success( successMessage );
		} );
	}

	/**
	 * Toggle the visibility of the custom CSS editor.
	 *
	 * @return {void}
	 */
	toggleVisibilityOfCustomCSSEditor() {
		const toggle = document.getElementById( 'frm_enable_single_style_custom_css' );
		const editor = document.getElementById( 'frm_single_style_custom_css_editor' );
		if ( ! toggle || ! editor ) {
			return;
		}
		toggle.addEventListener( 'change', event => {
			editor.classList.toggle( 'frm_hidden', ! event.target.checked );
		} );
	}

	/**
	 * Copy to clipboard if the Clipboard API is not available.
	 *
	 * @param {string}      couponCode The string being copied to the clipboard.
	 * @param {HTMLElement} copyButton Used to position the temporary input element.
	 * @return {boolean} True if the copy was successful, false otherwise.
	 */
	fallbackCopyToClipboard( couponCode, copyButton ) {
		if ( 'function' !== typeof document.execCommand ) {
			return false;
		}

		let copySuccess;

		const temp = document.createElement( 'input' );
		temp.setAttribute( 'type', 'text' );
		temp.value = couponCode;

		copyButton.parentElement.append( temp );

		temp.focus();
		temp.select();
		temp.setSelectionRange( 0, 99999 );

		// Hide the input so it doesn't show up in the UI.
		temp.style.position = 'absolute';
		temp.style.left = '-9999px';
		temp.style.top = '-9999px';

		try {
			copySuccess = document.execCommand( 'copy' );
		} catch ( error ) {
			copySuccess = false;
		}

		temp.remove();

		return copySuccess;
	}
}

new frmStyleOptions();
