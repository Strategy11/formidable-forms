/* global ajaxurl, frmGlobal */
/* eslint-disable formidable/prefer-document-fragment -- FormData.append is not a DOM insert. */

/**
 * Re-render a field preview on the server with the unsaved settings in its panel.
 *
 * @since x.x
 */

const DEFAULT_DELAY = 300;
const DEFAULT_ACTION = 'frm_refresh_field_preview';

/**
 * Add the field's settings from its panel to the request.
 *
 * @since x.x
 *
 * @param {FormData}    data    The request data.
 * @param {HTMLElement} panel   The field settings panel.
 * @param {string}      fieldId The field ID.
 * @return {void}
 */
export function appendPanelSettings( data, panel, fieldId ) {
	const settingName = new RegExp( `^(field_options\\[[^\\]]+_${ fieldId }\\]|default_value_${ fieldId })(\\[|$)` );

	panel.querySelectorAll( 'input[name], select[name], textarea[name]' ).forEach( control => {
		const { name, type } = control;
		if ( control.disabled || ! settingName.test( name ) ) {
			return;
		}

		if ( 'radio' === type ) {
			if ( control.checked ) {
				data.append( name, control.value );
			}
			return;
		}

		if ( 'checkbox' === type ) {
			if ( name.endsWith( '[]' ) ) {
				if ( control.checked ) {
					data.append( name, control.value );
				}
				return;
			}
			// An unchecked box is sent as 0, so it overrides a saved value.
			data.append( name, control.checked ? control.value : '0' );
			return;
		}

		if ( control.multiple && control.options ) {
			Array.from( control.options ).filter( option => option.selected ).forEach( option => data.append( name, option.value ) );
			return;
		}

		data.append( name, control.value );
	} );
}

/**
 * Add extra request data.
 *
 * @since x.x
 *
 * @param {FormData} data  The request data.
 * @param {Object}   extra The extra values, keyed by request name.
 * @return {void}
 */
function appendExtraData( data, extra ) {
	Object.entries( extra || {} ).forEach( ( [ key, value ] ) => {
		if ( Array.isArray( value ) ) {
			value.forEach( item => data.append( `${ key }[]`, item ) );
		} else if ( value !== null && 'object' === typeof value ) {
			data.append( key, JSON.stringify( value ) );
		} else {
			data.append( key, value ?? '' );
		}
	} );
}

/**
 * @since x.x
 *
 * @param {string}      action The AJAX action.
 * @param {FormData}    data   The request data.
 * @param {AbortSignal} signal Aborts the request.
 * @return {Promise<*>} The response data.
 */
async function postPreviewRequest( action, data, signal ) {
	// Actions are used as given when they already start with frm, like frm_refresh_field_preview or frmsurveys_preview.
	const fullAction = action.startsWith( 'frm' ) ? action : `frm_${ action }`;
	data.append( 'nonce', frmGlobal.nonce );
	const response = await fetch( `${ ajaxurl }?action=${ encodeURIComponent( fullAction ) }`, {
		method: 'POST',
		body: data,
		signal,
	} );
	const json = await response.json();
	if ( ! json?.success ) {
		throw new Error( `Field preview request failed: ${ fullAction }` );
	}
	return json.data;
}

/**
 * Swap a field preview body for the new markup.
 * The body element is replaced, so onFieldPreviewReady callbacks run for it again.
 *
 * @since x.x
 *
 * @param {HTMLElement|null} body The current preview body.
 * @param {string}           html The new preview markup.
 * @return {void}
 */
function replacePreviewBody( body, html ) {
	if ( ! body ) {
		return;
	}
	const newBody = body.cloneNode( false );
	newBody.innerHTML = html;
	body.replaceWith( newBody );
}

/**
 * Create refreshFieldPreview.
 *
 * @since x.x
 *
 * @param {Object}   deps                     Builder helpers.
 * @param {Function} deps.getFieldPreviewBody Returns the preview body of a field.
 * @param {Function} deps.post                Optional. Sends the request. Used by tests.
 * @return {Function} refreshFieldPreview( fieldId, options ).
 */
export function createRefreshFieldPreview( { getFieldPreviewBody, post = postPreviewRequest } ) {
	const pending = new Map();

	/**
	 * @param {string} fieldId The field ID.
	 * @param {Object} state   The field's pending state.
	 * @param {*}      result  What every waiting call resolves with.
	 * @return {void}
	 */
	function settle( fieldId, state, result ) {
		if ( pending.get( fieldId ) === state ) {
			pending.delete( fieldId );
		}
		state.resolvers.splice( 0 ).forEach( resolve => resolve( result ) );
	}

	/**
	 * @param {string} fieldId The field ID.
	 * @param {Object} state   The field's pending state.
	 * @param {Object} options The options of the latest call.
	 * @return {Promise<void>}
	 */
	async function send( fieldId, state, options ) {
		const controller = new AbortController();
		state.timer = null;
		state.controller = controller;

		const data = new FormData();
		data.append( 'field_id', fieldId );
		data.append( 'form_id', document.getElementById( 'form_id' )?.value || '' );
		const panel = document.getElementById( `frm-single-settings-${ fieldId }` );
		if ( panel ) {
			appendPanelSettings( data, panel, fieldId );
		}
		appendExtraData( data, options.data );

		let response;
		try {
			response = await post( options.action || DEFAULT_ACTION, data, controller.signal );
		} catch ( error ) {
			if ( controller.signal.aborted ) {
				// A newer call took over, and resolves the waiting calls.
				return;
			}
			console.error( `Failed to refresh field preview: ${ fieldId }`, error );
			settle( fieldId, state, null );
			return;
		}

		if ( controller.signal.aborted ) {
			return;
		}

		const field = document.getElementById( `frm_field_id_${ fieldId }` );
		if ( ! field?.isConnected ) {
			settle( fieldId, state, null );
			return;
		}

		const html = 'string' === typeof response ? response : response?.html ?? '';
		if ( 'function' === typeof options.apply ) {
			options.apply( html, field );
		} else {
			replacePreviewBody( getFieldPreviewBody( fieldId ), html );
		}

		/**
		 * Fires after a field preview was re-rendered with its unsaved settings.
		 *
		 * @since x.x
		 *
		 * @param {string}      fieldId The field ID.
		 * @param {HTMLElement} field   The field list item.
		 */
		wp.hooks.doAction( 'frm_field_preview_refreshed', fieldId, field );
		settle( fieldId, state, field );
	}

	/**
	 * Re-render a field preview on the server, without saving its settings.
	 *
	 * @since x.x
	 *
	 * @param {string|number} fieldId        The field ID.
	 * @param {Object}        options        Optional settings.
	 * @param {number}        options.delay  Milliseconds to wait for more calls for the same field. Default 300.
	 * @param {Object}        options.data   Extra request values.
	 * @param {string}        options.action The AJAX action. Default frm_refresh_field_preview.
	 * @param {Function}      options.apply  Called with the html and the field list item instead of the default swap.
	 * @return {Promise<HTMLElement|null>} The field list item, or null when the field is gone or the request failed.
	 */
	return function refreshFieldPreview( fieldId, options = {} ) {
		const key = String( fieldId );
		const delay = options.delay ?? DEFAULT_DELAY;

		return new Promise( resolve => {
			let state = pending.get( key );
			if ( ! state ) {
				state = { resolvers: [], timer: null, controller: null };
				pending.set( key, state );
			}
			clearTimeout( state.timer );
			state.controller?.abort();
			state.controller = null;
			state.resolvers.push( resolve );
			state.timer = setTimeout( () => send( key, state, options ), delay );
		} );
	};
}
