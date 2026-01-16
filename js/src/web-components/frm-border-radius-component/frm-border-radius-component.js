import { frmWebComponent } from '../frm-web-component';
import { __ } from '@wordpress/i18n';
import style from './frm-border-radius-component.css';

export class frmBorderRadiusComponent extends frmWebComponent {

	#onChange = () => {};
	#unitTypeOptions = [ 'px', 'em', '%' ];
	#value = '0px';
	#defaultValue = '0px';
	#usesMultipleValues = false;
	constructor() {
		super();
		this.componentStyle = style;
	}

	initView() {
		this.wrapper = document.createElement( 'div' );
		this.container = document.createElement( 'div' );

		this.wrapper.classList.add( 'frm-border-radius-component' );
		this.container.classList.add( 'frm-border-radius-container' );

		this.container.append(
			this.getInputWrapper(),
			this.getButton(),
			this.getBorderIndividualInputsWrapper()
		);

		this.wrapper.append( this.container );

		return this.wrapper;
	}

	parseDefaultValues() {
		if ( ! this.#defaultValue ) {
			return {
				top: { value: 0, unit: 'px' },
				bottom: { value: 0, unit: 'px' },
				left: { value: 0, unit: 'px' },
				right: { value: 0, unit: 'px' }
			};
		}

		const parts = this.#defaultValue.split( ' ' );
		return {
			top: this.parseValueUnit( parts[ 0 ] || '0px' ),
			bottom: this.parseValueUnit( parts[ 2 ] || parts[ 0 ] || '0px' ),
			left: this.parseValueUnit( parts[ 3 ] || parts[ 1 ] || parts[ 0 ] || '0px' ),
			right: this.parseValueUnit( parts[ 1 ] || parts[ 0 ] || '0px' )
		};
	}

	parseValueUnit( valueStr ) {
		const match = valueStr.match( /^(\d+)(px|em|%)?$/ );
		if ( ! match ) {
			return { value: 0, unit: 'px' };
		}
		return {
			value: parseInt( match[ 1 ], 10 ),
			unit: match[ 2 ] || 'px'
		};
	}

	getInputWrapper() {
		this.inputWrapper = document.createElement( 'div' );
		this.inputWrapper.classList.add( 'frm-input-wrapper' );
		this.inputWrapper.append(
			this.getInputValue(),
			this.getInputUnit(),
			this.getHiddenInput()
		);

		return this.inputWrapper;
	}

	getHiddenInput() {
		this.hiddenInput = document.createElement( 'input' );
		this.hiddenInput.type = 'hidden';
		this.hiddenInput.value = this.#value;

		if ( this.fieldName ) {
			this.hiddenInput.name = this.fieldName;
		}

		return this.hiddenInput;
	}

	getInputValue() {
		this.inputValue = document.createElement( 'input' );
		this.inputValue.type = 'text';
		this.inputValue.classList.add( 'frm-input-value' );

		if ( ! this.#usesMultipleValues ) {
			this.inputValue.value = parseInt( this.#defaultValue ) || 0;
		}

		this.inputValue.addEventListener( 'change', () => {
			const value = this.inputValue.value + this.inputUnit.value;
			this.hiddenInput.value = value;
			this.borderInputBottom.value = this.inputValue.value;
			this.borderInputTop.value = this.inputValue.value;
			this.borderInputLeft.value = this.inputValue.value;
			this.borderInputRight.value = this.inputValue.value;
			this.updateValue( value );
		} );
		return this.inputValue;
	}

	getInputUnit() {
		this.inputUnit = document.createElement( 'select' );
		this.inputUnit.classList.add( 'frm-input-unit' );
		this.#unitTypeOptions.forEach( option => {
			const opt = document.createElement( 'option' );
			opt.value = option;
			opt.textContent = option;
			this.inputUnit.append( opt );
		} );

		this.inputUnit.addEventListener( 'change', () => {
			this.hiddenInput.value = this.inputValue.value + this.inputUnit.value;
		} );

		return this.inputUnit;
	}

	getBorderIndividualInputsWrapper() {
		this.borderIndividualInputsWrapper = document.createElement( 'div' );
		this.borderIndividualInputsWrapper.classList.add( 'frm-border-individual-inputs-wrapper' );

		if ( ! this.#usesMultipleValues ) {
			this.borderIndividualInputsWrapper.classList.add( 'frm_hidden' );
		}

		this.borderIndividualInputsWrapper.append(
			this.getBorderInputTop(),
			this.getBorderInputRight(),
			this.getBorderInputLeft(),
			this.getBorderInputBottom()
		);

		return this.borderIndividualInputsWrapper;
	}

	getBorderInputTop() {
		const defaultValues = this.parseDefaultValues();
		const span = document.createElement( 'span' );
		span.classList.add( 'frm-border-input-top' );

		this.borderInputTop = document.createElement( 'input' );
		this.borderInputTop.type = 'text';
		this.borderInputTop.value = parseInt( defaultValues.top.value );
		span.append( this.borderInputTop );

		this.borderInputTop.addEventListener( 'change', () => this.buildBorderRadiusIndividualValue() );
		return span;
	}

	getBorderInputBottom() {
		const defaultValues = this.parseDefaultValues();
		const span = document.createElement( 'span' );
		span.classList.add( 'frm-border-input-bottom' );
		this.borderInputBottom = document.createElement( 'input' );
		this.borderInputBottom.type = 'text';
		this.borderInputBottom.value = parseInt( defaultValues.bottom.value );
		span.append( this.borderInputBottom );

		this.borderInputBottom.addEventListener( 'change', () => this.buildBorderRadiusIndividualValue() );
		return span;
	}

	getBorderInputLeft() {
		const defaultValues = this.parseDefaultValues();
		const span = document.createElement( 'span' );
		span.classList.add( 'frm-border-input-left' );
		this.borderInputLeft = document.createElement( 'input' );
		this.borderInputLeft.type = 'text';
		this.borderInputLeft.value = parseInt( defaultValues.left.value );
		span.append( this.borderInputLeft );

		this.borderInputLeft.addEventListener( 'change', () => this.buildBorderRadiusIndividualValue() );

		return span;
	}

	getBorderInputRight() {
		const defaultValues = this.parseDefaultValues();
		const span = document.createElement( 'span' );
		span.classList.add( 'frm-border-input-right' );
		this.borderInputRight = document.createElement( 'input' );
		this.borderInputRight.type = 'text';
		this.borderInputRight.value = parseInt( defaultValues.right.value );
		span.append( this.borderInputRight );

		this.borderInputRight.addEventListener( 'change', () => this.buildBorderRadiusIndividualValue() );

		return span;
	}

	buildBorderRadiusIndividualValue() {
		const unit = this.inputUnit.value;
		const value = `${ this.borderInputTop.value }${ unit } ${ this.borderInputRight.value }${ unit } ${ this.borderInputBottom.value }${ unit } ${ this.borderInputLeft.value }${ unit }`;
		this.updateValue( value );
	}

	updateValue( value ) {
		this.hiddenInput.value = value;

		this.#onChange( value );
	}

	getButton() {
		this.button = document.createElement( 'button' );
		this.button.type = 'button';
		this.button.textContent = __( 'Border Radius', 'formidable' );
		this.button.addEventListener( 'click', () => {
			this.borderIndividualInputsWrapper.classList.toggle( 'frm_hidden' );
		} );

		return this.button;
	}

	set onChange( callback ) {
		if ( 'function' !== typeof callback ) {
			throw new TypeError( `Expected a function, but received ${ typeof callback }` );
		}

		this.#onChange = callback;
	}

	set borderRadiusDefaultValue( value ) {
		this.#defaultValue = value;
		this.#usesMultipleValues = ! value.match( /^(\d+)(px|em|%)?$/ ) && '' !== value;
	}
}
