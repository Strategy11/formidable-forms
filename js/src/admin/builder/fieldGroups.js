module.exports = function createFieldGroups( {
	clearSettingsBox,
	confirmLinkClick,
	deleteAllSelectedFieldGroups,
	deselectFields,
	fieldsContainer,
	getClassForBlock,
	getEvenClassForSize,
	getFieldIdsToDelete,
	getFieldsInRow,
	getLayoutClasses,
	makeTabbable,
	maybeRemoveMultiselectPopup,
	syncLayoutClasses,
	unselectFieldGroups,
}, builder ) {
	const { span, svg, div } = frmDom;
	const { __, sprintf } = wp.i18n;
	let fieldGroupMessageDismissed = false;

	function clickFieldGroupLayout() {
		const hoverTarget = builder.fieldActions.getActiveHoverRow();

		if ( ! hoverTarget ) {
			return;
		}

		deselectFields();

		const sizeOfFieldGroup = getSizeOfFieldGroupFromChildElement( hoverTarget.querySelector( 'li.form-field' ) );

		hoverTarget.classList.add( 'frm-has-open-field-group-popup' );
		jQuery( document ).on( 'click', '#frm_builder_page', destroyFieldGroupPopupOnOutsideClick );

		const popupWrapper = div();
		popupWrapper.style.position = 'relative';
		popupWrapper.append( getFieldGroupPopup( sizeOfFieldGroup, this ) );
		this.parentNode.append( popupWrapper );

		const firstLayoutOption = popupWrapper.querySelector( '.frm-row-layout-option' );
		if ( firstLayoutOption ) {
			firstLayoutOption.focus();
		}
	}

	function destroyFieldGroupPopupOnOutsideClick( event ) {
		if ( event.target.classList.contains( 'frm-custom-field-group-layout' ) || event.target.classList.contains( 'frm-cancel-custom-field-group-layout' ) ) {
			return;
		}
		if ( ! jQuery( event.target ).closest( '#frm_field_group_controls' ).length && ! jQuery( event.target ).closest( '#frm_field_group_popup' ).length ) {
			destroyFieldGroupPopup();
		}
	}

	function getSizeOfFieldGroupFromChildElement( element ) {
		const $ul = jQuery( element ).closest( 'ul' );
		if ( $ul.length ) {
			return getFieldsInRow( $ul ).length;
		}
		return getSelectedFieldCount();
	}

	function getFieldGroupPopup( sizeOfFieldGroup, childElement ) {
		let popup = document.getElementById( 'frm_field_group_popup' );
		if ( ! popup ) {
			popup = div();
		} else {
			popup.innerHTML = '';
		}

		popup.id = 'frm_field_group_popup';

		const wrapper = div();
		wrapper.style.padding = '0 24px 12px';
		wrapper.append( getRowLayoutTitle() );

		const rowLayoutOptions = getRowLayoutOptions( sizeOfFieldGroup );

		const ul = childElement.closest( 'ul.frm_sorting' );
		if ( ul ) {
			maybeMarkRowLayoutAsActive( ul, rowLayoutOptions );
		}

		wrapper.append( rowLayoutOptions );

		popup.append( wrapper );
		popup.append( separator() );

		if ( sizeOfFieldGroup <= 6 ) {
			popup.append( getCustomLayoutOption() );
		}

		popup.append( getBreakIntoDifferentRowsOption() );

		return popup;
	}

	function maybeMarkRowLayoutAsActive( activeRow, options ) {
		const { length } = options.children;
		for ( let index = 0; index < length; ++index ) {
			const currentRow = options.children[ index ];
			if ( rowLayoutsMatch( currentRow, activeRow ) ) {
				currentRow.classList.add( 'frm-active-row-layout' );
				return;
			}
		}
	}

	function separator() {
		return document.createElement( 'hr' );
	}

	function getCustomLayoutOption() {
		const option = div();
		option.textContent = __( 'Custom layout', 'formidable' );
		jQuery( option ).prepend( getIconClone( 'frm_gear_svg' ) );
		option.classList.add( 'frm-custom-field-group-layout' );
		makeTabbable( option );
		return option;
	}

	function getIconClone( iconId ) {
		const clone = document.getElementById( iconId ).cloneNode( true );
		clone.id = '';
		return clone;
	}

	function getBreakIntoDifferentRowsOption() {
		const option = div();
		option.textContent = __( 'Break into rows', 'formidable' );
		jQuery( option ).prepend( getIconClone( 'frm_break_field_group_svg' ) );
		option.classList.add( 'frm-break-field-group' );
		makeTabbable( option );
		return option;
	}

	function getRowLayoutTitle() {
		const rowLayoutTitle = div();
		rowLayoutTitle.classList.add( 'frm-row-layout-title' );
		rowLayoutTitle.textContent = __( 'Row Layout', 'formidable' );
		return rowLayoutTitle;
	}

	function getRowLayoutOptions( size ) {
		const wrapper = getEmptyGridContainer();

		if ( size > 6 ) {
			wrapper.append( getRowLayoutOption( size, 'even' ) );
			return wrapper;
		}

		if ( 5 !== size ) {
			wrapper.append( getRowLayoutOption( size, 'even' ) );
		}
		if ( size % 2 === 1 ) {
			// only include the middle option for odd numbers because even doesn't make a lot of sense.
			wrapper.append( getRowLayoutOption( size, 'middle' ) );
		}
		if ( size < 6 ) {
			wrapper.append( getRowLayoutOption( size, 'left' ) );
			wrapper.append( getRowLayoutOption( size, 'right' ) );
		} else {
			const padding = div();
			padding.classList.add( 'frm_fourth' );
			wrapper.prepend( padding );
		}

		return wrapper;
	}

	function getRowLayoutOption( size, type ) {
		const option = div();
		option.classList.add( 'frm-row-layout-option' );
		makeTabbable( option, type );

		let useClass;
		switch ( size ) {
			case 6:
				useClass = 'frm_half';
				break;
			case 5:
				useClass = 'frm_third';
				break;
			default:
				if ( size > 6 ) {
					// We only show a single option at 6-12, so we use the full width.
					useClass = 'frm_full';
				} else {
					useClass = size % 2 === 1 ? 'frm_fourth' : 'frm_third';
				}
				break;
		}

		option.classList.add( useClass );
		option.setAttribute( 'layout-type', type );

		option.append( getRowForSizeAndType( size, type ) );
		return option;
	}

	function rowLayoutsMatch( row1, row2 ) {
		return getRowLayoutAsKey( row1 ) === getRowLayoutAsKey( row2 );
	}

	function getRowLayoutAsKey( row ) {
		let $fields;
		if ( row.classList.contains( 'frm-row-layout-option' ) ) {
			$fields = jQuery( row ).find( '.frm_grid_container' ).children();
		} else {
			$fields = getFieldsInRow( jQuery( row ) );
		}
		const sizes = [];
		$fields.each(
			function() {
				sizes.push( getSizeOfLayoutClass( getLayoutClassName( this.classList ) ) );
			}
		);
		return sizes.join( '-' );
	}

	function getRowForSizeAndType( size, type ) {
		const row = getEmptyGridContainer();
		for ( let index = 0; index < size; ++index ) {
			const block = div();
			block.classList.add( getClassForBlock( size, type, index ) );
			block.style.height = '16px';
			block.style.background = '#9EA9B8';
			block.style.borderRadius = '1px';
			row.append( block );
		}

		return row;
	}

	function getEmptyGridContainer() {
		const wrapper = div();
		wrapper.classList.add( 'frm_grid_container' );
		return wrapper;
	}

	/**
	 * Handle when a field group layout option (that sets grid classes/column sizing) is selected in the "Row Layout" popup.
	 *
	 * @return {void}
	 */
	function handleFieldGroupLayoutOptionClick() {
		const row = builder.fieldActions.getActiveHoverRow();
		if ( ! row ) {
			// The field group layout options also get clicked when merging multiple rows.
			// The following code isn't required for multiple rows though so just exit early.
			return;
		}

		const type = this.getAttribute( 'layout-type' );
		syncLayoutClasses( getFieldsInRow( jQuery( row ) ).first(), type );
		destroyFieldGroupPopup();
	}

	function handleFieldGroupLayoutOptionInsideMergeClick() {
		const $ul = mergeSelectedFieldGroups();
		const type = this.getAttribute( 'layout-type' );
		syncLayoutClasses( getFieldsInRow( $ul ).first(), type );
		unselectFieldGroups();
	}

	function mergeSelectedFieldGroups() {
		const $selectedFieldGroups = jQuery( fieldsContainer.querySelectorAll( '.frm-selected-field-group' ) );
		const $firstGroupUl = $selectedFieldGroups.first();
		$selectedFieldGroups.not( $firstGroupUl ).each(
			function() {
				getFieldsInRow( jQuery( this ) ).each(
					function() {
						const previousParent = this.parentNode;
						getFieldsInRow( $firstGroupUl ).last().after( this );
						if ( ! jQuery( previousParent ).children( 'li.form-field' ).length ) {
							// clean up the previous field group if we've removed all of its fields.
							previousParent.closest( 'li.frm_field_box' ).remove();
						}
					}
				);
			}
		);
		builder.fieldEditor.updateFieldOrder();
		syncLayoutClasses( getFieldsInRow( $firstGroupUl ).first() );
		return $firstGroupUl;
	}

	function customFieldGroupLayoutClick() {
		if ( null !== this.closest( '.frm-merge-fields-into-row' ) ) {
			return;
		}
		const $fields = getFieldsInRow( jQuery( builder.fieldActions.getActiveHoverRow() ) );
		setupCustomLayoutOptions( $fields );
	}

	function setupCustomLayoutOptions( $fields ) {
		const size = $fields.length;

		const popup = document.getElementById( 'frm_field_group_popup' );
		popup.innerHTML = '';

		const wrapper = div();
		wrapper.style.padding = '0 24px';

		const layoutClass = getEvenClassForSize( 5 === size ? 6 : size );

		const inputRow = div();
		inputRow.style.padding = '20px 0';
		inputRow.classList.add( 'frm_grid_container' );

		if ( 5 === size ) {
			// add a span to pad the inputs by 1 column, to account for the missing 2 columns.
			const paddingElement = document.createElement( 'span' );
			paddingElement.classList.add( 'frm1' );
			inputRow.append( paddingElement );
		}

		let inputValueOverride = getSelectedFieldCount() > 0 ? getSizeOfLayoutClass( getEvenClassForSize( size ) ) : false;
		if ( false !== inputValueOverride && inputValueOverride >= 12 ) {
			inputValueOverride = Math.floor( 12 / size );
		}

		for ( let index = 0; index < size; ++index ) {
			const inputField = document.createElement( 'input' );
			inputField.type = 'text';
			inputField.classList.add( layoutClass );
			inputField.classList.add( 'frm-custom-grid-size-input' );
			inputField.value = false !== inputValueOverride ? inputValueOverride : getSizeOfLayoutClass( getLayoutClassName( $fields.get( index ).classList ) );
			inputRow.append( inputField );
		}

		const heading = div();
		heading.classList.add( 'frm-builder-popup-heading' );
		heading.textContent = __( 'Enter number of columns for each field', 'formidable' );

		const label = div();
		label.classList.add( 'frm-builder-popup-subheading' );
		label.textContent = __( 'Layouts are based on a 12-column grid system', 'formidable' );

		wrapper.append( heading );
		wrapper.append( label );

		wrapper.append( inputRow );

		const buttonsWrapper = div();
		buttonsWrapper.style.textAlign = 'right';

		const cancelButton = getSecondaryButton();
		cancelButton.textContent = __( 'Cancel', 'formidable' );
		cancelButton.classList.add( 'frm-cancel-custom-field-group-layout' );
		cancelButton.style.marginRight = '10px';

		const saveButton = getPrimaryButton();
		saveButton.textContent = __( 'Save', 'formidable' );
		saveButton.classList.add( 'frm-save-custom-field-group-layout' );

		buttonsWrapper.append( cancelButton );
		buttonsWrapper.append( saveButton );

		wrapper.append( buttonsWrapper );

		popup.append( wrapper );

		setTimeout(
			function() {
				const firstInput = popup.querySelector( 'input.frm-custom-grid-size-input' ).focus();
				if ( firstInput ) {
					firstInput.focus();
				}
			},
			0
		);
	}

	function customFieldGroupLayoutInsideMergeClick() {
		const $fields = jQuery( fieldsContainer.querySelectorAll( '.frm-selected-field-group li.form-field' ) );
		setupCustomLayoutOptions( $fields );
	}

	function getPrimaryButton() {
		const button = getButton();
		button.classList.add( 'button-primary', 'frm-button-primary' );
		return button;
	}

	function getSecondaryButton() {
		const button = getButton();
		button.classList.add( 'button-secondary', 'frm-button-secondary' );
		return button;
	}

	function getButton() {
		const button = document.createElement( 'a' );
		button.setAttribute( 'href', '#' );
		button.classList.add( 'button' );
		button.style.textDecoration = 'none';
		return button;
	}

	function getSizeOfLayoutClass( className ) {
		switch ( className ) {
			case 'frm_half':
				return 6;
			case 'frm_third':
				return 4;
			case 'frm_two_thirds':
				return 8;
			case 'frm_fourth':
				return 3;
			case 'frm_three_fourths':
				return 9;
			case 'frm_sixth':
				return 2;
			default:
				if ( 0 === className.indexOf( 'frm' ) ) {
					return parseInt( className.substr( 3 ) );
				}

				// Anything missing a layout class should be a full width row.
				return 12;
		}
	}

	function getLayoutClassName( classList ) {
		const classes = getLayoutClasses();
		for ( let index = 0; index < classes.length; ++index ) {
			const currentClass = classes[ index ];
			if ( classList.contains( currentClass ) ) {
				return currentClass;
			}
		}
		return '';
	}

	function breakFieldGroupClick() {
		const row = builder.fieldActions.getActiveHoverRow();
		breakRow( row );
		destroyFieldGroupPopup();
	}

	function breakRow( row ) {
		const $row = jQuery( row );
		getFieldsInRow( $row ).each(
			function( index ) {
				const field = this;
				if ( 0 !== index ) {
					$row.parent().after( builder.fieldActions.wrapFieldLi( field ) );
				}
				stripLayoutFromFields( jQuery( field ) );
			}
		);
	}

	function stripLayoutFromFields( field ) {
		syncLayoutClasses( field, 'clear' );
	}

	function focusFieldGroupInputOnClick() {
		this.select();
	}

	function cancelCustomFieldGroupClick() {
		revertToFieldGroupPopupFirstPage( this );
	}

	function revertToFieldGroupPopupFirstPage( triggerElement ) {
		jQuery( document.getElementById( 'frm_field_group_popup' ) ).replaceWith(
			getFieldGroupPopup( getSizeOfFieldGroupFromChildElement( triggerElement ), triggerElement )
		);
	}

	function destroyFieldGroupPopup() {
		const popup = document.getElementById( 'frm_field_group_popup' );
		if ( ! popup ) {
			return;
		}
		const wrapper = document.querySelector( '.frm-has-open-field-group-popup' );
		if ( wrapper ) {
			wrapper.classList.remove( 'frm-has-open-field-group-popup' );
			popup.parentNode.remove();
		}
		jQuery( document ).off( 'click', '#frm_builder_page', destroyFieldGroupPopupOnOutsideClick );
	}

	function saveCustomFieldGroupClick() {
		const syncDetails = [];

		jQuery( document.getElementById( 'frm_field_group_popup' ).querySelectorAll( '.frm_grid_container input' ) )
			.each(
				function() {
					syncDetails.push( parseInt( this.value ) );
				}
			);

		const $controls = jQuery( document.getElementById( 'frm_field_group_controls' ) );

		if ( $controls.length && 'none' !== $controls.get( 0 ).style.display ) {
			syncLayoutClasses( getFieldsInRow( jQuery( builder.fieldActions.getActiveHoverRow() ) ).first(), syncDetails );
		} else {
			const $ul = mergeSelectedFieldGroups();
			syncLayoutClasses( getFieldsInRow( $ul ).first(), syncDetails );
			unselectFieldGroups();
		}

		destroyFieldGroupPopup();
	}

	function fieldGroupClick( e ) {
		maybeShowFieldGroupMessage();

		if ( e.target !== e.currentTarget ) {
			// only continue if the group itself was clicked / ignore when a field or a nested group is clicked.
			return;
		}

		const hoverTarget = builder.fieldActions.getActiveHoverRow();
		if ( ! hoverTarget ) {
			return;
		}

		const ctrlOrCmdKeyIsDown = e.ctrlKey || e.metaKey;
		const shiftKeyIsDown = e.shiftKey;
		// Get the selected groups first so the selected field's group is included when checking if the clicked group is active.
		const $selectedFieldGroups = getSelectedFieldGroups();
		const groupIsActive = hoverTarget.classList.contains( 'frm-selected-field-group' );

		let numberOfSelectedGroups = $selectedFieldGroups.length;

		if ( ctrlOrCmdKeyIsDown || shiftKeyIsDown ) {
			// multi-selecting
			if ( ctrlOrCmdKeyIsDown ) {
				if ( groupIsActive ) {
					// unselect if holding ctrl or cmd and the group was already active.
					--numberOfSelectedGroups;
					hoverTarget.classList.remove( 'frm-selected-field-group' );
					syncAfterMultiSelect( numberOfSelectedGroups );
					return; // exit early to avoid adding back frm-selected-field-group
				}

				++numberOfSelectedGroups;
			} else if ( shiftKeyIsDown && ! groupIsActive ) {
				++numberOfSelectedGroups; // include the one we're selecting right now.
				const $firstGroup = $selectedFieldGroups.first();

				let $range;
				if ( $firstGroup.parent().index() < jQuery( hoverTarget.parentNode ).index() ) {
					$range = $firstGroup.parent().nextUntil( hoverTarget.parentNode );
				} else {
					$range = $firstGroup.parent().prevUntil( hoverTarget.parentNode );
				}

				$range.each(
					function() {
						const $fieldGroup = jQuery( this ).closest( 'li' ).find( 'ul.frm_sorting' );
						if ( ! $fieldGroup.hasClass( 'frm-selected-field-group' ) ) {
							++numberOfSelectedGroups;
							$fieldGroup.addClass( 'frm-selected-field-group' );
						}
					}
				);
			}
		} else {
			// not multi-selecting
			unselectFieldGroups();
			numberOfSelectedGroups = 1;
		}

		hoverTarget.classList.add( 'frm-selected-field-group' );
		syncAfterMultiSelect( numberOfSelectedGroups );

		maybeHideFieldGroupMessage();

		jQuery( document ).off( 'click', unselectFieldGroups );
		jQuery( document ).on( 'click', unselectFieldGroups );
	}

	/**
	 * Hide the field group message by manipulating classes.
	 *
	 * @param {Element} fieldGroupMessage The field group message element.
	 * @return {void}
	 */
	function hideFieldGroupMessage( fieldGroupMessage ) {
		if ( ! fieldGroupMessage ) {
			return;
		}

		fieldGroupMessage.classList.add( 'frm_hidden' );
		fieldGroupMessage.classList.remove( 'frm-fadein-up-back' );
	}

	/**
	 * Show the field group message by manipulating classes.
	 *
	 * @param {Element} fieldGroupMessage The field group message element.
	 * @return {void}
	 */
	function showFieldGroupMessage( fieldGroupMessage ) {
		if ( ! fieldGroupMessage ) {
			return;
		}

		fieldGroupMessage.classList.remove( 'frm_hidden' );
		fieldGroupMessage.classList.add( 'frm-fadein-up-back' );
	}

	/**
	 * Maybe show a message if there are at least two rows.
	 *
	 * @return {void}
	 */
	function maybeShowFieldGroupMessage() {
		if ( fieldGroupMessageDismissed ) {
			return;
		}

		let fieldGroupMessage = document.getElementById( 'frm-field-group-message' );
		const rows = document.querySelectorAll( '.edit_form_item:not(.edit_field_type_end_divider)' );

		if ( rows.length < 2 ) {
			hideFieldGroupMessage( fieldGroupMessage );
			return;
		}

		if ( fieldGroupMessage ) {
			showFieldGroupMessage( fieldGroupMessage );
			return;
		}

		fieldGroupMessage = div( {
			id: 'frm-field-group-message',
			className: 'frm-flex-center frm-fadein-up-back',
			children: [
				span( {
					id: 'frm-field-group-message-dismiss',
					className: 'frm-flex-center',
					child: svg( { href: '#frm_close_icon' } )
				} )
			]
		} );

		// Insert the field group into the DOM
		document.getElementById( 'post-body-content' ).append( fieldGroupMessage );

		// Get and add the field group message text
		const messageText = getFieldGroupMessageText();
		fieldGroupMessage.prepend( messageText );

		// Set up a click event listener
		document.getElementById( 'frm-field-group-message-dismiss' ).addEventListener( 'click', () => {
			fieldGroupMessageDismissed = true;
			hideFieldGroupMessage( document.getElementById( 'frm-field-group-message' ) );
		} );
	}

	/**
	 * Get a span element with text about selecting multiple fields.
	 *
	 * @return {HTMLElement} A span element with the message and style classes.
	 */
	function getFieldGroupMessageText() {
		const text = document.createElement( 'span' );
		text.classList.add( 'frm-field-group-message-text', 'frm-flex-center' );
		text.innerHTML = sprintf( // eslint-disable-line @wordpress/valid-sprintf -- Format string is a localized variable
			/* translators: %1$s: Start span HTML, %2$s: end span HTML */
			frm_admin_js.holdShiftMsg,
			'<span class="frm-meta-tag frm-flex-center"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-shift" viewBox="0 0 16 16"><path d="M7.3 2a1 1 0 0 1 1.4 0l6.4 6.8a1 1 0 0 1-.8 1.7h-2.8v3a1 1 0 0 1-1 1h-5a1 1 0 0 1-1-1v-3H1.7a1 1 0 0 1-.8-1.7L7.3 2zm7 7.5L8 2.7 1.7 9.5h2.8a1 1 0 0 1 1 1v3h5v-3a1 1 0 0 1 1-1h2.8z"/></svg>',
			'</span>'
		);

		return text;
	}

	/**
	 * Maybe hide the field group message based on the number of selected rows.
	 *
	 * @return {void}
	 */
	function maybeHideFieldGroupMessage() {
		const selectedRowCount = fieldsContainer.querySelectorAll( '.frm-selected-field-group' ).length;
		if ( selectedRowCount < 2 ) {
			return;
		}

		const fieldGroupMessage = document.getElementById( 'frm-field-group-message' );
		hideFieldGroupMessage( fieldGroupMessage );
	}

	function getSelectedField() {
		return fieldsContainer.querySelector( 'li.form-field.selected' );
	}

	function getSelectedFieldGroups() {
		const $fieldGroups = jQuery( fieldsContainer.querySelectorAll( '.frm-selected-field-group' ) );
		if ( $fieldGroups.length ) {
			return $fieldGroups;
		}

		const selectedField = getSelectedField();
		if ( selectedField ) {
			// When a field is selected, consider the field's group as selected for multi-select.
			const selectedFieldGroup = selectedField.closest( 'ul' );
			if ( selectedFieldGroup ) {
				selectedFieldGroup.classList.add( 'frm-selected-field-group' );
				return jQuery( selectedFieldGroup );
			}
		}

		return jQuery();
	}

	function syncAfterMultiSelect( numberOfSelectedGroups ) {
		clearSettingsBox( true ); // unselect any fields if one is selected.
		if ( numberOfSelectedGroups >= 2 || ( 1 === numberOfSelectedGroups && selectedGroupHasMultipleFields() ) ) {
			addFieldMultiselectPopup();
		} else {
			maybeRemoveMultiselectPopup();
		}
		builder.fieldActions.maybeRemoveGroupHoverTarget();
	}

	function selectedGroupHasMultipleFields() {
		return getFieldsInRow( jQuery( fieldsContainer.querySelector( '.frm-selected-field-group' ) ) ).length > 1;
	}

	function addFieldMultiselectPopup() {
		getFieldMultiselectPopup();
	}

	function getFieldMultiselectPopup() {
		let popup = document.getElementById( 'frm_field_multiselect_popup' );

		if ( popup ) {
			popup.classList.toggle( 'frm-unmergable', ! selectedFieldsAreMergeable() );
			return popup;
		}

		popup = div();
		popup.id = 'frm_field_multiselect_popup';
		if ( ! selectedFieldsAreMergeable() ) {
			popup.classList.add( 'frm-unmergable' );
		}

		const mergeOption = div();
		mergeOption.classList.add( 'frm-merge-fields-into-row' );
		mergeOption.textContent = __( 'Merge into row', 'formidable' );

		const caret = document.createElement( 'a' );
		caret.style.marginLeft = '5px';
		caret.classList.add( 'frm_icon_font', 'frm_arrowdown6_icon' );
		caret.setAttribute( 'href', '#' );
		mergeOption.append( caret );

		popup.append( mergeOption );

		const verticalSeparator = div();
		verticalSeparator.classList.add( 'frm-multiselect-popup-separator' );
		popup.append( verticalSeparator );

		const deleteOption = div();
		deleteOption.classList.add( 'frm-delete-field-groups' );
		deleteOption.append( getIconClone( 'frm_trash_svg' ) );
		popup.append( deleteOption );

		document.getElementById( 'post-body-content' ).append( popup );

		jQuery( popup ).hide().fadeIn();

		return popup;
	}

	function selectedFieldsAreMergeable() {
		const selectedFieldGroups = fieldsContainer.querySelectorAll( '.frm-selected-field-group' );
		const { length } = selectedFieldGroups;
		if ( 1 === length ) {
			return false;
		}
		let totalFieldCount = 0;
		for ( let index = 0; index < length; ++index ) {
			const fieldGroup = selectedFieldGroups[ index ];
			if ( null !== fieldGroup.querySelector( '.edit_field_type_break, .edit_field_type_hidden' ) ) {
				return false;
			}
			totalFieldCount += getFieldsInRow( jQuery( fieldGroup ) ).length;
			if ( totalFieldCount > builder.state.MAX_FIELD_GROUP_SIZE ) {
				return false;
			}
		}
		return true;
	}

	function mergeFieldsIntoRowClick( event ) {
		if ( null !== event.originalEvent.target.closest( '#frm_field_group_popup' ) ) {
			// prevent clicks within the popup from triggering the button again.
			return;
		}

		if ( event.originalEvent.target.classList.contains( 'frm-custom-field-group-layout' ) ) {
			// avoid switching back to the first page when clicking the custom option nested inside of the merge option.
			return;
		}

		const size = getSelectedFieldCount();
		const popup = getFieldGroupPopup( size, fieldsContainer.querySelector( '.frm-selected-field-group' ).firstChild );
		this.append( popup );
	}

	function getSelectedFieldCount() {
		let count = 0;
		jQuery( fieldsContainer.querySelectorAll( '.frm-selected-field-group' ) ).each(
			function() {
				count += getFieldsInRow( jQuery( this ) ).length;
			}
		);
		return count;
	}

	function deleteFieldGroupsClick() {
		const fieldIdsToDelete = getFieldIdsToDelete( getSelectedFieldIds() );
		const deleteOnConfirm = getDeleteSelectedFieldGroupsOnConfirmFunction( fieldIdsToDelete );

		const multiselectPopup = document.getElementById( 'frm_field_multiselect_popup' );
		if ( multiselectPopup ) {
			multiselectPopup.remove();
		}

		const confirmMsg = wp.hooks.applyFilters(
			'frm_delete_fields_confirmation',
			builder.fieldOptions.confirmFieldsDeleteMessage( fieldIdsToDelete.length ),
			fieldIdsToDelete
		);
		this.setAttribute( 'data-frmverify', confirmMsg );
		confirmLinkClick( this );

		const confirmedClick = document.getElementById( 'frm-confirmed-click' );

		// Remove any previous delete field data so delete confirmation does not attempt
		// to delete a field that was already deleted or previously attempted and cancelled.
		confirmedClick?.removeAttribute( 'data-deletefield' );

		jQuery( confirmedClick ).on( 'click', deleteOnConfirm );
		jQuery( '#frm_confirm_modal' ).one( 'dialogclose', function() {
			jQuery( confirmedClick ).off( 'click', deleteOnConfirm );
		} );
	}

	function getSelectedFieldIds() {
		const deleteFieldIds = [];
		jQuery( fieldsContainer.querySelectorAll( '.frm-selected-field-group > li.form-field' ) )
			.each(
				function() {
					deleteFieldIds.push( this.dataset.fid );
				}
			);
		return deleteFieldIds;
	}

	function getDeleteSelectedFieldGroupsOnConfirmFunction( deleteFieldIds ) {
		return function( event ) {
			event.preventDefault();
			deleteAllSelectedFieldGroups( deleteFieldIds );
		};
	}

	return {
		breakFieldGroupClick,
		cancelCustomFieldGroupClick,
		clickFieldGroupLayout,
		customFieldGroupLayoutClick,
		customFieldGroupLayoutInsideMergeClick,
		deleteFieldGroupsClick,
		destroyFieldGroupPopup,
		fieldGroupClick,
		focusFieldGroupInputOnClick,
		getLayoutClassName,
		getSizeOfLayoutClass,
		handleFieldGroupLayoutOptionClick,
		handleFieldGroupLayoutOptionInsideMergeClick,
		mergeFieldsIntoRowClick,
		saveCustomFieldGroupClick,
	};
};
