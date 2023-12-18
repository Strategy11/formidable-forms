export class FrmCounter {

	/**
	 * Init FrmCounter
	 *
	 * @param {Element} element
	 * @param {object} options
	 * @param {integer} options.timetoFinish - Max time in mileseconds for counter to complete the animation.
	 */
	constructor( element, options ) {
		if ( ! element instanceof Element || ! element.dataset.counter ) {
			return;
		}

		this.template = 'default';
		if ( element.dataset.type ) {
			this.template = element.dataset.type;
		}

		this.element         = element;
		this.value           = parseInt( element.dataset.counter, 10 );
		this.activeCounter   = 0;
		this.locale          = element.dataset.locale ? element.dataset.locale.replace( '_', '-' ) : 'en-US';
		this.timeoutInterval = 50;
		this.timetoFinish    = 'undefined' !== typeof options && 'undefined' !== typeof options.timetoFinish ? Math.ceil( options.timetoFinish / this.timeoutInterval ) : Math.ceil( 1400 / this.timeoutInterval );
		this.valueStep       = this.value / this.timetoFinish;

		if ( 0 === this.value ) {
			return;
		}

		this.animate();
	}

	formatNumber( number ) {
		if ( 'currency' === this.template ) {
			return number.toLocaleString( this.locale, { minimumFractionDigits: 2 });
		}
		return number;
	}

	animate() {
		if ( Math.round( this.activeCounter ) < this.value ) {
			this.activeCounter += this.valueStep;
			this.element.innerText = this.formatNumber( Math.round( this.activeCounter ) );
			setTimeout( this.animate.bind( this ), this.timeoutInterval );
		} else {
			this.element.innerText = this.formatNumber( this.value );
		}

	}

}