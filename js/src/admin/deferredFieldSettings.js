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
 * @since 7.0
 * @param {HTMLElement} field    The inserted field preview.
 * @param {string}      fieldId  Numeric field ID.
 * @param {Object}      metadata Saved name, type, key, order, classes, and alignment.
 * @return {void}
 */
export function addFieldSettingsMetadata( field, fieldId, metadata ) {
	const container = frmDom.div( { className: 'frm-deferred-settings-meta' } );
	container.hidden = true;
	const inputs = Object.entries( metadataInputs ).flatMap( ( [ key, [ name, prefix ] ] ) => {
		let value = metadata[ key ];
		if ( value === null || value === undefined ) {
			return [];
		}
		if ( key === 'type' && value === 'select' ) {
			// Match the field type setting, which posts "dropdown" because some security tools flag "select" as SQL injection.
			value = 'dropdown';
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
 * @since 7.0
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
 * @since 7.0
 * @param {string} fieldId Numeric field ID.
 * @return {void}
 */
export function forgetFieldSettings( fieldId ) {
	pendingSettings.delete( String( fieldId ) );
}

/**
 * Insert a settings panel once, preserving any layout and order edits made before selection.
 *
 * @since 7.0
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
	/**
	 * Initialize controls after a deferred settings panel enters the DOM.
	 *
	 * Contract for code outside Lite that binds to settings panel controls:
	 * - A panel that loads deferred is added later than page load, and only once. This
	 *   happens when the field is selected, when code calls frmAdminBuild.ensureFieldSettings(),
	 *   or when a choice edit needs the conditional logic rows that point at the edited field.
	 * - This action is the only signal for that insert. It fires once per panel, after the
	 *   saved layout and order values are restored and Lite's own markup preparation ran.
	 * - The panel sits inside its field preview and is usually hidden. It is moved into the
	 *   save form later, and frmShowedFieldSettings still fires each time it is shown.
	 * - A field added or duplicated in this session arrives with its panel already in the
	 *   page, so it never fires this action. Its insert is announced by the frm_added_field
	 *   DOM event instead. Bind with delegated handlers, or initialize on this action and on
	 *   frmShowedFieldSettings, and keep that initialization idempotent.
	 * - No jQuery events and no frm_added_field event fire for this insert.
	 *
	 * @since x.x
	 * @param {HTMLElement} settings The inserted field settings panel.
	 */
	wp.hooks.doAction( 'frm_field_settings_loaded', settings );
	return settings;
}

/**
 * Restore panels beside their previews so dependent choice updates can find their controls.
 *
 * @since 7.0
 * @param {Function} initialize Prepares newly inserted settings controls.
 * @return {void}
 */
export function materializeAllFieldSettings( initialize ) {
	for ( const fieldId of pendingSettings.keys() ) {
		materializeFieldSettings( fieldId, initialize );
	}
}

/**
 * Check whether cached settings HTML holds a conditional logic row that uses a field as its trigger.
 *
 * @since x.x
 * @param {string} html    Cached settings panel HTML.
 * @param {string} fieldId Numeric ID of the trigger field.
 * @return {boolean} True when a logic row in the panel references the field.
 */
function hasLogicRowForField( html, fieldId ) {
	if ( ! html.includes( 'frm_logic_row' ) || ! html.includes( `"${ fieldId }"` ) ) {
		return false;
	}

	// A template parses the markup without running scripts or loading images.
	const template = frmDom.tag( 'template' );
	template.innerHTML = html;
	return Array.from( template.content.querySelectorAll( '.frm_logic_row .frm_logic_field_opts' ) ).some(
		select => select.value === fieldId
	);
}

/**
 * Restore only the cached panels whose conditional logic rows use a field as their trigger.
 *
 * Choice edits only need the logic rows that point at the edited field, so the
 * rest of the deferred panels stay cached.
 *
 * @since x.x
 * @param {string|number} fieldId    Numeric ID of the trigger field.
 * @param {Function}      initialize Prepares newly inserted settings controls.
 * @return {void}
 */
export function materializeLogicFieldSettings( fieldId, initialize ) {
	const triggerId = String( fieldId );
	const matches = [];
	for ( const [ key, html ] of pendingSettings ) {
		if ( hasLogicRowForField( html, triggerId ) ) {
			matches.push( key );
		}
	}

	if ( ! matches.length ) {
		return;
	}

	matches.forEach( key => materializeFieldSettings( key, initialize ) );
}
