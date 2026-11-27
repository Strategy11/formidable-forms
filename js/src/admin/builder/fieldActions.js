const { UNRENDERED_FIELD_SELECTOR } = require( '../fieldPreviewSelectors' );
const { captureFieldSettings, applyFieldSettings, restoreFieldSettingValues } = require( '../duplicateFieldSettings' );

module.exports = function createFieldActions( {
	$newFields,
	builderPage,
	countFieldTypeInForm,
	deleteTooltips,
	deselectFields,
	ensureFieldSettings,
	fieldUpdated,
	fieldsContainer,
	getFieldsInRow,
	getLayoutClasses,
	hydrateBuilderSelectsIn,
	infoModal,
	initiateMultiselect,
	makeTabbable,
	maybeHideQuantityProductFieldOption,
	moveFieldSettings,
	syncLayoutClasses,
	toggleOneSectionHolder,
	unselectFieldGroups,
	updateFieldGroupControls,
}, builder ) {
	const { tag, div } = frmDom;
	const { __, sprintf } = wp.i18n;
	let activeHoverRow = null;

	function addFieldClick() {
		/*jshint validthis:true */
		const $thisObj = jQuery( this );
		// there is no real way to disable a <a> (with a valid href attribute) in HTML - https://css-tricks.com/how-to-disable-links/
		if ( $thisObj.hasClass( 'disabled' ) ) {
			return false;
		}

		const $button = $thisObj.closest( '.frmbutton' );
		const fieldType = $button.attr( 'id' );

		if ( $button.hasClass( 'frm_at_limit' ) ) {
			showLimitModal();
			return false;
		}

		if ( frm_admin_js.paymentsSettingsModal && 'object' === typeof frm_admin_js.paymentsSettingsModal && [ 'product', 'quantity', 'total' ].includes( fieldType ) ) {
			// These fields require payment gateway installed.
			infoModal( frm_admin_js.paymentsSettingsModal );
			return false;
		}

		if ( builder.dragDrop.shouldStopInsertingField( fieldType ) ) {
			// We do not want to return false here.
			// Otherwise it causes issues with trying to add a new slider field
			// when clicking the button.
			return;
		}

		let hasBreak = 0;
		if ( 'summary' === fieldType ) {
			hasBreak = $newFields.children( 'li[data-type="break"]' ).length > 0 ? 1 : 0;
		}

		const formId = builder.state.thisFormId;
		jQuery.ajax( {
			type: 'POST',
			url: ajaxurl,
			data: builder.dragDrop.getInsertNewFieldArgs( fieldType, 0, formId, hasBreak ),
			success( msg ) {
				handleAddFieldClickResponse( msg );

				const fieldId = builder.dragDrop.checkMsgForFieldId( msg );
				if ( fieldId ) {
					/**
					 * Fires after a field is added.
					 *
					 * @since 6.23
					 *
					 * @param {Object} fieldData            The field data.
					 * @param {string} fieldData.field      The field HTML.
					 * @param {string} fieldData.field_type The field type.
					 * @param {string} fieldData.form_id    The form ID.
					 */
					wp.hooks.doAction( 'frm_after_field_added_in_form_builder', {
						field: msg,
						fieldId,
						fieldType,
						form_id: formId,
					} );
				}
			},
			error: builder.dragDrop.handleInsertFieldError
		} );
		return false;
	}

	function showLimitModal() {
		const wrapper = document.querySelector( '.frm_wrap' );
		if ( ! wrapper ) {
			return;
		}

		const temporaryAnchor = document.createElement( 'a' );
		temporaryAnchor.setAttribute( 'data-frmverify', __( 'This field type has reached its limit.', 'formidable' ) );

		wrapper.append( temporaryAnchor );
		temporaryAnchor.click();
		temporaryAnchor.remove();

		const confirmButton = document.getElementById( 'frm-confirmed-click' );
		if ( confirmButton ) {
			confirmButton.style.display = 'none';
		}
	}

	function handleAddFieldClickResponse( msg ) {
		builder.state.formEditorContainer.classList.add( 'frm-has-fields' );
		const replaceWith = wrapFieldLi( msg );
		const submitField = $newFields[ 0 ].querySelector( '.edit_field_type_submit' );

		if ( ! submitField ) {
			$newFields.append( replaceWith );
		} else {
			jQuery( submitField.closest( '.frm_field_box:not(.form-field)' ) ).before( replaceWith );
		}

		afterAddField( msg, true );

		replaceWith.each(
			function() {
				builder.dragDrop.makeDroppable( this.querySelector( 'ul.frm_sorting' ) );
				builder.dragDrop.makeDraggable( this.querySelector( '.form-field' ), '.frm-move' );
			}
		);
	}

	function insertFormField( fieldType, fieldOptions = {} ) {
		return new Promise( resolve => {
			const formId = builder.state.thisFormId;
			let hasBreak = 0;

			if ( 'summary' === fieldType ) {
				hasBreak = $newFields.children( 'li[data-type="break"]' ).length > 0 ? 1 : 0;
			}

			jQuery.ajax( {
				type: 'POST',
				url: ajaxurl,
				data: Object.assign( builder.dragDrop.getInsertNewFieldArgs( fieldType, 0, formId, hasBreak ), { field_options: fieldOptions } ),
				success( msg ) {
					resolve( msg );

					setTimeout( () => {
						afterAddField( msg, true );

						const fieldId = builder.dragDrop.checkMsgForFieldId( msg );
						if ( fieldId ) {
							/**
							 * Fires after a field is added.
							 *
							 * @since 6.23
							 *
							 * @param {Object} fieldData            The field data.
							 * @param {string} fieldData.field      The field HTML.
							 * @param {string} fieldData.field_type The field type.
							 * @param {string} fieldData.form_id    The form ID.
							 */
							wp.hooks.doAction( 'frm_after_field_added_in_form_builder', {
								field: msg,
								fieldId,
								fieldType,
								form_id: formId,
							} );
						}
					}, 10 );
				},
				error: builder.dragDrop.handleInsertFieldError
			} );
		} );
	}

	/**
	 * Returns true if a field can be duplicated.
	 *
	 * @since 6.19
	 *
	 * @param {HTMLElement} field
	 * @param {number}      maxFieldsInGroup Maximum allowed fields in a group.
	 *
	 * @return {boolean} True if the field can be added to the group.
	 */
	function canDuplicateField( field, maxFieldsInGroup ) {
		if ( field.classList.contains( 'frm-page-collapsed' ) ) {
			return false;
		}
		const fieldGroup = field.closest( 'li.frm_field_box:not(.form-field)' );
		if ( ! fieldGroup ) {
			return true;
		}
		const fieldsInGroup = getFieldsInRow( jQuery( fieldGroup.querySelector( 'ul' ) ) ).length;
		return fieldsInGroup < maxFieldsInGroup;
	}

	function duplicateField() {
		const maxFieldsInGroup = builder.state.MAX_FIELD_GROUP_SIZE;

		const $field = jQuery( this ).closest( 'li.form-field' );
		const newRowId = this.getAttribute( 'frm-target-row-id' );

		if ( ! newRowId?.startsWith( 'frm_field_group_' ) && ! canDuplicateField( $field.get( 0 ), maxFieldsInGroup ) ) {
			/* translators: %1$d: Maximum number of fields allowed in a field group. */
			infoModal( sprintf( __( 'You can only have a maximum of %1$d fields in a field group. Delete or move out a field from the group and try again.', 'formidable' ), maxFieldsInGroup ) );
			return;
		}

		closeOpenFieldDropdowns();
		const fieldId = $field.data( 'fid' );
		const children = builder.fieldPreview.fieldsInSection( fieldId );
		const originalSettings = ensureFieldSettings( fieldId );
		if ( originalSettings ) {
			hydrateBuilderSelectsIn( originalSettings );
		}
		const settingsSnapshot = originalSettings
			? captureFieldSettings( originalSettings, $field[ 0 ] ) : null;
		let fieldOrder;

		if ( null !== newRowId ) {
			fieldOrder = this.getAttribute( 'frm-field-order' );
		}

		jQuery.ajax( {
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'frm_duplicate_field',
				field_id: fieldId,
				form_id: builder.state.thisFormId,
				children,
				nonce: frmGlobal.nonce
			},
			success( msg ) {
				const duplicatedFieldId = 'string' === typeof msg ? builder.dragDrop.checkMsgForFieldId( msg ) : 0;
				if ( ! duplicatedFieldId ) {
					// Nothing was created, for example when the field type is not allowed where the field is.
					infoModal( __( 'This field could not be duplicated.', 'formidable' ) );
					return;
				}

				let newRow;

				let replaceWith;

				if ( null !== newRowId ) {
					newRow = document.getElementById( newRowId );
					if ( newRow ) {
						replaceWith = builder.dragDrop.msgAsjQueryObject( msg );
						jQuery( newRow ).append( replaceWith );
						builder.dragDrop.makeDraggable( replaceWith.get( 0 ), '.frm-move' );
						if ( null !== fieldOrder ) {
							newRow.lastElementChild.setAttribute( 'frm-field-order', fieldOrder );
						}
						afterAddField( msg, false );
						maybeDuplicateUnsavedSettings( settingsSnapshot, duplicatedFieldId );
						setLayoutClassesForDuplicatedFieldInGroup( $field[ 0 ], document.getElementById( `frm_field_id_${ duplicatedFieldId }` ) );
						jQuery( newRow ).trigger(
							'frm_added_duplicated_field_to_row',
							{
								duplicatedFieldHtml: msg,
								originalFieldId: fieldId
							}
						);
						return;
					}
				}

				if ( $field.siblings( 'li.form-field' ).length ) {
					replaceWith = builder.dragDrop.msgAsjQueryObject( msg );
					$field.after( replaceWith );
					syncLayoutClasses( $field );
					builder.dragDrop.makeDraggable( replaceWith.get( 0 ), '.frm-move' );
				} else {
					replaceWith = wrapFieldLi( msg );
					$field.parent().parent().after( replaceWith );
					builder.dragDrop.makeDroppable( replaceWith.get( 0 ).querySelector( 'ul.frm_sorting' ) );
					builder.dragDrop.makeDraggable( replaceWith.get( 0 ).querySelector( 'li.form-field' ), '.frm-move' );
				}

				afterAddField( msg, false );
				maybeDuplicateUnsavedSettings( settingsSnapshot, duplicatedFieldId );
				toggleOneSectionHolder( replaceWith.find( '.start_divider' ) );
				$field[ 0 ].querySelector( '.frm-dropdown-menu.dropdown-menu-right' )?.classList.remove( 'show' );
				setLayoutClassesForDuplicatedFieldInGroup( $field[ 0 ], document.getElementById( `frm_field_id_${ duplicatedFieldId }` ) );
			}
		} );
		return false;
	}

	/**
	 * Sets the layout classes for a duplicated field in a field group from the layout classes of the original field.
	 *
	 * @param {HTMLElement} field    The original field.
	 * @param {HTMLElement} newField The duplicated field.
	 *
	 * @return {void}
	 */
	function setLayoutClassesForDuplicatedFieldInGroup( field, newField ) {
		if ( ! newField || ! field.closest( 'li.frm_field_box:not(.form-field)' ) ) {
			return;
		}
		const classesInput = document.getElementById( `frm_classes_${ newField.dataset.fid }` );
		if ( ! classesInput ) {
			return;
		}
		const layoutClasses = getLayoutClasses();
		const fieldClasses = classesInput.value.split( /\s+/ ).filter(
			name => name && name !== 'frm_first' && ! layoutClasses.includes( name )
		);
		const layoutClass = builder.fieldGroups.getLayoutClassName( field.classList );
		if ( layoutClass ) {
			fieldClasses.push( layoutClass );
		}
		classesInput.value = fieldClasses.join( ' ' );
		classesInput.dispatchEvent( new Event( 'change', { bubbles: true } ) );
	}

	/**
	 * Overlay unsaved controls and refresh their UI after PHP creates the saved-value copy.
	 *
	 * @since 7.1
	 * @param {Object|null} snapshot   Source settings captured before the duplication request.
	 * @param {number}      newFieldId The field ID returned by PHP.
	 * @return {void}
	 */
	function maybeDuplicateUnsavedSettings( snapshot, newFieldId ) {
		if ( ! snapshot ) {
			return;
		}
		const settings = ensureFieldSettings( newFieldId );
		const field = document.getElementById( `frm_field_id_${ newFieldId }` );
		if ( ! settings || ! field ) {
			return;
		}
		hydrateBuilderSelectsIn( settings );
		moveFieldSettings( settings );
		const changed = applyFieldSettings( snapshot, settings, field );
		changed.forEach( input => {
			if ( input.closest( '.frm_sortable_field_opts' ) ||
				input.name === `field_options[type_${ newFieldId }]` ||
				input.dataset.changeatt === 'class' ) {
				return;
			}
			if ( input.classList.contains( 'wp-editor-area' ) ) {
				window.tinymce?.get( input.id )?.setContent( input.value );
			}
			input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		} );
		// Handlers such as image toggles choose defaults. The source's values take precedence.
		restoreFieldSettingValues( snapshot, settings );
		const classesInput = settings.querySelector( '.frm_classes' );
		if ( classesInput ) {
			if ( field.closest( 'li.frm_field_box:not(.form-field)' ) ) {
				classesInput.value = classesInput.value.replace( /\bfrm_first\b/g, '' ).trim();
			}
			classesInput.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		}
		// Plain preview bindings also cover defaults read directly from TinyMCE before blur.
		settings.querySelectorAll( '[data-changeme]:not(.frm_classes)' ).forEach( input => builder.fieldOptions.liveChanges.call( input ) );
		const required = settings.querySelector( '.frm_req_field' );
		if ( required ) {
			field.querySelector( '.frm_required' )?.classList.toggle( 'frm_hidden', ! required.checked );
			builder.fieldOptions.toggleValidationBox( required.checked, `.frm_required_details${ newFieldId }` );
		}
		const unique = settings.querySelector( '.frm_mark_unique' );
		if ( unique ) {
			builder.fieldOptions.toggleValidationBox( unique.checked, `.frm_unique_details${ newFieldId }` );
		}
		if ( settings.querySelector( '.frm_sortable_field_opts' ) ) {
			builder.fieldPreview.resetDisplayedOpts( newFieldId );
		}
		fieldUpdated();
	}

	function closeOpenFieldDropdowns() {
		const openSettings = document.querySelector( '.frm-field-settings-open' );
		if ( openSettings ) {
			openSettings.classList.remove( 'frm-field-settings-open' );
			jQuery( document ).off( 'click', '#frm_builder_page', handleClickOutsideOfFieldSettings );
			jQuery( '.frm-field-action-icons .dropdown.open' ).removeClass( 'open' );
		}
	}

	function handleClickOutsideOfFieldSettings( event ) {
		if ( ! jQuery( event.originalEvent.target ).closest( '.frm-field-action-icons' ).length ) {
			closeOpenFieldDropdowns();
		}
	}

	function checkForMultiselectKeysOnMouseMove( event ) {
		const keyIsDown = Boolean( event.ctrlKey || event.metaKey || event.shiftKey );
		builderPage.classList.toggle( 'frm-multiselect-key-is-down', keyIsDown );
		checkForActiveHoverTarget( event );
	}

	/**
	 * @since 7.1
	 * @return {HTMLElement|null} The hovered row, while it remains in the builder.
	 */
	function getActiveHoverRow() {
		if ( activeHoverRow && ( ! activeHoverRow.isConnected || ! activeHoverRow.classList.contains( 'frm-field-group-hover-target' ) ) ) {
			activeHoverRow = null;
		}
		return activeHoverRow;
	}

	function checkForActiveHoverTarget( event ) {
		const container = builder.state.postBodyContent;
		if ( container.classList.contains( 'frm-dragging-field' ) ) {
			return;
		}

		const hoverRow = getActiveHoverRow();
		if ( hoverRow?.querySelector( '.frm-field-settings-open' ) ) {
			// do not set a hover target if a dropdown is open for the current hover target.
			return;
		}

		const { target } = event;
		if ( ! target.classList.contains( 'edit_field_type_divider' ) ) {
			const list = target.closest( 'ul.frm_sorting' );

			if ( null !== list && ! list.classList.contains( 'start_divider' ) && 'frm-show-fields' !== list.id ) {
				if ( list.querySelector( `:scope > :is(${ UNRENDERED_FIELD_SELECTOR })` ) ) {
					maybeRemoveGroupHoverTarget();
					return;
				}

				if ( list === hoverRow ) {
					return;
				}

				const previousHoverTarget = maybeRemoveGroupHoverTarget();
				if ( false !== previousHoverTarget && ! jQuery( previousHoverTarget ).is( list ) ) {
					builder.fieldGroups.destroyFieldGroupPopup();
				}
				updateFieldGroupControls( jQuery( list ), getFieldsInRow( jQuery( list ) ).length );
				list.classList.add( 'frm-field-group-hover-target' );
				activeHoverRow = list;
				builder.state.wpBodyContent.addEventListener( 'mousemove', maybeRemoveHoverTargetOnMouseMove );
			}
		}
	}

	function maybeRemoveGroupHoverTarget() {
		const controls = builder.state.fieldGroupControls;
		if ( controls ) {
			controls.style.display = 'none';
		}

		const previousHoverTarget = getActiveHoverRow();
		builder.state.wpBodyContent.removeEventListener( 'mousemove', maybeRemoveHoverTargetOnMouseMove );
		if ( ! previousHoverTarget ) {
			return false;
		}

		previousHoverTarget.classList.remove( 'frm-field-group-hover-target' );
		activeHoverRow = null;
		return previousHoverTarget;
	}

	function maybeRemoveHoverTargetOnMouseMove( event ) {
		const target = builder.state.dragState.dragging ? document.elementFromPoint( event.clientX, event.clientY ) : event.target;
		if ( fieldsContainer.contains( target ) ) {
			return;
		}
		maybeRemoveGroupHoverTarget();
		deleteTooltips();
	}

	function onFieldActionDropdownShow( isFieldGroup ) {
		unselectFieldGroups();

		// maybe offset the dropdown if it goes off the edge of the screen or container.
		setTimeout(
			function() {
				const ul = document.querySelector( '.dropdown .frm-dropdown-menu.show' );

				if ( ! ul ) {
					return;
				}
				if ( null === ul.getAttribute( 'aria-label' ) ) {
					ul.setAttribute( 'aria-label', __( 'More Options', 'formidable' ) );
				}
				if ( 0 === ul.children.length ) {
					fillFieldActionDropdown( ul, true === isFieldGroup );
				}
				const $ul = jQuery( ul );
				if ( $ul.offset().left > jQuery( window ).width() - $ul.outerWidth() ) {
					ul.style.left = `${ -$ul.outerWidth() }px`;
				} else if ( builder.state.postBodyContent && $ul.offset().left < builder.state.postBodyContent.getBoundingClientRect().left ) {
					ul.style.right = 'auto';
					ul.style.left = '0';
				}
				const firstAnchor = ul.firstElementChild.querySelector( 'a' );
				if ( firstAnchor ) {
					firstAnchor.focus();
				}
			},
			0
		);
	}

	function onFieldGroupActionDropdownShow() {
		onFieldActionDropdownShow( true );
	}

	function changeSectionStyle( e ) {
		const collapsedSection = e.target.closest( '.frm-section-collapsed' );
		if ( ! collapsedSection ) {
			return;
		}

		if ( e.type === 'show' ) {
			collapsedSection.style.zIndex = 3;
		} else {
			collapsedSection.style.zIndex = 1;
		}
	}

	function fillFieldActionDropdown( ul, isFieldGroup ) {
		const classSuffix = isFieldGroup ? '_field_group' : '_field';
		const options = [ getDeleteActionOption( isFieldGroup ), getDuplicateActionOption( isFieldGroup ) ];
		if ( ! isFieldGroup ) {
			options.push(
				{ class: 'frm_select', icon: 'frm_settings_icon', label: __( 'Field Settings', 'formidable' ) }
			);
		}
		options.forEach(
			function( option ) {
				const li = document.createElement( 'div' );
				li.classList.add( 'frm_more_options_li', 'dropdown-item' );

				const anchor = document.createElement( 'a' );
				anchor.classList.add( option.class + classSuffix );
				anchor.setAttribute( 'href', '#' );
				makeTabbable( anchor );

				const span = document.createElement( 'span' );
				span.textContent = option.label;
				anchor.innerHTML = `<svg class="frmsvg"><use href="#${ option.icon }"></use></svg>`;
				anchor.append( document.createTextNode( ' ' ) );
				anchor.append( span );

				li.append( anchor );
				ul.append( li );
			}
		);
	}

	function getDeleteActionOption( isFieldGroup ) {
		const option = { class: 'frm_delete', icon: 'frm_delete_icon' };
		option.label = isFieldGroup ? __( 'Delete Group', 'formidable' ) : __( 'Delete', 'formidable' );
		return option;
	}

	function getDuplicateActionOption( isFieldGroup ) {
		const option = { class: 'frm_clone', icon: 'frm_clone_icon' };
		option.label = isFieldGroup ? __( 'Duplicate Group', 'formidable' ) : __( 'Duplicate', 'formidable' );
		return option;
	}

	function wrapFieldLi( field ) {
		const wrapper = div();

		if ( 'string' === typeof field ) {
			wrapper.innerHTML = field;
		} else {
			wrapper.append( field );
		}

		let result = jQuery();
		Array.from( wrapper.children ).forEach(
			li => {
				result = result.add(
					jQuery( '<li>' )
						.addClass( 'frm_field_box' )
						.html(
							jQuery( '<ul>' ).addClass( 'frm_grid_container frm_sorting' ).append( li )
						)
				);
			}
		);

		return result;
	}

	function wrapFieldLiInPlace( li ) {
		const ul = tag(
			'ul',
			{
				className: 'frm_grid_container frm_sorting'
			}
		);
		const wrapper = tag(
			'li',
			{
				className: 'frm_field_box',
				child: ul
			}
		);

		li.replaceWith( wrapper );
		ul.append( li );

		builder.dragDrop.makeDroppable( ul );
		builder.dragDrop.makeDraggable( wrapper, '.frm-move' );
	}

	function afterAddField( msg, addFocus ) {
		const regex = /id="(\S+)"/;
		const match = regex.exec( msg );
		const field = match ? document.getElementById( match[ 1 ] ) : null;
		if ( ! field ) {
			return;
		}
		const section = wp.hooks.applyFilters( 'frm_builder_new_container_selector', `#${ match[ 1 ] }.edit_field_type_divider ul.frm_sorting.start_divider`, field );
		const $thisSection = jQuery( section );
		const type = field.getAttribute( 'data-type' );

		checkHtmlForNewFields( msg );

		let toggled = false;

		fieldUpdated();
		builder.dragDrop.setupSortable( section );

		if ( 'quantity' === type ) {
			// try to automatically attach a product field
			builder.fieldOptions.maybeSetProductField( field );
		}

		if ( 'product' === type || 'quantity' === type ) {
			// quantity too needs to be a part of the if statement especially because of the very
			// 1st quantity field (or even if it's just one quantity field in the form).
			maybeHideQuantityProductFieldOption();
		}

		if ( $thisSection.length ) {
			$thisSection.parent( '.frm_field_box' ).children( '.frm_no_section_fields' ).addClass( 'frm_block' );
		} else {
			const $parentSection = jQuery( field ).closest( 'ul.frm_sorting.start_divider' );
			if ( $parentSection.length ) {
				toggleOneSectionHolder( $parentSection );
				toggled = true;
			}
		}

		builder.dragDrop.addClass( field, 'frm-newly-added' );
		setTimeout( function() {
			field.classList.remove( 'frm-newly-added' );
		}, 1000 );

		// The server moves the submit row below the new field in the database, using the highest saved order.
		// Copy those orders so the inputs match the database, then renumber from the builder.
		// The saved orders can be lower than the builder positions, and the submit row would end up above other fields.
		const lastRowOrderInput = field.querySelector( '#frm-last-row-fields-order' );
		if ( lastRowOrderInput ) {
			updateLastRowFieldsOrder( JSON.parse( lastRowOrderInput.value ) );
		}

		// This also renumbers page breaks.
		builder.fieldEditor.updateFieldOrder();

		if ( addFocus ) {
			const bounding = field.getBoundingClientRect();
			const container = document.getElementById( 'post-body-content' );
			const inView = bounding.top >= 0 &&
					bounding.left >= 0 &&
					bounding.right <= ( window.innerWidth || document.documentElement.clientWidth ) &&
					bounding.bottom <= ( window.innerHeight || document.documentElement.clientHeight )
			;

			if ( ! inView ) {
				container.scroll( {
					top: container.scrollHeight,
					left: 0,
					behavior: 'smooth'
				} );
			}

			if ( toggled === false ) {
				toggleOneSectionHolder( $thisSection );
			}
		}

		deselectFields();
		initiateMultiselect();

		fieldsContainer.classList.remove( 'frm-over-droppable' );

		maybeDisableFieldButtonAtLimit( type );

		// Bootstrap 5 uses data-bs-toggle instead of data-toggle, and requires that elements have the dropdown-menu class.
		field.querySelectorAll( '[data-toggle]' ).forEach( toggle => toggle.setAttribute( 'data-bs-toggle', toggle.getAttribute( 'data-toggle' ) ) );
		field.querySelectorAll( '.frm-dropdown-menu' ).forEach( dropdownMenu => dropdownMenu.classList.add( 'dropdown-menu' ) );

		const addedEvent = new Event( 'frm_added_field', { bubbles: false } );
		addedEvent.frmField = field;
		addedEvent.frmSection = section;
		addedEvent.frmType = type;
		addedEvent.frmToggles = toggled;
		document.dispatchEvent( addedEvent );
	}

	/**
	 * @since 6.27
	 *
	 * @param {string} type
	 * @return {void}
	 */
	function maybeDisableFieldButtonAtLimit( type ) {
		const button = document.getElementById( type );
		if ( button?.dataset.limit && countFieldTypeInForm( type ) >= button.dataset.limit ) {
			button.classList.add( 'frm_at_limit' );
		}
	}

	function updateLastRowFieldsOrder( fieldsOrder ) {
		if ( ! fieldsOrder || 'object' !== typeof fieldsOrder ) {
			return;
		}

		Object.keys( fieldsOrder ).forEach( fieldId => {
			const orderInput = document.querySelector( `input[name="field_options[field_order_${ fieldId }]"]` );
			if ( orderInput ) {
				orderInput.value = fieldsOrder[ fieldId ];
			}
		} );
	}

	/**
	 * Since multiple new fields may get added when a new field is inserted, check the HTML.
	 *
	 * @param {string} html
	 * @return {void}
	 */
	function checkHtmlForNewFields( html ) {
		const element = div();
		element.innerHTML = html;
		element.querySelectorAll( '.form-field' ).forEach( addFieldIdToDraftFieldsInput );
	}

	/**
	 * @param {HTMLElement} field
	 * @return {void}
	 */
	function addFieldIdToDraftFieldsInput( field ) {
		if ( ! field.dataset.fid ) {
			return;
		}

		const draftInput = document.getElementById( 'draft_fields' );
		if ( ! draftInput ) {
			return;
		}

		if ( '' === draftInput.value ) {
			draftInput.value = field.dataset.fid;
		} else {
			const split = draftInput.value.split( ',' );
			if ( ! split.includes( field.dataset.fid ) ) {
				draftInput.value += `,${ field.dataset.fid }`;
			}
		}
	}

	return {
		addFieldClick,
		afterAddField,
		changeSectionStyle,
		checkForMultiselectKeysOnMouseMove,
		closeOpenFieldDropdowns,
		duplicateField,
		getActiveHoverRow,
		handleAddFieldClickResponse,
		handleClickOutsideOfFieldSettings,
		insertFormField,
		maybeRemoveGroupHoverTarget,
		onFieldActionDropdownShow,
		onFieldGroupActionDropdownShow,
		showLimitModal,
		wrapFieldLi,
		wrapFieldLiInPlace,
	};
};
