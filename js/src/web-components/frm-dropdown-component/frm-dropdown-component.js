import { frmWebComponent } from '../frm-web-component';
import style from './frm-dropdown-component.css';

export class frmDropdownComponent extends frmWebComponent {
	constructor() {
		super();
		this.select         = document.createElement( 'select' );
		this.componentStyle = style;
		this._onChange      = null;
	}

	initView() {
		this.wrapper = document.createElement( 'div' );
		this.wrapper.classList.add( 'frm-dropdown-component' );
		this.wrapper.appendChild( this.getSelect() );
		return this.wrapper;
	}

	getSelect() {
		this.select.id = this.componentId;
		this.select.name = this.fieldName;
		return this.select;
	}

	useShadowDom() {
		return true;
	}

	initSelectOptions() {
		const optionsNodes = this.querySelectorAll( 'option' );
		optionsNodes.forEach( option => {
			const opt = document.createElement( 'option' );
			opt.value = option.value;
			opt.textContent = option.textContent;
			option.remove();
			this.select.appendChild( opt );
		});
	}

	afterViewInit() {
		this.initSelectOptions();
		this.select.addEventListener( 'change', () => {
			this._onChange( this.select.value );
		});
	}

	set addOptions( options ) {
		options.forEach( option => {
			const opt = document.createElement( 'option' );
			opt.value = option.value;
			opt.textContent = option.label;
			this.select.appendChild( opt );
		});
	}

	set onChange( callback ) {
		if ( 'function' !== typeof callback ) {
			throw new Error( 'Callback must be a function' );
		}

		this._onChange = callback;
	}
}