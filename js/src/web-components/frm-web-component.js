export class frmWebComponent extends HTMLElement {
	constructor() {
		if ( new.target === frmWebComponent ) {
			throw new Error('frmWebComponent is an abstract class and cannot be instantiated directly');
		}
		super();

		this.initOptions();

		if ( this.useShadowDom ) {
			this.attachShadow({ mode: 'open' });
		}
	}

	initOptions() {
		this.useShadowDom = 'false' === this.getAttribute( 'data-shadow-dom' ) ? false : true;
	}

	/*
	* Load the component style.
	* @return string
	*/
	loadStyle() {
		const style = document.createElement( 'style' );
		style.textContent = this.componentStyle;
		return style;
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
		const wrapper = this.useShadowDom ? this.shadowRoot : this;
		wrapper.innerHTML = '';
		wrapper.appendChild( this.loadStyle() );
		wrapper.appendChild( view );

		this.whenElementBecomesVisible().then( () => {
			this.afterViewInit( this );
		});
	}

	/**
	 * Waits for the element to become visible in the viewport.
	 * @return {Promise} - A promise that resolves when the element is visible.
	 */
	whenElementBecomesVisible() {
		// eslint-disable-next-line compat/compat
		return new Promise( ( resolve ) => {
			// eslint-disable-next-line compat/compat
			if ( 'undefined' === typeof window.IntersectionObserver ) {
				requestAnimationFrame( () => resolve() );
				return;
			}

			// eslint-disable-next-line compat/compat
			const observer = new IntersectionObserver( ( entries ) => {
				entries.forEach( entry => {
					// The element is in viewport and its visibility is greater than 0.
					if ( entry.isIntersecting && entry.intersectionRatio > 0 ) {
						observer.disconnect();
						requestAnimationFrame( () => resolve() );
					}
				});
			}, { threshold: 0.1 } );

			const element = this.useShadowDom ? this.shadowRoot : this;

			observer.observe( element );
		});
	}

	/**
	 * After the view is initialized and the element/wrapper is visible in the viewport.
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
		this.render();
	}

	/*
	* Called by browser when the component is removed from the DOM.
	* @return void
	*/
	disconnectedCallback() {}
}
