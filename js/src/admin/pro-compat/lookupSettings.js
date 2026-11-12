/* global frmAdminBuild, ajaxurl, frmGlobal */

/**
 * Lite's original lookup field settings code, for Pro versions that do not register their own.
 * The code was moved here from admin.js.
 *
 * @since x.x
 */

/**
 * @since x.x
 * @return {string|number} The ID of the form in the builder, or 0.
 */
function getFormId() {
	return document.getElementById( 'form_id' )?.value ?? 0;
}

function addWatchLookupRow() {
	/*jshint validthis:true */
	const id = jQuery( this ).closest( '.frm-single-settings' ).data( 'fid' );
	const formId = getFormId();
	const lookupBlockRows = document.getElementById( `frm_watch_lookup_block_${ id }` ).children;

	jQuery.ajax( {
		type: 'POST',
		url: ajaxurl,
		data: {
			action: 'frm_add_watch_lookup_row',
			form_id: formId,
			field_id: id,
			row_key: frmAdminBuild.getNewRowId( lookupBlockRows, `frm_watch_lookup_${ id }_` ),
			nonce: frmGlobal.nonce
		},
		success( newRow ) {
			const watchRowBlock = jQuery( document.getElementById( `frm_watch_lookup_block_${ id }` ) );
			watchRowBlock.append( newRow );
			watchRowBlock.fadeIn( 'slow' );

			// Show the "Watch Lookup Fields" label if it was hidden
			document.getElementById( `frm_watch_lookup_label_${ id }` )?.classList.remove( 'frm-force-hidden' );
		}
	} );
	return false;
}

function updateGetValueFieldSelection() {
	/*jshint validthis:true */
	const fieldID = this.id.replace( 'get_values_form_', '' );
	const fieldSelect = document.getElementById( `get_values_field_${ fieldID }` );
	const fieldType = this.getAttribute( 'data-fieldtype' );

	if ( this.value === '' ) {
		fieldSelect.options.length = 1;
	} else {
		const formID = this.value;
		jQuery.ajax( {
			type: 'POST', url: ajaxurl,
			data: {
				action: 'frm_get_options_for_get_values_field',
				form_id: formID,
				field_type: fieldType,
				nonce: frmGlobal.nonce
			},
			success( fields ) {
				fieldSelect.innerHTML = fields;
			}
		} );
	}
}

// Clear the Watch Fields option when Lookup field switches to "Text" option
function maybeClearWatchFields() {
	/*jshint validthis:true */
	let link;
	let lookupBlock;
	const fieldID = this.name.replace( 'field_options[data_type_', '' ).replace( ']', '' );

	link = document.getElementById( `frm_add_watch_lookup_link_${ fieldID }` );
	if ( ! link ) {
		return;
	}
	link = link.parentNode;

	if ( this.value === 'text' ) {
		lookupBlock = document.getElementById( `frm_watch_lookup_block_${ fieldID }` );
		if ( lookupBlock ) {
			// Clear and hide the Watch Fields option
			lookupBlock.innerHTML = '';
			link.classList.add( 'frm_hidden' );

			// Hide the Watch Fields row
			link.previousElementSibling.style.display = 'none';
			link.previousElementSibling.previousElementSibling.style.display = 'none';
			link.previousElementSibling.previousElementSibling.previousElementSibling.style.display = 'none';
		}
	} else {
		// Show the Watch Fields option
		link.classList.remove( 'frm_hidden' );
	}

	frmAdminBuild.toggleMultiSelect( fieldID, this.value );
}

/**
 * @since x.x
 * @return {void}
 */
export function registerLookupSettings() {
	const registered = frmAdminBuild.registerBuilderFeature( 'lookup_settings', {
		addWatchLookupRow,
		updateGetValueFieldSelection,
		maybeClearWatchFields,
	} );
	if ( ! registered ) {
		return;
	}

	jQuery( document ).on( 'click', '#new_fields .frm_add_watch_lookup_row', addWatchLookupRow );
	jQuery( document ).on( 'change', '#new_fields .frm_get_values_form', updateGetValueFieldSelection );
	jQuery( document ).on( 'change', '#new_fields select[name^="field_options[data_type_"]', maybeClearWatchFields );
}
