const pendingOrders = new Map();

/**
 * Track order changes without activating settings, and synchronize existing controls.
 *
 * @since x.x
 * @param {HTMLFormElement} form The builder save form.
 * @return {boolean} Whether any order changed.
 */
export function syncFieldOrder( form ) {
	const inputsById = new Map();
	const prefix = 'field_options[field_order_';
	document.querySelectorAll( `input[name^="${ prefix }"]` ).forEach( input => {
		const fieldId = input.name.slice( prefix.length, -1 );
		if ( ! inputsById.has( fieldId ) ) {
			inputsById.set( fieldId, [] );
		}
		inputsById.get( fieldId ).push( input );
	} );

	let changed = false;
	const orders = {};
	document.querySelectorAll( '#frm-show-fields li.frm_field_box' ).forEach( ( field, index ) => {
		const fieldId = field.dataset.fid;
		const order = index + 1;
		const inputs = inputsById.get( fieldId ) || [];
		if ( inputs.some( input => Number( input.value ) !== order ) ) {
			pendingOrders.set( fieldId, order );
			inputs.forEach( input => {
				input.value = order;
			} );
			changed = true;
		}
		if ( pendingOrders.has( fieldId ) ) {
			pendingOrders.set( fieldId, order );
			orders[ fieldId ] = order;
		}
	} );

	for ( const fieldId of pendingOrders.keys() ) {
		if ( orders[ fieldId ] === undefined ) {
			pendingOrders.delete( fieldId );
		}
	}

	let input = form.querySelector( '[name="frm_field_order"]' );
	if ( ! pendingOrders.size && ! input ) {
		return changed;
	}
	if ( ! input ) {
		input = document.createElement( 'input' );
		input.type = 'hidden';
		input.name = 'frm_field_order';
		form.insertBefore( input, form.querySelector( '#frm-end-form-marker' ) );
	}
	input.value = JSON.stringify( orders );
	return changed;
}

/**
 * Clear confirmed changes while keeping any order edits made during the save request.
 *
 * @since x.x
 * @param {HTMLFormElement} form        The builder save form.
 * @param {string}          savedOrders The JSON map sent with the successful save.
 * @return {void}
 */
export function confirmFieldOrderSaved( form, savedOrders ) {
	if ( ! savedOrders ) {
		return;
	}
	Object.entries( JSON.parse( savedOrders ) ).forEach( ( [ fieldId, order ] ) => {
		if ( pendingOrders.get( fieldId ) === order ) {
			pendingOrders.delete( fieldId );
		}
	} );
	const input = form.querySelector( '[name="frm_field_order"]' );
	if ( input ) {
		input.value = JSON.stringify( Object.fromEntries( pendingOrders ) );
	}
}
