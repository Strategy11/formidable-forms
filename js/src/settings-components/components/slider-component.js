/**
 * Internal dependencies
 */
import { HIDDEN_CLASS } from 'core/constants';
import frmDependentUpdaterComponent from '../../admin/components/dependent-updater-component';

// Units that describe a length and can be announced next to the number.
const MEASUREMENT_UNITS = [ 'px', 'em', '%' ];

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

		this.eventsChange = [];

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
			const steps = this.settings.steps || ( element.dataset.steps ? JSON.parse( element.dataset.steps ) : null );
			this.options.push( {
				maxValue: parseInt( element.dataset.maxValue, 10 ),
				element,
				index,
				steps,
				dependentUpdater: parentWrapper.classList.contains( 'frm-style-dependent-updater-component' ) ? new frmDependentUpdaterComponent( parentWrapper ) : null
			} );
		} );
	}

	/**
	 * Initializes the slider component.
	 */
	init() {
		this.initListeners();
		this.initFill();
	}

	/**
	 * Attaches event listeners to each slider element.
	 */
	initListeners() {
		this.sliderElements.forEach( ( element, index ) => {
			this.eventsChange[ index ] = new Event( 'change', {
				bubbles: true,
				cancelable: true
			} );

			const rangeInput = element.querySelector( '.frm-slider' );
			const valueInput = element.querySelector( '.frm-slider-value input[type="text"]' );

			if ( ! rangeInput || ! valueInput ) {
				return;
			}

			this.initSteps( rangeInput, index );

			// Update display while dragging.
			rangeInput.addEventListener( 'input', () => {
				const value = this.getRangeValue( rangeInput, index );
				valueInput.value = value;
				this.refreshRange( rangeInput, element, value );
				this.syncGroupSliders( element, value );
			} );

			// Commit value to hidden input on release.
			rangeInput.addEventListener( 'change', () => {
				const value = this.getRangeValue( rangeInput, index );
				this.options[ index ].fullValue = this.updateValue( element, value + frmSliderComponent.getUnit( element ) );
				this.triggerValueChange( index );
			} );

			// Sync text input changes back to the range.
			valueInput.addEventListener( 'change', event => {
				const unit = frmSliderComponent.getUnit( element );

				if ( parseFloat( rangeInput.max ) < parseFloat( event.target.value ) ) {
					return;
				}

				this.setRangeValue( rangeInput, index, valueInput.value );
				this.refreshRange( rangeInput, element, valueInput.value );
				this.options[ index ].fullValue = this.updateValue( element, valueInput.value + unit );
				this.triggerValueChange( index );
			} );

			this.expandSliderGroup( element );
			this.updateOnUnitChange( element, rangeInput, valueInput, index );
			frmSliderComponent.maybeDisableUnitDropdown( element );
		} );
	}

	/**
	 * Sets the initial fill position for all sliders.
	 */
	initFill() {
		this.sliderElements.forEach( ( element, index ) => {
			const rangeInput = element.querySelector( '.frm-slider' );
			if ( ! rangeInput ) {
				return;
			}

			const value = this.getRangeValue( rangeInput, index );
			this.options[ index ].fullValue = value + frmSliderComponent.getUnit( element );
			this.refreshRange( rangeInput, element, value );
		} );
	}

	/**
	 * Reads the unit currently selected for a slider.
	 *
	 * @since x.x
	 *
	 * @param {HTMLElement} element - The slider component element.
	 * @return {string} The selected unit, or an empty string when the slider has no unit dropdown.
	 */
	static getUnit( element ) {
		const select = element.querySelector( 'select' );
		return select ? select.value : '';
	}

	/**
	 * Switches a stepped slider over to index positions.
	 *
	 * A range input can only step by a fixed amount, so a slider limited to an uneven list of values
	 * (for instance 1, 2, 3, 4, 6, 12 grid columns) is driven by the position in that list instead.
	 * Every arrow key press then lands on an allowed value.
	 *
	 * @since x.x
	 *
	 * @param {HTMLInputElement} rangeInput - The native range input element.
	 * @param {number}           index      - The index of this slider in the options array.
	 * @return {void}
	 */
	initSteps( rangeInput, index ) {
		const { steps } = this.options[ index ];

		if ( ! steps || 0 === steps.length ) {
			return;
		}

		const currentValue = parseFloat( rangeInput.value );

		rangeInput.min = '0';
		rangeInput.max = String( steps.length - 1 );
		rangeInput.step = '1';
		rangeInput.value = String( frmSliderComponent.getClosestStepIndex( currentValue, steps ) );
	}

	/**
	 * Finds the position in the steps array holding the value closest to the one given.
	 *
	 * @since x.x
	 *
	 * @param {number} value - The value to look for.
	 * @param {Array}  steps - The list of values the slider is allowed to take.
	 * @return {number} The index of the closest step.
	 */
	static getClosestStepIndex( value, steps ) {
		let closest = 0;
		let smallestDiff = Math.abs( value - steps[ 0 ] );

		for ( let i = 1; i < steps.length; i++ ) {
			const diff = Math.abs( value - steps[ i ] );
			if ( diff < smallestDiff ) {
				smallestDiff = diff;
				closest = i;
			}
		}

		return closest;
	}

	/**
	 * Reads the value a slider represents, resolving the step list when there is one.
	 *
	 * @since x.x
	 *
	 * @param {HTMLInputElement} rangeInput - The native range input element.
	 * @param {number}           index      - The index of this slider in the options array.
	 * @return {number} The current value.
	 */
	getRangeValue( rangeInput, index ) {
		const { steps } = this.options[ index ];

		if ( steps && steps.length > 0 ) {
			return steps[ parseInt( rangeInput.value, 10 ) ];
		}

		return parseFloat( rangeInput.value );
	}

	/**
	 * Moves a slider to the position representing the given value.
	 *
	 * @since x.x
	 *
	 * @param {HTMLInputElement} rangeInput - The native range input element.
	 * @param {number}           index      - The index of this slider in the options array.
	 * @param {number|string}    value      - The value to move to.
	 * @return {void}
	 */
	setRangeValue( rangeInput, index, value ) {
		const { steps } = this.options[ index ];

		if ( steps && steps.length > 0 ) {
			rangeInput.value = String( frmSliderComponent.getClosestStepIndex( parseFloat( value ), steps ) );
			return;
		}

		frmSliderComponent.applyStep( rangeInput, value );
		rangeInput.value = value;
	}

	/**
	 * Makes sure the range can hold the given value exactly.
	 *
	 * A range snaps its value to the step, so a fractional value needs a step fine enough to land
	 * on. Whole numbers keep stepping by one so the arrow keys stay useful.
	 *
	 * @since x.x
	 *
	 * @param {HTMLInputElement} rangeInput - The native range input element.
	 * @param {number|string}    value      - The value about to be set.
	 * @return {void}
	 */
	static applyStep( rangeInput, value ) {
		const decimals = ( String( value ).split( '.' )[ 1 ] || '' ).length;
		rangeInput.step = decimals > 0 ? `0.${ '0'.repeat( decimals - 1 ) }1` : '1';
	}

	/**
	 * Updates the track fill and the value screen readers announce.
	 *
	 * @since x.x
	 *
	 * @param {HTMLInputElement} rangeInput - The native range input element.
	 * @param {HTMLElement}      element    - The slider component element.
	 * @param {number|string}    value      - The value the slider now represents.
	 * @return {void}
	 */
	refreshRange( rangeInput, element, value ) {
		frmSliderComponent.updateFill( rangeInput );

		const unit = frmSliderComponent.getUnit( element );
		const suffix = MEASUREMENT_UNITS.includes( unit ) ? unit : '';
		rangeInput.setAttribute( 'aria-valuetext', `${ value }${ suffix }` );
	}

	/**
	 * Updates the CSS custom property that drives the active-track fill colour.
	 *
	 * The ratio is read back from the range input itself so the fill can never disagree
	 * with where the browser paints the thumb.
	 *
	 * @since x.x
	 *
	 * @param {HTMLInputElement} rangeInput - The native range input element.
	 * @return {void}
	 */
	static updateFill( rangeInput ) {
		const min = parseFloat( rangeInput.min ) || 0;
		const max = parseFloat( rangeInput.max );
		const value = parseFloat( rangeInput.value );
		const span = max - min;
		const ratio = span > 0 ? Math.min( Math.max( ( value - min ) / span, 0 ), 1 ) : 0;

		rangeInput.style.setProperty( '--frm-fill', ratio );
	}

	/**
	 * Syncs grouped child sliders (top/bottom or left/right) to the parent group value.
	 *
	 * @since x.x
	 *
	 * @param {HTMLElement}   element - The parent slider component element.
	 * @param {number|string} value   - The new numeric value (without unit).
	 * @return {void}
	 */
	syncGroupSliders( element, value ) {
		if ( ! element.classList.contains( 'frm-has-multiple-values' ) && ! element.classList.contains( 'frm-has-independent-fields' ) ) {
			return;
		}

		const childSliders = element.classList.contains( 'frm-has-independent-fields' )
			? element.querySelectorAll( '.frm-independent-slider-field' )
			: this.getSliderGroupItems( element );

		childSliders.forEach( child => {
			const childRange = child.querySelector( '.frm-slider' );
			const childText = child.querySelector( '.frm-slider-value input[type="text"]' );
			const childIndex = this.getSliderIndex( child );

			if ( childRange && -1 !== childIndex ) {
				this.setRangeValue( childRange, childIndex, value );
				this.refreshRange( childRange, child, value );
			}

			if ( childText ) {
				childText.value = value;
			}
		} );
	}

	expandSliderGroup( element ) {
		const svgIcon = element.querySelector( '.frmsvg' );

		if ( element.dataset.displaySliders === undefined || null === svgIcon ) {
			return;
		}

		const sliderGroupItems = this.getSliderGroupItems( element );
		svgIcon.addEventListener( 'click', () => {
			sliderGroupItems.forEach( item => {
				item.classList.toggle( HIDDEN_CLASS );
			} );
		} );
	}

	/**
	 * Updates the range max, fill, and hidden input when the unit dropdown changes.
	 *
	 * @param {HTMLElement}      element    - The slider component element.
	 * @param {HTMLInputElement} rangeInput - The native range input element.
	 * @param {HTMLInputElement} valueInput - The visible text input.
	 * @param {number}           index      - The index of this slider in the options array.
	 */
	updateOnUnitChange( element, rangeInput, valueInput, index ) {
		const select = element.querySelector( 'select' );

		if ( ! select ) {
			return;
		}

		select.addEventListener( 'change', event => {
			const unit = event.target.value.toLowerCase();

			if ( '' === unit ) {
				element.classList.add( 'frm-disabled', 'frm-empty' );
				rangeInput.disabled = true;

				// Drop the old unit from what is announced, the value no longer carries one.
				this.refreshRange( rangeInput, element, this.getRangeValue( rangeInput, index ) );
				return;
			}

			if ( 'auto' === unit ) {
				element.classList.add( 'frm-disabled' );
				rangeInput.disabled = true;

				// The slider no longer stands for a number, so announce the keyword that replaced it.
				rangeInput.setAttribute( 'aria-valuetext', unit );
				this.updateValue( element, 'auto' );
				this.triggerValueChange( index );

				return;
			}

			element.classList.remove( 'frm-disabled', 'frm-empty' );
			rangeInput.disabled = false;

			if ( ! this.options[ index ].steps ) {
				rangeInput.max = this.getMaxValue( unit, index );
			}

			// Lowering the max makes the browser clamp the range, so read the value back rather than
			// trusting the text input, which would otherwise save a number the new unit does not allow.
			const value = this.getRangeValue( rangeInput, index );
			valueInput.value = value;

			this.options[ index ].fullValue = value + unit;
			this.updateValue( element, this.options[ index ].fullValue );
			this.refreshRange( rangeInput, element, value );
			this.triggerValueChange( index );
		} );
	}

	/**
	 * Disables the unit dropdown if there is only a single unit option.
	 *
	 * @param {HTMLElement} element - The slider element.
	 */
	static maybeDisableUnitDropdown( element ) {
		const select = element.querySelector( 'select' );
		if ( ! select ) {
			return;
		}

		const options = Array.from( select.options ).filter( option => '' !== option.value );
		if ( 1 >= options.length ) {
			select.classList.add( 'frm-single-unit' );
			select.addEventListener( 'mousedown', event => event.preventDefault() );
		}
	}

	/**
	 * Retrieves an array of slider group items based on the provided element.
	 *
	 * @param {HTMLElement} element - The element to retrieve slider group items from.
	 * @return {NodeList} - An array-like object containing the slider group items.
	 */
	getSliderGroupItems( element ) {
		if ( element.dataset.displaySliders === undefined ) {
			return [];
		}

		// A slider is not always inside a style component, and throwing here would abandon the
		// setup of every slider that comes after this one.
		const wrapper = element.closest( '.frm-style-component' );
		if ( ! wrapper ) {
			return [];
		}

		const slidersGroup = element.dataset.displaySliders.split( ',' );
		const query = slidersGroup.map( item => {
			return `.frm-slider-component[data-type="${ item }"]`;
		} ).join( ', ' );

		return wrapper.querySelectorAll( query );
	}

	/**
	 * Returns the index of the specified slider element.
	 *
	 * @since x.x
	 *
	 * @param {HTMLElement} slider - The slider element.
	 * @return {number} The index of the slider element, or -1 when it is not tracked.
	 */
	getSliderIndex( slider ) {
		const option = this.options.find( item => item.element === slider );
		return option ? option.index : -1;
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
			if ( userBaseFontSizeInput ) {
				userBaseFontSizeInput.value = 'true';
			}
		}
		if ( element.classList.contains( 'frm-has-multiple-values' ) ) {
			const input = element.closest( '.frm-style-component' ).querySelector( 'input[type="hidden"]' );
			const inputValue = input.value.split( ' ' );
			const { type } = element.dataset;

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
				slider.querySelector( '.frm-slider-value input[type="text"]' ).value = parseFloat( value );
				slider.querySelector( 'select' ).value = unitMeasure;
			} );

			return newValue;
		}

		if ( element.classList.contains( 'frm-has-independent-fields' ) ) {
			const inputValues = element.querySelectorAll( '.frm-slider-value input[type="hidden"]' );
			const visibleValues = element.querySelectorAll( '.frm-slider-value input[type="text"]' );
			inputValues.forEach( ( input, index ) => {
				input.value = value;
				visibleValues[ index + 1 ].value = parseFloat( value );
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
		return MEASUREMENT_UNITS.find( unit => value.includes( unit ) ) || '';
	}
}
