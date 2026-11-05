/* global frmAdminBuild, frm_admin_js, ajaxurl, frmGlobal */

/**
 * Lite's original repeater, embedded form and taxonomy settings code, for Pro versions that do not register their own.
 * The code was moved here from admin.js.
 *
 * @since x.x
 */

const frmAdminJs = frm_admin_js;

function toggleRepeatButtons() {
	/*jshint validthis:true */
	const $thisField = jQuery( this ).closest( '.frm_field_box' );
	$thisField.find( '.repeat_icon_links' ).removeClass( 'repeat_format repeat_formatboth repeat_formattext' ).addClass( `repeat_format${ this.value }` );
	if ( this.value === 'text' || this.value === 'both' ) {
		$thisField.find( '.frm_repeat_text' ).show();
		$thisField.find( '.repeat_icon_links a' ).addClass( 'frm_button' );
	} else {
		$thisField.find( '.frm_repeat_text' ).hide();
		$thisField.find( '.repeat_icon_links a' ).removeClass( 'frm_button' );
	}
}

function checkRepeatLimit() {
	/*jshint validthis:true */
	const val = this.value;
	if ( val !== '' && ( val < 2 || val > 200 ) ) {
		frmAdminBuild.infoModal( frmAdminJs.repeat_limit_min );
		this.value = '';
	}
}

function checkCheckboxSelectionsLimit() {
	/*jshint validthis:true */
	const val = this.value;
	if ( val !== '' && ( val < 1 || val > 200 ) ) {
		frmAdminBuild.infoModal( frmAdminJs.checkbox_limit );
		this.value = '';
	}
}

function updateRepeatText( obj, addRemove ) {
	const $thisField = jQuery( obj ).closest( '.frm_field_box' );
	$thisField.find( `.frm_${ addRemove }_form_row .frm_repeat_label` ).text( obj.value );
}

function toggleFormTax() {
	/*jshint validthis:true */
	const id = jQuery( this ).closest( '.frm-single-settings' ).data( 'fid' );
	const val = this.value;
	const $showFields = document.getElementById( `frm_show_selected_fields_${ id }` );
	const $showForms = document.getElementById( `frm_show_selected_forms_${ id }` );

	jQuery( $showForms ).find( 'select' ).val( '' );
	if ( val === 'form' ) {
		$showForms.style.display = 'inline';
		empty( $showFields );
	} else {
		$showFields.style.display = 'none';
		$showForms.style.display = 'none';
		getTaxOrFieldSelection( val, id );
	}
}

function getFieldSelection() {
	/*jshint validthis:true */
	const formId = this.value;
	if ( formId ) {
		const fieldId = jQuery( this ).closest( '.frm-single-settings' ).data( 'fid' );
		getTaxOrFieldSelection( formId, fieldId );
	}
}

function getTaxOrFieldSelection( formId, fieldId ) {
	if ( formId ) {
		jQuery.ajax( {
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'frm_get_field_selection',
				field_id: fieldId,
				form_id: formId,
				nonce: frmGlobal.nonce
			},
			success( msg ) {
				const $selectedFields = jQuery( `#frm_show_selected_fields_${ fieldId }` );
				$selectedFields.toggleClass( 'frm6', ! msg.includes( 'frm-inline-message' ) );
				$selectedFields.html( msg ).show();
			}
		} );
	}
}

function maybeChangeEmbedFormMsg() {
	const fieldId = jQuery( this ).closest( '.frm-single-settings' ).data( 'fid' );
	let fieldItem = document.getElementById( `frm_field_id_${ fieldId }` );
	if ( ! fieldItem || 'form' !== fieldItem.dataset.type ) {
		return;
	}

	fieldItem = jQuery( fieldItem );

	if ( this.options[ this.selectedIndex ].value ) {
		fieldItem.find( '.frm-not-set' )[ 0 ].classList.add( 'frm_hidden' );
		const embedMsg = fieldItem.find( '.frm-embed-message' );
		embedMsg.html( embedMsg.data( 'embedmsg' ) + this.options[ this.selectedIndex ].text );
		fieldItem.find( '.frm-embed-field-placeholder' )[ 0 ].classList.remove( 'frm_hidden' );
	} else {
		fieldItem.find( '.frm-not-set' )[ 0 ].classList.remove( 'frm_hidden' );
		fieldItem.find( '.frm-embed-field-placeholder' )[ 0 ].classList.add( 'frm_hidden' );
	}
}

function empty( $obj ) {
	if ( $obj !== null ) {
		while ( $obj.firstChild ) {
			$obj.firstChild.remove();
		}
	}
}

/**
 * @since x.x
 * @return {void}
 */
export function registerRepeaterSettings() {
	const registered = frmAdminBuild.registerBuilderFeature( 'repeater_settings', {
		toggleRepeatButtons,
		checkRepeatLimit,
		checkCheckboxSelectionsLimit,
		updateRepeatText,
		toggleFormTax,
		getFieldSelection,
		getTaxOrFieldSelection,
		maybeChangeEmbedFormMsg,
	} );
	if ( ! registered ) {
		return;
	}

	jQuery( document ).on( 'change', '#new_fields .frm_repeat_format', toggleRepeatButtons );
	jQuery( document ).on( 'change', '#new_fields .frm_repeat_limit', checkRepeatLimit );
	jQuery( document ).on( 'change', '#new_fields .frm_js_checkbox_limit', checkCheckboxSelectionsLimit );
	jQuery( document ).on( 'input', '#new_fields input[name^="field_options[add_label_"]', function() {
		updateRepeatText( this, 'add' );
	} );
	jQuery( document ).on( 'input', '#new_fields input[name^="field_options[remove_label_"]', function() {
		updateRepeatText( this, 'remove' );
	} );
	jQuery( document ).on( 'change', '#new_fields .frm_tax_form_select', toggleFormTax );
	jQuery( document ).on( 'change', '#new_fields .frm_get_field_selection', getFieldSelection );
	jQuery( document ).on( 'change', '#new_fields select[name^="field_options[form_select_"]', maybeChangeEmbedFormMsg );
}
