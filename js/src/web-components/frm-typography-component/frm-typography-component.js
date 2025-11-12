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
				value: '26.25px',
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
		this.unitTypeOptions = ['px', 'em', '%'];
	}

	initView() {
		this.wrapper   = document.createElement( 'div' );
		this.container = document.createElement( 'div' );

		this.wrapper.classList.add( 'frm-typography-component', 'frm-typography' );
		this.container.classList.add( 'frm-typography-container' );

		this.container.append(
			this.getSelect(),
			this.getUnitValueWrapper(),
			this.getHiddenInput()
		);

		this.wrapper.appendChild( this.container );
		return this.wrapper;
	}

	getSelect() {
		this.select = document.createElement( 'select' );
		if ( null !== this.componentId ) {
			this.select.id = this.componentId;
		}
		if ( null !== this.fieldName ) {
			this.select.name = `${this.fieldName}[size]`;
		}
		this.getDefaultOptions( this.select );
		return this.select;
	}

	getDefaultOptions( select ) {
		this.defaultOptions.forEach( option => {
			const opt = document.createElement( 'option' );
			opt.value = option.value;
			opt.textContent = option.label;
			select.appendChild( opt );
		});
	}

	getUnitValueWrapper() {
		this.unitValueWrapper = document.createElement( 'div' );
		this.unitValueWrapper.classList.add( 'frm-unit-value' );
		this.unitValueWrapper.appendChild( this.getUnitValueInput() );
		this.unitValueWrapper.appendChild( this.getUnitTypeSelect() );
		return this.unitValueWrapper;
	}

	getUnitValueInput() {
		this.unitValueInput = document.createElement( 'input' );
		if ( null !== this.componentId ) {
			this.unitValueInput.id = this.componentId + '-unit';
		}
		if ( null !== this.fieldName ) {
			this.unitValueInput.name = `${this.fieldName}[unit]`;
		}
		this.unitValueInput.type = 'text';
		this.unitValueInput.value = `${parseInt( this.defaultOptions.find( option => option.value === this.value )?.value ) || 21}`;
		return this.unitValueInput;
	}

	getUnitTypeSelect() {
		this.unitTypeSelect = document.createElement( 'select' );

		if ( null !== this.componentId ) {
			this.unitTypeSelect.id = this.componentId + '-unit-type';
		}
		if ( null !== this.fieldName ) {
			this.unitTypeSelect.name = `${this.fieldName}[unit-type]`;
		}

		this.unitTypeOptions.forEach( option => {
			const opt = document.createElement( 'option' );
			opt.value = option;
			opt.textContent = option;
			this.unitTypeSelect.appendChild( opt );
		});
		return this.unitTypeSelect;
	}

	getHiddenInput() {
		this.hiddenInput = document.createElement( 'input' );
		this.hiddenInput.type = 'hidden';
		if ( null !== this.fieldName ) {
			this.hiddenInput.name = `${this.fieldName}[value]`;
		}
		this.hiddenInput.value = this.value;
		return this.hiddenInput;
	}

	afterViewInit() {
		this.select.addEventListener( 'change', () => {
			const value = this.getUnitValue( this.select.value );
			this.unitValueInput.value = value.value;
			this.hiddenInput.value = value.value + value.unit;
			this.unitTypeSelect.value = value.unit;
		});
		this.hiddenInput.addEventListener( 'change', () => {
			this._onChange( this.hiddenInput.value );
		});
	}

	getUnitValue( value ) {
		const unitType = value.match( /^([\d.]+)(px|em|%)?$/ )[2] || 'px';
		return {
			value: parseInt( value ),
			unit: unitType
		};
	}

	set onChange( callback ) {
		if ( 'function' !== typeof callback ) {
			throw new Error( 'Callback must be a function' );
		}

		this._onChange = callback;
	}
}