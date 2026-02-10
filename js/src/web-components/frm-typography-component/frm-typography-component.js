import { frmWebComponent } from '../frm-web-component';
import { __ } from '@wordpress/i18n';
import style from './frm-typography-component.css';

export class frmTypographyComponent extends frmWebComponent {
	#onChange = () => {}; // eslint-disable-line class-methods-use-this, no-empty-function
	#defaultOptions = [
		{
			value: '21px',
			label: __( 'Default', 'formidable' )
		},
	];
	#unitTypeOptions = [ 'px', 'em', '%' ];
	#defaultValue = '21px';
	static #instanceCount = 0;

	constructor() {
		super();
		this.componentStyle = style;
		this.#defaultOptions = [
			{
				value: '',
				label: __( 'Default', 'formidable' )
			},
			{
				value: '18px',
				label: __( 'Small', 'formidable' )
			},
			{
				value: '21px',
				label: __( 'Regular', 'formidable' )
			},
			{
				value: '26px',
				label: __( 'Large', 'formidable' )
			},
			{
				value: '32px',
				label: __( 'Larger', 'formidable' )
			},
			{
				value: 'custom',
				label: __( 'Custom', 'formidable' )
			},
		];
	}

	initOptions() {
		super.initOptions();
		if ( null === this.componentId ) {
			this.componentId = 'frm-typography-web-component-' + ( ++frmTypographyComponent.#instanceCount );
		}
	}

	initView() {
		this.wrapper = document.createElement( 'div' );
		this.container = document.createElement( 'div' );

		this.wrapper.classList.add( 'frm-typography-component', 'frm-typography' );
		this.container.classList.add( 'frm-typography-container' );

		this.container.append(
			this.getSelect(),
			this.getUnitValueWrapper(),
			this.getHiddenInput()
		);

		this.wrapper.append( this.container );
		return this.wrapper;
	}

	/**
	 * A method to get the select element.
	 *
	 * @return {Element} - The select element.
	 */
	getSelect() {
		this.select = document.createElement( 'select' );
		this.select.setAttribute( 'aria-label', __( 'Font size', 'formidable' ) );
		if ( null !== this.componentId ) {
			this.select.id = this.componentId;
		}
		if ( null !== this.fieldName ) {
			this.select.name = `${ this.fieldName }[size]`;
		}
		this.getDefaultOptions( this.select );
		return this.select;
	}

	/**
	 * A method to get the default options for the select element.
	 *
	 * @param {Element} select - The select element.
	 * @return {void}
	 */
	getDefaultOptions( select ) {
		this.#defaultOptions.forEach( option => {
			const opt = document.createElement( 'option' );
			opt.value = option.value;
			opt.textContent = option.label;
			opt.selected = ( option.value === 'custom' && frmTypographyComponent.isCustomFonSize( this.#defaultValue ) ) || option.value === this.#defaultValue;
			select.append( opt );
		} );
	}

	/**
	 * A method to check if the value is a custom font size.
	 *
	 * @param {string} value - The value to check if it is a custom font size.
	 * @return {boolean} - True if the value is a custom font size, false otherwise.
	 */
	static isCustomFonSize( value ) {
		return -1 === [ '', '18px', '21px', '26px', '32px' ].indexOf( value );
	}

	/**
	 * A method to change the select value.
	 *
	 * @param {string} value - The value to change the select value for.
	 * @return {void}
	 */
	changeSelectValue( value ) {
		const isCustomFonSize = frmTypographyComponent.isCustomFonSize( value );
		Array.from( this.select.options ).forEach( option => {
			option.selected = ( option.value === 'custom' && isCustomFonSize ) || option.value === value;
		} );
	}

	/**
	 * A method to get the unit value wrapper element.
	 *
	 * @return {Element} - The unit value wrapper element.
	 */
	getUnitValueWrapper() {
		this.unitValueWrapper = document.createElement( 'div' );
		this.unitValueWrapper.classList.add( 'frm-unit-value' );
		this.unitValueWrapper.append( this.getUnitValueInput() );
		this.unitValueWrapper.append( this.getUnitTypeSelect() );
		return this.unitValueWrapper;
	}

	/**
	 * A method to get the unit value input element.
	 *
	 * @return {Element} - The unit value input element.
	 */
	getUnitValueInput() {
		this.unitValueInput = document.createElement( 'input' );
		this.unitValueInput.setAttribute( 'aria-label', __( 'Font size value', 'formidable' ) );
		if ( null !== this.componentId ) {
			this.unitValueInput.id = `${ this.componentId }-unit`;
		}
		if ( null !== this.fieldName ) {
			this.unitValueInput.name = `${ this.fieldName }[unit]`;
		}

		this.unitValueInput.type = 'text';
		this.unitValueInput.value = '' !== this.#defaultValue ? `${ parseInt( this.#defaultValue, 10 ) }` : '';

		this.unitValueInput.addEventListener( 'change', event => {
			const value = '' !== event.target.value ? event.target.value + this.unitTypeSelect.value : '';
			this.changeSelectValue( value );
			this.#onChange( value );
		} );

		return this.unitValueInput;
	}

	/**
	 * A method to get the unit type select element.
	 *
	 * @return {Element} - The unit type select element.
	 */
	getUnitTypeSelect() {
		this.unitTypeSelect = document.createElement( 'select' );
		this.unitTypeSelect.setAttribute( 'aria-label', __( 'Font size unit', 'formidable' ) );

		if ( null !== this.componentId ) {
			this.unitTypeSelect.id = `${ this.componentId }-unit-type`;
		}
		if ( null !== this.fieldName ) {
			this.unitTypeSelect.name = `${ this.fieldName }[unit-type]`;
		}

		this.#unitTypeOptions.forEach( option => {
			const opt = document.createElement( 'option' );
			opt.value = option;
			opt.textContent = option;
			this.unitTypeSelect.append( opt );
		} );
		return this.unitTypeSelect;
	}

	/**
	 * A method to get the hidden input element.
	 *
	 * @return {Element} - The hidden input element.
	 */
	getHiddenInput() {
		this.hiddenInput = document.createElement( 'input' );
		this.hiddenInput.type = 'hidden';
		if ( null !== this.fieldName ) {
			this.hiddenInput.name = `${ this.fieldName }[value]`;
		}
		this.hiddenInput.value = this.#defaultValue;
		return this.hiddenInput;
	}

	/**
	 * A method to set the change event listener for the select element.
	 *
	 * @return {void} - The unit value.
	 */
	afterViewInit() {
		this.select.addEventListener( 'change', () => {
			const value = frmTypographyComponent.getUnitValue( this.select.value );
			this.unitValueInput.value = value.value;
			this.hiddenInput.value = value.value + value.unit;
			this.#onChange( this.hiddenInput.value );
			this.unitTypeSelect.value = value.unit;
		} );
	}

	/**
	 * A method to get the unit value.
	 *
	 * @param {string} value - The value to get the unit value for.
	 * @return {Object} - The unit value.
	 */
	static getUnitValue( value ) {
		const defaultValue = { value: '', unit: '' };
		if ( ! value ) {
			return defaultValue;
		}
		const match = value.match( /^([\d.]+)(px|em|%)?$/ );

		if ( ! match ) {
			return defaultValue;
		}

		return {
			value: parseInt( value, 10 ),
			unit: match[ 2 ] || 'px'
		};
	}

	/**
	 * A method to set the change event listener for the select element.
	 *
	 * @param {Function} callback - The callback function to call when the select element is changed.
	 * @return {void}
	 */
	set onChange( callback ) { // eslint-disable-line accessor-pairs
		if ( 'function' !== typeof callback ) {
			throw new TypeError( `Expected a function, but received ${ typeof callback }` );
		}

		this.#onChange = callback;
	}

	/**
	 * A method to set dynamically the default value for the typography component.
	 *
	 * @param {string} value - The value to set dynamically the default value for.
	 * @return {void}
	 */
	set typographyDefaultValue( value ) { // eslint-disable-line accessor-pairs
		this.#defaultValue = value;
	}
}
