
/**
 * Internal dependencies
 */
import { HIDDEN_CLASS } from 'core/constants';
import frmDependentUpdaterComponent from '../../admin/components/dependent-updater-component';

/**
 * Represents a slider component.
 *
 * @class frmSliderComponent
 */
export default class frmSliderComponent {
	constructor( sliderElements = [], settings = {} ) {
		this.loadedByWebComponent = sliderElements.length > 0;
		this.sliderElements = sliderElements.length > 0 ? sliderElements : document.querySelectorAll( '.frm-slider-component' );
		this.settings = settings;

		if ( 0 === this.sliderElements.length ) {
			return;
		}

		// The slider bullet point width in pixels. Used in value calculation on drag event.
		this.sliderBulletWidth = 16;
		this.sliderMarginRight = 5;
		this.eventsChange = [];

		const { debounce } = frmDom.util;
		this.valueChangeDebouncer = debounce( index => this.triggerValueChange( index ), 25 );

		this.initOptions();
		this.init();
	}

	/**
	 * Initializes the options for the slider component.
	 */
	initOptions() {
		this.options = [];
		this.sliderElements.forEach( ( element, index ) => {
			const parentWrapper = element.classList.contains( 'frm-has-multiple-values' ) ? element.closest( '.frm-style-component' ) : element;
			this.options.push( {
				dragging: false,
				startX: 0,
				translateX: 0,
				maxValue: parseInt( element.dataset.maxValue, 10 ),
				element: element,
				index: index,
				value: 0,
				dependentUpdater: parentWrapper.classList.contains( 'frm-style-dependent-updater-component' ) ? new frmDependentUpdaterComponent( parentWrapper ) : null
			} );
		} );
	}

	/**
	 * Initializes the slider component.
	 */
	init() {
		this.initDraggable();

		if ( this.loadedByWebComponent ) {
			this.initSlidersPositionInsideWebComponent();
			return;
		}

		this.initSlidersPosition();
	}

	/**
	 * Initializes the draggable functionality for the slider component.
	 */
	initDraggable() {
		this.sliderElements.forEach( ( element, index ) => {
			this.eventsChange[ index ] = new Event( 'change', {
				bubbles: true,
				cancelable: true
			} );
			const draggableBullet = element.querySelector( '.frm-slider-bullet' );
			const valueInput = element.querySelector( '.frm-slider-value input[type="text"]' );

			valueInput.addEventListener( 'change', event => {
				const unit = element.querySelector( 'select' ).value;

				if ( this.getMaxValue( unit, index ) < parseInt( event.target.value, 10 ) ) {
					return;
				}

				this.initSliderWidth( element );
				this.options[ index ].fullValue = this.updateValue( element, valueInput.value + unit );
				this.triggerValueChange( index );
			} );

			this.expandSliderGroup( element );
			this.updateOnUnitChange( element, valueInput, index );
			this.changeSliderPositionOnClick( element, valueInput, index );

			draggableBullet.addEventListener( 'mousedown', event => {
				event.preventDefault();
				event.stopPropagation();
				if ( element.classList.contains( 'frm-disabled' ) ) {
					return;
				}
				this.enableDragging( event, index );
			} );

			draggableBullet.addEventListener( 'mousemove', event => {
				if ( element.classList.contains( 'frm-disabled' ) ) {
					return;
				}
				this.moveTracker( event, index );
			} );

			draggableBullet.addEventListener( 'mouseup', event => {
				if ( element.classList.contains( 'frm-disabled' ) ) {
					return;
				}
				this.disableDragging( index, event );
			} );

			draggableBullet.addEventListener( 'mouseleave', event => {
				if ( element.classList.contains( 'frm-disabled' ) ) {
					return;
				}
				this.disableDragging( index, event );
			} );
		} );
	}

	expandSliderGroup( element ) {
		const svgIcon = element.querySelector( '.frmsvg' );

		if ( 'undefined' === typeof element.dataset.displaySliders || null === svgIcon ) {
			return;
		}

		const sliderGroupItems = this.getSliderGroupItems( element );
		svgIcon.addEventListener( 'click', ( ) => {
			sliderGroupItems.forEach( item => {
				item.classList.toggle( HIDDEN_CLASS );
			} );
		} );
	}

	updateOnUnitChange( element, valueInput, index ) {
		element.querySelector( 'select' ).addEventListener( 'change', event => {
			const unit = event.target.value.toLowerCase();

			if ( '' === unit ) {
				element.classList.add( 'frm-disabled', 'frm-empty' );
				return;
			}

			if ( 'auto' === unit ) {
				element.classList.add( 'frm-disabled' );
				this.updateValue( element, 'auto' );
				this.triggerValueChange( index );

				return;
			}

			element.classList.remove( 'frm-disabled', 'frm-empty' );
			this.options[ index ].fullValue = valueInput.value + unit;
			this.updateValue( element, this.options[ index ].fullValue );
			this.triggerValueChange( index );
		} );
	}

	changeSliderPositionOnClick( element, valueInput, index ) {
		const frmSlider = element.querySelector( '.frm-slider' );
		const customEvent = new Event( 'change', {
			bubbles: true,
			cancelable: true
		} );

		frmSlider.addEventListener( 'click', event => {
			if ( element.classList.contains( 'frm-disabled' ) ) {
				return;
			}

			event.preventDefault();
			event.stopPropagation();

			if ( ! event.target.classList.contains( 'frm-slider' ) && ! event.target.classList.contains( 'frm-slider-active-track' ) ) {
				return;
			}

			const sliderWidth = frmSlider.offsetWidth - this.sliderBulletWidth;
			const sliderRect = frmSlider.getBoundingClientRect();
			const deltaX = event.clientX - sliderRect.left - this.sliderBulletWidth;
			const unit = element.querySelector( 'select' ).value;
			const value = this.calculateValue( sliderWidth, deltaX, this.getMaxValue( unit, index ) );

			if ( value < 0 ) {
				return;
			}

			this.options[ index ].fullValue = this.updateValue( element, value + unit );
			this.initChildSlidersWidth( element, deltaX, index, value + unit );

			valueInput.value = value;
			valueInput.dispatchEvent( customEvent );
		} );
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
		const query = slidersGroup.map( item => {
			return `.frm-slider-component[data-type="${ item }"]`;
		} ).join( ', ' );

		return element.closest( '.frm-style-component' ).querySelectorAll( query );
	}

	initSlidersPositionInsideWebComponent() {
		this.sliderElements.forEach( ( element, index ) => {
			this.initSliderWidth( element, index );
		} );
	}

	/**
	 * Initializes the position of sliders when a accordion section is opened.
	 */
	initSlidersPosition() {
		const accordionitems = document.querySelectorAll( '#frm_style_sidebar .accordion-section h3' );
		const quickSettings = document.querySelector( '.frm-quick-settings' );
		const openedAccordion = document.querySelector( '.accordion-section.open' );

		// Detect if upload background image upload has triggered and initialize the "Image Opacity" slider width.
		wp.hooks.addAction( 'frm_pro_on_bg_image_upload', 'formidable', event => {
			const imageBackgroundOpacitySlider = event.closest( '.accordion-section-content' ).querySelector( '#frm-bg-image-opacity-slider' );
			this.initSlidersWidth( imageBackgroundOpacitySlider );
		} );

		// init the sliders width from "Quick Settings" page.
		if ( null !== quickSettings ) {
			this.initSlidersWidth( quickSettings );
		}

		// Init the sliders width in opened accordion section from "Advanced Settings" page.
		if ( null !== openedAccordion ) {
			this.initSlidersWidth( openedAccordion );
		}

		// init the sliders width everytime when an accordion section is opened from "Advanced Settings" page.
		accordionitems.forEach( item => {
			item.addEventListener( 'click', event => {
				this.initSlidersWidth( event.target.closest( '.accordion-section' ) );
			} );
		} );

		this.initSliderPositionOnFieldShapeChange();
	}

	/**
	 * Initializes the width of "Corner Radius" slider that is dynamically is displayed on "Field Shape" option change from "Quick Settings".
	 *
	 * @return {void}
	 */
	initSliderPositionOnFieldShapeChange() {
		const fieldShapeType = document.querySelector( '.frm-style-component.frm-field-shape' );

		if ( null === fieldShapeType ) {
			return;
		}

		const radioButtons = fieldShapeType.querySelectorAll( 'input[type="radio"]' );
		radioButtons.forEach( radio => {
			radio.addEventListener( 'change', event => {
				if ( event.target.checked && 'rounded-corner' === event.target.value ) {
					const slider = document.querySelector( 'div[data-frm-element="field-shape-corner-radius"] .frm-slider-component' );
					this.initSliderWidth( slider );
				}
			} );
		} );
	}

	/**
	 * Initializes the width of sliders within a given section.
	 *
	 * @param {HTMLElement} section - The section containing the sliders.
	 * @return {void}
	 */
	initSlidersWidth( section ) {
		const sliders = section.querySelectorAll( '.frm-slider-component' );
		sliders.forEach( slider => {
			setTimeout( () => {
				this.initSliderWidth( slider );
			}, 100 );
		} );
	}

	/**
	 * Initializes the width of a slider.
	 *
	 * @param {HTMLElement} slider      - The slider element.
	 * @param {number}      sliderIndex - The index of the slider.
	 * @return {void}
	 */
	initSliderWidth( slider, sliderIndex = null ) {
		if ( slider.classList.contains( 'frm-disabled' ) ) {
			return;
		}
		const index = sliderIndex !== null ? sliderIndex : this.getSliderIndex( slider );
		const sliderWidth = slider.querySelector( '.frm-slider' ).offsetWidth - this.sliderBulletWidth;
		const value = parseInt( slider.querySelector( '.frm-slider-value input[type="text"]' ).value, 10 );
		const unit = slider.querySelector( 'select' ).value;
		const deltaX = '%' === unit ? Math.round( sliderWidth * value / 100 ) : Math.ceil( ( value / this.options[ index ].maxValue ) * sliderWidth );

		slider.querySelector( '.frm-slider-active-track' ).style.width = `${ deltaX }px`;
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
			item.querySelector( '.frm-slider-active-track' ).style.width = `${ width }px`;
			this.options[ index + childIndex + 1 ].translateX = width;
			this.options[ index + childIndex + 1 ].value = value;
		} );
	}

	/**
	 * Returns the index of the specified slider element.
	 *
	 * @param {HTMLElement} slider - The slider element.
	 * @return {number} The index of the slider element.
	 */
	getSliderIndex( slider ) {
		return this.options.filter( option => {
			return option.element === slider;
		} )[ 0 ].index;
	}

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
		let deltaX = event.clientX - this.options[ index ].startX;
		const element = this.sliderElements[ index ];
		const sliderWidth = element.querySelector( '.frm-slider' ).offsetWidth;

		// Ensure deltaX does not go below 0
		deltaX = Math.max( deltaX, 0 );

		if ( deltaX + ( this.sliderBulletWidth / 2 ) + this.sliderMarginRight >= sliderWidth ) {
			return;
		}
		const unit = element.querySelector( 'select' ).value;
		const value = this.calculateValue( sliderWidth, deltaX, this.getMaxValue( unit, index ) );

		element.querySelector( '.frm-slider-value input[type="text"]' ).value = value;
		element.querySelector( '.frm-slider-bullet .frm-slider-value-label' ).innerText = value;
		element.querySelector( '.frm-slider-active-track' ).style.width = `${ deltaX }px`;
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
	 * Enables dragging for the slider component.
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
		if ( false === this.options[ index ].dragging ) {
			return;
		}
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

		const input = this.sliderElements[ index ].classList.contains( 'frm-has-multiple-values' ) ? this.sliderElements[ index ].closest( '.frm-style-component' ).querySelector( 'input[type="hidden"]' ) : this.sliderElements[ index ].querySelectorAll( '.frm-slider-value input[type="hidden"]' );
		if ( input instanceof NodeList ) {
			input.forEach( item => {
				item.dispatchEvent( this.eventsChange[ index ] );
			} );
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

		return Math.min( value, maxValue );
	}

	/**
	 * Updates the value of a slider component.
	 *
	 * @param {HTMLElement} element - The slider component element.
	 * @param {string}      value   - The new value to be set.
	 * @return {string} - The updated value.
	 */
	updateValue( element, value ) {
		// When the slider component is used for "Base Font Size", we need to update a hidden input field when change happens to indicate that the "Base Font Size" has been adjusted.
		// Used to avoid conflicts with other possible font sizes adjustemnts in "Advanced Settings" when moving from "Quick Settings" when "Base Font Size" is not changed.
		if ( element.classList.contains( 'frm-base-font-size' ) ) {
			const userBaseFontSizeInput = document.querySelector( 'input[name="frm_style_setting[post_content][use_base_font_size]"]' );
			if ( null !== userBaseFontSizeInput ) {
				userBaseFontSizeInput.value = 'true';
			}
		}
		if ( element.classList.contains( 'frm-has-multiple-values' ) ) {
			const input = element.closest( '.frm-style-component' ).querySelector( 'input[type="hidden"]' );
			const inputValue = input.value.split( ' ' );
			const type = element.dataset.type;

			if ( ! inputValue[ 2 ] ) {
				inputValue[ 2 ] = '0px';
			}

			if ( ! inputValue[ 3 ] ) {
				inputValue[ 3 ] = '0px';
			}

			switch ( type ) {
				case 'vertical':
					inputValue[ 0 ] = value;
					inputValue[ 2 ] = value;
					break;

				case 'horizontal':
					inputValue[ 1 ] = value;
					inputValue[ 3 ] = value;
					break;

				case 'top':
					inputValue[ 0 ] = value;
					break;

				case 'bottom':
					inputValue[ 2 ] = value;
					break;

				case 'left':
					inputValue[ 3 ] = value;
					break;

				case 'right':
					inputValue[ 1 ] = value;
					break;
			}

			const newValue = inputValue.join( ' ' );
			input.value = newValue;

			const childSlidersGroup = this.getSliderGroupItems( element );
			childSlidersGroup.forEach( slider => {
				const unitMeasure = this.getUnitMeasureFromValue( value );
				slider.querySelector( '.frm-slider-value input[type="text"]' ).value = parseInt( value, 10 );
				slider.querySelector( 'select' ).value = unitMeasure;
			} );

			return newValue;
		}

		if ( element.classList.contains( 'frm-has-independent-fields' ) ) {
			const inputValues = element.querySelectorAll( '.frm-slider-value input[type="hidden"]' );
			const visibleValues = element.querySelectorAll( '.frm-slider-value input[type="text"]' );
			inputValues.forEach( ( input, index ) => {
				input.value = value;
				visibleValues[ index + 1 ].value = parseInt( value, 10 );
			} );

			return value;
		}

		element.querySelector( '.frm-slider-value input[type="hidden"]' ).value = value;
		return value;
	}

	/**
	 * Returns the unit of measurement used in the given value.
	 *
	 * @param {string} value - The value to check for the unit of measurement.
	 * @return {string} The unit of measurement ('%', 'px', 'em') found in the value, or an empty string if none is found.
	 */
	getUnitMeasureFromValue( value ) {
		return [ '%', 'px', 'em' ].find( unit => value.includes( unit ) ) || '';
	}
}
