/* global frmAdminBuild, ajaxurl, frmGlobal */

/**
 * Lite's original field conditional logic row code, for Pro versions that do not register their own.
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

function addFieldLogicRow() {
	/*jshint validthis:true */
	const trigger = this;
	const id = jQuery( this ).closest( '.frm-single-settings' ).data( 'fid' );
	const formId = getFormId();
	const logicRows = document.getElementById( `frm_logic_row_${ id }` ).querySelectorAll( '.frm_logic_row' );
	jQuery.ajax( {
		type: 'POST',
		url: ajaxurl,
		data: {
			action: 'frm_add_logic_row',
			form_id: formId,
			field_id: id,
			nonce: frmGlobal.nonce,
			meta_name: frmAdminBuild.getNewRowId( logicRows, `frm_logic_${ id }_` ),
			fields: frmAdminBuild.getFieldList()
		},
		success( html ) {
			jQuery( document.getElementById( `logic_${ id }` ) ).fadeOut( 'fast', function() {
				const logicRow = document.getElementById( `frm_logic_row_${ id }` );
				logicRow.insertAdjacentHTML( 'beforeend', html );

				const newRow = frmAdminBuild.getAppendedLogicRow( logicRow );
				const logicRowText = newRow?.querySelector( '.frm-logic-rule-text' );
				if ( logicRowText ) {
					logicRowText.textContent = logicRow.dataset.ruleText;
				}

				const logicRows = logicRow.closest( '.frm_logic_rows' );
				logicRows.style.height = 'auto';
				jQuery( logicRows ).fadeIn( 'fast' );

				if ( newRow ) {
					frmAdminBuild.triggerLogicRowEvent( 'frm_logic_row_added', newRow, logicRow, trigger );
				}
			} );
		}
	} );
	return false;
}

function getFieldValues() {
	/*jshint validthis:true */
	let isTaxonomy;
	const val = this.value;

	if ( val ) {
		const parentIDs = this.parentNode.id.replace( 'frm_logic_', '' ).split( '_' );
		const fieldID = parentIDs[ 0 ];
		const metaKey = parentIDs[ 1 ];
		const valueField = document.getElementById( `frm_field_id_${ val }` );
		if ( ! valueField ) {
			// The condition checks something other than a field, like an add-on's option. Let the server render its value input.
			window.frmGetFieldValues( val, fieldID, metaKey, this.getAttribute( 'data-type' ) );
			return;
		}

		const valueFieldType = valueField.getAttribute( 'data-ftype' );
		const fill = document.getElementById( `frm_show_selected_values_${ fieldID }_${ metaKey }` );
		const optionName = `field_options[hide_opt_${ fieldID }][]`;
		const optionID = `frm_field_logic_opt_${ fieldID }`;
		let input = false;
		let showSelect = valueFieldType === 'select' || valueFieldType === 'checkbox' || valueFieldType === 'radio';
		const showText = valueFieldType === 'text' || valueFieldType === 'email' || valueFieldType === 'phone' || valueFieldType === 'url' || valueFieldType === 'number';

		if ( showSelect ) {
			isTaxonomy = document.getElementById( `frm_has_hidden_options_${ val }` );
			if ( isTaxonomy ) {
				// get the category options with ajax
				showSelect = false;
			}
		}

		if ( showSelect || showText ) {
			const comparison = document.querySelector( `#frm_logic_${ fieldID }_${ metaKey } [name="field_options[hide_field_cond_${ fieldID }][]"]` ).value;
			fill.innerHTML = '';
			const creatingValuesDropdown = showSelect && ! [ 'LIKE', 'not LIKE', 'LIKE%', '%LIKE' ].includes( comparison );
			if ( creatingValuesDropdown ) {
				input = document.createElement( 'select' );
			} else {
				input = document.createElement( 'input' );
				input.type = 'text';
			}
			input.name = optionName;
			input.id = `${ optionID }_${ metaKey }`;
			fill.append( input );

			if ( creatingValuesDropdown ) {
				const fillField = document.getElementById( input.id );
				frmAdminBuild.fillDropdownOpts( fillField, {
					sourceID: val,
					placeholder: '',
					other: true
				} );
			}
		} else {
			const thisType = this.getAttribute( 'data-type' );
			const callback = () => {
				const event = new CustomEvent( 'frm_logic_options_loaded' );
				event.frmData = { valueFieldType, fieldID, metaKey };
				document.dispatchEvent( event );
			};

			window.frmGetFieldValues( val, fieldID, metaKey, thisType, undefined, callback );
		}
	}
}

/**
 * @since x.x
 * @return {void}
 */
export function registerFieldLogicRows() {
	const registered = frmAdminBuild.registerBuilderFeature( 'field_logic_rows', {
		addFieldLogicRow,
		getFieldValues,
	} );
	if ( ! registered ) {
		return;
	}

	// Older Pro versions can take over adding a condition row with this filter.
	// Checked at click time, since Pro's script may not have run yet.
	jQuery( document ).on( 'click', '#new_fields .frm_add_logic_row', function() {
		if ( wp.hooks.applyFilters( 'frm_should_add_logic_row_in_lite', true ) ) {
			return addFieldLogicRow.call( this );
		}
	} );
	jQuery( document ).on( 'change', '#new_fields .frm_logic_field_opts', getFieldValues );
}
