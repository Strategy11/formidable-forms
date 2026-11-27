import assert from 'node:assert/strict';
import { register } from 'node:module';
import { test } from 'node:test';

// The source imports its siblings without an extension, the way webpack resolves them.
register( `data:text/javascript,${ encodeURIComponent( `
export async function resolve( specifier, context, next ) {
	if ( specifier.startsWith( './' ) && ! /\\.[cm]?js$/.test( specifier ) ) {
		return next( specifier + '.js', context );
	}
	return next( specifier, context );
}
` ) }` );

// Each request waits until the test settles it, so the tests can act while Save is waiting.
const requests = [];
const events = [];

// Stands in for the builder's fieldsAwaitingSettings, which deferSaveForFieldSettings in admin.js passes as is.
const awaitingFieldIds = new Set();

globalThis.window = {};
globalThis.document = {
	getElementById: id => id.startsWith( 'frm_field_id_' ) ? { dataset: { type: 'text' } } : null
};
globalThis.frmDom = {
	ajax: {
		doJsonPost( action, formData ) {
			const fieldIds = formData.getAll( 'field_ids[]' );
			return new Promise( ( resolve, reject ) => requests.push( { fieldIds, resolve, reject } ) );
		}
	}
};

const { queueFieldSettings, whenFieldSettingsLoaded, waitForAwaitedFieldSettings } = await import( '../../js/src/admin/fieldSettingsLoader.js' );

queueFieldSettings( {
	formId: '1',
	fieldIds: [ '30', '31', '32', '33' ],
	// Like handleLoadedFieldSettings, an awaited panel moves into the save form when it arrives.
	onLoaded: fieldIds => fieldIds.forEach( fieldId => {
		if ( awaitingFieldIds.delete( fieldId ) ) {
			events.push( `move ${ fieldId }` );
		}
	} )
} );

// The loader reports failed requests to the console.
console.error = () => {};

function flush() {
	return new Promise( resolve => setTimeout( resolve, 0 ) );
}

function takeRequest( fieldIds ) {
	const index = requests.findIndex( request => request.fieldIds.join( ',' ) === fieldIds.join( ',' ) );
	assert.notEqual( index, -1, `A request for ${ fieldIds.join( ',' ) } was sent.` );
	return requests.splice( index, 1 )[ 0 ];
}

function settle( fieldIds ) {
	takeRequest( fieldIds ).resolve( { settings: Object.fromEntries( fieldIds.map( fieldId => [ fieldId, '<div></div>' ] ) ) } );
	return flush();
}

function fail( fieldIds ) {
	takeRequest( fieldIds ).reject( new Error( 'Network error' ) );
	return flush();
}

// Same as deferSaveForFieldSettings: wait, then save.
function save() {
	return waitForAwaitedFieldSettings( awaitingFieldIds ).then( failedFieldIds => {
		events.push( 'save' );
		return failedFieldIds;
	} );
}

test( 'keeps Save waiting for a field added while it waits', async () => {
	events.length = 0;
	whenFieldSettingsLoaded( [ '30' ], awaitingFieldIds, () => events.push( 'update 30' ) );
	await flush();

	const saved = save();

	// A choice rename reads the logic of another field while Save is waiting.
	whenFieldSettingsLoaded( [ '31' ], awaitingFieldIds, () => events.push( 'update 31' ) );
	await flush();
	assert.equal( awaitingFieldIds.has( '31' ), true );

	await settle( [ '30' ] );
	assert.deepEqual( events, [ 'move 30', 'update 30' ], 'Save still waits for 31.' );
	assert.equal( awaitingFieldIds.has( '31' ), true, 'The field added during the wait is kept.' );

	await settle( [ '31' ] );
	assert.deepEqual( await saved, [] );
	assert.deepEqual( events, [ 'move 30', 'update 30', 'move 31', 'update 31', 'save' ] );
	assert.equal( awaitingFieldIds.size, 0 );
} );

test( 'reports a field that failed in an earlier pass after waiting for a later one', async () => {
	events.length = 0;
	whenFieldSettingsLoaded( [ '32' ], awaitingFieldIds, () => events.push( 'update 32' ) );
	await flush();

	const saved = save();
	whenFieldSettingsLoaded( [ '33' ], awaitingFieldIds, () => events.push( 'update 33' ) );
	await flush();

	await fail( [ '32' ] );
	// Save asks once more for the failed field.
	await fail( [ '32' ] );
	assert.equal( events.includes( 'save' ), false, 'Save still waits for 33.' );

	await settle( [ '33' ] );
	assert.deepEqual( await saved, [ '32' ], 'The failed field reaches the confirmation step.' );
	assert.deepEqual( events, [ 'update 32', 'move 33', 'update 33', 'save' ] );
	assert.equal( awaitingFieldIds.size, 0, 'The confirmation step adds failed fields back when it does not save.' );
} );
