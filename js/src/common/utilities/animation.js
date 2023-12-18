export class FrmAnimate {

	/**
	 * Construct FrmAnimate
	 *
	 * @param {Element|Element[]} elements
	 * @param {'default'|'cascade'|'cascade-3d'} type - The animation type: default | cascade | cascade-3d
	 *
	 */
	constructor( elements, type = 'default' ) {
		this.elements    = elements;
		this.cssFilePath = 'admin/animations.css';
		this.type        = type;

		this.initOnceInAllInstances();
		this.prepareElements();
	}

	/**
	 * Init animation - fadeIn.
	 * Requires this.type = 'default';
	 * ex: new FrmAnimate( elements ).fadeIn();
	 */
	fadeIn() {
		this.applyStyleToElements( element => {
			element.classList.add( 'frm-fadein-up' );

			element.addEventListener( 'animationend', () => {
				this.resetOpacity();
				element.classList.remove( 'frm-fadein-up' );
			}, { once: true });
		});
	}

	/**
	 * Init animation - cascadeFadeIn.
	 * Requires this.type = 'cascade'|'cascade-3d';
	 * ex: new FrmAnimate( elements, 'cascade' ).cascadeFadeIn();
	 *     new FrmAnimate( elements, 'cascade-3d' ).cascadeFadeIn();
	 *
	 * @param {float} delay - The transition delay value.
	 *
	 */
	cascadeFadeIn( delay = 0.03 ) {
		setTimeout( () => {
			this.applyStyleToElements( ( element, index ) => {
				element.classList.remove( 'frm-animate' );
				element.style.transitionDelay = ( index + 1 ) * delay + 's';
			});
		}, 200 );
	}

	initOnceInAllInstances() {
		if ( true === FrmAnimate.init ) {
			return;
		}
		FrmAnimate.init = true;
		this.loadCssFile();
	}

	getCssFileUrl() {
		if ( ! window.frmGlobal ) {
			return '';
		}
		return window.frmGlobal.url + '/css/' + this.cssFilePath  ;
	}

	loadCssFile() {
		const style = document.createElement( 'link' );
		style.href  = this.getCssFileUrl();
		style.rel   = 'stylesheet';
		document.getElementsByTagName( 'head' )[0].appendChild( style );
	}

	prepareElements() {
		this.applyStyleToElements( element => {
			if ( 'default' === this.type ) {
				element.style.opacity = '0.0';
			}
			if ( 'cascade' === this.type ) {
				element.classList.add( 'frm-init-cascade-animation' );
			}
			if ( 'cascade-3d' === this.type ) {
				element.classList.add( 'frm-init-fadein-3d' );
			}
			element.classList.add( 'frm-animate' );
		});
	}

	resetOpacity() {
		this.applyStyleToElements( element => element.style.opacity = '1.0' );
	}

	applyStyleToElements( callback ) {
		if ( this.elements instanceof Element ) {
			callback( this.elements, 0 );
			return;
		}
		if ( 0 < this.elements.length ) {
			this.elements.forEach( ( element, index ) => callback( element, index ) );
		}
	}
}
