export class frmAnimate {
	/**
	 * Construct frmAnimate
	 *
	 * @param {Element|Element[]}                elements The elements to animate.
	 * @param {'default'|'cascade'|'cascade-3d'} type     The animation type: default | cascade | cascade-3d
	 */
	constructor( elements, type = 'default' ) {
		this.elements = elements;
		this.type = type;

		this.prepareElements();
	}

	/**
	 * Init animation - fadeIn.
	 * Requires this.type = 'default';
	 * ex: new frmAnimate( elements ).fadeIn();
	 */
	fadeIn() {
		this.applyStyleToElements( element => {
			element.classList.add( 'frm-fadein-up' );

			element.addEventListener( 'animationend', () => {
				this.resetOpacity();
				element.classList.remove( 'frm-fadein-up' );
			}, { once: true } );
		} );
	}

	/**
	 * Init animation - cascadeFadeIn.
	 * Requires this.type = 'cascade'|'cascade-3d';
	 * ex: new frmAnimate( elements, 'cascade' ).cascadeFadeIn();
	 *     new frmAnimate( elements, 'cascade-3d' ).cascadeFadeIn();
	 *
	 * @param {float} delay The transition delay value.
	 */
	cascadeFadeIn( delay = 0.03 ) {
		setTimeout( () => {
			this.applyStyleToElements( ( element, index ) => {
				element.classList.remove( 'frm-animate' );
				element.style.transitionDelay = ( ( index + 1 ) * delay ) + 's';
			} );
		}, 200 );
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
		} );
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
