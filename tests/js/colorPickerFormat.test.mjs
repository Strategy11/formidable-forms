import assert from 'node:assert/strict';
import { test } from 'node:test';
import { colorToHex, formatColor } from '../../js/src/color-picker/format-color.js';

test( 'hex stays hex when the previous value is hex or empty', () => {
	assert.equal( formatColor( '#802939', '#1d2939' ), '#802939' );
	assert.equal( formatColor( '#802939', '' ), '#802939' );
} );

test( 'a translucent color from a hex value is saved as rgba', () => {
	assert.equal( formatColor( '#ff000080', '#1d2939' ), 'rgba(255, 0, 0, 0.502)' );
} );

test( 'rgb and rgba formats are preserved', () => {
	assert.equal( formatColor( '#4199fd', 'rgb(0, 0, 0)' ), 'rgb(65, 153, 253)' );
	assert.equal( formatColor( '#4199fd', 'RGBA(0, 0, 0, 0.5)' ), 'rgba(65, 153, 253, 1)' );
	assert.equal( formatColor( '#4199fd80', 'rgb(0, 0, 0)' ), 'rgba(65, 153, 253, 0.502)' );
} );

test( 'hsl and hsla formats are preserved', () => {
	assert.equal( formatColor( '#ff0000', 'hsl(0, 0%, 0%)' ), 'hsl(0, 100%, 50%)' );
	assert.equal( formatColor( '#00ff00', 'hsl(0, 0%, 0%)' ), 'hsl(120, 100%, 50%)' );
	assert.equal( formatColor( '#0000ff', 'hsla(0, 0%, 0%, 1)' ), 'hsla(240, 100%, 50%, 1)' );
	assert.equal( formatColor( '#808080', 'hsl(0, 0%, 0%)' ), 'hsl(0, 0%, 50.196%)' );
	assert.equal( formatColor( '#ff00ff80', 'hsl(0, 0%, 0%)' ), 'hsla(300, 100%, 50%, 0.502)' );
} );

test( 'the selected format overrides the previous setting format', () => {
	assert.equal( formatColor( '#ff8000', '#000000', 'rgb' ), 'rgb(255, 128, 0)' );
	assert.equal( formatColor( '#ff0000', 'rgb(0, 0, 0)', 'hsl' ), 'hsl(0, 100%, 50%)' );
	assert.equal( formatColor( '#ff000080', 'hsla(0, 100%, 50%, 0.5)', 'hex' ), '#ff000080' );
	assert.equal( formatColor( '#ff000080', '#000000', 'hsl' ), 'hsla(0, 100%, 50%, 0.502)' );
} );

test( 'short hex and invalid picker values are handled safely', () => {
	assert.equal( formatColor( '#f80', '', 'rgb' ), 'rgb(255, 136, 0)' );
	assert.equal( formatColor( '#f008', '', 'rgb' ), 'rgba(255, 0, 0, 0.533)' );
	assert.equal( formatColor( '', '#123456', 'rgb' ), '#123456' );
	assert.equal( formatColor( '#nope', '#123456', 'hsl' ), '#123456' );
} );

test( 'switching formats preserves color and alpha', () => {
	for ( const hex of [ '#4199fd', '#ff8000', '#fe0102', '#808080', '#000000', '#ffffff', '#4199fd80', '#4199fd00' ] ) {
		for ( const format of [ 'rgb', 'hsl', 'hex' ] ) {
			const saved = formatColor( hex, '', format );
			assert.equal( colorToHex( saved ).slice( 0, 7 ), hex.slice( 0, 7 ), saved );
			if ( hex.length === 9 ) {
				assert.equal( colorToHex( saved ), hex, saved );
			}
		}
	}
} );

test( 'saved functional colors convert without losing fractional hues or percent alpha', () => {
	assert.equal( colorToHex( 'rgb(100% 50% 0% / 50%)' ), '#ff800080' );
	assert.equal( colorToHex( 'hsl(390deg 100% 50% / 50%)' ), '#ff800080' );
	assert.equal( colorToHex( 'hsl(33.5, 100%, 50%)' ), '#ff8e00' );
	assert.equal( colorToHex( 'HSLA(.5turn, 100%, 50%, 0)' ), '#00ffff00' );
	assert.equal( colorToHex( '' ), '' );
	assert.equal( colorToHex( 'var(--brand)' ), '' );
} );
