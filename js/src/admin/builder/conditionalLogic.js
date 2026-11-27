const { materializeAllFieldSettings } = require( '../deferredFieldSettings' );
module.exports = function createConditionalLogic( {
	builderPage,
	frmAdminJs,
	getAppendedLogicRow,
	getChoiceNewValueAndLabel,
	getFieldList,
	getNewRowId,
	moveFieldSettings,
	prepareLoadedFieldMarkup,
	triggerLogicRowEvent,
}, builder ) {
	const optionMap = {};

	function addFieldLogicRow() {
		/*jshint validthis:true */
		const trigger = this;
		const id = jQuery( this ).closest( '.frm-single-settings' ).data( 'fid' );
		const formId = builder.state.thisFormId;
		const logicRows = document.getElementById( `frm_logic_row_${ id }` ).querySelectorAll( '.frm_logic_row' );
		jQuery.ajax( {
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'frm_add_logic_row',
				form_id: formId,
				field_id: id,
				nonce: frmGlobal.nonce,
				meta_name: getNewRowId( logicRows, `frm_logic_${ id }_` ),
				fields: getFieldList()
			},
			success( html ) {
				jQuery( document.getElementById( `logic_${ id }` ) ).fadeOut( 'fast', function() {
					const logicRow = document.getElementById( `frm_logic_row_${ id }` );
					logicRow.insertAdjacentHTML( 'beforeend', html );

					const newRow = getAppendedLogicRow( logicRow );
					const logicRowText = newRow?.querySelector( '.frm-logic-rule-text' );
					if ( logicRowText ) {
						logicRowText.textContent = logicRow.dataset.ruleText;
					}

					const logicRows = logicRow.closest( '.frm_logic_rows' );
					logicRows.style.height = 'auto';
					jQuery( logicRows ).fadeIn( 'fast' );

					if ( newRow ) {
						triggerLogicRowEvent( 'frm_logic_row_added', newRow, logicRow, trigger );
					}
				} );
			}
		} );
		return false;
	}

	function addWatchLookupRow() {
		/*jshint validthis:true */
		const id = jQuery( this ).closest( '.frm-single-settings' ).data( 'fid' );
		const formId = builder.state.thisFormId;
		const lookupBlockRows = document.getElementById( `frm_watch_lookup_block_${ id }` ).children;

		jQuery.ajax( {
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'frm_add_watch_lookup_row',
				form_id: formId,
				field_id: id,
				row_key: getNewRowId( lookupBlockRows, `frm_watch_lookup_${ id }_` ),
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

	function resetOptionTextDetails() {
		jQuery( '.frm-single-settings ul input[type="text"][name^="field_options[options_"]' ).filter( '[data-value-on-load]' ).removeAttr( 'data-value-on-load' );
		jQuery( 'input[type="hidden"][name^=optionmap]' ).remove();
	}

	function optionTextAlreadyExists( input ) {
		const fieldId = jQuery( input ).closest( '.frm-single-settings' ).attr( 'data-fid' );
		const optionInputs = jQuery( input ).closest( 'ul' ).get( 0 ).querySelectorAll( `.field_${ fieldId }_option` );
		let index;
		let optionInput;

		for ( index in optionInputs ) {
			if ( Object.hasOwn( optionInputs, index ) ) {
				optionInput = optionInputs[ index ];
				if ( optionInput.id !== input.id && optionInput.value === input.value && optionInput.getAttribute( 'data-duplicate' ) !== 'true' ) {
					return true;
				}
			}
		}

		return false;
	}

	function onOptionTextFocus() {
		let input;
		let fieldId;

		if ( this.getAttribute( 'data-value-on-load' ) === null ) {
			this.setAttribute( 'data-value-on-load', this.value );

			fieldId = jQuery( this ).closest( '.frm-single-settings' ).attr( 'data-fid' );
			input = document.createElement( 'input' );
			input.value = this.value;
			input.setAttribute( 'type', 'hidden' );
			input.setAttribute( 'name', `optionmap[${ fieldId }][${ this.value }]` );
			this.parentNode.append( input );

			if ( optionMap[ fieldId ] === undefined ) {
				optionMap[ fieldId ] = {};
			}

			optionMap[ fieldId ][ this.value ] = input;
		}

		if ( this.getAttribute( 'data-duplicate' ) === 'true' ) {
			this.removeAttribute( 'data-duplicate' );

			// we want to use original value if actually still a duplicate
			if ( optionTextAlreadyExists( this ) ) {
				this.setAttribute( 'data-value-on-focus', this.getAttribute( 'data-value-on-load' ) );
				return;
			}
		}

		if ( '' !== this.value || frmAdminJs.new_option !== this.getAttribute( 'data-value-on-focus' ) ) {
			this.setAttribute( 'data-value-on-focus', this.value );
		}
	}

	/**
	 * Returns an object that has the old and new values and labels, when a field choice is changed.
	 *
	 * @param {HTMLElement} input The changed input element.
	 * @return {Object} Object with oldValue, oldLabel, newValue, newLabel properties.
	 */
	function getChoiceOldAndNewValues( input ) {
		const { oldValue, oldLabel } = getChoiceOldValueAndLabel( input );
		const { newValue, newLabel } = getChoiceNewValueAndLabel( input );

		return { oldValue, oldLabel, newValue, newLabel };
	}

	/**
	 * Returns an object that has the old value and label, when a field choice is changed.
	 *
	 * @param {HTMLElement} choiceElement The changed choice element.
	 * @return {Object} Object with oldValue and oldLabel properties.
	 */
	function getChoiceOldValueAndLabel( choiceElement ) {
		const usingSeparateValues = choiceElement.closest( '.frm-single-settings' ).querySelector( '.frm_toggle_sep_values' )?.checked ?? false;
		const singleOptionContainer = choiceElement.closest( '.frm_single_option' );

		let oldValue;
		let oldLabel;

		if ( usingSeparateValues ) {
			if ( choiceElement.parentElement.classList.contains( 'frm_single_option' ) ) { // label changed
				oldValue = singleOptionContainer.querySelector( '.frm_option_key input[type="text"]' ).getAttribute( 'data-value-on-focus' );
				oldLabel = choiceElement.getAttribute( 'data-value-on-focus' );
				return { oldValue, oldLabel };
			}
		}
		oldValue = choiceElement.getAttribute( 'data-value-on-focus' );
		oldLabel = singleOptionContainer.querySelector( 'input[type="text"]' ).getAttribute( 'data-value-on-focus' );

		return { oldValue, oldLabel };
	}

	function onOptionTextBlur() {
		let fieldIndex;
		let settingId;
		let setting;

		const { oldValue, oldLabel, newValue, newLabel } = getChoiceOldAndNewValues( this );

		if ( oldValue === newValue && oldLabel === newLabel ) {
			return;
		}

		const singleSettingsContainer = this.closest( '.frm-single-settings' );

		const fieldId = singleSettingsContainer.getAttribute( 'data-fid' );
		const originalValue = this.getAttribute( 'data-value-on-load' );

		// check if the newValue is already mapped to another option
		// if it is, mark as duplicate and return
		if ( optionTextAlreadyExists( this ) ) {
			this.setAttribute( 'data-duplicate', 'true' );

			if ( optionMap[ fieldId ] !== undefined && optionMap[ fieldId ][ originalValue ] !== undefined ) {
				// unmap any other change that may have happened before instead of changing it to something unused
				optionMap[ fieldId ][ originalValue ].value = originalValue;
			}

			return;
		}

		if ( optionMap[ fieldId ] !== undefined && optionMap[ fieldId ][ originalValue ] !== undefined ) {
			optionMap[ fieldId ][ originalValue ].value = newValue;
		}

		builder.fieldLoading.whenLogicSettingsLoaded( fieldId, () => {
			materializeAllFieldSettings( prepareLoadedFieldMarkup );
			const fieldIds = [];
			const rows = builderPage.querySelectorAll( '.frm_logic_row' );
			const rowLength = rows.length;
			for ( let rowIndex = 0; rowIndex < rowLength; rowIndex++ ) {
				const row = rows[ rowIndex ];
				const opts = row.querySelector( '.frm_logic_field_opts' );

				if ( opts.value !== fieldId ) {
					continue;
				}

				const logicId = row.id.split( '_' )[ 2 ];
				const valueSelect = row.querySelector( `select[name="field_options[hide_opt_${ logicId }][]"]` );

				let optionMatches;
				if ( '' === oldValue ) {
					optionMatches = [];
				} else {
					optionMatches = valueSelect.querySelectorAll( `option[value="${ oldValue }"]` );
				}

				let option;
				if ( ! optionMatches.length ) {
					optionMatches = valueSelect.querySelectorAll( `option[value="${ newValue }"]` );

					if ( ! optionMatches.length ) {
						if ( ! singleSettingsContainer.querySelector( '.frm_toggle_sep_values' )?.checked ) {
							option = searchSelectByText( valueSelect, oldValue ); // Find conditional logic option with oldValue
						}

						if ( ! option ) {
							option = document.createElement( 'option' );
							valueSelect.append( option );
						}
					}
				}

				if ( optionMatches.length ) {
					option = optionMatches[ optionMatches.length - 1 ];
				}

				option.setAttribute( 'value', newValue );
				option.textContent = newLabel;

				if ( ! fieldIds.includes( logicId ) ) {
					fieldIds.push( logicId );
				}
			}

			for ( fieldIndex in fieldIds ) {
				if ( Object.hasOwn( fieldIds, fieldIndex ) ) {
					settingId = fieldIds[ fieldIndex ];
					setting = document.getElementById( `frm-single-settings-${ settingId }` );
					moveFieldSettings( setting );
				}
			}
		} );
	}

	/**
	 * Returns an option element that matches a string with its text content.
	 *
	 * @param {HTMLElement} selectElement The select element to search.
	 * @param {string}      searchText    The text to search for.
	 * @return {HTMLElement|null}               Matching option element or null.
	 */
	function searchSelectByText( selectElement, searchText ) {
		const { options } = selectElement;

		for ( let i = 0; i < options.length; i++ ) {
			const option = options[ i ];
			if ( searchText === option.textContent ) {
				return option;
			}
		}

		return null;
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

		builder.fieldOptions.toggleMultiSelect( fieldID, this.value );
	}

	return {
		addFieldLogicRow,
		addWatchLookupRow,
		maybeClearWatchFields,
		onOptionTextBlur,
		onOptionTextFocus,
		resetOptionTextDetails,
		updateGetValueFieldSelection,
	};
};
