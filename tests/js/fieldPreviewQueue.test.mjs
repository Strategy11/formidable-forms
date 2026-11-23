import assert from 'node:assert/strict';
import { test } from 'node:test';
import { queueFieldPreview, renderFieldPreviewNow, forgetFieldPreview, setInitialFieldPreviews } from '../../js/src/admin/fieldPreviewQueue.js';

const frames = [];
const root = {};
let observer;
globalThis.document = { getElementById: () => root };
globalThis.requestAnimationFrame = callback => frames.push( callback );
globalThis.IntersectionObserver = class {
	constructor( callback, options ) {
		this.callback = callback;
		this.options = options;
		this.observed = new Set();
		observer = this;
	}
	observe( target ) {
		this.observed.add( target );
	}
	unobserve( target ) {
		this.observed.delete( target );
	}
};

async function paint() {
	while ( frames.length ) {
		frames.shift()();
		await new Promise( resolve => setTimeout( resolve, 0 ) );
	}
}

test( 'offscreen previews stay in memory and visible previews initialize together once', async () => {
	const near = { dataset: { fid: '1' }, isConnected: true };
	const far = { dataset: { fid: '2' }, isConnected: true };
	const drawn = [];
	const initialized = [];
	const initialize = fields => initialized.push( fields );
	queueFieldPreview( near, () => {
		drawn.push( 'near' ); return near;
	}, initialize );
	queueFieldPreview( far, () => {
		drawn.push( 'far' ); return far;
	}, initialize );
	await paint();
	assert.deepEqual( drawn, [] );
	assert.equal( observer.options.root, root );
	observer.callback( [ { target: near, isIntersecting: true }, { target: far, isIntersecting: false } ] );
	await paint();
	assert.deepEqual( drawn, [ 'near' ] );
	assert.deepEqual( initialized, [ [ near ] ] );
	assert.equal( observer.observed.has( far ), true );
	observer.callback( [ { target: near, isIntersecting: true } ] );
	await paint();
	assert.deepEqual( drawn, [ 'near' ] );
	assert.equal( renderFieldPreviewNow( 2 ), far );
	assert.deepEqual( drawn, [ 'near', 'far' ] );
	assert.equal( renderFieldPreviewNow( '2' ), null );
} );

test( 'leaving the viewport before a slice and deleting a queued field prevent rendering', async () => {
	const field = { dataset: { fid: '3' }, isConnected: true };
	let calls = 0;
	queueFieldPreview( field, () => {
		calls++; return field;
	}, () => {
		assert.fail( 'A cancelled preview must not initialize controls.' );
	} );
	observer.callback( [ { target: field, isIntersecting: true } ] );
	observer.callback( [ { target: field, isIntersecting: false } ] );
	await paint();
	assert.equal( calls, 0 );
	forgetFieldPreview( '3' );
	assert.equal( observer.observed.has( field ), false );
	assert.equal( renderFieldPreviewNow( '3' ), null );
	assert.equal( calls, 0 );
} );

test( 'detached placeholders never recreate deleted previews or initialize controls', async () => {
	const field = { dataset: { fid: '4' }, isConnected: false };
	let calls = 0;
	queueFieldPreview( field, () => {
		calls++; return field;
	}, () => {
		calls++;
	} );
	observer.callback( [ { target: field, isIntersecting: true } ] );
	await paint();
	assert.equal( calls, 0 );
	assert.equal( observer.observed.has( field ), false );
} );

test( 'forms below forty fields render every queued preview without viewport notifications', async () => {
	const fields = Array.from( { length: 39 }, ( _, index ) => ( {
		dataset: { fid: String( 100 + index ) }, isConnected: true
	} ) );
	setInitialFieldPreviews( fields );
	const drawn = [];
	const initialize = fields => assert.ok( fields.length > 0 );
	fields.forEach( field => queueFieldPreview( field, () => {
		drawn.push( field ); return field;
	}, initialize ) );
	await paint();
	assert.deepEqual( drawn, fields );
} );

test( 'forms at forty fields eagerly draw the first fifteen positions, including already rendered fields', async () => {
	const fields = Array.from( { length: 40 }, ( _, index ) => ( {
		dataset: { fid: String( 200 + index ) }, isConnected: true
	} ) );
	setInitialFieldPreviews( fields );
	const drawn = [];
	const initialize = fields => assert.ok( fields.length > 0 );
	// The first four positions already have PHP previews, so only queue the remaining fields.
	fields.slice( 4 ).forEach( field => queueFieldPreview( field, () => {
		drawn.push( field ); return field;
	}, initialize ) );
	await paint();
	assert.deepEqual( drawn, fields.slice( 4, 15 ) );
	assert.equal( observer.observed.has( fields[ 15 ] ), true );
	observer.callback( [ { target: fields[ 15 ], isIntersecting: true } ] );
	await paint();
	assert.equal( drawn.at( -1 ), fields[ 15 ] );
	fields.forEach( field => forgetFieldPreview( field.dataset.fid ) );
	setInitialFieldPreviews( [] );
} );
