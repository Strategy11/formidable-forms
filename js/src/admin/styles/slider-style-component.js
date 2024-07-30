import frmStyleDependentUpdaterComponent from './dependent-updater-component';
/**
 * Represents a slider style component.
 * @class frmSliderStyleComponent
 */
export default class frmSliderStyleComponent {

	constructor() {
		this.elements = document.querySelectorAll( '.frm-slider-component' );
		if ( 0 === this.elements.length ) {
			return;
		}

		// The slider bullet point width in pixels. Used in value calculation on drag event.
		this.sliderBulletWidth = 16;
		this.sliderMarginRight = 5;
		this.eventsChange = [];

		const { debounce }        = frmDom.util;
		this.valueChangeDebouncer = debounce( ( index ) => this.triggerValueChange( index ), 25 );

		this.initOptions();
		this.init();
	}

	/**
	 * Initializes the options for the slider style component.
	 */
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
				dependentUpdater: parentWrapper.classList.contains( 'frm-style-dependent-updater-component' ) ? new frmStyleDependentUpdaterComponent( parentWrapper ) : null
			});
		});
	}

	/**
	 * Initializes the slider style component.
	 */
	init() {
		this.initSlidersPosition();
		this.initDraggable();
	}

	/**
	 * Initializes the draggable functionality for the slider style component.
	 */
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
				event.preventDefault();
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

	/**
	 * Retrieves an array of slider group items based on the provided element.
	 *
	 * @param {HTMLElement} element - The element to retrieve slider group items from.
	 * @return {NodeList} - An array-like object containing the slider group items.
	 */
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

	/**
	 * Initializes the position of sliders.
	 */
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

	/**
	 * Initializes the width of sliders within a given section.
	 *
	 * @param {HTMLElement} section - The section containing the sliders.
	 * @return {void}
	 */
	initSlidersWidth( section ) {
		const sliders = section.querySelectorAll( '.frm-slider-component' );
		sliders.forEach( ( slider ) => {
			setTimeout( () => {
				this.initSliderWidth( slider );
			}, 100 );
		});
	}

	/**
	 * Initializes the width of a slider.
	 *
	 * @param {HTMLElement} slider - The slider element.
	 * @return {void}
	 */
	initSliderWidth( slider ) {
		const index       = this.getSliderIndex( slider );
		const sliderWidth = slider.querySelector( '.frm-slider' ).offsetWidth - this.sliderBulletWidth;
		const value       = parseInt( slider.querySelector( '.frm-slider-value input[type="text"]' ).value, 10 );
		const unit        = slider.querySelector( 'select' ).value;
		const deltaX      = '%' === unit ? Math.round( sliderWidth * value / 100 ) : Math.ceil( ( value / this.options[ index ].maxValue ) * sliderWidth );

		slider.querySelector( '.frm-slider-active-track' ).style.width = `${deltaX}px`;
		this.options[ index ].translateX = deltaX;
		this.options[ index ].value = value + unit;
	}

	/**
	 * Initializes the width of child sliders.
	 *
	 * @param {HTMLElement} slider - The parent slider element.
	 * @param {number}      width  - The width to set for the child sliders.
	 * @param {number}      index  - The starting index for the child sliders.
	 * @param {number}      value  - The value to set for the child sliders.
	 */
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

	/**
	 * Returns the index of the specified slider element.
	 *
	 * @param {HTMLElement} slider - The slider element.
	 * @return {number} The index of the slider element.
	 */
	getSliderIndex( slider ) {
		return this.options.filter( ( option ) => {
			return option.element === slider;
		})[0].index;
	};

	/**
	 * Handles the movement of the slider tracker.
	 *
	 * @param {Event}  event - The event object representing the mouse movement.
	 * @param {number} index - The index of the slider element.
	 * @return {void}
	 */
	moveTracker( event, index ) {
		if ( ! this.options[ index ].dragging ) {
			return;
		}
		let deltaX        = event.clientX - this.options[ index ].startX;
		const element     = this.elements[ index ];
		const sliderWidth = element.querySelector( '.frm-slider' ).offsetWidth;

		// Ensure deltaX does not go below 0
		deltaX = Math.max( deltaX, 0 );

		if ( deltaX + this.sliderBulletWidth / 2 + this.sliderMarginRight  >= sliderWidth ) {
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

	/**
	 * Get the maximum value based on the unit and index.
	 *
	 * @param {string} unit  - The unit of measurement.
	 * @param {number} index - The index of the option.
	 * @return {number} The maximum value.
	 */
	getMaxValue( unit, index ) {
		return '%' === unit ? 100 : this.options[ index ].maxValue;
	}

	/**
	 * Enables dragging for the slider style component.
	 *
	 * @param {Event}  event - The event object.
	 * @param {number} index - The index of the option being dragged.
	 */
	enableDragging( event, index ) {
		event.target.classList.add( 'frm-dragging' );
		this.options[ index ].dragging = true;
		this.options[ index ].startX = event.clientX - this.options[ index ].translateX;
	}

	/**
	 * Disables dragging for a specific index.
	 *
	 * @param {number} index - The index of the option to disable dragging for.
	 * @param {Event}  event - The event object triggered by the dragging action.
	 */
	disableDragging( index, event ) {
		if ( false === this.options[ index ].dragging ) { return; }
		event.target.classList.remove( 'frm-dragging' );
		this.options[ index ].dragging = false;
		this.triggerValueChange( index );
	}

	/**
	 * Triggers a value change for the specified index.
	 *
	 * @param {number} index - The index of the value to be changed.
	 */
	triggerValueChange( index ) {
		if ( null !== this.options[ index ].dependentUpdater ) {
			this.options[ index ].dependentUpdater.updateAllDependentElements( this.options[ index ].fullValue );
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

	/**
	 * Calculates the value based on the width, deltaX, and maxValue.
	 *
	 * @param {number} width    - The width of the slider.
	 * @param {number} deltaX   - The change in x-coordinate.
	 * @param {number} maxValue - The maximum value.
	 * @return {number} - The calculated value.
	 */
	calculateValue( width, deltaX, maxValue ) {

		// Indicates the additional value generated by the slider's drag progress (up to 100%) and the width of the slider bullet.
		// Generates a more accurate value for the slider's start (0) and end (maximum value) positions, taking into account the slider's position and bullet width.
		const delta = Math.ceil( this.sliderBulletWidth * ( deltaX / width ) );

		const value = Math.ceil( ( ( deltaX + delta ) / width ) * maxValue );

		return Math.min( value, maxValue )
	}

	/**
	 * Updates the value of a slider component.
	 *
	 * @param {HTMLElement} element - The slider component element.
	 * @param {string}      value   - The new value to be set.
	 * @return {string} - The updated value.
	 */
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