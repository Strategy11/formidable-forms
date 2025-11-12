import { frmWebComponent } from '../frm-web-component';
import { __ } from '@wordpress/i18n';
import style from './frm-border-radius-component.css';

export class frmBorderRadiusComponent extends frmWebComponent {
	constructor() {
		super();
		this._onChange       = null;
		this.componentStyle  = style;
		this.unitTypeOptions = ['px', 'em', '%'];
		this.value = '4px';
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

		this.wrapper.appendChild( this.container );

		return this.wrapper;
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
		this.hiddenInput.value = this.value;
		return this.hiddenInput;
	}

	getInputValue() {
		this.inputValue = document.createElement( 'input' );
		this.inputValue.type = 'text';
		this.inputValue.classList.add( 'frm-input-value' );
		this.inputValue.value = parseInt( this.value );

		this.inputValue.addEventListener( 'change', () => {
			const value = this.inputValue.value + this.inputUnit.value;
			this.hiddenInput.value = value;
			this.borderInputBottom.value = this.inputValue.value;
			this.borderInputTop.value = this.inputValue.value;
			this.borderInputLeft.value = this.inputValue.value;
			this.borderInputRight.value = this.inputValue.value;
			this.updateValue( value );
		});
		return this.inputValue;
	}

	getInputUnit() {
		this.inputUnit = document.createElement( 'select' );
		this.inputUnit.classList.add( 'frm-input-unit' );
		this.unitTypeOptions.forEach( option => {
			const opt = document.createElement( 'option' );
			opt.value = option;
			opt.textContent = option;
			this.inputUnit.appendChild( opt );
		});

		this.inputUnit.addEventListener( 'change', () => {
			this.hiddenInput.value = this.inputValue.value + this.inputUnit.value;
		});

		return this.inputUnit;
	}

	getBorderIndividualInputsWrapper() {
		this.borderIndividualInputsWrapper = document.createElement( 'div' );
		this.borderIndividualInputsWrapper.classList.add( 'frm-border-individual-inputs-wrapper', 'frm_hidden' );
		this.borderIndividualInputsWrapper.append(
			this.getBorderInputTop(),
			this.getBorderInputRight(),
			this.getBorderInputLeft(),
			this.getBorderInputBottom()
		);

		return this.borderIndividualInputsWrapper;
	}

	getBorderInputTop() {
		const span = document.createElement( 'span' );
		span.classList.add( 'frm-border-input-top' );
		this.borderInputTop = document.createElement( 'input' );
		this.borderInputTop.type = 'text';
		this.borderInputTop.value = parseInt( this.value );
		span.appendChild( this.borderInputTop );

		this.borderInputTop.addEventListener( 'change', () => {
			this.hiddenInput.value = this.buildBorderRadiusIndividualValue();
		} );
		return span;
	}

	getBorderInputBottom() {
		const span = document.createElement( 'span' );
		span.classList.add( 'frm-border-input-bottom' );
		this.borderInputBottom = document.createElement( 'input' );
		this.borderInputBottom.type = 'text';
		this.borderInputBottom.value = parseInt( this.value );
		span.appendChild( this.borderInputBottom );

		this.borderInputBottom.addEventListener( 'change', () => {
			this.hiddenInput.value = this.buildBorderRadiusIndividualValue();
		} );
		return span;
	}

	getBorderInputLeft() {
		const span = document.createElement( 'span' );
		span.classList.add( 'frm-border-input-left' );
		this.borderInputLeft = document.createElement( 'input' );
		this.borderInputLeft.type = 'text';
		this.borderInputLeft.value = parseInt( this.value );
		span.appendChild( this.borderInputLeft );

		this.borderInputLeft.addEventListener( 'change', () => {
			this.hiddenInput.value = this.buildBorderRadiusIndividualValue();
		} );
		return span;
	}

	getBorderInputRight() {
		const span = document.createElement( 'span' );
		span.classList.add( 'frm-border-input-right' );
		this.borderInputRight = document.createElement( 'input' );
		this.borderInputRight.type = 'text';
		this.borderInputRight.value = parseInt( this.value );
		span.appendChild( this.borderInputRight );

		this.borderInputRight.addEventListener( 'change', () => {
			this.buildBorderRadiusIndividualValue();
		} );

		return span;
	}

	buildBorderRadiusIndividualValue() {
		const unit  = this.inputUnit.value;
		const value = `${this.borderInputTop.value}${unit} ${this.borderInputRight.value}${unit} ${this.borderInputLeft.value}${unit} ${this.borderInputBottom.value}${unit}`;
		this.updateValue( value );
	}

	updateValue( value ) {
		this.hiddenInput.value = value;

		if ( ! this._onChange ) {
			return;
		}

		this._onChange( value );
	}

	getButton() {
		this.button = document.createElement( 'button' );
		this.button.textContent = __( 'Border Radius', 'formidable' );
		this.button.addEventListener( 'click', () => {
			this.borderIndividualInputsWrapper.classList.toggle( 'frm_hidden' );
		});

		return this.button;
	}

	set onChange( callback ) {
		if ( 'function' !== typeof callback ) {
			throw new Error( 'Callback must be a function' );
		}

		this._onChange = callback;
	}

}