/**
 * Preserve the input's RGB or HSL format when WordPress returns hexadecimal.
 *
 * @since 7.1
 * @param {string} hex            Color returned by WordPress.
 * @param {string} previous       Previously saved color.
 * @param {string} selectedFormat Selected picker format.
 * @return {string} Formatted color.
 */
export function formatColor( hex, previous, selectedFormat ) {
	if ( ! /^#[\da-f]{3,4}$|^#[\da-f]{6}([\da-f]{2})?$/i.test( hex ) ) {
		return previous;
	}
	if ( hex.length < 6 ) {
		hex = `#${ hex.slice( 1 ).replace( /./g, channel => channel + channel ) }`;
	}
	const channels = hex.slice( 1 ).match( /../g ).map( channel => parseInt( channel, 16 ) );
	const [ red, green, blue ] = channels;
	const alpha = channels.length === 4 ? Math.round( channels[ 3 ] / 255 * 1000 ) / 1000 : 1;
	const format = selectedFormat || previous.trim().match( /^(rgba?|hsla?)\(/i )?.[ 1 ].toLowerCase();
	if ( ! format || format === 'hex' ) {
		if ( alpha < 1 && format !== 'hex' ) {
			return `rgba(${ red }, ${ green }, ${ blue }, ${ alpha })`;
		}
		return hex;
	}
	const transparent = alpha < 1 || format.endsWith( 'a' );
	const suffix = transparent ? `, ${ alpha }` : '';
	if ( format.startsWith( 'rgb' ) ) {
		return `rgb${ transparent ? 'a' : '' }(${ red }, ${ green }, ${ blue }${ suffix })`;
	}
	const r = red / 255;
	const g = green / 255;
	const b = blue / 255;
	const max = Math.max( r, g, b );
	const min = Math.min( r, g, b );
	const delta = max - min;
	const lightness = ( max + min ) / 2;
	let hue = 0;
	if ( delta ) {
		if ( max === r ) {
			hue = ( g - b ) / delta;
		} else if ( max === g ) {
			hue = ( ( b - r ) / delta ) + 2;
		} else {
			hue = ( ( r - g ) / delta ) + 4;
		}
	}
	const saturation = delta ? delta / ( 1 - Math.abs( ( 2 * lightness ) - 1 ) ) : 0;
	const h = Math.round( ( ( ( hue * 60 ) + 360 ) % 360 ) * 1000 ) / 1000;
	const s = Math.round( saturation * 100000 ) / 1000;
	const l = Math.round( lightness * 100000 ) / 1000;
	return `hsl${ transparent ? 'a' : '' }(${ h }, ${ s }%, ${ l }%${ suffix })`;
}

/**
 * Check if a value is a hexadecimal color without its leading hash.
 *
 * This matches FrmStylesHelper::is_hex.
 *
 * @since 7.1
 * @param {string} color Color value.
 * @return {boolean} True when the value is a bare hex color.
 */
export function isBareHex( color ) {
	return /^(?:[\da-f]{3,4}|[\da-f]{6}|[\da-f]{8})$/i.test( color );
}

/**
 * Prepend the hash to a bare hex color, like FrmStylesHelper::add_color_hash_if_needed.
 *
 * Style settings save hex colors without the hash.
 *
 * @since 7.1
 * @param {string} color Color value.
 * @return {string} The color, with a hash when it is a bare hex color.
 */
export function addColorHash( color ) {
	const trimmed = color.trim();
	return isBareHex( trimmed ) ? `#${ trimmed }` : trimmed;
}

/**
 * Check if text typed in the picker's hex field is a complete hex color.
 *
 * @since 7.1
 * @param {string} value Hex field text, with or without the hash.
 * @return {boolean} True when the text is a complete hex color.
 */
export function isTypedHexValid( value ) {
	return isBareHex( value.trim().replace( /^#/, '' ) );
}

/**
 * Check if an input in the WordPress color picker is its hex text field.
 *
 * The hex field is a text input limited to 7 or 9 characters. The RGB and HSL fields are number inputs.
 *
 * @since 7.1
 * @param {HTMLInputElement} input An input rendered by the color picker.
 * @return {boolean} True for the hex text field.
 */
export function isHexField( input ) {
	return 'number' !== input.type && [ '7', '9' ].includes( input.getAttribute( 'maxlength' ) );
}

/**
 * Convert a saved picker value to the hexadecimal value WordPress emits.
 *
 * @since 7.1
 * @param {string} color Saved color value.
 * @return {string} Hex color, or an empty string when unsupported.
 */
export function colorToHex( color ) {
	color = addColorHash( color ).toLowerCase();
	if ( color === 'transparent' ) {
		return '#00000000';
	}
	if ( /^#(?:[\da-f]{3,4}|[\da-f]{6}|[\da-f]{8})$/i.test( color ) ) {
		return color;
	}
	const match = color.match( /^(rgb|hsl)a?\(([^()]+)\)$/i );
	if ( ! match ) {
		return '';
	}
	const parts = match[ 2 ].trim().replace( /[,/]/g, ' ' ).split( /\s+/ );
	if ( parts.length < 3 || parts.length > 4 || parts.some( part => ! Number.isFinite( parseFloat( part ) ) ) ) {
		return '';
	}
	const values = parts.map( part => parseFloat( part ) );
	let channels;
	if ( match[ 1 ].toLowerCase() === 'rgb' ) {
		channels = values.slice( 0, 3 ).map( ( value, index ) => parts[ index ].endsWith( '%' ) ? value * 255 / 100 : value );
	} else {
		let [ hue ] = values;
		if ( parts[ 0 ].endsWith( 'grad' ) ) {
			hue *= 0.9;
		} else if ( parts[ 0 ].endsWith( 'rad' ) ) {
			hue *= 180 / Math.PI;
		} else if ( parts[ 0 ].endsWith( 'turn' ) ) {
			hue *= 360;
		}
		hue = ( ( hue % 360 ) + 360 ) % 360;
		const saturation = Math.max( 0, Math.min( 100, values[ 1 ] ) ) / 100;
		const lightness = Math.max( 0, Math.min( 100, values[ 2 ] ) ) / 100;
		const amplitude = saturation * Math.min( lightness, 1 - lightness );
		channels = [ 0, 8, 4 ].map( offset => {
			const position = ( offset + ( hue / 30 ) ) % 12;
			return 255 * ( lightness - ( amplitude * Math.max( -1, Math.min( position - 3, 9 - position, 1 ) ) ) );
		} );
	}
	if ( parts.length === 4 ) {
		channels.push( values[ 3 ] * 255 / ( parts[ 3 ].endsWith( '%' ) ? 100 : 1 ) );
	}
	return `#${ channels.map( value => Math.round( Math.max( 0, Math.min( 255, value ) ) ).toString( 16 ).padStart( 2, '0' ) ).join( '' ) }`;
}
