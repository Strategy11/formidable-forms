import { frmWebComponent } from '../frm-web-component';
import style from './frm-colorpicker-component.css';

class frmColorpickerLiteComponent extends frmWebComponent {
	constructor() {
		super();
		this.input          = document.createElement( 'input' );
		this.componentStyle = style;
		this._onChange      = null;
	}

	initView() {
		const wrapper = document.createElement( 'div' );
		wrapper.classList.add( 'frm-colorpicker-component', 'frm-colorpicker' );
		wrapper.appendChild( this.getInput() );
		return wrapper;
	}

	getInput() {
		this.input.type = 'text';
		// this.input.classList.add( 'hex' );

		if ( null !== this.fieldName ) {
			this.input.name = this.fieldName;
		}

		if ( null !== this.defaultValue ) {
			this.input.value = this.defaultValue;
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
		const colorPickerOptions = 'function' === typeof this._onChange ? {
			change: ( event, ui ) => this._onChange( event, ui ),
		} : {};

		jQuery( this.input ).wpColorPicker( colorPickerOptions );
	}

	get color() {
		return jQuery( this.input ).wpColorPicker( 'color' );
	}

	set color( value ) {
		this.input.value = value;
	}

	set onChange( callback ) {
		if ( 'function' !== typeof callback ) {
			throw new Error( 'Callback must be a function' );
		}

		this._onChange = callback;
	}
}

// The color picker component that may be a mixin of the color picker pro component.
export const frmColorpickerComponent = window.frmColorpickerProComponent ? window.frmColorpickerProComponent( frmColorpickerLiteComponent ) : frmColorpickerLiteComponent;