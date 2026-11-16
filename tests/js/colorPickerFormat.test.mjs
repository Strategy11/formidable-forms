import assert from 'node:assert/strict';
import { test } from 'node:test';
import { formatColor } from '../../js/src/color-picker/format-color.js';

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
	assert.equal( formatColor( '#808080', 'hsl(0, 0%, 0%)' ), 'hsl(0, 0%, 50%)' );
	assert.equal( formatColor( '#ff00ff80', 'hsl(0, 0%, 0%)' ), 'hsla(300, 100%, 50%, 0.502)' );
} );
