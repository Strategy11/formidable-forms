const pendingSettings = new Map();
const metadataInputs = {
	name: [ 'name', 'frm_name_' ],
	type: [ 'type', 'field_options_type_' ],
	key: [ 'field_key', 'field_options_field_key_' ],
	order: [ 'field_order', '' ],
	classes: [ 'classes', 'frm_classes_' ],
	align: [ 'align', 'field_options_align_' ]
};

/**
 * Keep lightweight values available to layout code and field lists without shipping input HTML.
 *
 * @since x.x
 * @param {HTMLElement} field    The inserted field preview.
 * @param {string}      fieldId  Numeric field ID.
 * @param {Object}      metadata Saved name, type, key, order, classes, and alignment.
 * @return {void}
 */
export function addFieldSettingsMetadata( field, fieldId, metadata ) {
	const container = frmDom.div( { className: 'frm-deferred-settings-meta' } );
	container.hidden = true;
	const inputs = Object.entries( metadataInputs ).flatMap( ( [ key, [ name, prefix ] ] ) => {
		const value = metadata[ key ];
		if ( value === null || value === undefined ) {
			return [];
		}
		const input = frmDom.tag( 'input' );
		input.type = 'hidden';
		input.name = `field_options[${ name }_${ fieldId }]`;
		input.value = value;
		if ( prefix ) {
			input.id = prefix + fieldId;
		}
		if ( key === 'classes' ) {
			input.classList.add( 'frm_classes' );
			input.dataset.changeme = `frm_field_id_${ fieldId }`;
			input.dataset.changeatt = 'class';
		}
		return [ input ];
	} );
	container.append( ...inputs );
	field.append( container );
}

/**
 * Cache server-rendered settings without parsing their HTML or creating controls.
 *
 * @since x.x
 * @param {string} fieldId Numeric field ID.
 * @param {string} html    Settings panel HTML returned by the builder endpoint.
 * @return {void}
 */
export function cacheFieldSettings( fieldId, html ) {
	if ( html ) {
		pendingSettings.set( String( fieldId ), html );
	}
}

/**
 * Forget settings when a field is deleted so saving cannot restore its controls.
 *
 * @since x.x
 * @param {string} fieldId Numeric field ID.
 * @return {void}
 */
export function forgetFieldSettings( fieldId ) {
	pendingSettings.delete( String( fieldId ) );
}

/**
 * Insert a settings panel once, preserving any layout and order edits made before selection.
 *
 * @since x.x
 * @param {string}   fieldId    Numeric field ID.
 * @param {Function} initialize Prepares newly inserted settings controls.
 * @return {HTMLElement|null} The existing or newly inserted settings panel.
 */
export function materializeFieldSettings( fieldId, initialize ) {
	const key = String( fieldId );
	const settingsId = `frm-single-settings-${ key }`;
	const existing = document.getElementById( settingsId );
	if ( existing ) {
		forgetFieldSettings( key );
		return existing;
	}

	const html = pendingSettings.get( key );
	if ( ! html ) {
		return null;
	}

	const field = document.getElementById( `frm_field_id_${ key }` );
	if ( ! field ) {
		forgetFieldSettings( key );
		return null;
	}

	// The preview is outside the save form. Selection or an edit moves this panel into it.
	field.insertAdjacentHTML( 'beforeend', html );
	const settings = document.getElementById( settingsId );
	const metadata = field.querySelector( '.frm-deferred-settings-meta' );
	if ( metadata ) {
		const controls = Array.from( settings.querySelectorAll( 'input, select' ) );
		metadata.querySelectorAll( 'input' ).forEach( input => {
			const control = controls.find(
				candidate => candidate.name === input.name
			);
			if ( ! control ) {
				return;
			}
			if ( input.id ) {
				control.value = input.value;
			} else {
				// Keep the order input object, which the drag and drop code caches.
				control.replaceWith( input );
			}
		} );
		metadata.remove();
	}

	forgetFieldSettings( key );
	initialize( settings );
	return settings;
}

/**
 * Restore panels beside their previews so dependent choice updates can find their controls.
 *
 * @since x.x
 * @param {Function} initialize Prepares newly inserted settings controls.
 * @return {void}
 */
export function materializeAllFieldSettings( initialize ) {
	for ( const fieldId of pendingSettings.keys() ) {
		materializeFieldSettings( fieldId, initialize );
	}
}
