import { frmWebComponent } from '../frm-web-component';
import frmSliderComponent from '../../settings-components/components/slider-component.js';
import style from './frm-range-slider-component.css';
import { __ } from '@wordpress/i18n';

export class frmRangeSliderComponent extends frmWebComponent {
	#onChange = () => {};  
	#sliderDefaultValue = '0px';
	#sliderDefaultMultipleValues = {
		vertical: { value: 0, unit: 'px' },
		top: { value: 0, unit: 'px' },
		bottom: { value: 0, unit: 'px' },
		horizontal: { value: 0, unit: 'px' },
		left: { value: 0, unit: 'px' },
		right: { value: 0, unit: 'px' }
	};
	#hasMultipleValues = false;
	#sliderAvailableUnits = [ 'px', 'em', '%' ];
	#sliderMaxValue = 100;
	#sliderSteps = null;
	static #instanceCount = 0;

	constructor() {
		super();
		this.componentStyle = style;
	}

	initOptions() {
		super.initOptions();
		if ( null === this.componentId ) {
			this.componentId = `frm-range-slider-web-component-${ ++frmRangeSliderComponent.#instanceCount }`;
		}
	}

	/**
	 * A method to set the change event listener for the slider component.
	 *
	 * @param {Function} callback - The callback function to call when the slider component is changed.
	 * @return {void}
	 */
	set onChange( callback ) {  
		if ( 'function' !== typeof callback ) {
			throw new TypeError( `Expected a function, but received ${ typeof callback }` );
		}

		this.#onChange = callback;
	}

	/**
	 * A method to set the has multiple values flag. This flag is used to determine if the slider component should display multiple values.
	 *
	 * @param {boolean} value - The value to set.
	 * @return {void}
	 */
	set hasMultipleValues( value ) {  
		this.#hasMultipleValues = value;
	}

	/**
	 * A method to set the default multiple values. This values are used to determine the default values for the slider component.
	 *
	 * @param {Object} value - The value to set.
	 * @return {void}
	 */
	set sliderDefaultMultipleValues( value ) {  
		this.#sliderDefaultMultipleValues = value;
	}

	/**
	 * A method to set the default value for the single slider component. This value is used to determine the default value for the single slider component.
	 *
	 * @param {string|number} value - The value to set.
	 * @return {void}
	 */
	set sliderDefaultValue( value ) {  
		this.#sliderDefaultValue = String( value );
	}

	/**
	 * A method to set the available units for the slider component. This units are used to determine the available units for the slider component.
	 *
	 * @param {Array} value - The value to set.
	 * @return {void}
	 */
	set sliderAvailableUnits( value ) {  
		this.#sliderAvailableUnits = value;
	}

	/**
	 * A method to set the max value that the slider can take.
	 *
	 * @param {string|number} value - The value to set.
	 * @return {void}
	 */
	set sliderMaxValue( value ) {  
		this.#sliderMaxValue = value.toString();
	}

	set steps( value ) {  
		this.#sliderSteps = value;
	}

	useShadowDom() {  
		return false;
	}

	initView() {
		this.wrapper = document.createElement( 'div' );
		this.slidersContainer = document.createElement( 'div' );

		this.slidersContainer.classList.add( 'frm-sliders-container' );
		this.wrapper.classList.add( 'frm-style-component' );

		const config = {
			maxValue: parseInt( this.getAttribute( 'data-max-value' ) || this.#sliderMaxValue, 10 ),
			units: this.getAvailableUnits(),
			componentClass: this.getAttribute( 'data-component-class' ) || '',
			componentId: this.componentId,
			fieldName: this.fieldName ? `name="${ this.fieldName }"` : '',
			fieldValue: this.defaultValue || this.#sliderDefaultValue
		};

		if ( this.hasMultipleSliderValues() ) {
			this.createMultipleValuesSlider( this.slidersContainer, { ...config, defaultValues: this.parseDefaultMultipleValues() } );
			this.wrapper.append( this.slidersContainer );
			return this.wrapper;
		}

		this.slidersContainer.append( this.createSlider( {
			maxValue: config.maxValue,
			units: config.units,
			value: frmRangeSliderComponent.parseValueUnit( config.fieldValue ),
			addHiddenInputValue: true
		} ) );

		this.wrapper.append( this.slidersContainer );
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
			return this.#sliderAvailableUnits;
		}

		return attr.split( ',' ).map( u => u.trim() );
	}

	/**
	 * A method to parse the default values for the multiple values slider component. It will check the data-values attribute first and if it is not set, it will return the default values.
	 * If the values haven't been set via data-values attribute or dynamically via this._sliderDefaultMultipleValues it will return the default values.
	 *
	 * @return {Object} - The default values.
	 */
	parseDefaultMultipleValues() {
		const valuesAttr = this.getAttribute( 'data-values' ) || this.#sliderDefaultMultipleValues;

		const parts = valuesAttr.split( ' ' );
		const getPart = ( id, fallbackId ) => parts[ id ] || parts[ fallbackId ] || '0px';

		return {
			vertical: frmRangeSliderComponent.parseValueUnit( getPart( 0 ) ),
			top: frmRangeSliderComponent.parseValueUnit( getPart( 0 ) ),
			bottom: frmRangeSliderComponent.parseValueUnit( getPart( 2, 0 ) ),
			horizontal: frmRangeSliderComponent.parseValueUnit( getPart( 1, 0 ) ),
			left: frmRangeSliderComponent.parseValueUnit( getPart( 3, 1 ) ),
			right: frmRangeSliderComponent.parseValueUnit( getPart( 1, 0 ) )
		};
	}

	/**
	 * A method to parse the value and unit for the slider component.
	 *
	 * @param {string} valueStr - The value string to parse.
	 * @return {Object} - The value and unit object.
	 */
	static parseValueUnit( valueStr ) {
		const defaultValue = { value: 0, unit: 'px' };

		if ( ! valueStr ) {
			return defaultValue;
		}

		const match = valueStr.match( /^(\d+)(px|em|%|\s)?$/ );
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
		return this.getAttribute( 'data-has-multiple-values' ) === 'true' || this.#hasMultipleValues;
	}

	/**
	 * A method to create the multiple values slider.
	 *
	 * @param {Element} wrapper - The wrapper element.
	 * @param {Object}  options - The options for the slider.
	 * @return {void}
	 */
	createMultipleValuesSlider( wrapper, options ) {
		const { maxValue, units, componentClass, fieldValue, defaultValues } = options;
		const groups = [
			{
				type: 'vertical',
				displaySliders: 'top,bottom',
				iconSvgId: 'frm-margin-top-bottom',
				ariaLabel: 'Vertical value',
				defaultValues: defaultValues.vertical,
			},
			{
				type: 'horizontal',
				displaySliders: 'left,right',
				iconSvgId: 'frm-margin-left-right',
				ariaLabel: 'Horizontal value',
				defaultValues: defaultValues.horizontal,
			}
		];

		if ( componentClass ) {
			wrapper.className = componentClass;
		}

		groups.forEach( group => {
			wrapper.append( this.createSliderGroup( {
				type: group.type,
				displaySliders: group.displaySliders,
				maxValue,
				units,
				value: group.defaultValues,
				iconSvgId: group.iconSvgId,
				ariaLabel: group.ariaLabel,
				defaultValues: defaultValues,
				addHiddenInputValue: false
			} ) );
		} );

		if ( fieldValue ) {
			wrapper.append( this.createSliderHiddenInputValue( fieldValue ) );
		}
	}

	/**
	 * A method to create the hidden input value for the slider component. This hidden input value is used to store the value of the slider component.
	 *
	 * @param {string} fieldValue - The field value to set.
	 * @return {Element} - The hidden input value element.
	 */
	createSliderHiddenInputValue( fieldValue ) {
		if ( ! fieldValue ) {
			return null;
		}

		const input = document.createElement( 'input' );
		Object.assign( input, { type: 'hidden', value: fieldValue } );

		if ( this.fieldName ) {
			input.setAttribute( 'name', this.fieldName );
		}

		if ( this.componentId ) {
			input.id = this.componentId;
		}

		if ( this.#onChange ) {
			input.addEventListener( 'change', () => {
				this.#onChange( input.value );
			} );
		}

		return input;
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
			slider.append( this.createSlider( {
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
	 * A method to create the slider track. This method is used to create the slider track.
	 *
	 * @param {Object} value - The value of the slider.
	 * @return {Element} - The slider track element.
	 */
	static createSliderTrack( value ) {
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

		bullet.append( valueLabel );
		activeTrack.append( bullet );
		slider.append( activeTrack );

		return slider;
	}

	/**
	 * A method to create the value and unit selection. This method is used to create the value and unit selection.
	 *
	 * @param {Object} value     - The value of the slider.
	 * @param {string} ariaLabel - The aria label of the slider.
	 * @param {Array}  units     - The units of the slider.
	 * @param {string} baseId    - The base ID for the form elements.
	 * @return {Element} - The value and unit selection element.
	 */
	static createSliderValueAndUnitSelection( value, ariaLabel, units, baseId ) {
		const valueContainer = document.createElement( 'div' );
		valueContainer.classList.add( 'frm-slider-value' );

		const valueInput = document.createElement( 'input' );
		valueInput.type = 'text';
		valueInput.value = value.value.toString();

		if ( ariaLabel ) {
			valueInput.setAttribute( 'aria-label', ariaLabel );
		}

		const unitSelect = document.createElement( 'select' );
		unitSelect.setAttribute( 'aria-label', __( 'Value unit', 'formidable' ) );

		if ( baseId ) {
			valueInput.id = `${ baseId }-value`;
			unitSelect.id = `${ baseId }-unit`;
		}

		units.forEach( unit => unitSelect.append( frmRangeSliderComponent.createDropdownOption( unit, unit, value.unit === unit ) ) );

		valueContainer.append( valueInput, unitSelect );

		return valueContainer;
	}

	/**
	 * A method to create the dropdown option. This method is used to create the dropdown option.
	 *
	 * @param {string}  value    - The value of the option.
	 * @param {string}  label    - The label of the option.
	 * @param {boolean} selected - Whether the option is selected.
	 * @return {Element} - The dropdown option element.
	 */
	static createDropdownOption( value, label, selected = false ) {
		const option = document.createElement( 'option' );
		option.value = value;
		option.textContent = label;
		option.selected = selected;
		return option;
	}

	/**
	 * A method to create the SVG icon. This method is used to create the SVG icon.
	 *
	 * @param {string} iconSvgId - The ID of the SVG icon.
	 * @return {Element} - The SVG icon element.
	 */
	static createSvgIcon( iconSvgId ) {
		const svg = document.createElementNS( 'http://www.w3.org/2000/svg', 'svg' );
		svg.classList.add( 'frmsvg' );

		const use = document.createElementNS( 'http://www.w3.org/2000/svg', 'use' );
		use.setAttribute( 'href', `#${ iconSvgId }` );
		svg.append( use );

		return svg;
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
			sliderContainer.append( frmRangeSliderComponent.createSvgIcon( iconSvgId ) );
		}

		// Slider track
		sliderContainer.append( frmRangeSliderComponent.createSliderTrack( value ) );
		flexContainer.append( sliderContainer );

		// Value input and unit select
		const baseId = `${ this.componentId }${ type ? `-${ type }` : '' }`;
		const valueContainer = frmRangeSliderComponent.createSliderValueAndUnitSelection( value, ariaLabel, units, baseId );

		if ( addHiddenInputValue ) {
			valueContainer.append( this.createSliderHiddenInputValue( options ) );
		}

		flexContainer.append( valueContainer );
		sliderWrapper.append( flexContainer );

		return sliderWrapper;
	}

	afterViewInit() {
		const defaultValues = this.hasMultipleSliderValues() ? this.parseDefaultMultipleValues() : frmRangeSliderComponent.parseValueUnit( this.defaultValue );
		const options = Object.assign( { defaultValues }, { steps: this.#sliderSteps } );
		new frmSliderComponent( this.wrapper.querySelectorAll( '.frm-slider-component' ), options );  
	}
}
