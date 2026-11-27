/**
 * Request the settings panels for field previews the browser drew from page data.
 *
 * Nearby settings load in the background. A field the builder needs right away, because
 * it was selected or edited, jumps the queue in its own request.
 *
 * @since 7.1
 */

import { cacheFieldSettings } from './deferredFieldSettings';
import { getFieldLoadBatch } from './fieldLoadWeights';

/**
 * How many fields to ask for in one request. Settings cost about the same to render in any
 * batch, so this mostly decides how many times WordPress loads.
 */
const BATCH_SIZE = 50;
// Keep a batch requested by an interaction small so its first panel arrives promptly.
const URGENT_FIELD_WEIGHT = 5;

/**
 * How many background requests may run at once. Requests for fields the builder needs right
 * away are not counted, so they never wait behind the background queue.
 */
const CONCURRENCY = 1;
const nearbyFields = new Set();
let observer;

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
 * @since 7.1
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
 * Start background prefetch for previews near the viewport.
 *
 * @since 7.1
 * @return {void}
 */
export function startFieldSettingsQueue() {
	backgroundStarted = true;
	fillQueue();
}

/**
 * @since x.x
 * @param {HTMLElement} field A rendered preview whose settings can be prefetched.
 * @return {void}
 */
export function observeFieldSettings( field ) {
	if ( ! queuedFieldIds.has( field.dataset.fid ) ) {
		return;
	}

	observer ??= new IntersectionObserver( entries => {
		for ( const { target, isIntersecting } of entries ) {
			const id = target.dataset.fid;
			if ( isIntersecting && queuedFieldIds.has( id ) ) {
				nearbyFields.add( id );
			} else {
				nearbyFields.delete( id );
			}
		}
		fillQueue();
	}, {
		root: document.getElementById( 'post-body-content' ),
		rootMargin: '400px 0px'
	} );
	observer.observe( field );
}

/**
 * @since 7.1
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
 * @since 7.1
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
 * @since 7.1
 * @param {string[]} fieldIds Numeric field IDs.
 * @return {Promise<void>} Resolves once none of the fields has a request in progress.
 */
export function waitForFieldSettings( fieldIds ) {
	return Promise.allSettled( fieldIds.map( fieldId => requests.get( String( fieldId ) ) ) ).then( () => {
		// Callers only wait for the requests, so the results are dropped.
	} );
}

/**
 * Run a change once the settings panels it reads have loaded.
 *
 * Pending panels are requested right away and added to awaitingFieldIds, so the builder holds a
 * save for them and moves each one into the save form when it arrives. The change runs after
 * every request settles, even one that failed, and runs at once when nothing is pending.
 *
 * @since x.x
 * @param {string[]}    fieldIds         The fields whose settings the change reads.
 * @param {Set<string>} awaitingFieldIds The builder's fields waiting for their settings.
 * @param {Function}    update           The change to run.
 * @return {Promise<void>} Resolves after the change has run.
 */
export function whenFieldSettingsLoaded( fieldIds, awaitingFieldIds, update ) {
	const pendingFieldIds = fieldIds.map( String ).filter( isFieldSettingsPending );
	if ( ! pendingFieldIds.length ) {
		update();
		return Promise.resolve();
	}

	pendingFieldIds.forEach( fieldId => {
		awaitingFieldIds.add( fieldId );
		requestFieldSettingsNow( fieldId );
	} );
	// The requests are sent in a microtask, so wait for them after it.
	return Promise.resolve().then( () => waitForFieldSettings( pendingFieldIds ) ).then( update );
}

/**
 * Ask once more for settings whose last request failed.
 *
 * @since 7.1
 * @param {string[]} fieldIds Numeric field IDs.
 * @return {Promise<string[]>} Resolves with the fields whose settings still failed to load.
 */
export function retryFailedFieldSettings( fieldIds ) {
	const failed = fieldIds.map( String ).filter( fieldId => failedFieldIds.has( fieldId ) );
	if ( ! failed.length ) {
		return Promise.resolve( [] );
	}

	// Send now instead of in a microtask, so the requests exist before waiting on them.
	failed.forEach( fieldId => urgentFieldIds.add( fieldId ) );
	sendUrgentRequests();
	return waitForFieldSettings( failed ).then( () => failed.filter( fieldId => failedFieldIds.has( fieldId ) ) );
}

/**
 * Wait until no field the builder is waiting on still has settings to load.
 *
 * Each pass waits for the fields awaited when it starts, retries the ones that failed, and then
 * removes only those fields from awaitingFieldIds. A field added in the meantime, for example by
 * a choice rename that reads another field's logic, keeps its place and gets another pass.
 *
 * @since x.x
 * @param {Set<string>} awaitingFieldIds The builder's fields waiting for their settings.
 * @return {Promise<string[]>} Resolves with the fields whose settings still failed to load.
 */
export function waitForAwaitedFieldSettings( awaitingFieldIds ) {
	const fieldIds = [ ...awaitingFieldIds ];
	return waitForFieldSettings( fieldIds ).then( () => retryFailedFieldSettings( fieldIds ) ).then( failed => {
		// Loaded fields were already removed. Deleted fields have nothing to save.
		fieldIds.forEach( fieldId => awaitingFieldIds.delete( fieldId ) );
		if ( ! awaitingFieldIds.size ) {
			return failed;
		}

		return waitForAwaitedFieldSettings( awaitingFieldIds ).then( failedLater => [ ...new Set( [ ...failed, ...failedLater ] ) ] );
	} );
}

/**
 * Forget a deleted field so its settings are not requested.
 *
 * @since 7.1
 * @param {string} fieldId Numeric field ID.
 * @return {void}
 */
export function cancelFieldSettings( fieldId ) {
	const key = String( fieldId );
	const field = document.getElementById( `frm_field_id_${ key }` );
	if ( field ) {
		observer?.unobserve( field );
	}
	nearbyFields.delete( key );
	queuedFieldIds.delete( key );
	failedFieldIds.delete( key );
	urgentFieldIds.delete( key );
}

function sendUrgentRequests() {
	const fieldIds = [ ...urgentFieldIds ];
	urgentFieldIds.clear();

	for ( let index = 0; index < fieldIds.length; ) {
		const batch = getFieldLoadBatch( fieldIds.slice( index ), BATCH_SIZE, URGENT_FIELD_WEIGHT );
		sendRequest( batch );
		index += batch.length;
	}
}

function fillQueue() {
	if ( ! backgroundStarted ) {
		return;
	}

	while ( activeRequests < CONCURRENCY && nearbyFields.size ) {
		sendBackgroundRequest( getFieldLoadBatch( nearbyFields, BATCH_SIZE, URGENT_FIELD_WEIGHT ) );
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
		const field = document.getElementById( `frm_field_id_${ fieldId }` );
		if ( field ) {
			observer?.unobserve( field );
		}
		nearbyFields.delete( fieldId );
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
	// Builder select sharing also checks the POST action when rendering these panels.
	formData.append( 'action', 'frm_load_field_settings' );
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
