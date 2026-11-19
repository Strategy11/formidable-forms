/* global frmAdminBuild, frmDom */

/**
 * Lite's original conditional logic option sync, for Pro versions that do not register their own.
 *
 * Unlike the original, it only restores the cached settings panels whose logic rows use
 * the edited field, and returns early when no logic row uses it. It also keeps a choice that
 * is being renamed in its place, where the original moved it to the end of the value dropdowns.
 *
 * @since x.x
 */

/**
 * Get the logic rows on the page whose trigger is a field.
 *
 * @since x.x
 *
 * @param {string|number} fieldId The trigger field ID.
 * @return {HTMLElement[]} The matching logic rows.
 */
function getLogicRowsForField( fieldId ) {
	frmAdminBuild.materializeLogicFieldSettings( fieldId );

	const builderPage = document.getElementById( 'frm_builder_page' );
	if ( ! builderPage ) {
		return [];
	}

	return Array.from( builderPage.querySelectorAll( '.frm_logic_row' ) ).filter(
		// Loose comparison, as the field ID may be a number.
		row => row.querySelector( '.frm_logic_field_opts' )?.value == fieldId
	);
}

/**
 * Get the value box of the choice that has a label.
 *
 * @since x.x
 *
 * @param {HTMLElement[]} choiceRows     The visible choice rows of the field.
 * @param {number}        optionIndex    The position of the choice in the field options.
 * @param {string}        expectedOption The choice label.
 * @param {string}        fieldId        The field ID.
 * @return {string|undefined} The saved value of the choice.
 */
function getExpectedOptionValue( choiceRows, optionIndex, expectedOption, fieldId ) {
	const labelSelector = `.field_${ fieldId }_option`;
	let choiceRow = choiceRows[ optionIndex ];
	if ( choiceRow?.querySelector( labelSelector )?.value !== expectedOption ) {
		// A filter may have changed the options, so find the choice by its label instead.
		choiceRow = choiceRows.find( row => row.querySelector( labelSelector )?.value === expectedOption );
	}

	return choiceRow?.querySelector( '.frm_option_key input[type="text"]' )?.value;
}

function adjustConditionalLogicOptionOrders( fieldId, type ) {
	const rows = getLogicRowsForField( fieldId );
	if ( ! rows.length ) {
		return;
	}

	const fieldOptions = wp.hooks.applyFilters( 'frm_conditional_logic_field_options', getFieldOptions( fieldId ), { type, fieldId } );
	const optionLength = fieldOptions.length;

	const optionsContainer = document.getElementById( `frm_field_${ fieldId }_opts` );
	const choiceRows = optionsContainer ? Array.from( optionsContainer.querySelectorAll( '.frm_single_option:not(.frm_hidden)' ) ) : [];
	const fieldChoices = optionsContainer ? Array.from( optionsContainer.querySelectorAll( 'input[data-value-on-focus]' ) ) : [];

	rows.forEach( row => {
		const logicId = row.id.split( '_' )[ 2 ];
		const valueSelect = row.querySelector( `select[name="field_options[hide_opt_${ logicId }][]"]` );
		if ( ! valueSelect ) {
			return;
		}

		for ( let optionIndex = optionLength - 1; optionIndex >= 0; optionIndex-- ) {
			const expectedOption = fieldOptions[ optionIndex ];
			let expectedOptionValue = getExpectedOptionValue( choiceRows, optionIndex, expectedOption, fieldId );
			if ( ! expectedOptionValue ) {
				expectedOptionValue = expectedOption;
			}

			const optionMatch = valueSelect.querySelector( `option[value="${ expectedOptionValue }"]` );

			const { newValue, newLabel } = getNewConditionalLogicOption( fieldId, expectedOption );

			const expectedChoiceEl = fieldChoices.find( element => element.value === expectedOption );
			if ( expectedChoiceEl ) {
				const oldValue = expectedChoiceEl.dataset.valueOnFocus;
				const oldMatch = oldValue && valueSelect.querySelector( `option[value="${ oldValue }"]` );
				if ( oldMatch ) {
					// The choice is being renamed. Keep its option in place, and let the rename update it.
					valueSelect.prepend( oldMatch );
					continue;
				}
			}
			prependValueSelectWithOptionMatch( valueSelect, optionMatch, newValue, newLabel );
		}

		const optionMatch = valueSelect.querySelector( 'option[value=""]' );
		if ( optionMatch !== null ) {
			valueSelect.prepend( optionMatch );
		}
	} );
}

/**
 * Returns an object that has a value and label for new conditional logic option, for a given option value.
 *
 * @param {number} fieldId
 * @param {string} expectedOption The expected option value.
 * @return {Object} Object with value and label for the new option.
 */
function getNewConditionalLogicOption( fieldId, expectedOption ) {
	const optionsContainer = document.getElementById( `frm_field_${ fieldId }_opts` );

	const expectedOptionInput = optionsContainer?.querySelector( `input[value="${ expectedOption }"]` );

	if ( expectedOptionInput ) {
		return frmAdminBuild.getChoiceNewValueAndLabel( expectedOptionInput );
	}

	return { newValue: expectedOption, newLabel: expectedOption };
}

function prependValueSelectWithOptionMatch( valueSelect, optionMatch, newValue, newLabel ) {
	if ( optionMatch === null && ! valueSelect.querySelector( `option[value="${ newValue }"]` ) ) {
		optionMatch = frmDom.tag( 'option', { text: newLabel } );
		optionMatch.value = newValue;
	}

	valueSelect.prepend( optionMatch );
}

function getFieldOptions( fieldId ) {
	frmAdminBuild.ensureFieldSettings( fieldId );
	const options = [];
	const optsContainer = document.getElementById( `frm_field_${ fieldId }_opts` );

	if ( ! optsContainer ) {
		return options;
	}
	const listItems = optsContainer.querySelectorAll( '.frm_single_option' );
	const { length } = listItems;

	for ( let index = 0; index < length; index++ ) {
		const li = listItems[ index ];

		if ( li.classList.contains( 'frm_hidden' ) ) {
			continue;
		}

		const input = li.querySelector( `.field_${ fieldId }_option` );
		options.push( input.value );
	}
	return options;
}

/**
 * Update the logic rows that use a field after one of its choices is renamed.
 *
 * Moved from onOptionTextBlur in admin.js.
 *
 * @since x.x
 *
 * @param {Object}      args                   The edited choice.
 * @param {string}      args.fieldId           The ID of the field that owns the choice.
 * @param {string}      args.oldValue          The choice value before the edit.
 * @param {string}      args.newValue          The choice value after the edit.
 * @param {string}      args.newLabel          The choice label after the edit.
 * @param {HTMLElement} args.settingsContainer The settings panel of the field.
 * @return {void}
 */
function updateLogicRowsForChoiceEdit( { fieldId, oldValue, newValue, newLabel, settingsContainer } ) {
	const rows = getLogicRowsForField( fieldId );
	if ( ! rows.length ) {
		return;
	}

	const fieldIds = [];
	rows.forEach( row => {
		const logicId = row.id.split( '_' )[ 2 ];
		const valueSelect = row.querySelector( `select[name="field_options[hide_opt_${ logicId }][]"]` );
		if ( ! valueSelect ) {
			return;
		}

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
				if ( ! settingsContainer.querySelector( '.frm_toggle_sep_values' )?.checked ) {
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
	} );

	// Used as a constructor, it collects the panels and moves them into the save form at once.
	const settingsMover = new frmAdminBuild.moveFieldSettings();
	fieldIds.forEach( settingId => {
		settingsMover.append( document.getElementById( `frm-single-settings-${ settingId }` ) );
	} );
	settingsMover.moveFields();
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

/**
 * @since x.x
 * @return {void}
 */
export function registerLogicOptionSync() {
	frmAdminBuild.registerBuilderFeature( 'logic_option_sync', {
		adjustConditionalLogicOptionOrders,
		updateLogicRowsForChoiceEdit,
	} );
}
