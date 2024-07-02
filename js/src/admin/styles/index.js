import { frmTabsNavigator } from '../../components/class-tabs-navigator';

export class frmRadioStyleComponent {

	constructor() {
		if ( 0 === document.querySelectorAll( '.frm-style-component.frm-radio-component' ).length ) {
			return;
		}

		this.elements = document.querySelectorAll( '.frm-style-component.frm-radio-component' );
		this.init();
	}

	init() {
		this.elements.forEach( ( element ) => {
			this.initOnRadioChange( element );
		});
	}

	initOnRadioChange( wrapper ) {
		wrapper.querySelectorAll( 'input[type="radio"]' ).forEach( ( radio ) => {
			radio.addEventListener( 'change', ( event ) => {
				this.onRadioChange( event.target.closest( '.frm-style-component.frm-radio-component' ) );
			});
		});
	}

	onRadioChange( wrapper ) {
		const activeItem = wrapper.querySelector( 'input[type="radio"]:checked + label' );
		this.moveTracker( activeItem, wrapper );
	}

	getRadioIndex( radio ) {
		const radioButtons = Array.from( wrapper.querySelectorAll( 'input[type="radio"]' ) );
		return radioButtons.indexOf( radio );
	}

	moveTracker( activeItem, wrapper ) {
		const offset  = activeItem.offsetLeft;
		const width   = activeItem.offsetWidth;
		const tracker = wrapper.querySelector( '.frm-radio-active-tracker' );

		tracker.style.left = 0;
		tracker.style.width = width;
		tracker.style.transform = `translateX(${ offset }px)`;
	}
}

export class frmSliderStyleComponent {
	
	constructor() {
		if ( 0 === document.querySelectorAll( '.frm-style-component.frm-slider-component' ).length ) {
			return;
		}

		this.eventChange = new Event( 'change' );
		this.elements = document.querySelectorAll( '.frm-style-component.frm-slider-component' );
		this.initOptions();
		this.init();
	}

	initOptions() {
		this.options = [];
		this.elements.forEach( ( element ) => {
			this.options.push({
				dragging: false,
				startX: 0,
				translateX: 0,
				maxValue: parseInt( element.dataset.maxValue, 10 ),
			});
		});
	}

	init() {
		this.elements.forEach( ( element, index ) => {
			const draggableBullet = element.querySelector( '.frm-slider-bullet' );

			draggableBullet.addEventListener( 'mousedown', (event) => {
				this.enableDragging( event, index );
			});

			document.addEventListener( 'mousemove', ( event ) => {
				this.moveTracker( event, index );
			});

			document.addEventListener( 'mouseup', () => {
				this.disableDragging( index );
			});

			draggableBullet.addEventListener( 'mouseleave', () => {
				this.disableDragging( index );
			});
		});
	}

	moveTracker( event, index ) {
		if ( ! this.options[ index ].dragging ) {
			return;
		}
		const deltaX = event.clientX - this.options[ index ].startX;
		const sliderWidth = this.elements[ index ].querySelector( '.frm-slider' ).offsetWidth;
		if ( deltaX + 12 >= sliderWidth ) {
			return;
		}

		const value = this.calculateValue( sliderWidth, deltaX, this.options[ index ].maxValue );
		const unit  = this.elements[ index ].querySelector( 'select' ).value;

		this.elements[ index ].querySelector( '.frm-slider-value input[type="text"]' ).value = value;
		this.elements[ index ].querySelector( '.frm-slider-value input[type="hidden"]' ).value = value + unit;
		this.elements[ index ].querySelector( '.frm-slider-active-track' ).style.width = `${deltaX}px`;
		this.options[ index ].translateX = deltaX;
	}

	enableDragging( event, index ) {
		this.options[ index ].dragging = true;
		this.options[ index ].startX = event.clientX - this.options[ index ].translateX;
	}

	disableDragging( index ) {
		if ( false === this.options[ index ].dragging ) { return; }
		console.log('dispatch');
		this.elements[ index ].querySelector( '.frm-slider-value input[type="hidden"]' ).dispatchEvent( this.eventChange );
		this.options[ index ].dragging = false;
	}

	calculateValue( width, deltaX, maxValue ) {
		const value = Math.ceil( ( ( deltaX + 14 ) / width ) * maxValue );
		if ( value < 0 ) {
			return 0;
		}
		return value > maxValue ? maxValue : value;
	}

}

export class frmTabsStyleComponent {

	constructor() {
		if ( 0 === document.querySelectorAll( '.frm-style-tabs-wrapper' ).length ) {
			return;
		}

		this.elements = document.querySelectorAll( '.frm-style-tabs-wrapper' );
		this.init();
	}

	init() {
		this.elements.forEach( ( element ) => {
			new frmTabsNavigator( element );
		});
	}

	initOnTabClick( wrapper ) {
		this.initActiveBackgroundWidth( wrapper );
		wrapper.querySelectorAll( '.frm-tab-item' ).forEach( ( tab ) => {
			tab.addEventListener( 'click', ( event ) => {
				this.onTabClick( event.target.closest( '.frm-tabs-wrapper' ) );
			});
		});
	}
}

new frmRadioStyleComponent();
new frmSliderStyleComponent();
new frmTabsStyleComponent();
