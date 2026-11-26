/* oxlint-disable import/no-nodejs-modules -- Tests run with the Node test runner. */
import assert from 'node:assert/strict';
import { test } from 'node:test';

let initialization = 0;

/**
 * Capture the appearance sent to Stripe by running the real Link initialization.
 *
 * @param {string}        color       Field color to convert.
 * @param {Array<string>} backgrounds Ancestor backgrounds, nearest first.
 * @return {Promise<string>} Background color sent to Stripe.
 */
async function getStripeColor( color, backgrounds = [] ) {
	let appearance;
	const events = [];
	const captured = new Error( 'Stripe appearance captured' );
	const card = {
		parentElement: backgrounds.reduceRight( ( parentElement, backgroundColor ) => ( { parentElement, backgroundColor } ), null )
	};
	const form = { querySelector: () => ( { value: '1' } ) };
	const document = {
		getElementsByClassName: () => [ card ],
		querySelector: selector => '.frm-card-element' === selector ? card : form
	};
	const selection = {
		ready: callback => callback(),
		find: () => selection,
		not: () => selection,
		attr: () => selection
	};
	const Stripe = () => ( {
		elements: options => {
			( { appearance } = options );
			// Stop at the Stripe API boundary before mounting unrelated iframe controls.
			throw captured;
		}
	} );
	const globals = {
		document,
		window: {
			location: { protocol: 'https:' },
			getComputedStyle: element => element
		},
		jQuery: () => selection,
		Stripe,
		frmFrontForm: { triggerCustomEvent: ( ...args ) => events.push( args ) },
		frm_stripe_vars: {
			appearanceRules: {
				'.Input': { color, backgroundColor: color },
				'.Input:focus': { borderColor: color }
			}
		}
	};
	const previous = new Map( Object.keys( globals ).map( name => [ name, Object.getOwnPropertyDescriptor( globalThis, name ) ] ) );
	Object.assign( globalThis, globals );
	try {
		await assert.rejects( import( `../../stripe/js/frmstrp.js?test=${ initialization++ }` ), error => error === captured );
	} finally {
		for ( const [ name, descriptor ] of previous ) {
			if ( descriptor ) {
				Object.defineProperty( globalThis, name, descriptor );
			} else {
				delete globalThis[ name ];
			}
		}
	}
	assert.equal( events[ 0 ][ 1 ], 'frmStripeLiteDisableSubmit' );
	assert.equal( appearance.variables.colorText, appearance.variables.colorBackground );
	if ( color ) {
		assert.equal( appearance.variables.colorPrimary, appearance.variables.colorBackground );
	} else {
		assert.equal( Object.prototype.hasOwnProperty.call( appearance.variables, 'colorPrimary' ), false );
	}
	return appearance.variables.colorBackground;
}

/**
 * Convert a field color through the real Stripe appearance initialization.
 *
 * @param {string}        color    Field color.
 * @param {Array<number>} backdrop RGB backdrop channels.
 * @return {Promise<string>} Color sent to Stripe.
 */
async function maybeAdjustColorForStripe( color, backdrop = [ 255, 255, 255 ] ) {
	return await getStripeColor( color, [ `rgb(${ backdrop.join( ',' ) })` ] );
}

test( 'Stripe blends numeric and percentage alpha over the supplied backdrop', async () => {
	for ( const alpha of [ '0.5', '50%' ] ) {
		assert.equal( await maybeAdjustColorForStripe( `rgba(255, 0, 0, ${ alpha })`, [ 0, 0, 255 ] ), '#800080' );
	}
	assert.equal( await maybeAdjustColorForStripe( 'rgba(255,255,255,0.1)', [ 17, 24, 39 ] ), '#292f3d' );
	assert.equal( await maybeAdjustColorForStripe( 'rgba(0,0,0,0.5)' ), '#808080' );
	assert.equal( await maybeAdjustColorForStripe( 'rgba(255 0 0 / 50%)', [ 0, 0, 255 ] ), '#800080' );
} );

test( 'Stripe preserves opaque colors and clamps alpha at its boundaries', async () => {
	for ( const color of [ '#ff8000', 'rgb(255, 128, 0)', '' ] ) {
		assert.equal( await maybeAdjustColorForStripe( color ), color );
	}
	for ( const alpha of [ '1', '100%', '150%', 'invalid', '' ] ) {
		assert.equal( await maybeAdjustColorForStripe( `rgba(255,0,0,${ alpha })` ), '#ff0000' );
	}
	for ( const alpha of [ '0', '0%', '-10%' ] ) {
		assert.equal( await maybeAdjustColorForStripe( `rgba(255,0,0,${ alpha })`, [ 17, 24, 39 ] ), '#111827' );
	}
} );

test( 'Stripe normalizes percentage and fractional RGB channels before blending', async () => {
	assert.equal( await maybeAdjustColorForStripe( 'rgba(100%,0%,0%,50%)', [ 0, 0, 255 ] ), '#800080' );
	assert.equal( await maybeAdjustColorForStripe( 'rgba(50%,50%,50%,1)' ), '#808080' );
	assert.equal( await maybeAdjustColorForStripe( 'rgba(127.6,0,0,1)' ), '#800000' );
	assert.equal( await maybeAdjustColorForStripe( 'rgba(300,-5,300,0.5)' ), '#ff80ff' );
} );

test( 'Stripe finds the first solid RGB backdrop and falls back to white', async () => {
	assert.equal( await getStripeColor( 'rgba(0,0,0,0)', [ 'rgba(0,0,0,0)', 'rgba(255,0,0,0.5)', 'rgb(17,24,39)', 'rgb(255,255,255)' ] ), '#111827' );
	assert.equal( await getStripeColor( 'rgba(0,0,0,0)' ), '#ffffff' );
	assert.equal( await getStripeColor( 'rgba(0,0,0,0)', [ 'rgba(17,24,39,1)' ] ), '#111827' );
} );

test( 'Stripe normalizes solid sRGB and percentage backgrounds before blending', async () => {
	for ( const color of [ 'color(srgb 1 1 1)', 'color(srgb 1 1 1 / 100%)', 'rgb(100% 100% 100% / 100%)' ] ) {
		assert.equal( await getStripeColor( 'rgba(255,255,255,50%)', [ color ] ), '#ffffff' );
	}
	assert.equal( await getStripeColor( 'rgba(0,0,0,0)', [ 'color(srgb 0.2 0.4 0.6)' ] ), '#336699' );
} );

test( 'Stripe skips translucent and unsupported modern background formats', async () => {
	for ( const color of [ 'color(srgb 1 1 1 / 50%)', 'lab(100 0 0)', 'oklch(1 0 0)', 'color(display-p3 1 1 1)' ] ) {
		assert.equal( await getStripeColor( 'rgba(0,0,0,0)', [ color, 'rgb(17,24,39)' ] ), '#111827', color );
	}
} );
