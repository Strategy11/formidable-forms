export class FrmCounter {

	constructor( element, options ) {
		if ( ! element instanceof Element || null === element.getAttribute( 'data-counter' ) ) {
			return;
		}

		this.template = 'default';
		if ( null !== element.getAttribute( 'data-type' ) ) {
			this.template = element.getAttribute( 'data-type' );
		}

		this.element       = element;
		this.value         = parseInt( element.getAttribute( 'data-counter' ), 10 );
		this.activeCounter = 0;
		this.speed         = 'undefined' !== typeof options && 'undefined' !== typeof options.speed ? options.speed : 270;
		this.valueStep     = Math.ceil( this.value / this.speed );

		this.animate();
	}

	formatNumber( number ) {
		if ( 'currency' === this.template ) {
			return number.toLocaleString( undefined, { minimumFractionDigits: 2 });
		}
		return number;
	}

	animate() {
		if ( this.activeCounter < this.value ) {
			this.activeCounter += this.valueStep;
			this.element.innerText = this.formatNumber( this.activeCounter );
			setTimeout( () => {
				this.animate();
			}, 4 );
		} else {
			this.element.innerText = this.formatNumber( this.value );
		}

	}

}
