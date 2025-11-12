import { frmWebComponent } from '../frm-web-component';
import frmSliderComponent from '../../../src/settings-components/components/slider-component.js';
import style from './frm-range-slider-component.css';
import { __ } from '@wordpress/i18n';

export class frmRangeSliderComponent extends frmWebComponent {
	constructor() {
		super();
		this.componentStyle = style;
		this._onChange = null;
	}

	set onChange( callback ) {
		if ( 'function' !== typeof callback ) {
			throw new Error( 'Callback must be a function' );
		}

		this._onChange = callback;
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
		const hasMultipleValues = this.getAttribute( 'data-has-multiple-values' ) === 'true';
		const maxValue = parseInt( this.getAttribute( 'data-max-value' ) || '100', 10 );
		const units = this.parseAttributeArray( 'data-units' );
		const componentClass = this.getAttribute( 'data-component-class' ) || '';
		const componentId = this.componentId;
		const fieldName = this.fieldName ? `name="${ this.fieldName }"` : '';
		const fieldValue = this.defaultValue || '';

		// Parse values from data attribute
		const values = this.parseValues();

		this.createMultipleValuesSlider( this.slidersContainer, {
			maxValue,
			units,
			componentClass,
			componentId,
			fieldName,
			fieldValue,
			values
		} );

		// Top slider (hidden)
		this.slidersContainer.appendChild( this.createSlider( {
			type: 'top',
			maxValue,
			units,
			value: values.top,
			iconSvgId: 'frm-margin-top',
			ariaLabel: 'Top value',
			hidden: true
		} ) );

		// Bottom slider (hidden)
		this.slidersContainer.appendChild( this.createSlider( {
			type: 'bottom',
			maxValue,
			units,
			value: values.bottom,
			iconSvgId: 'frm-margin-bottom',
			ariaLabel: 'Bottom value',
			hidden: true
		} ) );

		// Horizontal slider (group)
		this.slidersContainer.appendChild( this.createSliderGroup( {
			type: 'horizontal',
			displaySliders: 'left,right',
			maxValue,
			units,
			value: values.horizontal,
			iconSvgId: 'frm-margin-left-right',
			ariaLabel: 'Horizontal value'
		} ) );

		// Left slider (hidden)
		this.slidersContainer.appendChild( this.createSlider( {
			type: 'left',
			maxValue,
			units,
			value: values.left,
			iconSvgId: 'frm-margin-left',
			ariaLabel: 'Left value',
			hidden: true
		} ) );

		// Right slider (hidden)
		this.slidersContainer.appendChild( this.createSlider( {
			type: 'right',
			maxValue,
			units,
			value: values.right,
			iconSvgId: 'frm-margin-right',
			ariaLabel: 'Right value',
			hidden: true
		} ) );

		this.wrapper.appendChild( this.slidersContainer );

		return this.wrapper;
	}

	parseAttributeArray( attrName ) {
		const attr = this.getAttribute( attrName );
		if ( ! attr ) {
			return [ '', 'px', 'em', '%' ];
		}
		try {
			return JSON.parse( attr );
		} catch ( e ) {
			return attr.split( ',' ).map( u => u.trim() );
		}
	}

	parseValues() {
		const valuesAttr = this.getAttribute( 'data-values' );
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

		try {
			return JSON.parse( valuesAttr );
		} catch ( e ) {
			const parts = valuesAttr.split( ' ' );
			return {
				vertical: this.parseValueUnit( parts[ 0 ] || '0px' ),
				top: this.parseValueUnit( parts[ 0 ] || '0px' ),
				bottom: this.parseValueUnit( parts[ 2 ] || '0px' ),
				horizontal: this.parseValueUnit( parts[ 1 ] || '0px' ),
				left: this.parseValueUnit( parts[ 3 ] || '0px' ),
				right: this.parseValueUnit( parts[ 1 ] || '0px' )
			};
		}
	}

	parseValueUnit( valueStr ) {
		const match = valueStr.match( /^(\d+)(px|em|%)?$/ );
		if ( ! match ) {
			return { value: 0, unit: 'px' };
		}
		return {
			value: parseInt( match[ 1 ], 10 ),
			unit: match[ 2 ] || 'px'
		};
	}

	createMultipleValuesSlider( wrapper, options ) {
		const { maxValue, units, componentClass, componentId, fieldName, fieldValue, values } = options;

		if ( componentClass ) {
			wrapper.className = componentClass;
		}

		// Vertical slider (group)
		wrapper.appendChild( this.createSliderGroup( {
			type: 'vertical',
			displaySliders: 'top,bottom',
			maxValue,
			units,
			value: values.vertical,
			iconSvgId: 'frm-margin-top-bottom',
			ariaLabel: 'Vertical value'
		} ) );

		const hiddenInput = document.createElement( 'input' );
		hiddenInput.type = 'hidden';
		if ( fieldName ) {
			hiddenInput.setAttribute( 'name', this.fieldName );
		}
		hiddenInput.value = fieldValue;
		if ( componentId ) {
			hiddenInput.id = componentId;
		}
		hiddenInput.addEventListener( 'change', () => {
			this._onChange( hiddenInput.value );
		});
		wrapper.appendChild( hiddenInput );
	}

	createSliderGroup( options ) {
		const slider = this.createSlider( options );
		slider.classList.add( 'frm-group-sliders' );
		slider.setAttribute( 'data-display-sliders', options.displaySliders );
		return slider;
	}

	createSlider( options ) {
		const { type, maxValue, units, value, iconSvgId, ariaLabel, hidden } = options;

		const sliderWrapper = document.createElement( 'div' );
		sliderWrapper.classList.add( 'frm-slider-component', 'frm-has-multiple-values' );
		if ( hidden ) {
			sliderWrapper.classList.add( 'frm_hidden' );
		}
		sliderWrapper.setAttribute( 'data-type', type );
		sliderWrapper.setAttribute( 'data-max-value', maxValue.toString() );

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
		flexContainer.appendChild( valueContainer );

		sliderWrapper.appendChild( flexContainer );
		return sliderWrapper;
	}

	afterViewInit() {
		new frmSliderComponent( this.wrapper.querySelectorAll( '.frm-slider-component' ) );
	}
}

