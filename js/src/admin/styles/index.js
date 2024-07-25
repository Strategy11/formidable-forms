import { __ } from '@wordpress/i18n';
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

		const { debounce }        = frmDom.util;
		this.valueChangeDebouncer = debounce( ( index ) => this.triggerValueChange( index ), 25 );

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
			const valueInput      = element.querySelector( '.frm-slider-value input[type="text"]' );

			if ( 'undefined' !== typeof element.dataset.displaySliders ) {
				const sliderGroupItems = this.getSliderGroupItems( element );

				if ( null !== element.querySelector( '.frmsvg' ) ) {
					element.querySelector( '.frmsvg' ).addEventListener( 'click', ( ) => {
						sliderGroupItems.forEach( ( item ) => {
							item.classList.toggle( 'frm_hidden' );
						});
					});
				}
			}

			valueInput.addEventListener( 'change', ( event ) => {
				const unit = element.querySelector( 'select' ).value;

				if ( this.getMaxValue( unit, index ) < parseInt( event.target.value, 10 ) ) {
					return;
				}

				this.initSliderWidth( element );
				this.updateValue( element, valueInput.value + unit );
				this.triggerValueChange( index );
			});

			draggableBullet.addEventListener( 'mousedown', (event) => {
				this.enableDragging( event, index );
			});

			draggableBullet.addEventListener( 'mousemove', ( event ) => {
				this.moveTracker( event, index );
			});

			draggableBullet.addEventListener( 'mouseup', ( event) => {
				this.disableDragging( index, event );
			});

			draggableBullet.addEventListener( 'mouseleave', ( event ) => {
				this.disableDragging( index, event );
			});
		});
	}

	getSliderGroupItems( element ) {
		if ( 'undefined' === typeof element.dataset.displaySliders ) {
			return [];
		}
		const slidersGroup = element.dataset.displaySliders.split( ',' );
		const query        = slidersGroup.map( ( item ) => {
			return `.frm-slider-component[data-type="${item}"]`;
		}).join( ', ' );

		return element.closest( '.frm-style-component' ).querySelectorAll( query )
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

	initChildSlidersWidth( slider, width, index, value ) {
		if ( ! slider.classList.contains( 'frm-has-independent-fields' ) && ! slider.classList.contains( 'frm-has-multiple-values' ) ) {
			return;
		}
		const childSliders = slider.classList.contains( 'frm-has-independent-fields' ) ? slider.querySelectorAll( '.frm-independent-slider-field' ) : this.getSliderGroupItems( slider );

		childSliders.forEach( ( item, childIndex ) => {
			item.querySelector( '.frm-slider-active-track' ).style.width = `${width}px`;
			this.options[ index + childIndex + 1 ].translateX = width;
			this.options[ index + childIndex + 1 ].value = value;
		});
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
		const unit  = element.querySelector( 'select' ).value;
		const value = this.calculateValue( sliderWidth, deltaX, this.getMaxValue( unit, index ) );

		element.querySelector( '.frm-slider-value input[type="text"]' ).value = value;
		element.querySelector( '.frm-slider-bullet .frm-slider-value-label' ).innerText = value;
		element.querySelector( '.frm-slider-active-track' ).style.width = `${deltaX}px`;
		this.initChildSlidersWidth( element, deltaX, index, value + unit );

		this.options[ index ].translateX = deltaX;
		this.options[ index ].value = value + unit;
		this.options[ index ].fullValue = this.updateValue( element, this.options[ index ].value );
		this.valueChangeDebouncer( index );
	}

	getMaxValue( unit, index ) {
		return '%' === unit ? 100 : this.options[ index ].maxValue;
	}

	enableDragging( event, index ) {
		event.target.classList.add( 'frm-dragging' );
		this.options[ index ].dragging = true;
		this.options[ index ].startX = event.clientX - this.options[ index ].translateX;
	}

	disableDragging( index, event ) {
		if ( false === this.options[ index ].dragging ) { return; }
		event.target.classList.remove( 'frm-dragging' );
		this.options[ index ].dragging = false;
		this.triggerValueChange( index );
	}

	triggerValueChange( index ) {
		if ( null !== this.options[ index ].dependendUpdater ) {
			this.options[ index ].dependendUpdater.updateAllDependendElements( this.options[ index ].fullValue );
			return;
		}

		const input = this.elements[ index ].classList.contains( 'frm-has-multiple-values' ) ? this.elements[ index ].closest('.frm-style-component').querySelector( 'input[type="hidden"]' ) : this.elements[ index ].querySelectorAll( '.frm-slider-value input[type="hidden"]' );
		if ( input instanceof NodeList ) {
			input.forEach( ( item ) => {
				item.dispatchEvent( this.eventsChange[ index ] );
			});
			return;
		}
		input.dispatchEvent( this.eventsChange[ index ] );
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
			const input      = element.closest( '.frm-style-component' ).querySelector( 'input[type="hidden"]' );
			const inputValue = input.value.split( ' ' );
			const type       = element.dataset.type;

			if ( ! inputValue[2] ) {
				inputValue[2] = '0px';
			}

			if ( ! inputValue[3] ) {
				inputValue[3] = '0px';
			}

			switch ( type ) {
				case 'vertical':
					inputValue[0] = value;
					inputValue[2] = value;
					break;

				case 'horizontal':
					inputValue[1] = value;
					inputValue[3] = value;
					break;

				case 'top':
					inputValue[0] = value;
					break;

				case 'bottom':
					inputValue[2] = value;
					break;

				case 'left':
					inputValue[3] = value;
					break;

				case 'right':
					inputValue[1] = value;
					break;
			}

			const newValue = inputValue.join( ' ' );
			input.value = newValue;

			const childSlidersGroup = this.getSliderGroupItems( element );
			childSlidersGroup.forEach( ( slider ) => {
				slider.querySelector( '.frm-slider-value input[type="text"]' ).value = parseInt( value, 10 );
			});

			return newValue;
		}

		if ( element.classList.contains( 'frm-has-independent-fields' ) ) {
			const inputValues   = element.querySelectorAll( '.frm-slider-value input[type="hidden"]' );
			const visibleValues = element.querySelectorAll( '.frm-slider-value input[type="text"]' );
			inputValues.forEach( ( input, index ) => {
				input.value = value;
				visibleValues[ index + 1 ].value = parseInt( value, 10 );
			});

			return value;
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
		this.success = frmDom.success;
		this.init();
		this.initHover();
	}

	init() {
		new frmRadioStyleComponent();
		new frmSliderStyleComponent();
		new frmTabsStyleComponent();

		this.initColorPickerDependendUpdaterComponents();
		this.initStyleClassCopyToClipboard( __( 'The class name has been copied.', 'formidable' ) );
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
			const container = event.target.closest( '.wp-picker-container' );
			const id        = event.target.getAttribute( 'id' );

			container.querySelector( '.wp-color-result-text' ).innerText = value;

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

	initStyleClassCopyToClipboard( successMessage ) {
		const copyLabel = document.querySelector( '.frm-copy-text' );
		copyLabel.addEventListener( 'click', ( event ) => {
			const className = event.target.innerText;
			navigator.clipboard.writeText( className ).then( () => {
				this.success( successMessage );
			});
		})
	}

}

new frmStyleOptions();
