import { frmWebComponent } from '../frm-web-component';
import { __ } from '@wordpress/i18n';
import style from './frm-typography-component.css';

export class frmTypographyComponent extends frmWebComponent {
	constructor() {
		super();
		this.componentStyle = style;
		this.defaultOptions = [
			{
				value: '21px',
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
				value: '',
				label: __( 'Custom', 'formidable' )
			},
		];
		this.value = '21px';
		this.unitTypeOptions = [ 'px', 'em', '%' ];
		this._onChange = () => {};
		this._defaultValue = '21px';
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
		this.defaultOptions.forEach( option => {
			const opt = document.createElement( 'option' );
			opt.value = option.value;
			opt.textContent = option.label;
			opt.selected = option.value === this._defaultValue;
			select.append( opt );
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
		if ( null !== this.componentId ) {
			this.unitValueInput.id = this.componentId + '-unit';
		}
		if ( null !== this.fieldName ) {
			this.unitValueInput.name = `${ this.fieldName }[unit]`;
		}

		this.unitValueInput.type = 'text';
		this.unitValueInput.value = `${ parseInt( this.defaultOptions.find( option => option.value === this._defaultValue )?.value ) || 21 }`;

		this.unitValueInput.addEventListener( 'change', event => {
			const selectValue = this.select.value;
			if ( this.defaultOptions.some( option => option.value === selectValue ) && '' !== selectValue ) {
				return;
			}
			this._onChange( event.target.value + this.unitTypeSelect.value );
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

		if ( null !== this.componentId ) {
			this.unitTypeSelect.id = this.componentId + '-unit-type';
		}
		if ( null !== this.fieldName ) {
			this.unitTypeSelect.name = `${ this.fieldName }[unit-type]`;
		}

		this.unitTypeOptions.forEach( option => {
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
		this.hiddenInput.value = this.value;
		return this.hiddenInput;
	}

	/**
	 * A method to set the change event listener for the select element.
	 *
	 * @return {void} - The unit value.
	 */
	afterViewInit() {
		this.select.addEventListener( 'change', () => {
			const value = this.getUnitValue( this.select.value );
			this.unitValueInput.value = value.value;
			this.hiddenInput.value = value.value + value.unit;
			this._onChange( this.hiddenInput.value );
			this.unitTypeSelect.value = value.unit;
		} );
	}

	/**
	 * A method to get the unit value.
	 *
	 * @param {string} value - The value to get the unit value for.
	 * @return {Object} - The unit value.
	 */
	getUnitValue( value ) {
		const defaultValue = { value: 0, unit: 'px' };
		if ( ! value ) {
			return defaultValue;
		}
		const unitType = value.match( /^([\d.]+)(px|em|%)?$/ )[ 2 ] || 'px';

		if ( ! unitType ) {
			return defaultValue;
		}

		return {
			value: parseInt( value ),
			unit: unitType
		};
	}

	/**
	 * A method to set the change event listener for the select element.
	 *
	 * @param {Function} callback - The callback function to call when the select element is changed.
	 * @return {void}
	 */
	set onChange( callback ) {
		if ( 'function' !== typeof callback ) {
			throw new TypeError( `Expected a function, but received ${ typeof callback }` );
		}

		this._onChange = callback;
	}

	/**
	 * A method to set dynamically the default value for the typography component.
	 *
	 * @param {string} value - The value to set dynamically the default value for.
	 * @return {void}
	 */
	set typographyDefaultValue( value ) {
		this._defaultValue = value;
	}
}
