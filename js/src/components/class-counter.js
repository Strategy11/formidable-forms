export class frmCounter {
	/**
	 * Init frmCounter
	 *
	 * @param {Element} element
	 * @param {Object}  options
	 * @param {integer} options.timetoFinish - Max time in mileseconds for counter to complete the animation.
	 */
	constructor( element, options ) {
		if ( ! ( element instanceof Element ) || ! element.dataset.counter ) {
			return;
		}

		this.template = element.dataset.type || 'default';
		this.element = element;
		this.value = parseInt( element.dataset.counter, 10 );
		this.activeCounter = 0;
		this.locale = element.dataset.locale ? element.dataset.locale.replace( '_', '-' ) : 'en-US';
		this.timeoutInterval = 50;
		this.timeToFinish = 'undefined' !== typeof options && 'undefined' !== typeof options.timetoFinish ? options.timetoFinish : 1400;
		this.valueStep = this.value / Math.ceil( this.timeToFinish / this.timeoutInterval );

		if ( 0 === this.value ) {
			return;
		}

		this.animate();
	}

	formatNumber( number ) {
		if ( 'currency' === this.template ) {
			return number.toLocaleString( this.locale, { minimumFractionDigits: 2 } );
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
