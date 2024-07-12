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
		if ( 0 === document.querySelectorAll( '.frm-slider-component' ).length ) {
			return;
		}

		this.eventsChange = [];
		this.elements = document.querySelectorAll( '.frm-slider-component' );
		this.initOptions();
		this.init();
	}

	initOptions() {
		this.options = [];
		this.elements.forEach( ( element, index ) => {
			const parentWrapper = element.classList.contains( 'frm-has-multiple-values' ) ? element.closest( '.frm-style-component' ) : element;
			this.options.push({
				dragging: false,
				startX: 0,
				translateX: 0,
				maxValue: parseInt( element.dataset.maxValue, 10 ),
				element: element,
				index: index,
				value: 0,
				dependendUpdater: parentWrapper.classList.contains( 'frm-style-dependend-updater-component' ) ? new frmStyleDependendUpdaterComponent( parentWrapper ) : null
			});
		});
	}

	init() {
		this.initSlidersPosition();
		this.initDraggable();
	}

	initDraggable() {
		this.elements.forEach( ( element, index ) => {
			this.eventsChange[ index ] = new Event( 'change', { 
				'bubbles': true,
				'cancelable': true
			} );
			const draggableBullet = element.querySelector( '.frm-slider-bullet' );

			draggableBullet.addEventListener( 'mousedown', (event) => {
				this.enableDragging( event, index );
			});

			draggableBullet.addEventListener( 'mousemove', ( event ) => {
				this.moveTracker( event, index );
			});

			draggableBullet.addEventListener( 'mouseup', () => {
				this.disableDragging( index );
			});

			draggableBullet.addEventListener( 'mouseleave', () => {
				this.disableDragging( index );
			});
		});
	}

	initSlidersPosition() {
		const accordionitems = document.querySelectorAll( '#frm_style_sidebar .accordion-section h3' );

		if ( null !== document.querySelector( '.frm-quick-settings' ) ) {
			this.initSlidersWidth( document.querySelector( '.frm-quick-settings' ) );
		}

		accordionitems.forEach( ( item, index ) => {
			if ( 0 === index ) {
				this.initSlidersWidth( item.closest( '.accordion-section' ) );
			}
			item.addEventListener( 'click', ( event ) => {
				this.initSlidersWidth( event.target.closest( '.accordion-section' ) );
			});
		});
	}

	initSlidersWidth( section ) {
		const sliders = section.querySelectorAll( '.frm-slider-component' );
		sliders.forEach( ( slider ) => {
			setTimeout( () => {
				this.initSliderWidth( slider );
			}, 100 );
		});
	}

	initSliderWidth( slider ) {
		const index       = this.getSliderIndex( slider );
		const sliderWidth = slider.querySelector( '.frm-slider' ).offsetWidth - 14;
		const value       = parseInt( slider.querySelector( '.frm-slider-value input[type="text"]' ).value, 10 );
		const unit        = slider.querySelector( 'select' ).value;
		const deltaX      = '%' === unit ? Math.round( sliderWidth * value / 100 ) : Math.ceil( ( value / this.options[ index ].maxValue ) * sliderWidth );

		slider.querySelector( '.frm-slider-active-track' ).style.width = `${deltaX}px`;
		this.options[ index ].translateX = deltaX;
		this.options[ index ].value = value + unit;
	}

	getSliderIndex( slider ) {
		return this.options.filter( ( option ) => {
			return option.element === slider;
		})[0].index;
	};

	moveTracker( event, index ) {
		if ( ! this.options[ index ].dragging ) {
			return;
		}
		const element     = this.elements[ index ];
		const deltaX      = event.clientX - this.options[ index ].startX;
		const sliderWidth = element.querySelector( '.frm-slider' ).offsetWidth;

		if ( deltaX + 12 >= sliderWidth ) {
			return;
		}
		const unit     = element.querySelector( 'select' ).value;
		const maxValue = '%' === unit ? 100 : this.options[ index ].maxValue;
		const value    = this.calculateValue( sliderWidth, deltaX, maxValue );

		element.querySelector( '.frm-slider-value input[type="text"]' ).value = value;
		element.querySelector( '.frm-slider-active-track' ).style.width = `${deltaX}px`;
		this.options[ index ].translateX = deltaX;
		this.options[ index ].value = value + unit;
		this.options[ index ].fullValue = this.updateValue( element, this.options[ index ].value );
	}

	enableDragging( event, index ) {
		this.options[ index ].dragging = true;
		this.options[ index ].startX = event.clientX - this.options[ index ].translateX;
	}

	disableDragging( index ) {
		if ( false === this.options[ index ].dragging ) { return; }

		if ( null === this.options[ index ].dependendUpdater ) {
			const input = this.elements[ index ].classList.contains( 'frm-has-multiple-values' ) ? this.elements[ index ].closest('.frm-style-component').querySelector( 'input[type="hidden"]' ) : this.elements[ index ].querySelector( '.frm-slider-value input[type="hidden"]' );
			input.dispatchEvent( this.eventsChange[ index ] );
		} else {
			this.options[ index ].dependendUpdater.updateAllDependendElements( this.options[ index ].fullValue );
		}
		this.options[ index ].dragging = false;
	}

	calculateValue( width, deltaX, maxValue ) {
		const value = Math.ceil( ( ( deltaX + 14 ) / width ) * maxValue );
		if ( value < 0 ) {
			return 0;
		}
		return value > maxValue ? maxValue : value;
	}

	updateValue( element, value ) {
		if ( element.classList.contains( 'frm-has-multiple-values' ) ) {
			const input = element.closest( '.frm-style-component' ).querySelector( 'input[type="hidden"]' );
			const inputValue   = input.value.split( ' ' );
			const type       = element.dataset.type;

			if ( 'vertical' === type ) {
				inputValue[0] = value;
			} else {
				inputValue[1] = value;
			}
			const newValue = inputValue.join( ' ' );
			input.value = newValue;
			return newValue;
		}

		element.querySelector( '.frm-slider-value input[type="hidden"]' ).value = value;
		return value;

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

export class frmStyleDependendUpdaterComponent {

	constructor( component ) {
		this.component = component;
		this.data = {
			propagateInputs: this.initPropagationList( JSON.parse( this.component.dataset.willChange ) ),
			changeEvent: new Event( 'change', { bubbles: true } )
		};
	}

	initPropagationList( inputNames ) {
		const list = [];
		inputNames.forEach( ( name ) => {
			const input = document.querySelector( `input[name="${name}"]` );
			if ( null !== input ) {
				list.push( input );
			}
		});
		return list;
	}

	updateAllDependendElements( value ) {
		this.data.propagateInputs.forEach( ( input ) => {
			input.value = value;
		});
		this.data.propagateInputs[0].dispatchEvent( this.data.changeEvent );
	}
}

export class frmStyleOptions {

	constructor() {
		this.init();
		this.initHover();
	}

	init() {
		new frmRadioStyleComponent();
		new frmSliderStyleComponent();
		new frmTabsStyleComponent();

		this.initColorPickerDependendUpdaterComponents();
	}

	initColorPickerDependendUpdaterComponents() {
		const components = document.querySelectorAll( '.frm-style-dependend-updater-component.frm-colorpicker' );
		const elements   = [];

		components.forEach( ( component ) => {
			const element = component.querySelector( 'input.hex' );
			const id      = 'undefined' !== typeof element ? element.getAttribute( 'id' ) : null;

			if ( null !== id ) {
				elements.push({
					id: id,
					dependendUpdaterClass: new frmStyleDependendUpdaterComponent( component, 'colorpicker' )
				});
			}
		});

		wp.hooks.addAction( 'frm_style_options_color_change', 'formidable', ( { event, value } ) => {
			const id = event.target.getAttribute( 'id' );
			elements.forEach( ( element ) => {
				if ( element.id === id ) {
					element.dependendUpdaterClass.updateAllDependendElements( value );
				}
			});
		});
	}

	initHover() {
		const settingsWrapper = document.querySelector( '.frm-right-panel .styling_settings .accordion-container' );
		if ( null === settingsWrapper ) {
			return;
		}
		const hoverElement = document.createElement( 'div' );
		hoverElement.classList.add( 'frm_hidden' );
		hoverElement.classList.add( 'frm-style-settings-hover' );
		settingsWrapper.appendChild( hoverElement );

		const styleOptionsMenu = settingsWrapper.querySelector( ':scope > ul' );

		styleOptionsMenu.querySelectorAll( ':scope > li' ).forEach( ( item ) => {
			item.querySelector('h3').addEventListener( 'mouseover', ( event ) => {
				hoverElement.style.transform = `translateY(${event.target.closest('li').offsetTop}px)`;
				hoverElement.classList.add( 'frm-animating' );
				hoverElement.classList.remove( 'frm_hidden' );
				setTimeout( () => { hoverElement.classList.remove( 'frm-animating' ); }, 250 );
			});
		});

		const accordionitems = document.querySelectorAll( '#frm_style_sidebar .accordion-section h3' );
		accordionitems.forEach( ( item ) => {
			item.addEventListener( 'click', () => {
				hoverElement.classList.add( 'frm_hidden' );
			});
		});
	}
}

new frmStyleOptions();
