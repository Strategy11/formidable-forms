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
const loadedFieldIds = [];

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

const { queueFieldSettings, requestFieldSettingsNow, waitForFieldSettings, retryFailedFieldSettings, isFieldSettingsPending } = await import( '../../js/src/admin/fieldSettingsLoader.js' );

queueFieldSettings( {
	formId: '1',
	fieldIds: [ '10', '11', '12' ],
	onLoaded: fieldIds => loadedFieldIds.push( ...fieldIds )
} );

// The loader reports failed requests to the console.
console.error = () => {};

async function failRequest( fieldId ) {
	responses.push( new Error( 'Network error' ) );
	requestFieldSettingsNow( fieldId );
	await Promise.resolve();
	await waitForFieldSettings( [ fieldId ] );
}

test( 'retryFailedFieldSettings resolves empty when nothing failed', async () => {
	assert.deepEqual( await retryFailedFieldSettings( [ '12' ] ), [] );
	assert.equal( requestedFieldIds.length, 0 );
} );

test( 'retryFailedFieldSettings loads a failed field on the second try', async () => {
	await failRequest( '10' );
	assert.equal( isFieldSettingsPending( '10' ), true );

	responses.push( { settings: { 10: '<div></div>' } } );
	assert.deepEqual( await retryFailedFieldSettings( [ '10' ] ), [] );
	assert.deepEqual( requestedFieldIds.at( -1 ), [ '10' ] );
	assert.deepEqual( loadedFieldIds, [ '10' ] );
	assert.equal( isFieldSettingsPending( '10' ), false );
} );

test( 'retryFailedFieldSettings reports a field that fails again', async () => {
	await failRequest( '11' );
	const requestsBefore = requestedFieldIds.length;

	responses.push( new Error( 'Network error' ) );
	assert.deepEqual( await retryFailedFieldSettings( [ '11', '12' ] ), [ '11' ] );
	assert.equal( requestedFieldIds.length, requestsBefore + 1 );
	assert.equal( isFieldSettingsPending( '11' ), true );
} );
