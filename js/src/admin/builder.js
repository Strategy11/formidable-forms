const { updateAddressPreview } = require( './fieldPreview' );
const { renderFieldPreviewNow, forgetFieldPreview, setInitialFieldPreviews } = require( './fieldPreviewQueue' );
const { forgetFieldSettings, materializeFieldSettings, materializeAllFieldSettings } = require( './deferredFieldSettings' );
const { queueFieldSettings, isFieldSettingsPending, requestFieldSettingsNow, cancelFieldSettings } = require( './fieldSettingsLoader' );
const { hydrateFieldPreviews } = require( './fieldPreviews' );
const { initFieldListHoverPill } = require( './fieldListHoverPill' );
const { initFieldSizePreview } = require( './fieldSizePreview' );
const { hydrateFieldPlaceholders } = require( './fieldPlaceholders' );

function createBuilder( shared ) {
	const {
		$newFields,
		addFormNameModalEvents,
		addImageToOption,
		adjustVisibilityValuesForEveryoneValues,
		builderForm,
		builderPage,
		fieldUpdated,
		fieldsContainer,
		hydrateBuilderSelect,
		infoModal,
		initiateMultiselect,
		isProductField,
		maybeHideQuantityProductFieldOption,
		maybeHideShortcodes,
		popAllProductFields,
		removeImageFromOption,
		renumberPageBreaks,
		toggleSectionHolder,
		updateShortcodesPopupPosition,
		usingSeparateValues,
	} = shared;
	const builder = { state: shared.adminState };

	// Footer scripts can restore placeholders before add-ons inspect the builder's fields.
	hydrateFieldPlaceholders();

	// Fields drawn from page data have their list items in place before anything scans the builder.
	builder.state.browserPreviewFieldIds = hydrateFieldPreviews();

	setInitialFieldPreviews( document.getElementById( 'frm-show-fields' )?.querySelectorAll( 'li.form-field' ) || [] );

	builder.state.MAX_FIELD_GROUP_SIZE = 12;

	// Resolves once the Field Options tab is showing the selected field's settings. See showFieldOptionsTab.
	builder.state.fieldOptionsTabShown = Promise.resolve();

	let optionsPanel;

	builder.state.dragState = {
		dragging: false,
		draggable: null,
		offsetsRefreshed: false,
		submitButtonRow: null
	};

	builder.state.dragDropAttachers = new WeakMap();

	builder.state.activeFieldLoadRequests = 0;

	builder.dragDrop = require( './builder/dragDrop' )( shared, builder );
	builder.fieldLoading = require( './builder/fieldLoading' )( shared, builder );
	builder.fieldActions = require( './builder/fieldActions' )( shared, builder );
	builder.fieldOptions = require( './builder/fieldOptions' )( shared, builder );
	builder.fieldGroups = require( './builder/fieldGroups' )( shared, builder );
	builder.conditionalLogic = require( './builder/conditionalLogic' )( shared, builder );
	builder.fieldPreview = require( './builder/fieldPreview' )( shared, builder );
	builder.fieldEditor = require( './builder/fieldEditor' )( shared, builder );

	if ( builder.state.thisFormId && builder.state.browserPreviewFieldIds.length ) {
		queueFieldSettings( {
			formId: builder.state.thisFormId,
			fieldIds: builder.state.browserPreviewFieldIds,
			onLoaded: builder.fieldLoading.handleLoadedFieldSettings
		} );

		// Start the first field's small request before the background preview batches occupy the server.
		const firstFieldId = fieldsContainer.querySelector( 'li.form-field' )?.dataset.fid;
		if ( firstFieldId === builder.state.browserPreviewFieldIds[ 0 ] ) {
			requestFieldSettingsNow( firstFieldId );
		}
	}

	return {

		buildInit() {
			// Listen on the list rather than on the rows. Most of a form's fields arrive over ajax
			// after this runs, and binding to the rows that happen to be here now left those later
			// fields without their drag and save icons for good.
			$newFields.on( 'click', '> li, .frm_grid_container li', function() {
				const fieldId = this.querySelector( 'li' )?.dataset.fid || this.dataset.fid;
				builder.fieldEditor.maybeAddSaveAndDragIcons( fieldId );
				builder.fieldEditor.addOptionControlsToField( fieldId );
			} );

			builder.state.wpBodyContent = document.getElementById( 'wpbody-content' );
			builder.state.formEditorContainer = document.getElementById( 'frm_form_editor_container' );
			optionsPanel = document.getElementById( 'frm-options-panel' );
			builder.state.optionsTab = document.getElementById( 'frm-options-panel-tab' );
			builder.state.settingsScrollContainer = builderPage.querySelector( '.frm-settings-panel.frm-scrollbar-wrapper' );
			builder.state.defaultFieldSettings = optionsPanel.querySelector( ':scope > .frm-single-settings' );
			builder.state.activeSettingsPanel = builderForm.querySelector( '.frm-single-settings:not(.frm_hidden)' );
			hydrateFieldPlaceholders();
			[ 'click', 'dblclick', 'mousedown', 'mousemove', 'mouseover', 'mouseout', 'contextmenu' ].forEach( eventType => {
				fieldsContainer.addEventListener( eventType, builder.fieldLoading.ignoreLoadingFieldMouseEvent, true );
			} );
			fieldsContainer.addEventListener( 'click', builder.fieldLoading.prepareInteractedFieldSettings, true );
			fieldsContainer.addEventListener( 'focusin', event => {
				if ( event.target.matches( 'input, select, textarea, [contenteditable]' ) ) {
					builder.fieldLoading.prepareInteractedFieldSettings( event );
				}
			}, true );

			document.addEventListener( 'focusin', event => {
				if ( event.target.matches( 'select[data-frm-options]' ) ) {
					hydrateBuilderSelect( event.target );
				}
			} );
			jQuery( '#frm_builder_page' ).on( 'mouseup', '*:not(.frm-show-box)', maybeHideShortcodes );

			builder.state.debouncedSyncAfterDragAndDrop = builder.fieldEditor.debounce( builder.dragDrop.syncAfterDragAndDrop, 10 );
			builder.state.postBodyContent = document.getElementById( 'post-body-content' );
			builder.state.$postBodyContent = jQuery( builder.state.postBodyContent );

			builder.fieldLoading.observeFieldPlaceholders();
			builder.fieldLoading.fillFieldLoadQueue();

			builder.dragDrop.setupSortable( 'ul.frm_sorting' );
			builder.fieldLoading.initBrowserPreviewFields();

			document.querySelectorAll( '.field_type_list > li:not(.frm_show_upgrade):not(.frm_show_update)' ).forEach( builder.dragDrop.makeDraggable );
			initFieldListHoverPill();
			initFieldSizePreview();

			jQuery( 'ul.field_type_list, .field_type_list li, ul.frm_code_list, .frm_code_list li, .frm_code_list li a, #frm_adv_info #category-tabs li, #frm_adv_info #category-tabs li a' ).disableSelection();

			jQuery( '.frm_submit_ajax' ).on( 'click', builder.fieldEditor.submitBuild );
			jQuery( '.frm_submit_no_ajax' ).on( 'click', builder.fieldEditor.submitNoAjax );

			addFormNameModalEvents();

			jQuery( 'a.edit-form-status' ).on( 'click', builder.fieldEditor.slideDown );
			jQuery( '.cancel-form-status' ).on( 'click', builder.fieldEditor.slideUp );
			jQuery( '.save-form-status' ).on( 'click', function() {
				const newStatus = jQuery( document.getElementById( 'form_change_status' ) ).val();
				jQuery( 'input[name="new_status"]' ).val( newStatus );
				jQuery( document.getElementById( 'form-status-display' ) ).html( newStatus );
				jQuery( '.cancel-form-status' ).trigger( 'click' );
				return false;
			} );

			jQuery( '.frm_form_builder form' ).first().on( 'submit', function() {
				jQuery( '.inplace_field' ).trigger( 'blur' );
			} );

			initiateMultiselect();
			renumberPageBreaks();

			const $builderForm = jQuery( builderForm );
			const builderArea = builder.state.formEditorContainer;

			// Formidable Pro can take over adding a condition row itself (see its builder.js) once
			// it no longer needs this fallback for sites running an older, incompatible Pro version.
			// Checked at click time, not here at setup time, since Pro's script may not have run yet.
			$builderForm.on( 'click', '.frm_add_logic_row', function() {
				if ( wp.hooks.applyFilters( 'frm_should_add_logic_row_in_lite', true ) ) {
					return builder.conditionalLogic.addFieldLogicRow.call( this );
				}
			} );

			$builderForm.on( 'click', '.frm_add_watch_lookup_row', builder.conditionalLogic.addWatchLookupRow );
			$builderForm.on( 'change', '.frm_get_values_form', builder.conditionalLogic.updateGetValueFieldSelection );
			$builderForm.on( 'change', '.frm_logic_field_opts', builder.fieldPreview.getFieldValues );
			$builderForm.on( 'frm-multiselect-changed', 'select[name^="field_options[admin_only_"]', adjustVisibilityValuesForEveryoneValues );

			jQuery( document.getElementById( 'frm-insert-fields' ) ).on( 'click', '.frm_add_field', builder.fieldActions.addFieldClick );
			$newFields.on( 'click', '.frm_clone_field', builder.fieldActions.duplicateField );
			$builderForm.on( 'blur', 'input[id^="frm_calc"]', builder.fieldOptions.checkCalculationCreatedByUser );
			$builderForm.on( 'change', 'input.frm_format_opt, input.frm_max_length_opt', builder.fieldOptions.toggleInvalidMsg );
			$builderForm.on( 'change click', '[data-changeme]', builder.fieldOptions.liveChanges );
			const previewInputHandlers = new WeakMap();
			builderForm.addEventListener( 'input', event => {
				const input = event.target;
				if ( input.matches( '[data-changeme]' ) ) {
					if ( ! previewInputHandlers.has( input ) ) {
						previewInputHandlers.set( input, builder.fieldEditor.debounce( builder.fieldOptions.liveChanges ) );
					}
					previewInputHandlers.get( input ).call( input );
					fieldUpdated();
				}
			} );
			frmDom.util.documentOn( 'change', '.frm-single-settings select[id^="address_type_"]', function() {
				updateAddressPreview( this, builder.fieldOptions.liveChanges );
			} );
			$builderForm.on( 'click', 'input.frm_req_field', builder.fieldOptions.markRequired );
			$builderForm.on( 'click', '.frm_mark_unique', builder.fieldOptions.markUnique );

			$builderForm.on( 'change', '.frm_repeat_format', builder.fieldPreview.toggleRepeatButtons );
			$builderForm.on( 'change', '.frm_repeat_limit', builder.fieldPreview.checkRepeatLimit );
			$builderForm.on( 'change', '.frm_js_checkbox_limit', builder.fieldPreview.checkCheckboxSelectionsLimit );
			$builderForm.on( 'input', 'input[name^="field_options[add_label_"]', function() {
				builder.fieldPreview.updateRepeatText( this, 'add' );
			} );
			$builderForm.on( 'input', 'input[name^="field_options[remove_label_"]', function() {
				builder.fieldPreview.updateRepeatText( this, 'remove' );
			} );
			$builderForm.on( 'change', 'select[name^="field_options[data_type_"]', builder.conditionalLogic.maybeClearWatchFields );
			jQuery( builderArea ).on( 'click', '.frm-collapse-page', builder.fieldPreview.maybeCollapsePage );
			jQuery( builderArea ).on( 'click', '.frm-collapse-section', builder.fieldPreview.maybeCollapseSection );
			$builderForm.on( 'click', '.frm-single-settings h3, .frm-single-settings h4.frm-collapsible', builder.fieldPreview.maybeCollapseSettings );
			$builderForm.on( 'keydown', '.frm-single-settings h3, .frm-single-settings h4.frm-collapsible', function( event ) {
				// If so, only proceed if the key pressed was 'Enter' or 'Space'
				if ( event.key === 'Enter' || event.key === ' ' ) {
					event.preventDefault();
					builder.fieldPreview.maybeCollapseSettings.call( this, event );
				}
			} );

			jQuery( builderArea ).on( 'show.bs.dropdown hide.bs.dropdown', builder.fieldActions.changeSectionStyle );

			$builderForm.on( 'change', '.frm_toggle_sep_values', builder.fieldOptions.toggleSepValues );
			$builderForm.on( 'change', '.frm_toggle_image_options', builder.fieldOptions.toggleImageOptions );
			$builderForm.on( 'click', '.frm_remove_image_option', removeImageFromOption );
			$builderForm.on( 'click', '.frm_choose_image_box', addImageToOption );
			$builderForm.on( 'change', '.frm_hide_image_text', builder.fieldOptions.refreshOptionDisplay );
			$builderForm.on( 'change', '.frm_field_options_image_size', builder.fieldOptions.setImageSize );
			$builderForm.on( 'click', '.frm_multiselect_opt', builder.fieldOptions.toggleMultiselect );
			$newFields.on( 'mousedown', 'input, textarea, select', builder.fieldOptions.stopFieldFocus );
			$newFields.on( 'click', 'input[type=radio], input[type=checkbox]', builder.fieldOptions.stopFieldFocus );
			$newFields.on( 'click', '.frm_delete_field', builder.fieldOptions.clickDeleteField );
			$newFields.on( 'click', '.frm_select_field', builder.fieldOptions.clickSelectField );
			jQuery( document ).on( 'click', '.frm_delete_field_group', builder.fieldOptions.clickDeleteFieldGroup );
			jQuery( document ).on( 'click', '.frm_clone_field_group', builder.fieldOptions.duplicateFieldGroup );
			jQuery( document ).on( 'click', '#frm_field_group_controls > span:first-child', builder.fieldGroups.clickFieldGroupLayout );
			jQuery( document ).on( 'click', '.frm-row-layout-option', builder.fieldGroups.handleFieldGroupLayoutOptionClick );
			jQuery( document ).on( 'click', '.frm-merge-fields-into-row .frm-row-layout-option', builder.fieldGroups.handleFieldGroupLayoutOptionInsideMergeClick );
			jQuery( document ).on( 'click', '.frm-custom-field-group-layout', builder.fieldGroups.customFieldGroupLayoutClick );
			jQuery( document ).on( 'click', '.frm-merge-fields-into-row .frm-custom-field-group-layout', builder.fieldGroups.customFieldGroupLayoutInsideMergeClick );
			jQuery( document ).on( 'click', '.frm-break-field-group', builder.fieldGroups.breakFieldGroupClick );
			$newFields.on( 'click', '#frm_field_group_popup .frm_grid_container input', builder.fieldGroups.focusFieldGroupInputOnClick );
			jQuery( document ).on( 'click', '.frm-cancel-custom-field-group-layout', builder.fieldGroups.cancelCustomFieldGroupClick );
			jQuery( document ).on( 'click', '.frm-save-custom-field-group-layout', builder.fieldGroups.saveCustomFieldGroupClick );
			$newFields.on( 'click', 'ul.frm_sorting', builder.fieldGroups.fieldGroupClick );
			jQuery( document ).on( 'click', '.frm-merge-fields-into-row', builder.fieldGroups.mergeFieldsIntoRowClick );
			jQuery( document ).on( 'click', '.frm-delete-field-groups', builder.fieldGroups.deleteFieldGroupsClick );
			$newFields.on( 'click', '.frm-field-action-icons [data-toggle="dropdown"]', function() {
				this.closest( 'li.form-field' ).classList.add( 'frm-field-settings-open' );
				jQuery( document ).on( 'click', '#frm_builder_page', builder.fieldActions.handleClickOutsideOfFieldSettings );
			} );
			$newFields.on( 'mousemove', 'ul.frm_sorting', builder.fieldActions.checkForMultiselectKeysOnMouseMove );
			$newFields.on( 'show.bs.dropdown', '.frm-field-action-icons', builder.fieldActions.onFieldActionDropdownShow );
			jQuery( document ).on( 'show.bs.dropdown', '#frm_field_group_controls', builder.fieldActions.onFieldGroupActionDropdownShow );
			$builderForm.on( 'click', '.frm_single_option a[data-removeid]', builder.fieldOptions.deleteFieldOption );
			$builderForm.on( 'mousedown', '.frm_single_option input[type=radio]', builder.fieldOptions.maybeUncheckRadio );
			$builderForm.on( 'focusin', '.frm_single_option input[type=text]', builder.fieldOptions.maybeClearOptText );
			$builderForm.on( 'click', '.frm_add_opt', builder.fieldOptions.addFieldOption );
			$builderForm.on( 'change', '.frm_single_option input', builder.fieldPreview.resetOptOnChange );
			$builderForm.on( 'change', '.frm_image_id', builder.fieldPreview.resetOptOnChange );
			$builderForm.on( 'change', '.frm_toggle_mult_sel', builder.fieldOptions.toggleMultSel );

			$newFields.on( 'click', '.frm_primary_label', builder.fieldPreview.clickLabel );
			$newFields.on( 'click', '.frm_description', builder.fieldPreview.clickDescription );
			$newFields.on( 'click', 'li.ui-state-default:not(.frm_noallow)', builder.fieldPreview.clickVis );
			$newFields.on( 'dblclick', 'li.ui-state-default', builder.fieldPreview.openAdvanced );
			$builderForm.on( 'change', '.frm_tax_form_select', builder.fieldPreview.toggleFormTax );
			$builderForm.on( 'change', 'select.conf_field', builder.fieldOptions.addConf );

			$builderForm.on( 'change', '.frm_get_field_selection', builder.fieldPreview.getFieldSelection );

			$builderForm.on( 'click', '.frm-show-inline-modal', builder.fieldEditor.maybeShowInlineModal );
			$builderForm.on( 'keydown', '.frm-show-inline-modal', function( event ) {
				const { key } = event;
				if ( key === 'Enter' || key === ' ' ) {
					event.preventDefault();
					builder.fieldEditor.maybeShowInlineModal.call( this, event );
				}
			} );

			$builderForm.on( 'click', '.frm-inline-modal .dismiss', builder.fieldEditor.dismissInlineModal );
			jQuery( document ).on( 'change', '[data-frmchange]', builder.fieldEditor.changeInputtedValue );

			document.addEventListener( 'click', builder.fieldEditor.closeModalOnOutsideClick );

			$builderForm.on( 'change', '.frm_include_extras_field', builder.fieldOptions.rePopCalcFieldsForSummary );
			$builderForm.on( 'change', 'select[name^="field_options[form_select_"]', builder.fieldEditor.maybeChangeEmbedFormMsg );

			jQuery( document ).on( 'submit', '#frm_js_build_form', builder.fieldEditor.buildSubmittedNoAjax );
			jQuery( document ).on( 'change', '#frm_builder_page input:not(.frm-search-input):not(.frm-custom-grid-size-input), #frm_builder_page select, #frm_builder_page textarea', fieldUpdated );

			popAllProductFields();

			frmDom.util.documentOn( 'change', '.frm_product_price', builder.fieldEditor.validateProductPriceValue );
			jQuery( document ).on( 'change', '.frmjs_prod_data_type_opt', builder.fieldEditor.toggleProductType );

			jQuery( document ).on( 'focus', '.frm-single-settings ul input[type="text"][name^="field_options[options_"]', builder.conditionalLogic.onOptionTextFocus );
			jQuery( document ).on( 'blur', '.frm-single-settings ul input[type="text"][name^="field_options[options_"]', builder.conditionalLogic.onOptionTextBlur );

			frmDom.util.documentOn( 'click', '.frm-show-field-settings', builder.fieldPreview.clickVis );
			frmDom.util.documentOn( 'change', 'select.frm_format_dropdown, select.frm_phone_type_dropdown', builder.fieldPreview.maybeUpdateFormatInput );

			// Navigate to the next input field on pressing Enter in a single option field
			$builderForm.on( 'keydown', '.frm_single_option input[name^="field_options["], .frm_single_option input[name^="rows_"]', event => {
				if ( 'Enter' === event.key ) {
					builder.fieldEditor.focusNextSingleOptionInput( event.currentTarget );
				}
			} );

			builder.fieldOptions.initBulkOptionsOverlay();
			builder.fieldEditor.hideEmptyEle();
			document.addEventListener( 'frm_added_field', event => builder.fieldEditor.hideEmptyEle( event.frmField ) );
			wp.hooks.addAction( 'frm_field_settings_loaded', 'formidableAdmin', builder.fieldEditor.hideEmptyEle );
			maybeHideQuantityProductFieldOption();
			builder.fieldEditor.handleNameFieldOnFormBuilder();
			toggleSectionHolder();
			builder.fieldEditor.handleShowPasswordLiveUpdate();
			document.addEventListener( 'scroll', updateShortcodesPopupPosition, true );
			document.addEventListener( 'change', builder.fieldEditor.handleBuilderChangeEvent );
			document.querySelector( '.frm_form_builder' ).addEventListener( 'mousedown', event => {
				if ( event.shiftKey ) {
					event.preventDefault();
				}
			} );
			wp.hooks.addAction( 'frmShowedFieldSettings', 'formidableAdmin', ( showBtn, fieldSettingsEl ) => {
				builder.fieldEditor.hideEmptyEle( fieldSettingsEl );
				fieldSettingsEl.querySelectorAll( '.frm-collapse-me' ).forEach( builder.fieldPreview.addSlideAnimationCssVars );
				builder.dragDrop.setupFieldOptionSorting( fieldSettingsEl );
			}, 9999 );

			if ( frm_admin_js.pricingFieldsModal && 'object' === typeof frm_admin_js.pricingFieldsModal ) {
				infoModal( frm_admin_js.pricingFieldsModal, '550px' );
			}

			if ( ! builder.state.activeFieldLoadRequests ) {
				builder.fieldOptions.scheduleBulkOptionsOverlay();
			}
		},

		updateOpts( fieldId, opts, modal ) {
			const separate = usingSeparateValues( fieldId );
			const action = isProductField( fieldId ) ? 'frm_bulk_products' : 'frm_import_options';
			jQuery.ajax( {
				type: 'POST',
				url: ajaxurl,
				data: {
					action,
					field_id: fieldId,
					opts,
					separate,
					nonce: frmGlobal.nonce
				},
				success( html ) {
					document.getElementById( `frm_field_${ fieldId }_opts` ).innerHTML = html;
					wp.hooks.doAction( 'frm_after_bulk_edit_opts', fieldId );
					builder.fieldPreview.resetDisplayedOpts( fieldId );

					if ( modal !== undefined ) {
						modal.dialog( 'close' );
						document.getElementById( 'frm-update-bulk-opts' ).classList.remove( 'frm_loading_button' );
					}
				}
			} );
		},

		addRadioCheckboxOpt: builder.fieldPreview.addRadioCheckboxOpt,
		insertFormField: builder.fieldActions.insertFormField,
		handleInsertFieldByDraggingResponse: builder.dragDrop.handleInsertFieldByDraggingResponse,
		handleAddFieldClickResponse: builder.fieldActions.handleAddFieldClickResponse,
		syncAfterDragAndDrop: builder.dragDrop.syncAfterDragAndDrop,
		maybeCollapseSettings: builder.fieldPreview.maybeCollapseSettings,
	};
}

wp.hooks.addFilter( 'frmAdminBuilder', 'formidable', () => {
	const fieldsAwaitingSettings = new Set();
	return {
		createBuilder: shared => createBuilder( { ...shared, fieldsAwaitingSettings } ),
		renderFieldPreviewNow,
		materializeAllFieldSettings,
		ensureFieldSettings( fieldId, prepareLoadedFieldMarkup ) {
			renderFieldPreviewNow( fieldId );
			const settings = materializeFieldSettings( fieldId, prepareLoadedFieldMarkup );
			if ( ! settings && isFieldSettingsPending( fieldId ) ) {
				// Finish the job once the settings arrive. See handleLoadedFieldSettings.
				fieldsAwaitingSettings.add( String( fieldId ) );
				requestFieldSettingsNow( fieldId );
			}
			return settings;
		},
		forgetField( fieldId ) {
			fieldsAwaitingSettings.delete( String( fieldId ) );
			forgetFieldSettings( fieldId );
			forgetFieldPreview( fieldId );
			cancelFieldSettings( fieldId );
		},
	};
} );
