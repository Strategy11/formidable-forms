export class frmWebComponent extends HTMLElement {
	constructor() {
		if ( new.target === frmWebComponent ) {
			throw new Error( 'frmWebComponent is an abstract class and cannot be instantiated directly' );
		}
		super();

		if ( this.useShadowDom() ) {
			this.attachShadow({ mode: 'open' });
		}
	}

	initOptions() {
		this.fieldName = this.getAttribute( 'name' ) || null;
		this.defaultValue = this.getAttribute( 'value' ) || null;
		this.componentId = this.getAttribute( 'id' ) || null;
	}

	getLabelText() {
		if ( this._labelText ) {
			return this._labelText;
		}

		const label = this.querySelector( 'span.frm-component-label' );
		if ( null === label ) {
			return null;
		}

		return label.innerText;
	}

	useShadowDom() {
		return 'false' !== this.getAttribute( 'data-shadow-dom' );
	}

	/*
	* Load the component style.
	* @return string
	*/
	loadStyle() {
		const style = document.createElement( 'style' );
		this.componentStyle = this.componentStyle.replace( '--frm-plugin-url', frmGlobal?.url || '' );
		style.textContent += this.componentStyle;
		return style;
	}

	getWrapper() {
		return this.useShadowDom() ? this.shadowRoot : this;
	}

	/*
	* Render the component inside the shadow root.
	* @return void
	*/
	render() {
		const view = this.initView();
		if ( ! view ) {
			return;
		}

		const wrapper = this.getWrapper();
        wrapper.innerHTML = '';

		view.classList.add( 'frm-component' );
		wrapper.append( ...this.getViewItems( view ) );

		this.addLabelToView( view );

		this.whenElementBecomesVisible().then( () => this.afterViewInit( this ) );
	}

	addLabelToView( view ) {
		const labelText = this.getLabelText();
		if ( null === labelText || null === view ) {
			return;
		}

		const label = document.createElement( 'span' );
		label.classList.add( 'frm-component-label' );
		label.textContent = labelText;

		view.prepend( label );
	}

	getViewItems( view ) {
		return [ this.loadStyle(), view ].filter( item => item !== null );
	}

	/**
	 * Waits for the element to become visible in the viewport.
	 *
	 * @return {Promise} - A promise that resolves when the element is visible.
	 */
	whenElementBecomesVisible() {
		// eslint-disable-next-line compat/compat
		return new Promise( resolve => {
			// eslint-disable-next-line compat/compat
			if ( 'undefined' === typeof window.IntersectionObserver ) {
				requestAnimationFrame( () => resolve() );
				return;
			}

			// eslint-disable-next-line compat/compat
			const observer = new IntersectionObserver( entries => {
				entries.forEach( entry => {
					// The element is in viewport and its visibility is greater than 0.
					if ( entry.isIntersecting && entry.intersectionRatio > 0 ) {
						observer.disconnect();
						requestAnimationFrame( () => resolve() );
					}
				} );
			}, { threshold: 0.1 } );

			const element = this.useShadowDom() ? this.shadowRoot.host : this;

			if ( element ) {
				observer.observe( this );
			}
		});
	}

	set frmLabel( text ) {
		this._labelText = text;
	}

	/**
	 * After the view is initialized and the element/wrapper is visible in the viewport.
	 *
	 * @param {Element} wrapper - The wrapper element.
	 */
	afterViewInit( wrapper ) {
		// Override in child class.
	}

	/**
	 * Constructs the view in the DOM.
	 * return {Element} - The wrapper element.
	 */
	initView() {
		// Override in child class.
	}

	/*
	* Called by browser when the component is rendered to the DOM.
	* @return void
	*/
	connectedCallback() {
		this.initOptions();
		this.render();
	}

	/*
	* Called by browser when the component is removed from the DOM.
	* @return void
	*/
	disconnectedCallback() {}
}
