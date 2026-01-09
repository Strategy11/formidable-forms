import { frmWebComponent } from '../frm-web-component';
import frmSliderComponent from '../../../src/settings-components/components/slider-component.js';
import style from './frm-range-slider-component.css';
import { __ } from '@wordpress/i18n';

export class frmRangeSliderComponent extends frmWebComponent {
	constructor() {
		super();
		this.componentStyle = style;
		this._onChange = null;
		this._sliderDefaultMultipleValues = null;
		this._sliderDefaultValue = '0px';
		this._hasMultipleValues = false;
		this._sliderAvailableUnits = [ 'px', 'em', '%' ];
	}

	/**
	 * A method to set the change event listener for the slider component.
	 *
	 * @param {Function} callback - The callback function to call when the slider component is changed.
	 * @return {void}
	 */
	set onChange( callback ) {
		if ( 'function' !== typeof callback ) {
			throw new Error( 'Callback must be a function' );
		}

		this._onChange = callback;
	}

	/**
	 * A method to set the has multiple values flag. This flag is used to determine if the slider component should display multiple values.
	 *
	 * @param {boolean} value - The value to set.
	 * @return {void}
	 */
	set hasMultipleValues( value ) {
		this._hasMultipleValues = value;
	}

	/**
	 * A method to set the default multiple values. This values are used to determine the default values for the slider component.
	 *
	 * @param {Object} value - The value to set.
	 * @return {void}
	 */
	set sliderDefaultMultipleValues( value ) {
		this._sliderDefaultMultipleValues = value;
	}

	/**
	 * A method to set the default value for the single slider component. This value is used to determine the default value for the single slider component.
	 *
	 * @param {string} value - The value to set.
	 * @return {void}
	 */
	set sliderDefaultValue( value ) {
		this._sliderDefaultValue = value;
	}

	/**
	 * A method to set the available units for the slider component. This units are used to determine the available units for the slider component.
	 *
	 * @param {Array} value - The value to set.
	 * @return {void}
	 */
	set sliderAvailableUnits( value ) {
		this._sliderAvailableUnits = value;
	}

	useShadowDom() {
		return false;
	}

	initView() {
		this.wrapper = document.createElement( 'div' );
		this.slidersContainer = document.createElement( 'div' );

		this.slidersContainer.classList.add( 'frm-sliders-container' );
		this.wrapper.classList.add( 'frm-style-component' );

		// Get data from attributes
		const maxValue = parseInt( this.getAttribute( 'data-max-value' ) || '100', 10 );
		const units = this.getAvailableUnits();
		const componentClass = this.getAttribute( 'data-component-class' ) || '';
		const componentId = this.componentId;
		const fieldName = this.fieldName ? `name="${ this.fieldName }"` : '';
		const fieldValue = this.defaultValue || this._sliderDefaultValue;

		if ( this.hasMultipleSliderValues() ) {
			const defaultValues = this.parseDefaultValues();
			this.createMultipleValuesSlider( this.slidersContainer, {
				maxValue,
				units,
				componentClass,
				componentId,
				fieldName,
				fieldValue,
				defaultValues
			} );

			this.wrapper.appendChild( this.slidersContainer );
			return this.wrapper;
		}

		this.slidersContainer.appendChild( this.createSlider( {
			maxValue,
			units,
			value: this.parseValueUnit( fieldValue ),
			addHiddenInputValue: true
		} ) );

		this.wrapper.appendChild( this.slidersContainer );
		return this.wrapper;
	}

	/**
	 * A method to get the available units for the slider component. It will checke the data-units attribute first and if it is not set, it will return the default available units.
	 *
	 * @return {Array} - The available units.
	 */
	getAvailableUnits() {
		const attr = this.getAttribute( 'data-units' );
		if ( ! attr ) {
			return this._sliderAvailableUnits;
		}

		return attr.split( ',' ).map( u => u.trim() );
	}

	/**
	 * A method to parse the default values for the multiple values slider component. It will check the data-values attribute first and if it is not set, it will return the default values.
	 * If the values haven't been set via data-values attribute or dynamically via this._sliderDefaultMultipleValues it will return the default values.
	 *
	 * @return {Object} - The default values.
	 */
	parseDefaultValues() {
		const valuesAttr = this.getAttribute( 'data-values' ) || this._sliderDefaultMultipleValues;
		if ( ! valuesAttr ) {
			return {
				vertical: { value: 0, unit: 'px' },
				top: { value: 0, unit: 'px' },
				bottom: { value: 0, unit: 'px' },
				horizontal: { value: 0, unit: 'px' },
				left: { value: 0, unit: 'px' },
				right: { value: 0, unit: 'px' }
			};
		}

		const parts = valuesAttr.split( ' ' );
		return {
			vertical: this.parseValueUnit( parts[ 0 ] || '0px' ),
			top: this.parseValueUnit( parts[ 0 ] || '0px' ),
			bottom: this.parseValueUnit( parts[ 2 ] || parts[ 0 ] || '0px' ),
			horizontal: this.parseValueUnit( parts[ 1 ] || '0px' ),
			left: this.parseValueUnit( parts[ 3 ] || parts[ 1 ] || '0px' ),
			right: this.parseValueUnit( parts[ 1 ] || '0px' )
		};
	}

	parseValueUnit( valueStr ) {
		const defaultValue = { value: 0, unit: 'px' };

		if ( ! valueStr ) {
			return defaultValue;
		}

		const match = valueStr.match( /^(\d+)(px|em|%)?$/ );
		if ( ! match ) {
			return defaultValue;
		}
		return {
			value: parseInt( match[ 1 ], 10 ),
			unit: match[ 2 ] || 'px'
		};
	}

	/**
	 * A method to check if the slider component has multiple values. It will check the data-has-multiple-values attribute first and if it is not set, it will return the default value.
	 *
	 * @return {boolean} - The has multiple values flag.
	 */
	hasMultipleSliderValues() {
		return this.getAttribute( 'data-has-multiple-values' ) === 'true' || this._hasMultipleValues;
	}

	createMultipleValuesSlider( wrapper, options ) {
		const { maxValue, units, componentClass, componentId, fieldName, fieldValue, defaultValues } = options;

		if ( componentClass ) {
			wrapper.className = componentClass;
		}

		// Vertical slider (group)
		wrapper.appendChild( this.createSliderGroup( {
			type: 'vertical',
			displaySliders: 'top,bottom',
			maxValue,
			units,
			value: defaultValues.vertical,
			iconSvgId: 'frm-margin-top-bottom',
			ariaLabel: 'Vertical value',
			defaultValues: defaultValues,
			addHiddenInputValue: false
		} ) );

		// Horizontal slider (group)
		wrapper.appendChild( this.createSliderGroup( {
			type: 'horizontal',
			displaySliders: 'left,right',
			maxValue,
			units,
			value: defaultValues.horizontal,
			iconSvgId: 'frm-margin-left-right',
			ariaLabel: 'Horizontal value',
			defaultValues: defaultValues,
			addHiddenInputValue: false
		} ) );

		wrapper.appendChild( this.createSliderHiddenInputValue( { fieldName, fieldValue, componentId } ) );
	}

	/**
	 * A method to create the hidden input value for the slider component. This hidden input value is used to store the value of the slider component.
	 *
	 * @param {Object} options - The options for the slider.
	 * @return {Element} - The hidden input value element.
	 */
	createSliderHiddenInputValue( options ) {
		const { fieldName, fieldValue, componentId } = options;
		const hiddenInput = document.createElement( 'input' );
		hiddenInput.type = 'hidden';
		hiddenInput.value = fieldValue;

		if ( fieldName ) {
			hiddenInput.setAttribute( 'name', fieldName );
		}

		if ( componentId ) {
			hiddenInput.id = componentId;
		}

		if ( this._onChange ) {
			hiddenInput.addEventListener( 'change', () => {
				this._onChange( hiddenInput.value );
			} );
		}

		return hiddenInput;
	}

	/**
	 * A method to create the slider group. This method is used to create the slider group.
	 *
	 * @param {Object} options - The options for the slider.
	 * @return {Element} - The slider group element.
	 */
	createSliderGroup( options ) {
		const slider = this.createSlider( options );
		slider.classList.add( 'frm-group-sliders', 'frm-has-multiple-values' );
		slider.setAttribute( 'data-display-sliders', options.displaySliders );

		const slidersGroupItems = options.displaySliders.split( ',' );
		slidersGroupItems.forEach( item => {
			slider.appendChild( this.createSlider( {
				type: item,
				maxValue: options.maxValue,
				units: options.units,
				value: options.defaultValues[ item ],
				iconSvgId: `frm-margin-${ item }`,
				ariaLabel: `${ item } value`,
				hidden: true,
				addHiddenInputValue: false
			} ) );
		} );

		return slider;
	}

	/**
	 * A method to create the slider. This method is used to create the slider.
	 *
	 * @param {Object} options - The options for the slider.
	 * @return {Element} - The slider element.
	 */
	createSlider( options ) {
		const { type, maxValue, units, value, iconSvgId, ariaLabel, hidden, addHiddenInputValue } = options;

		const sliderWrapper = document.createElement( 'div' );
		sliderWrapper.classList.add( 'frm-slider-component' );
		sliderWrapper.setAttribute( 'data-max-value', maxValue.toString() );

		if ( hidden ) {
			sliderWrapper.classList.add( 'frm_hidden' );
		}

		if ( type ) {
			sliderWrapper.setAttribute( 'data-type', type );
		}

		const flexContainer = document.createElement( 'div' );
		flexContainer.classList.add( 'frm-flex-justify' );

		// Slider container
		const sliderContainer = document.createElement( 'div' );
		sliderContainer.classList.add( 'frm-slider-container' );

		// Icon
		if ( iconSvgId ) {
			const svg = document.createElementNS( 'http://www.w3.org/2000/svg', 'svg' );
			svg.classList.add( 'frmsvg' );

			const use = document.createElementNS( 'http://www.w3.org/2000/svg', 'use' );
			use.setAttributeNS( 'http://www.w3.org/1999/xlink', 'xlink:href', `#${ iconSvgId }` );
			svg.appendChild( use );

			sliderContainer.appendChild( svg );
		}

		// Slider track
		const slider = document.createElement( 'span' );
		slider.classList.add( 'frm-slider' );
		slider.setAttribute( 'tabindex', '0' );

		const activeTrack = document.createElement( 'span' );
		activeTrack.classList.add( 'frm-slider-active-track' );

		const bullet = document.createElement( 'span' );
		bullet.classList.add( 'frm-slider-bullet' );

		const valueLabel = document.createElement( 'span' );
		valueLabel.classList.add( 'frm-slider-value-label' );
		valueLabel.textContent = value.value.toString();

		bullet.appendChild( valueLabel );
		activeTrack.appendChild( bullet );
		slider.appendChild( activeTrack );
		sliderContainer.appendChild( slider );
		flexContainer.appendChild( sliderContainer );

		// Value input and unit select
		const valueContainer = document.createElement( 'div' );
		valueContainer.classList.add( 'frm-slider-value' );

		const valueInput = document.createElement( 'input' );
		valueInput.type = 'text';
		valueInput.setAttribute( 'aria-label', ariaLabel );
		valueInput.value = value.value.toString();

		const unitSelect = document.createElement( 'select' );
		unitSelect.setAttribute( 'aria-label', __( 'Value unit', 'formidable' ) );
		units.forEach( unit => {
			const option = document.createElement( 'option' );
			option.value = unit;
			option.textContent = unit;
			if ( value.unit === unit ) {
				option.selected = true;
			}
			unitSelect.appendChild( option );
		} );

		valueContainer.appendChild( valueInput );
		valueContainer.appendChild( unitSelect );

		if ( addHiddenInputValue ) {
			valueContainer.appendChild( this.createSliderHiddenInputValue( options ) );
		}

		flexContainer.appendChild( valueContainer );

		sliderWrapper.appendChild( flexContainer );
		return sliderWrapper;
	}

	afterViewInit() {
		const defaultValues = this.hasMultipleSliderValues() ? this.parseDefaultValues() : this.parseValueUnit( this.defaultValue );
		new frmSliderComponent( this.wrapper.querySelectorAll( '.frm-slider-component' ), { defaultValues } );
	}
}

