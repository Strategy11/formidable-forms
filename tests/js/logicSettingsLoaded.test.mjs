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

const responses = [];
const requestedFieldIds = [];
const events = [];

// Stands in for the builder's fieldsAwaitingSettings. See whenLogicSettingsLoaded in admin.js.
const awaitingFieldIds = new Set();

globalThis.window = {};
globalThis.document = {
	getElementById: id => id.startsWith( 'frm_field_id_' ) ? { dataset: { type: 'text' } } : null
};
globalThis.frmDom = {
	ajax: {
		doJsonPost( action, formData ) {
			requestedFieldIds.push( formData.getAll( 'field_ids[]' ) );
			const response = responses.shift();
			return response instanceof Error ? Promise.reject( response ) : Promise.resolve( response );
		}
	}
};

const { queueFieldSettings, whenFieldSettingsLoaded, isFieldSettingsPending } = await import( '../../js/src/admin/fieldSettingsLoader.js' );

queueFieldSettings( {
	formId: '1',
	fieldIds: [ '20', '21', '22', '23' ],
	// Like handleLoadedFieldSettings, an awaited panel moves into the save form when it arrives.
	onLoaded: fieldIds => fieldIds.forEach( fieldId => {
		if ( awaitingFieldIds.delete( fieldId ) ) {
			events.push( `move ${ fieldId }` );
		}
	} )
} );

// The loader reports failed requests to the console.
console.error = () => {};

function whenLogicSettingsLoaded( dependentIds ) {
	return whenFieldSettingsLoaded( dependentIds, fieldId => awaitingFieldIds.add( fieldId ), () => events.push( 'update' ) );
}

function settings( fieldIds ) {
	return { settings: Object.fromEntries( fieldIds.map( fieldId => [ fieldId, '<div></div>' ] ) ) };
}

test( 'runs the update at once when no dependent panel is pending', async () => {
	events.length = 0;
	const done = whenLogicSettingsLoaded( [] );
	assert.deepEqual( events, [ 'update' ] );
	await done;
	assert.equal( requestedFieldIds.length, 0 );
} );

test( 'waits for a dependent panel and moves it into the save form before the update', async () => {
	events.length = 0;
	responses.push( settings( [ '20' ] ) );
	const done = whenLogicSettingsLoaded( [ '20' ] );
	assert.deepEqual( events, [], 'The update waits for the panel.' );
	assert.equal( awaitingFieldIds.has( '20' ), true, 'Saving waits for the panel too.' );

	await done;
	assert.deepEqual( requestedFieldIds.at( -1 ), [ '20' ] );
	assert.deepEqual( events, [ 'move 20', 'update' ] );
	assert.equal( awaitingFieldIds.size, 0 );
	assert.equal( isFieldSettingsPending( '20' ), false );
} );

test( 'loads several dependents in one request and skips panels already loaded', async () => {
	events.length = 0;
	const requestsBefore = requestedFieldIds.length;
	responses.push( settings( [ '21', '22' ] ) );
	await whenLogicSettingsLoaded( [ '20', '21', '22' ] );

	assert.equal( requestedFieldIds.length, requestsBefore + 1 );
	assert.deepEqual( requestedFieldIds.at( -1 ), [ '21', '22' ] );
	assert.deepEqual( events, [ 'move 21', 'move 22', 'update' ] );
	assert.equal( awaitingFieldIds.size, 0 );
} );

test( 'still runs the update when the request fails, and keeps saving waiting on the panel', async () => {
	events.length = 0;
	responses.push( new Error( 'Network error' ) );
	await whenLogicSettingsLoaded( [ '23' ] );

	assert.deepEqual( requestedFieldIds.at( -1 ), [ '23' ] );
	assert.deepEqual( events, [ 'update' ], 'Nothing arrived to move.' );
	assert.equal( awaitingFieldIds.has( '23' ), true, 'Saving retries the panel. See deferSaveForFieldSettings.' );
	assert.equal( isFieldSettingsPending( '23' ), true );
} );
