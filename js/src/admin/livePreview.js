/* global frmDom */

/**
 * Declarative builder preview updates from settings with a data-frmpreview attribute.
 *
 * This is a sibling of the data-changeme handling in admin.js. New modes go here, never on
 * data-changeme, because older Lite versions misbehave on data-changeme values they do not know.
 *
 * @since x.x
 */

const INPUT_DELAY = 150;
const TRUNCATE_MIN_WORDS = 3;
const TRUNCATE_SHORT_LENGTH = 10;
const TRUNCATE_NO_SPACE_ALLOWANCE = 50;
const ELLIPSIS = '...';
const BARE_NUMBER = /^-?(?:\d+(?:\.\d*)?|\.\d+)$/;
const PREVIEW_INPUT_SELECTOR = '[data-frmpreview]';
const UNIT_INPUT_SELECTOR = '.frm-unit-input';

/**
 * Get the setting key and field ID from a setting name like field_options[size_12].
 *
 * @since x.x
 *
 * @param {HTMLElement|null} input The setting input.
 * @return {{key: string, fieldId: string}|null} The parsed name, or null when it is not a field setting.
 */
export function getSettingKey( input ) {
	const match = /^field_options\[(.+)_(\d+)\]$/.exec( input?.name || '' );
	return match ? { key: match[ 1 ], fieldId: match[ 2 ] } : null;
}

/**
 * Shorten text the way FrmAppHelper::truncate does with its default arguments.
 *
 * @since x.x
 *
 * @param {string} text   The text to shorten.
 * @param {number} length The length to stop at.
 * @return {string} The shortened text, with ... when anything was cut.
 */
export function truncateText( text, length ) {
	if ( length <= 0 ) {
		return '';
	}

	const originalLength = text.length;
	if ( length <= TRUNCATE_SHORT_LENGTH ) {
		return text.slice( 0, length ) + ( length < originalLength ? ELLIPSIS : '' );
	}

	let sub = '';
	let subLength = 0;

	for ( const word of text.split( ' ' ) ) {
		const part = ( '' !== sub ? ' ' : '' ) + word;
		const totalLength = ( sub + part ).length;

		if ( totalLength > length && sub.includes( ' ' ) ) {
			break;
		}

		sub += part;
		subLength += part.length;

		if ( sub.split( ' ' ).length - 1 > TRUNCATE_MIN_WORDS && totalLength >= length ) {
			break;
		}
	}

	// Text without spaces is cut when it runs far over the limit.
	if ( sub.length >= length + TRUNCATE_NO_SPACE_ALLOWANCE && ! sub.includes( ' ', length ) ) {
		sub = sub.slice( 0, length + TRUNCATE_SHORT_LENGTH );
	}

	return sub + ( subLength < originalLength ? ELLIPSIS : '' );
}

/**
 * Keep a numeric value inside the input's own min and max attributes.
 *
 * @since x.x
 *
 * @param {HTMLElement} input The setting input.
 * @param {*}           value The value to check.
 * @return {*} The value, clamped when it is numeric and outside the range.
 */
export function clampToInputRange( input, value ) {
	if ( 'string' !== typeof value || ! BARE_NUMBER.test( value.trim() ) ) {
		return value;
	}

	const number = Number( value );
	const min = getNumericAttribute( input, 'min' );
	const max = getNumericAttribute( input, 'max' );

	if ( null !== min && number < min ) {
		return String( min );
	}
	if ( null !== max && number > max ) {
		return String( max );
	}
	return value;
}

/**
 * @since x.x
 *
 * @param {HTMLElement} input The setting input.
 * @param {string}      name  The attribute name.
 * @return {number|null} The attribute as a number, or null when it is missing or not a number.
 */
function getNumericAttribute( input, name ) {
	const attribute = input.getAttribute( name );
	if ( null === attribute || '' === attribute.trim() || Number.isNaN( Number( attribute ) ) ) {
		return null;
	}
	return Number( attribute );
}

/**
 * Get the input that holds the data-frmpreview attribute for an event target.
 * A unit input keeps the attribute on its hidden input, and the visible controls change it.
 *
 * @since x.x
 *
 * @param {EventTarget} target The event target.
 * @return {HTMLElement|null} The preview input.
 */
function getPreviewInput( target ) {
	if ( ! target?.matches ) {
		return null;
	}
	if ( target.matches( PREVIEW_INPUT_SELECTOR ) ) {
		return target;
	}
	return target.closest( UNIT_INPUT_SELECTOR )?.querySelector( `:scope > ${ PREVIEW_INPUT_SELECTOR }` ) || null;
}

/**
 * Get the current value of a preview input.
 *
 * @since x.x
 *
 * @param {HTMLElement} input The preview input.
 * @return {boolean|string} True or false for a checkbox, otherwise the value.
 */
function getPreviewValue( input ) {
	if ( 'checkbox' === input.type ) {
		return input.checked;
	}

	const unitInput = input.closest( UNIT_INPUT_SELECTOR );
	if ( unitInput && 'hidden' === input.type ) {
		// Read the controls, since the hidden input may not be updated yet.
		const amount = unitInput.querySelector( '.frm-unit-input-control' )?.value.trim() ?? '';
		const unit = unitInput.querySelector( 'select' )?.value ?? '';
		return '' !== amount ? amount + unit : '';
	}

	return clampToInputRange( input, input.value );
}

/**
 * @since x.x
 *
 * @param {boolean|string} value The preview value.
 * @return {boolean} True when the value is checked or not empty.
 */
function isOn( value ) {
	return 'boolean' === typeof value ? value : '' !== value;
}

/**
 * @since x.x
 *
 * @param {boolean|string} value The preview value.
 * @return {string} The value as text.
 */
function asText( value ) {
	return 'boolean' === typeof value ? ( value ? '1' : '' ) : value;
}

/**
 * @since x.x
 *
 * @param {HTMLElement} target The element to style.
 * @param {string}      prop   The CSS property, in camel case, kebab case, or a custom property.
 * @param {string}      value  The CSS value, or an empty string to remove it.
 * @return {void}
 */
function setStyle( target, prop, value ) {
	if ( prop.includes( '-' ) ) {
		if ( '' === value ) {
			target.style.removeProperty( prop );
		} else {
			target.style.setProperty( prop, value );
		}
		return;
	}
	target.style[ prop ] = value;
}

/**
 * @since x.x
 *
 * @param {HTMLElement}    input   The preview input.
 * @param {HTMLElement[]}  targets The preview elements.
 * @param {boolean|string} value   The preview value.
 * @return {void}
 */
function swapClasses( input, targets, value ) {
	const classes = ( input.dataset.frmpreviewClasses || '' ).split( /\s+/ ).filter( Boolean );
	const prefix = input.dataset.frmpreviewPrefix || '';
	const text = asText( value );
	const newClass = prefix + text;
	const toRemove = new Set( classes );
	if ( prefix ) {
		// The set may list full class names or bare values.
		classes.forEach( className => toRemove.add( prefix + className ) );
	}
	const known = '' !== text && ( classes.includes( newClass ) || classes.includes( text ) );

	targets.forEach( target => {
		target.classList.remove( ...toRemove );
		if ( known ) {
			target.classList.add( newClass );
		}
	} );
}

/**
 * Apply one preview mode to the targets.
 *
 * @since x.x
 *
 * @param {string}         mode    The mode, like text or style:width:px.
 * @param {HTMLElement}    input   The preview input.
 * @param {HTMLElement[]}  targets The preview elements.
 * @param {boolean|string} value   The preview value.
 * @param {Object}         helpers Builder helpers.
 * @return {void}
 */
function applyMode( mode, input, targets, value, helpers ) {
	const [ name, ...args ] = mode.split( ':' );
	const text = asText( value );
	const on = isOn( value );

	switch ( name ) {
		case 'text': {
			let content = '' === text ? input.dataset.frmpreviewFallback ?? '' : text;
			const max = parseInt( input.dataset.frmpreviewMax, 10 );
			if ( max > 0 ) {
				content = truncateText( content, max );
			}
			targets.forEach( target => {
				target.textContent = content;
			} );
			break;
		}
		case 'attr':
			targets.forEach( target => {
				if ( '' === text ) {
					target.removeAttribute( args[ 0 ] );
				} else {
					target.setAttribute( args[ 0 ], 'boolean' === typeof value ? input.value : text );
				}
			} );
			break;
		case 'bool-attr':
			targets.forEach( target => target.toggleAttribute( args[ 0 ], on ) );
			break;
		case 'style': {
			// The property can be a custom property, which starts with --, so rejoin the parts.
			const unit = args.length > 1 ? args.pop() : '';
			const prop = args.join( ':' );
			const cssValue = '' !== text && BARE_NUMBER.test( text ) ? text + unit : text;
			targets.forEach( target => setStyle( target, prop, cssValue ) );
			break;
		}
		case 'toggle':
		case 'toggle-inverse': {
			const add = 'toggle' === name ? on : ! on;
			targets.forEach( target => target.classList.toggle( args[ 0 ], add ) );
			break;
		}
		case 'hide-empty': {
			const empty = ! on || '0' === text;
			targets.forEach( target => target.classList.toggle( 'frm_hidden', empty ) );
			break;
		}
		case 'class-swap':
			swapClasses( input, targets, value );
			break;
		case 'server': {
			const fieldId = input.closest( '.frm-single-settings' )?.dataset.fid || getSettingKey( input )?.fieldId;
			if ( fieldId ) {
				helpers.refreshFieldPreview( fieldId );
			}
			break;
		}
		default:
			break;
	}
}

/**
 * Get the elements a preview input updates.
 *
 * @since x.x
 *
 * @param {HTMLElement} input The preview input.
 * @return {HTMLElement[]} The matching elements.
 */
function getPreviewTargets( input ) {
	const selector = input.dataset.frmpreview;
	if ( ! selector ) {
		return [];
	}
	try {
		return Array.from( document.querySelectorAll( selector ) );
	} catch ( error ) {
		console.error( `Invalid data-frmpreview selector: ${ selector }`, error );
		return [];
	}
}

/**
 * Update the preview elements of a data-frmpreview input.
 *
 * @since x.x
 *
 * @param {HTMLElement} input   The preview input.
 * @param {Object}      helpers Builder helpers, with refreshFieldPreview.
 * @return {void}
 */
export function applyLivePreview( input, helpers ) {
	const targets = getPreviewTargets( input );
	const value = getPreviewValue( input );
	const modes = ( input.dataset.frmpreviewMode || '' ).split( ',' ).map( mode => mode.trim() ).filter( Boolean );

	modes.forEach( mode => applyMode( mode, input, targets, value, helpers ) );

	/**
	 * Fires after a setting updated the builder preview. It fires for data-changeme settings too.
	 *
	 * @since x.x
	 *
	 * @param {HTMLElement}   input   The setting input.
	 * @param {HTMLElement[]} targets The updated preview elements.
	 */
	wp.hooks.doAction( 'frm_after_live_change', input, targets );
}

/**
 * Listen for changes to data-frmpreview inputs.
 *
 * @since x.x
 *
 * @param {Object}   helpers                     Builder helpers.
 * @param {Function} helpers.refreshFieldPreview Re-renders a field preview on the server.
 * @return {void}
 */
export function initLivePreview( helpers ) {
	const apply = input => applyLivePreview( input, helpers );
	const debouncedApply = frmDom.util.debounceByKey( apply, INPUT_DELAY );

	document.addEventListener( 'input', event => {
		const input = getPreviewInput( event.target );
		if ( input ) {
			debouncedApply( input );
		}
	} );

	document.addEventListener( 'change', event => {
		const input = getPreviewInput( event.target );
		if ( ! input ) {
			return;
		}
		// The change applies the final value, so a preview still waiting from typing must not run after it.
		debouncedApply.cancel( input );
		apply( input );
	} );
}
