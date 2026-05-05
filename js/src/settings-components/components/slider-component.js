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

			// Update display while dragging.
			rangeInput.addEventListener( 'input', () => {
				valueInput.value = rangeInput.value;
				this.updateFill( rangeInput, index );
				this.syncGroupSliders( element, rangeInput.value, index );
			} );

			// Commit value to hidden input on release.
			rangeInput.addEventListener( 'change', () => {
				const unit = element.querySelector( 'select' ).value;
				this.options[ index ].fullValue = this.updateValue( element, rangeInput.value + unit );
				this.triggerValueChange( index );
			} );

			// Sync text input changes back to the range.
			valueInput.addEventListener( 'change', event => {
				const unit = element.querySelector( 'select' ).value;

				if ( this.getMaxValue( unit, index ) < parseInt( event.target.value, 10 ) ) {
					return;
				}

				rangeInput.value = valueInput.value;
				this.updateFill( rangeInput, index );
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
			if ( rangeInput ) {
				const unit = element.querySelector( 'select' ).value;
				this.options[ index ].fullValue = rangeInput.value + unit;
				this.updateFill( rangeInput, index );
			}
		} );
	}

	/**
	 * Updates the CSS custom property that drives the active-track fill colour.
	 *
	 * @since x.x
	 *
	 * @param {HTMLInputElement} rangeInput - The native range input element.
	 * @param {number}           index      - The index of this slider in the options array.
	 * @return {void}
	 */
	updateFill( rangeInput, index ) {
		const unit = rangeInput.closest( '.frm-slider-component' ).querySelector( 'select' ).value;
		const max = this.getMaxValue( unit, index );
		const percent = max > 0 ? Math.min( parseInt( rangeInput.value, 10 ) / max * 100, 100 ) : 0;
		rangeInput.style.setProperty( '--frm-fill', `${ percent }%` );
	}

	/**
	 * Syncs grouped child sliders (top/bottom or left/right) to the parent group value.
	 *
	 * @since x.x
	 *
	 * @param {HTMLElement} element - The parent slider component element.
	 * @param {string}      value   - The new numeric value (without unit).
	 * @param {number}      index   - The index of the parent slider in the options array.
	 * @return {void}
	 */
	syncGroupSliders( element, value, index ) {
		if ( ! element.classList.contains( 'frm-has-multiple-values' ) && ! element.classList.contains( 'frm-has-independent-fields' ) ) {
			return;
		}

		const childSliders = element.classList.contains( 'frm-has-independent-fields' )
			? element.querySelectorAll( '.frm-independent-slider-field' )
			: this.getSliderGroupItems( element );

		childSliders.forEach( ( child, childIndex ) => {
			const childRange = child.querySelector( '.frm-slider' );
			const childText = child.querySelector( '.frm-slider-value input[type="text"]' );

			if ( childRange ) {
				childRange.value = value;
				this.updateFill( childRange, index + childIndex + 1 );
			}

			if ( childText ) {
				childText.value = parseInt( value, 10 );
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
			rangeInput.max = this.getMaxValue( unit, index );
			this.options[ index ].fullValue = valueInput.value + unit;
			this.updateValue( element, this.options[ index ].fullValue );
			this.updateFill( rangeInput, index );
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
		const slidersGroup = element.dataset.displaySliders.split( ',' );
		const query = slidersGroup.map( item => {
			return `.frm-slider-component[data-type="${ item }"]`;
		} ).join( ', ' );

		return element.closest( '.frm-style-component' ).querySelectorAll( query );
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
