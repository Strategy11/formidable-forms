/**
 * Request the settings panels for field previews the browser drew from page data.
 *
 * Settings load in the background in page order. A field the builder needs right away, because
 * it was selected or edited, jumps the queue in its own request.
 *
 * @since x.x
 */

import { cacheFieldSettings } from './deferredFieldSettings';

/**
 * How many fields to ask for in one request. Settings cost about the same to render in any
 * batch, so this mostly decides how many times WordPress loads.
 */
const BATCH_SIZE = 50;

/**
 * How many background requests may run at once. Requests for fields the builder needs right
 * away are not counted, so they never wait behind the background queue.
 */
const CONCURRENCY = 2;

const queuedFieldIds = new Set();
const failedFieldIds = new Set();
const requests = new Map();
const urgentFieldIds = new Set();

let formId;
let onLoaded;
let activeRequests = 0;
let backgroundStarted = false;

/**
 * Register the fields that still need their settings.
 *
 * Nothing loads in the background until startFieldSettingsQueue() is called, but a field the
 * builder needs right away can be requested as soon as it is queued.
 *
 * @since x.x
 * @param {Object}   options
 * @param {string}   options.formId   The form being edited.
 * @param {string[]} options.fieldIds The fields that still need their settings, in page order.
 * @param {Function} options.onLoaded Called with the ids of each batch after its settings are cached.
 * @return {void}
 */
export function queueFieldSettings( options ) {
	( { formId, onLoaded } = options );
	options.fieldIds.forEach( fieldId => queuedFieldIds.add( fieldId ) );
}

/**
 * Load the queued settings in the background, in page order.
 *
 * @since x.x
 * @return {void}
 */
export function startFieldSettingsQueue() {
	backgroundStarted = true;
	fillQueue();
}

/**
 * @since x.x
 * @param {string} fieldId Numeric field ID.
 * @return {boolean} True until the field's settings have been requested and received.
 */
export function isFieldSettingsPending( fieldId ) {
	const key = String( fieldId );
	return queuedFieldIds.has( key ) || requests.has( key ) || failedFieldIds.has( key );
}

/**
 * Request a field's settings ahead of the background queue.
 *
 * Fields requested in the same task share one request.
 *
 * @since x.x
 * @param {string} fieldId Numeric field ID.
 * @return {void}
 */
export function requestFieldSettingsNow( fieldId ) {
	const key = String( fieldId );
	if ( requests.has( key ) || ! ( queuedFieldIds.has( key ) || failedFieldIds.has( key ) ) ) {
		return;
	}

	if ( ! urgentFieldIds.size ) {
		queueMicrotask( sendUrgentRequests );
	}
	urgentFieldIds.add( key );
}

/**
 * @since x.x
 * @param {string[]} fieldIds Numeric field IDs.
 * @return {Promise<void>} Resolves once none of the fields has a request in progress.
 */
export function waitForFieldSettings( fieldIds ) {
	return Promise.allSettled( fieldIds.map( fieldId => requests.get( String( fieldId ) ) ) ).then( () => {
		// Callers only wait for the requests, so the results are dropped.
	} );
}

/**
 * Forget a deleted field so its settings are not requested.
 *
 * @since x.x
 * @param {string} fieldId Numeric field ID.
 * @return {void}
 */
export function cancelFieldSettings( fieldId ) {
	const key = String( fieldId );
	queuedFieldIds.delete( key );
	failedFieldIds.delete( key );
	urgentFieldIds.delete( key );
}

function sendUrgentRequests() {
	const fieldIds = [ ...urgentFieldIds ];
	urgentFieldIds.clear();

	for ( let index = 0; index < fieldIds.length; index += BATCH_SIZE ) {
		sendRequest( fieldIds.slice( index, index + BATCH_SIZE ) );
	}
}

function fillQueue() {
	if ( ! backgroundStarted ) {
		return;
	}

	while ( activeRequests < CONCURRENCY && queuedFieldIds.size ) {
		sendBackgroundRequest( [ ...queuedFieldIds ].slice( 0, BATCH_SIZE ) );
	}
}

/**
 * @param {string[]} fieldIds The fields to request.
 * @return {void}
 */
function sendBackgroundRequest( fieldIds ) {
	++activeRequests;
	sendRequest( fieldIds ).finally( onBackgroundRequestDone );
}

function onBackgroundRequestDone() {
	--activeRequests;
	fillQueue();
}

/**
 * @param {string[]} fieldIds The fields to request.
 * @return {Promise<void>} Resolves after the response is handled, whether or not it succeeded.
 */
function sendRequest( fieldIds ) {
	fieldIds.forEach( fieldId => {
		queuedFieldIds.delete( fieldId );
		failedFieldIds.delete( fieldId );
	} );

	const request = fetchSettings( fieldIds ).then(
		data => handleSettings( fieldIds, data ),
		error => {
			// Keep the fields pending so a selection or edit can ask for them again.
			fieldIds.forEach( fieldId => failedFieldIds.add( fieldId ) );
			console.error( `Failed to load settings for fields: ${ fieldIds.join( ', ' ) }`, error );
		}
	).finally( () => fieldIds.forEach( fieldId => requests.delete( fieldId ) ) );

	fieldIds.forEach( fieldId => requests.set( fieldId, request ) );
	return request;
}

/**
 * @param {string[]} fieldIds The fields to request.
 * @return {Promise<Object>} The settings html keyed by field id, and shared tooltip and option definitions.
 */
function fetchSettings( fieldIds ) {
	const formData = new FormData();
	formData.append( 'form_id', formId );
	// eslint-disable-next-line formidable/prefer-document-fragment -- FormData, not the DOM
	fieldIds.forEach( fieldId => formData.append( 'field_ids[]', fieldId ) );
	// Only acknowledge received definitions so concurrent requests remain independent.
	formData.append( 'known_tooltips', Object.keys( window.frm_admin_js?.tooltips || {} ).join( ',' ) );
	formData.append( 'known_select_options', Object.keys( window.frm_admin_js?.selectOptions || {} ).join( ',' ) );
	return frmDom.ajax.doJsonPost( 'load_field_settings', formData );
}

/**
 * @param {string[]} fieldIds The fields that were requested.
 * @param {Object}   data     The response data.
 * @return {void}
 */
function handleSettings( fieldIds, data ) {
	const adminJs = window.frm_admin_js;
	if ( adminJs ) {
		adminJs.tooltips = { ...adminJs.tooltips, ...data.tooltips };
		adminJs.selectOptions = { ...adminJs.selectOptions, ...data.selectOptions };
	}

	const loadedFieldIds = fieldIds.filter( fieldId => {
		// A field deleted while its settings were loading should not get them back.
		if ( ! data.settings?.[ fieldId ] || ! document.getElementById( `frm_field_id_${ fieldId }` ) ) {
			return false;
		}
		cacheFieldSettings( fieldId, data.settings[ fieldId ] );
		return true;
	} );

	onLoaded( loadedFieldIds );
}
