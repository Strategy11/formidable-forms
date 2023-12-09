export class FrmCounter {

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
		this.speed           = 'undefined' !== typeof options && 'undefined' !== typeof options.speed ? options.speed : 270;
		this.valueStep       = Math.ceil( this.value / this.speed );
		this.timeoutInterval = this.initTimeoutInterval();

		if ( 0 === this.value ) {
			return;
		}

		this.animate();
	}

	initTimeoutInterval() {
		if ( this.value < 10 ) {
			return 160;
		}
		if ( this.value < 70 ) {
			return 40;
		}
		return 4;
	}

	formatNumber( number ) {
		if ( 'currency' === this.template ) {
			return number.toLocaleString( this.locale, { minimumFractionDigits: 2 });
		}
		return number;
	}

	animate() {
		if ( this.activeCounter < this.value ) {
			this.activeCounter += this.valueStep;
			this.element.innerText = this.formatNumber( this.activeCounter );
			setTimeout( this.animate.bind( this ), this.timeoutInterval );
		} else {
			this.element.innerText = this.formatNumber( this.value );
		}

	}

}
