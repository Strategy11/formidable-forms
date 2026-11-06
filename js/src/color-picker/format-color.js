/**
 * Preserve the input's RGB or HSL format when WordPress returns hexadecimal.
 *
 * @since x.x
 * @param {string} hex      Color returned by WordPress.
 * @param {string} previous Previously saved color.
 * @return {string} Formatted color.
 */
export function formatColor( hex, previous ) {
	const channels = hex.slice( 1 ).match( /../g ).map( channel => parseInt( channel, 16 ) );
	const [ red, green, blue ] = channels;
	const alpha = channels.length === 4 ? Math.round( channels[ 3 ] / 255 * 1000 ) / 1000 : 1;
	const format = previous.match( /^(rgba?|hsla?)\(/i )?.[ 1 ].toLowerCase();
	if ( ! format ) {
		if ( alpha < 1 ) {
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
	const h = Math.round( ( ( hue * 60 ) + 360 ) % 360 );
	const s = Math.round( saturation * 100 );
	const l = Math.round( lightness * 100 );
	return `hsl${ transparent ? 'a' : '' }(${ h }, ${ s }%, ${ l }%${ suffix })`;
}
