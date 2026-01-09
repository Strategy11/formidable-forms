import { frmWebComponent } from '../frm-web-component';
import style from './frm-dropdown-component.css';

export class frmDropdownComponent extends frmWebComponent {
	constructor() {
		super();
		this.select = document.createElement( 'select' );
		this.componentStyle = style;
		this._onChange = () => {};
	}

	/**
	 * Initializes the view. Called when the component is rendered.
	 *
	 * @return {Element} - The wrapper element.
	 */
	initView() {
		this.wrapper = document.createElement( 'div' );
		this.wrapper.classList.add( 'frm-dropdown-component' );
		this.wrapper.appendChild( this.getSelect() );
		return this.wrapper;
	}

	/**
	 * Gets the select element.
	 *
	 * @return {Element} - The select element.
	 */
	getSelect() {
		this.select.id = this.componentId;
		this.select.name = this.fieldName;
		return this.select;
	}

	/**
	 * Determines if the component should use shadow DOM. The dropdown component can utilize shadow DOM as it does not require external functional dependencies.
	 *
	 * @return {boolean} - True if the component should use shadow DOM, false otherwise.
	 */
	useShadowDom() {
		return true;
	}

	/**
	 * Initializes the select options. It will retrieve the all the options from the component and create new option elements.
	 *
	 * @return {void}
	 */
	initSelectOptions() {
		const optionsNodes = this.querySelectorAll( 'option' );
		optionsNodes.forEach( option => {
			const opt = document.createElement( 'option' );
			opt.value = option.value;
			opt.textContent = option.textContent;
			option.remove();
			this.select.appendChild( opt );
		} );
	}

	/**
	 * Called when the component is visible in the viewport.
	 *
	 * @return {void}
	 */
	afterViewInit() {
		this.initSelectOptions();
		this.select.addEventListener( 'change', () => {
			this._onChange( this.select.value );
		} );
	}

	/**
	 * A method to add options dynamically to the select element.
	 *
	 * @param {Array} options - The options to add.
	 * @return {void}
	 */
	set addOptions( options ) {
		options.forEach( option => {
			const opt = document.createElement( 'option' );
			opt.value = option.value;
			opt.textContent = option.label;
			opt.selected = option.selected;
			this.select.appendChild( opt );
		} );
	}

	/**
	 * A method to set the disabled state of the select element.
	 *
	 * @param {boolean} value - The value to set.
	 * @return {void}
	 */
	set disabled( value ) {
		this.select.disabled = value;
	}

	/**
	 * A method to set the change event listener for the select element.
	 *
	 * @param {Function} callback - The callback function to call when the select element is changed.
	 * @return {void}
	 */
	set onChange( callback ) {
		if ( 'function' !== typeof callback ) {
			throw new Error( 'Callback must be a function' );
		}

		this._onChange = callback;
	}

	/**
	 * A method to set the selected value of the select element.
	 *
	 * @param {string} value - The value to set.
	 * @return {void}
	 */
	set selectedValue( value ) {
		const option = Array.from( this.select.options ).find( option => option.value === value );
		if ( option ) {
			option.selected = true;
		}
	}
}

// A shorthand function to create a dropdown component.
window.frmDropdownComponent = ( id, options, onChangeCallback ) => {
	const dropdown = document.createElement( 'frm-dropdown-component' );
	dropdown.id = id;
	dropdown.addOptions = options;
	dropdown.onChange = onChangeCallback;
	return dropdown;
};
