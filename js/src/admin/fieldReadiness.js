/**
 * Run add-on setup once per field settings panel and once per drawn field preview,
 * however the panel or preview entered the builder.
 *
 * @since x.x
 */

const SETTINGS_SELECTOR = '.frm-single-settings[data-fid]';
const FIELD_SELECTOR = 'li.form-field[data-fid]';
// Loading and pending previews are placeholders that a drawn preview replaces later.
const UNRENDERED_CLASSES = [ 'frm_field_loading', 'frm_field_preview_pending' ];

/**
 * Get the element that holds a field's preview in the builder.
 *
 * @since x.x
 *
 * @param {string|number} fieldId The field ID.
 * @return {HTMLElement|null} The preview body.
 */
export function getFieldPreviewBody( fieldId ) {
	return document.getElementById( `field_${ fieldId }_inner_container` )?.querySelector( ':scope > .frm_form_fields' ) || null;
}

/**
 * Check whether a panel or preview matches a registration selector.
 *
 * @since x.x
 *
 * @param {HTMLElement} element  The panel, or the field list item.
 * @param {HTMLElement} scope    The part of the element to search inside.
 * @param {string}      selector The registration selector.
 * @return {boolean} True when the element or something in its scope matches.
 */
function elementMatches( element, scope, selector ) {
	try {
		return element.matches( selector ) || null !== scope.querySelector( selector );
	} catch ( error ) {
		console.error( `Invalid field readiness selector: ${ selector }`, error );
		return false;
	}
}

/**
 * Call a registered callback, logging its errors so one add-on cannot stop the others.
 *
 * @since x.x
 *
 * @param {Function} callback The registered callback.
 * @param {Array}    args     The callback arguments.
 * @return {void}
 */
function safeCall( callback, args ) {
	try {
		callback( ...args );
	} catch ( error ) {
		console.error( 'Field readiness callback failed', error );
	}
}

/**
 * Create the readiness helpers.
 *
 * @since x.x
 *
 * @param {Object}   deps                     Builder internals.
 * @param {Function} deps.ensureFieldSettings Materializes a deferred settings panel and returns it, or null.
 * @return {Object} onFieldSettingsReady, onFieldPreviewReady, whenFieldSettingsReady and listen.
 */
export function createFieldReadiness( { ensureFieldSettings } ) {
	const settingsRegistrations = [];
	const previewRegistrations = [];
	const settingsWaiters = new Map();
	let queuedPanels = null;
	let queuedFields = null;
	let listening = false;

	/**
	 * @param {Object}             registration The settings registration.
	 * @param {Array<HTMLElement>} panels       The panels to check.
	 */
	function runSettingsRegistration( registration, panels ) {
		panels.forEach( panel => {
			if ( registration.seen.has( panel ) || ! panel.isConnected || ! elementMatches( panel, panel, registration.selector ) ) {
				return;
			}
			registration.seen.add( panel );
			safeCall( registration.callback, [ panel, panel.dataset.fid ] );
		} );
	}

	/**
	 * Get what identifies a drawn preview, so a replaced preview body counts as new.
	 *
	 * @param {HTMLElement} field The field list item.
	 * @return {HTMLElement|null} The preview body, the list item when the field has no body, or null while loading.
	 */
	function getPreviewKey( field ) {
		if ( ! field.isConnected || UNRENDERED_CLASSES.some( className => field.classList.contains( className ) ) ) {
			return null;
		}
		return getFieldPreviewBody( field.dataset.fid ) || field;
	}

	/**
	 * @param {Object}             registration The preview registration.
	 * @param {Array<HTMLElement>} fields       The field list items to check.
	 */
	function runPreviewRegistration( registration, fields ) {
		fields.forEach( field => {
			const key = getPreviewKey( field );
			if ( ! key || registration.seen.has( key ) ) {
				return;
			}
			const scope = key === field ? field : key.parentElement;
			if ( ! elementMatches( field, scope, registration.selector ) ) {
				return;
			}
			registration.seen.add( key );
			safeCall( registration.callback, [ field, field.dataset.fid ] );
		} );
	}

	function resolveSettingsWaiters() {
		settingsWaiters.forEach( ( resolvers, fieldId ) => {
			const panel = document.getElementById( `frm-single-settings-${ fieldId }` );
			const field = document.getElementById( `frm_field_id_${ fieldId }` );
			if ( ! panel && field ) {
				return;
			}
			settingsWaiters.delete( fieldId );
			resolvers.forEach( resolve => resolve( panel ) );
		} );
	}

	function flush() {
		const panels = queuedPanels ? Array.from( queuedPanels ) : [];
		const fields = queuedFields ? Array.from( queuedFields ) : [];
		queuedPanels = null;
		queuedFields = null;

		settingsRegistrations.forEach( registration => runSettingsRegistration( registration, panels ) );
		previewRegistrations.forEach( registration => runPreviewRegistration( registration, fields ) );
		resolveSettingsWaiters();
	}

	function scheduleFlush() {
		if ( ! queuedPanels && ! queuedFields ) {
			// Wait until the code that added the field finishes, like copying unsaved settings to a duplicate.
			Promise.resolve().then( flush );
		}
		queuedPanels = queuedPanels || new Set();
		queuedFields = queuedFields || new Set();
	}

	/**
	 * Queue the settings panel and the preview of a field.
	 *
	 * @param {string|number} fieldId The field ID.
	 * @return {void}
	 */
	function queueField( fieldId ) {
		scheduleFlush();
		const panel = document.getElementById( `frm-single-settings-${ fieldId }` );
		const field = document.getElementById( `frm_field_id_${ fieldId }` );
		if ( panel ) {
			queuedPanels.add( panel );
		}
		if ( field ) {
			queuedFields.add( field );
		}
	}

	/**
	 * @param {HTMLElement|null} panel A settings panel.
	 * @return {void}
	 */
	function queuePanel( panel ) {
		scheduleFlush();
		if ( panel?.matches?.( SETTINGS_SELECTOR ) ) {
			queuedPanels.add( panel );
		}
	}

	/**
	 * Listen for every way a panel or a preview enters the builder, once.
	 *
	 * @return {void}
	 */
	function listen() {
		if ( listening ) {
			return;
		}
		listening = true;

		document.addEventListener( 'frm_added_field', event => {
			const fieldId = event.frmField?.dataset?.fid;
			if ( fieldId ) {
				queueField( fieldId );
			}
		} );
		document.addEventListener( 'frm_ajax_loaded_field', event => {
			( event.frmFields || [] ).forEach( ( { id } ) => queueField( id ) );
		} );
		wp.hooks.addAction( 'frm_field_settings_loaded', 'formidable/fieldReadiness', queuePanel );
		wp.hooks.addAction( 'frmShowedFieldSettings', 'formidable/fieldReadiness', ( showButton, panel ) => queuePanel( panel ) );
		wp.hooks.addAction( 'frm_field_preview_refreshed', 'formidable/fieldReadiness', fieldId => queueField( fieldId ) );
	}

	/**
	 * Run a callback once for each field settings panel that matches a selector.
	 *
	 * @since x.x
	 *
	 * @param {string}   selector A selector for the panel or something inside it.
	 * @param {Function} callback Called with the panel and the field ID.
	 * @return {void}
	 */
	function onFieldSettingsReady( selector, callback ) {
		const registration = { selector, callback, seen: new WeakSet() };
		settingsRegistrations.push( registration );
		listen();
		runSettingsRegistration( registration, Array.from( document.querySelectorAll( SETTINGS_SELECTOR ) ) );
	}

	/**
	 * Run a callback once for each drawn field preview that matches a selector, and again after
	 * its preview body is replaced.
	 *
	 * @since x.x
	 *
	 * @param {string}   selector A selector for the field list item or something in its preview.
	 * @param {Function} callback Called with the field list item and the field ID.
	 * @return {void}
	 */
	function onFieldPreviewReady( selector, callback ) {
		const registration = { selector, callback, seen: new WeakSet() };
		previewRegistrations.push( registration );
		listen();
		runPreviewRegistration( registration, Array.from( document.querySelectorAll( FIELD_SELECTOR ) ) );
	}

	/**
	 * Get a field's settings panel, loading it first when it is deferred.
	 *
	 * @since x.x
	 *
	 * @param {string|number} fieldId The field ID.
	 * @return {Promise<HTMLElement|null>} The panel, or null when the field is not in the builder.
	 */
	function whenFieldSettingsReady( fieldId ) {
		const key = String( fieldId );
		const panel = ensureFieldSettings( key );
		if ( panel ) {
			return Promise.resolve( panel );
		}
		if ( ! document.getElementById( `frm_field_id_${ key }` ) ) {
			return Promise.resolve( null );
		}
		listen();
		return new Promise( resolve => {
			if ( ! settingsWaiters.has( key ) ) {
				settingsWaiters.set( key, [] );
			}
			settingsWaiters.get( key ).push( resolve );
		} );
	}

	return { onFieldSettingsReady, onFieldPreviewReady, whenFieldSettingsReady, listen };
}
