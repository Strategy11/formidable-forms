import { frmWebComponent } from '../frm-web-component';
import style from './frm-colorpicker-component.css';

class frmColorpickerLiteComponent extends frmWebComponent {

	#onChange = () => {};
	#defaultValue = '';

	constructor() {
		super();
		this.input = document.createElement( 'input' );
		this.componentStyle = style;
	}

	initView() {
		const wrapper = document.createElement( 'div' );
		wrapper.classList.add( 'frm-colorpicker-component', 'frm-colorpicker' );
		wrapper.append( this.getInput() );
		return wrapper;
	}

	getInput() {
		this.input.type = 'text';
		this.input.classList.add( 'hex' );

		if ( null !== this.fieldName ) {
			this.input.name = this.fieldName;
		}

		if ( null !== this.#defaultValue ) {
			this.input.value = this.#defaultValue;
		}

		if ( null !== this.componentId ) {
			this.input.id = this.componentId;
		}

		return this.input;
	}

	useShadowDom() {
		return false;
	}

	afterViewInit() {
		const colorPickerOptions = 'function' === typeof this.#onChange ? {
			change: ( event, ui ) => this.#onChange( event, ui ),
		} : {};

		jQuery( this.input ).wpColorPicker( colorPickerOptions );
	}

	/**
	 * A method to get the color value.
	 *
	 * @return {string} - The color value.
	 */
	get color() {
		return jQuery( this.input ).wpColorPicker( 'color' );
	}

	/**
	 * A method to set the color value.
	 *
	 * @param {string} value - The value to set the color value for.
	 * @return {void}
	 */
	set color( value ) {
		this.input.value = value;
	}

	/**
	 * A method to set the change event listener for the color picker component.
	 *
	 * @param {Function} callback - The callback function to call when the color picker component is changed.
	 * @return {void}
	 */
	set onChange( callback ) {
		if ( 'function' !== typeof callback ) {
			throw new TypeError( `Expected a function, but received ${ typeof callback }` );
		}

		this.#onChange = callback;
	}
}

// The color picker component that may be a mixin of the color picker pro component.
export const frmColorpickerComponent = window.frmColorpickerProComponent ? window.frmColorpickerProComponent( frmColorpickerLiteComponent ) : frmColorpickerLiteComponent;
