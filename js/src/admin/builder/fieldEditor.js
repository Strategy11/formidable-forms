const { renderFieldPreviewNow } = require( '../fieldPreviewQueue' );
const { syncFieldOrder, confirmFieldOrderSaved } = require( '../fieldOrder' );

module.exports = function createFieldEditor( {
	builderForm,
	deselectFields,
	ensureFieldSettings,
	fieldUpdated,
	getFieldsInRow,
	getInputForIcon,
	getSVGHref,
	hideActiveFieldSettings,
	hydrateBuilderSelectsIn,
	infoModal,
	initiateMultiselect,
	moveFieldSettings,
	popCalcFields,
	popProductFields,
	preFormSave,
	renumberPageBreaks,
	showNameYourFormModal,
	showSaveAndReloadModal,
	triggerSubmit,
	updateFieldGroupControls,
	validateRangeSettings,
}, builder ) {
	const { tag, span, svg, div } = frmDom;
	const { __ } = wp.i18n;
	const icons = {
		save: svg( { href: '#frm_save_icon' } ),
		drag: svg( { href: '#frm_drag_icon', classList: [ 'frm_drag_icon', 'frm-drag' ] } )
	};

	function updateFieldOrder() {
		renumberPageBreaks();
		if ( syncFieldOrder( builderForm ) ) {
			fieldUpdated();
		}
	}

	function handleShowPasswordLiveUpdate() {
		frmDom.util.documentOn( 'change', '.frm_show_password_setting_input', event => {
			const fieldId = event.target.getAttribute( 'data-fid' );
			const fieldEl = document.getElementById( `frm_field_id_${ fieldId }` );
			if ( ! fieldEl ) {
				return;
			}

			fieldEl.classList.toggle( 'frm_disabled_show_password', ! event.target.checked );
		} );
	}

	function slideDown() {
		/*jshint validthis:true */
		const id = jQuery( this ).data( 'slidedown' );
		const $thisId = jQuery( document.getElementById( id ) );
		if ( $thisId.is( ':hidden' ) ) {
			$thisId.slideDown( 'fast' );
			this.style.display = 'none';
		}
		return false;
	}

	function slideUp() {
		/*jshint validthis:true */
		const id = jQuery( this ).data( 'slideup' );
		const $thisId = jQuery( document.getElementById( id ) );
		$thisId.slideUp( 'fast' );
		$thisId.siblings( 'a' ).show();
		return false;
	}

	/**
	 * Get rid of empty container that inserts extra space.
	 *
	 * @param {HTMLElement|Document} container The settings panel or page to clean.
	 */
	function hideEmptyEle( container = document ) {
		container.querySelectorAll( '.frm-hide-empty' ).forEach( element => {
			if ( element.textContent.trim().length === 0 ) {
				element.remove();
			}
		} );
	}

	/* Change the classes in the builder */
	function changeFieldClass( field, setting ) {
		let classes;
		let replace;
		let alignField;
		let replaceWith = ` ${ setting.value }`;
		const fieldId = field.getAttribute( 'data-fid' );

		if ( '' === replaceWith.trim() ) {
			const alignOption = setting.querySelector( 'option[data-align]' );
			replaceWith = alignOption ? ` ${ alignOption.getAttribute( 'data-align' ) }` : '';
		}
		// Include classes from multiple settings.
		if ( fieldId !== undefined ) {
			if ( setting.classList.contains( 'field_options_align' ) ) {
				replaceWith += ` ${ document.getElementById( `frm_classes_${ fieldId }` ).value }`;
			} else if ( setting.classList.contains( 'frm_classes' ) ) {
				alignField = document.getElementById( `field_options_align_${ fieldId }` );
				if ( alignField ) {
					replaceWith += ` ${ alignField.value }`;
				}
			}
		}
		replaceWith += ' ';

		// Allow for the column number dropdown.
		replaceWith = replaceWith.replace( ' block ', ' vertical_radio ' ).replace( ' inline ', ' horizontal_radio ' );

		classes = field.className.split( ' frmstart ' )[ 1 ];
		classes = 0 === classes.indexOf( 'frmend ' ) ? '' : classes.split( ' frmend ' )[ 0 ];

		if ( classes.trim() === '' ) {
			replace = ' frmstart  frmend ';
			if ( ! field.className.includes( replace ) ) {
				replace = ' frmstart frmend ';
			}
			replaceWith = ` frmstart ${ replaceWith.trim() } frmend `;
		} else {
			replace = classes.trim();
			replaceWith = replaceWith.trim();
		}

		const hadFrmFirstClass = field.classList.contains( 'frm_first' );

		field.className = field.className.replace( replace, replaceWith );

		if ( ! hadFrmFirstClass && field.classList.contains( 'frm_first' ) ) {
			maybeBreakFieldGroup( field );
		}
	}

	/**
	 * Break a field out of its field group when the frm_first class gets added to it.
	 *
	 * frm_first starts a new row, so FrmFieldGridHelper opens a new field group for the field
	 * the next time the builder loads. This applies the same split right away instead of
	 * leaving the builder showing a row that the reloaded form will not match.
	 *
	 * Fields after the target field move into the new group as well, since the new row is
	 * where they end up on reload too.
	 *
	 * @since 6.35
	 *
	 * @param {HTMLElement} field The field that just had the frm_first class added to it.
	 * @return {void}
	 */
	function maybeBreakFieldGroup( field ) {
		const list = field.parentElement;

		if ( ! list || 'UL' !== list.nodeName || list.classList.contains( 'start_divider' ) ) {
			// A field only ever needs breaking out when it is in a field group list.
			return;
		}

		const fieldGroup = list.parentElement;

		if ( ! fieldGroup || ! builder.dragDrop.isFieldGroup( fieldGroup ) ) {
			return;
		}

		const fieldsInRow = getFieldsInRow( jQuery( list ) ).get();

		if ( fieldsInRow.indexOf( field ) < 1 ) {
			// The field already starts its row, so there is nothing to break out of.
			return;
		}

		const newList = tag( 'ul', { className: 'frm_grid_container frm_sorting' } );
		const newFieldGroup = tag( 'li', { className: 'frm_field_box', child: newList } );

		fieldGroup.after( newFieldGroup );
		newList.append( ...getFieldAndFollowingFields( field ) );

		builder.dragDrop.makeDroppable( newList );
		builder.dragDrop.makeDraggable( newFieldGroup, '.frm-move' );

		updateFieldGroupControls( jQuery( list ), getFieldsInRow( jQuery( list ) ).length );
	}

	/**
	 * Get a field along with every field that follows it in the same list.
	 *
	 * The field group controls are shared between every group and get appended to whichever
	 * group is hovered, so only list items are included.
	 *
	 * @since 6.35
	 *
	 * @param {HTMLElement} field The field to start from.
	 * @return {Array.<HTMLElement>} The field and the fields after it.
	 */
	function getFieldAndFollowingFields( field ) {
		const fields = [];

		let sibling = field;
		while ( sibling ) {
			if ( 'LI' === sibling.nodeName ) {
				fields.push( sibling );
			}
			sibling = sibling.nextElementSibling;
		}

		return fields;
	}

	function maybeShowInlineModal( e ) {
		/*jshint validthis:true */
		e.preventDefault();
		showInlineModal( this, undefined, e );
	}

	function showInlineModal( icon, input, event ) {
		const box = document.getElementById( icon.getAttribute( 'data-open' ) );
		const container = jQuery( icon ).closest( 'p,ul' );
		const inputTrigger = input !== undefined;

		if ( container.hasClass( 'frm-open' ) ) {
			container.removeClass( 'frm-open' );
			box.classList.add( 'frm_hidden' );
		} else {
			if ( ! inputTrigger ) {
				input = getInputForIcon( icon );
			}
			if ( input ) {
				if ( ! inputTrigger ) {
					const { key } = event;
					if ( key !== 'Enter' && key !== ' ' ) {
						input.focus();
					}
				}
				container.after( box );
				box.setAttribute( 'data-fills', input.id.replace( '-proxy-input', '' ) );

				if ( box.id.indexOf( 'frm-calc-box' ) === 0 ) {
					popCalcFields( box, true );
				}
			}

			container.addClass( 'frm-open' );
			box.classList.remove( 'frm_hidden' );

			/**
			 * @since 6.4.1
			 */
			wp.hooks.doAction( 'frm_show_inline_modal', box, icon );
		}
	}

	function dismissInlineModal( e ) {
		/*jshint validthis:true */
		e.preventDefault();
		this.parentNode.classList.add( 'frm_hidden' );
		jQuery( `.frm-open [data-open="${ this.parentNode.id }"]` ).closest( '.frm-open' ).removeClass( 'frm-open' );
	}

	/**
	 * Close frm-modal-no-dismiss element when clicking outside of it
	 *
	 * @param {Event} event The click event
	 */
	function closeModalOnOutsideClick( { target } ) {
		if (
			target.closest( '.frm-inline-modal.frm-modal-no-dismiss' ) ||
			target.closest( '.frm-show-inline-modal' ) ||
			target.closest( '#frm_adv_info' ) ||
			target.closest( '.frm-token-proxy-input' )
		) {
			return;
		}

		// Close all inline modals (without close button) that are not hidden
		document.querySelectorAll( '.frm-inline-modal.frm-modal-no-dismiss:not(.frm_hidden)' ).forEach( modal => {
			modal.classList.add( 'frm_hidden' );
			modal.previousElementSibling.classList.remove( 'frm-open' );
		} );
	}

	function changeInputtedValue() {
		/*jshint validthis:true */
		let i;
		const action = this.getAttribute( 'data-frmchange' ).split( ',' );

		for ( i = 0; i < action.length; i++ ) {
			if ( action[ i ] === 'updateOption' ) {
				changeHiddenSeparateValue( this );
			} else if ( action[ i ] === 'updateDefault' ) {
				changeDefaultRadioValue( this );
			} else if ( action[ i ] === 'checkUniqueOpt' ) {
				builder.fieldPreview.checkUniqueOpt( this );
			} else {
				this.value = this.value[ action[ i ] ]();
			}
		}
	}

	/**
	 * When the saved value is changed, update the default value radio.
	 * @param {HTMLElement} input The changed input element.
	 */
	function changeDefaultRadioValue( input ) {
		const parentLi = getOptionParent( input );
		const key = parentLi.getAttribute( 'data-optkey' );
		const fieldId = getOptionFieldId( parentLi, key );
		const defaultRadio = parentLi.querySelector( `input[name="default_value_${ fieldId }"]` );

		if ( defaultRadio !== null ) {
			defaultRadio.value = input.value;
		}
	}

	/**
	 * If separate values are not enabled, change the saved value when
	 * the displayed value is changed.
	 * @param {HTMLElement} input The changed input element.
	 */
	function changeHiddenSeparateValue( input ) {
		let savedVal;
		const parentLi = getOptionParent( input );
		const key = parentLi.getAttribute( 'data-optkey' );
		const fieldId = getOptionFieldId( parentLi, key );
		const sep = document.getElementById( `separate_value_${ fieldId }` );

		if ( sep && sep.checked === false ) {
			// If separate values are not turned on.
			savedVal = document.getElementById( `field_key_${ fieldId }-${ key }` );
			savedVal.value = input.value;
			changeDefaultRadioValue( savedVal );
		}
	}

	function getOptionParent( input ) {
		let parentLi = input.parentNode;
		if ( parentLi.tagName !== 'LI' ) {
			parentLi = parentLi.parentNode;
		}
		return parentLi;
	}

	function getOptionFieldId( li, key ) {
		const liId = li.id;

		return liId.replace( 'frm_delete_field_', '' ).replace( `-${ key }_container`, '' );
	}

	function submitBuild() {
		/*jshint validthis:true */
		const $thisEle = this;

		if ( showNameYourFormModal() || builder.fieldLoading.deferSaveForFieldSettings( submitBuild, this ) ) {
			return;
		}

		preFormSave( this );

		const $form = jQuery( builderForm );
		const v = JSON.stringify( $form.serializeArray() );
		const savedOrders = builderForm.querySelector( '[name="frm_field_order"]' )?.value;

		jQuery( document.getElementById( 'frm_compact_fields' ) ).val( v );
		jQuery.ajax( {
			type: 'POST',
			url: ajaxurl,
			data: { action: 'frm_save_form', frm_compact_fields: v, nonce: frmGlobal.nonce },
			success( msg, textStatus, xhr ) {
				// An invalid save also returns 200 with the edit page, so keep the orders for the next save.
				if ( xhr.getResponseHeader( 'X-Frm-Form-Saved' ) === '1' ) {
					confirmFieldOrderSaved( builderForm, savedOrders );
				}
				afterFormSave( $thisEle );
				showFormSavedMessage( msg );
				reloadIfAddonActivatedAjaxSubmitOnly();
			},
			error( xhr ) {
				if ( xhr.getResponseHeader( 'X-Frm-Form-Saved' ) === '0' ) {
					// The server could not save everything. Keep the unsaved changes and orders for the next save.
					showFormSaveFailed( $thisEle, xhr.responseText );
					return;
				}
				triggerSubmit( document.getElementById( 'frm_js_build_form' ) );
			}
		} );
	}

	/**
	 * Stop the loading state and show the error returned by a save that did not complete.
	 * The changed fields count is kept so the unsaved changes warning still applies.
	 *
	 * @param {HTMLElement} button The save button.
	 * @param {string}      msg    The error message returned by the save request.
	 * @return {void}
	 */
	function showFormSaveFailed( button, msg ) {
		button.classList.remove( 'frm_loading_form', 'frm_loading_button' );
		button.setAttribute( 'aria-busy', 'false' );
		infoModal( msg );
	}

	/**
	 * Show the message returned after saving the form with AJAX.
	 * A plain success message pops up as a toast. A message with a link, like the
	 * form too long warning, stays at the top of the builder so there is time to read it.
	 *
	 * @param {string} msg The message HTML returned by the save request.
	 * @return {void}
	 */
	function showFormSavedMessage( msg ) {
		const content = span( {} );
		content.innerHTML = msg;

		if ( ! content.querySelector( 'a' ) ) {
			frmDom.success( content );
			return;
		}

		const postStuff = document.getElementById( 'post-body-content' );
		const message = div( { className: 'frm_updated_message', child: content } );
		postStuff.insertBefore( message, postStuff.firstChild );
	}

	function submitNoAjax() {
		/*jshint validthis:true */
		if ( showNameYourFormModal() || builder.fieldLoading.deferSaveForFieldSettings( submitNoAjax, this ) ) {
			return;
		}

		preFormSave( this );
		const form = jQuery( builderForm );
		jQuery( document.getElementById( 'frm_compact_fields' ) ).val( JSON.stringify( form.serializeArray() ) );
		triggerSubmit( document.getElementById( 'frm_js_build_form' ) );
	}

	function afterFormSave( button ) {
		button.classList.remove( 'frm_loading_form' );
		button.classList.remove( 'frm_loading_button' );
		builder.conditionalLogic.resetOptionTextDetails();
		builder.state.fieldsUpdated = 0;
		button.setAttribute( 'aria-busy', 'false' );

		setTimeout( function() {
			jQuery( '.frm_updated_message' ).fadeOut( 'slow', function() {
				this.remove();
			} );
		}, 5000 );
	}

	function clickAction( obj ) {
		const $thisobj = jQuery( obj );

		if ( obj.className.includes( 'selected' ) ) {
			return;
		}
		if ( obj.className.includes( 'edit_field_type_end_divider' ) && $thisobj.closest( '.edit_field_type_divider' ).hasClass( 'no_repeat_section' ) ) {
			return;
		}

		deselectFields();
		$thisobj.addClass( 'selected' );
		showFieldOptions( obj );
	}

	/**
	 * When a field is selected, show the field settings in the sidebar.
	 * @param {HTMLElement} obj The selected field element.
	 */
	function showFieldOptions( obj ) {
		const fieldId = obj.getAttribute( 'data-fid' );
		renderFieldPreviewNow( fieldId );
		obj = document.getElementById( `frm_field_id_${ fieldId }` ) || obj;
		const fieldType = obj.getAttribute( 'data-type' );
		hideActiveFieldSettings();

		const singleField = ensureFieldSettings( fieldId );
		if ( ! singleField ) {
			return;
		}
		hydrateBuilderSelectsIn( singleField );
		moveFieldSettings( singleField );

		if ( fieldType && 'quantity' === fieldType ) {
			popProductFields( jQuery( singleField ).find( '.frmjs_prod_field_opt' )[ 0 ] );
		}

		// Scroll settings panel to top
		builder.state.settingsScrollContainer?.scrollTo( {
			top: 0,
			behavior: 'instant'
		} );

		singleField.classList.remove( 'frm_hidden' );
		builder.state.activeSettingsPanel = singleField;
		initiateMultiselect( singleField );

		// Cancel slide animation on expanded sections so screen readers
		// can immediately access inputs after DOM re-insertion.
		singleField.querySelectorAll( 'h3:not(.frm-collapsed) + .frm-collapse-me' ).forEach(
			section => section.style.animation = 'none'
		);

		let editorReady = Promise.resolve();
		const editor = singleField.querySelector( '.wp-editor-area' );
		if ( editor ) {
			editorReady = frmDom.wysiwyg.init(
				editor,
				{ setupCallback: setupTinyMceEventHandlers }
			);
		}

		wp.hooks.doAction( 'frmShowedFieldSettings', obj, singleField );
		maybeAddShortcodesModalTriggerIcon( fieldType, singleField );

		showFieldOptionsTab( obj, singleField, editorReady );
	}

	/**
	 * Switch the sidebar to the Field Options tab, which slides the settings panel into view.
	 *
	 * The panel's setup work (deferred inits, TinyMCE, frmShowedFieldSettings listeners) all runs
	 * before this. Starting the slide during that work drops its frames and makes it look shaky,
	 * so the switch waits for TinyMCE to boot and then starts on a fresh frame.
	 *
	 * @since 7.0
	 *
	 * @param {HTMLElement}   obj         The selected field element.
	 * @param {HTMLElement}   singleField The field settings element.
	 * @param {Promise<void>} editorReady Resolves once the field's rich text editor has booted.
	 * @return {void}
	 */
	function showFieldOptionsTab( obj, singleField, editorReady ) {
		if ( builder.state.optionsTab.parentElement.classList.contains( 'frm-active' ) ) {
			// The tab is already open, so there is no slide to protect.
			builder.state.optionsTab.click();
			builder.state.fieldOptionsTabShown = Promise.resolve();
			return;
		}

		// Never hold the panel back for long, even if TinyMCE's scripts are slow to load.
		const MAX_EDITOR_WAIT = 300;
		const maxWait = new Promise( resolve => setTimeout( resolve, MAX_EDITOR_WAIT ) );

		builder.state.fieldOptionsTabShown = Promise.race( [ editorReady, maxWait ] ).then(
			() => new Promise( resolve => {
				requestAnimationFrame( () => {
					// Skip it if another field was selected, or the Add Fields tab was clicked, while waiting.
					if ( obj.classList.contains( 'selected' ) && ! singleField.classList.contains( 'frm_hidden' ) ) {
						builder.state.optionsTab.click();
					}
					resolve();
				} );
			} )
		);
	}

	function maybeAddShortcodesModalTriggerIcon( fieldType, singleField ) {
		if ( ! shouldAddShortcodesModalTriggerIcon( fieldType ) ) {
			return;
		}

		if ( singleField.querySelector( '.frm-show-box' ) ) {
			return;
		}
		singleField.querySelector( '.wp-editor-container' )?.classList.add( 'frm_has_shortcodes' );

		const wrapTextareaWithIconContainer = () => {
			const textareas = singleField.querySelectorAll( '.frm_has_shortcodes textarea' );
			textareas.forEach( textarea => {
				const wrapperSpan = span( { className: 'frm-with-right-icon' } );
				textarea.parentNode.insertBefore( wrapperSpan, textarea );
				wrapperSpan.append( createModalTriggerIcon() );
				wrapperSpan.append( textarea );
			} );
		};

		const createModalTriggerIcon = () => {
			return frmDom.svg( { href: '#frm_more_horiz_solid_icon', classList: [ 'frm-show-box' ] } );
		};

		wrapTextareaWithIconContainer();
	}

	function shouldAddShortcodesModalTriggerIcon( fieldType ) {
		const fieldsWithShortcodesBox = wp.hooks.applyFilters( 'frm_fields_with_shortcode_popup', [ 'html' ] );

		return fieldsWithShortcodesBox.includes( fieldType );
	}

	function setupTinyMceEventHandlers( editor ) {
		editor.on( 'Change', function() {
			handleTinyMceChange( editor );
		} );
	}

	function handleTinyMceChange( editor ) {
		if ( ! isTinyMceActive() || tinyMCE.activeEditor.isHidden() ) {
			return;
		}

		editor.targetElm.value = editor.getContent();
		jQuery( editor.targetElm ).trigger( 'change' );
	}

	function isTinyMceActive() {
		const activeSettings = builder.state.activeSettingsPanel;
		if ( ! activeSettings?.isConnected || activeSettings.classList.contains( 'frm_hidden' ) ) {
			return false;
		}

		const wrapper = activeSettings.querySelector( '.wp-editor-wrap' );
		return wrapper && wrapper.classList.contains( 'tmce-active' );
	}

	/**
	 * Handles 'change' event on the document.
	 *
	 * @since 6.16.3
	 *
	 * @param {Event} event
	 * @return {void}
	 */
	function handleBuilderChangeEvent( event ) {
		const { target } = event;
		maybeShowSaveAndReloadModal( target );
		validateRangeSettings( target );
	}

	/**
	 * Shows 'Save and Reload' modal if the target field's type is changed.
	 *
	 * @since 6.16.3
	 *
	 * @param {HTMLElement} target
	 * @return {void}
	 */
	function maybeShowSaveAndReloadModal( target ) {
		if ( ! target.id.startsWith( 'field_options_type_' ) ) {
			return;
		}
		const idParts = target.id.split( '_' );
		const fieldId = idParts.length && idParts[ idParts.length - 1 ];

		if ( document.querySelector( `#frm-single-settings-${ fieldId }` )?.classList.contains( `frm-type-${ target.value }` ) ) {
			// Do not show modal if the field type is reverted back to the original type when builder is loaded.
			return;
		}
		showSaveAndReloadModal();
	}

	function buildSubmittedNoAjax() {
		// set fieldsUpdated to 0 to avoid the unsaved changes pop up
		builder.state.fieldsUpdated = 0;
	}

	function reloadIfAddonActivatedAjaxSubmitOnly() {
		const submitButton = document.getElementById( 'frm_submit_side_top' );
		if ( submitButton.hasAttribute( 'data-new-addon-installed' ) && 'true' === submitButton.getAttribute( 'data-new-addon-installed' ) ) {
			submitButton.removeAttribute( 'data-new-addon-installed' );
			window.location.reload();
		}
	}

	function toggle( cname, id, show ) {
		if ( id === '#' ) {
			const cont = document.getElementById( cname );
			if ( show !== undefined ) {
				cont.style.display = show ? 'block' : 'none';
			} else {
				const hidden = cont.style.display;
				cont.style.display = hidden === 'none' ? 'block' : 'none';
			}
		} else if ( show !== undefined ) {
			if ( show ) {
				cname.show();
			} else {
				cname.hide();
			}
		} else if ( cname.is( ':visible' ) ) {
			cname.hide();
		} else {
			cname.show();
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

	function handleModalDismiss( input ) {
		const modalDismissers = document.querySelectorAll( '#frm_info_modal .dismiss, #frm_info_modal #frm-info-click, .ui-widget-overlay.ui-front' );
		function onModalClose() {
			input.classList.add( 'frm_invalid_field' );
			setTimeout( () => input.focus(), 0 );
			modalDismissers.forEach( el => {
				el.removeEventListener( 'click', onModalClose );
			} );
		}

		modalDismissers.forEach( el => {
			el.addEventListener( 'click', onModalClose );
		} );
	}

	function validateProductPriceValue( event ) {
		const { target } = event;
		const price = target.value.trim();
		if ( price.includes( '[' ) && price.includes( ']' ) ) {
			// This is a shortcode and should be assumed a valid price.
			return;
		}
		if ( isNaN( price.replace( /,/, '' ) ) ) {
			const validationFailMessage = __( 'Please enter a valid number.', 'formidable' );
			frmAdminBuild.infoModal( validationFailMessage );
			handleModalDismiss( target );
			return;
		}
		target.classList.remove( 'frm_invalid_field' );
	}

	function toggleProductType() {
		const settings = jQuery( this ).closest( '.frm-single-settings' );
		const container = settings.find( '.frmjs_product_choices' );
		const heading = settings.find( '.frm_prod_options_heading' );
		const currentVal = this.options[ this.selectedIndex ].value;
		const fieldId = this.name.replace( 'field_options[data_type_', '' ).replace( ']', '' );

		const displayFormatOptions = settings[ 0 ].querySelector( '.frm_display_format_options' );
		if ( displayFormatOptions ) {
			displayFormatOptions.setAttribute( 'data-product-type', currentVal );
		}

		container.removeClass( 'frm_prod_type_single frm_prod_type_user_def' );
		heading.removeClass( 'frm_prod_user_def' );

		if ( 'single' === currentVal ) {
			container.addClass( 'frm_prod_type_single' );

			// Build the single-product preview right away instead of waiting for the user to
			// also edit an option's label/price - the settings panel's option rows (and their
			// current label/price values) already exist regardless of this setting's value.
			builder.fieldPreview.updateSingleProductLabel( fieldId );
		} else if ( 'user_def' === currentVal ) {
			container.addClass( 'frm_prod_type_user_def' );
			heading.addClass( 'frm_prod_user_def' );
		} else {
			builder.fieldPreview.maybeReplaceSingleProductPreview( fieldId, currentVal );
		}

		wp.hooks.doAction( 'frm_product_type_toggled', currentVal, settings[ 0 ] );
	}

	/**
	 * Show, hide, and sort subfields of Name field on form builder.
	 *
	 * @since 4.11
	 */
	function handleNameFieldOnFormBuilder() {
		/**
		 * Gets subfield element from cache.
		 *
		 * @param {string} fieldId Field ID.
		 * @param {string} key     Cache key.
		 * @return {HTMLElement|undefined} Return the element from cache or undefined if not found.
		 */
		const getSubFieldElFromCache = ( fieldId, key ) => {
			window.frmCachedSubFields = window.frmCachedSubFields || {};
			window.frmCachedSubFields[ fieldId ] = window.frmCachedSubFields[ fieldId ] || {};
			return window.frmCachedSubFields[ fieldId ][ key ];
		};

		/**
		 * Sets subfield element to cache.
		 *
		 * @param {string}      fieldId Field ID.
		 * @param {string}      key     Cache key.
		 * @param {HTMLElement} el      Element.
		 */
		const setSubFieldElToCache = ( fieldId, key, el ) => {
			window.frmCachedSubFields = window.frmCachedSubFields || {};
			window.frmCachedSubFields[ fieldId ] = window.frmCachedSubFields[ fieldId ] || {};
			window.frmCachedSubFields[ fieldId ][ key ] = el;
		};

		/**
		 * Gets column class from the number of columns.
		 *
		 * @param {number} colCount Number of columns.
		 * @return {string} The column CSS class name.
		 */
		const getColClass = colCount => `frm${ parseInt( 12 / colCount ) }`;

		const colClasses = [ 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 ].map( num => `frm${ num }` );

		const allSubFieldNames = [ 'first', 'middle', 'last' ];

		/**
		 * Handles name layout change.
		 *
		 * @param {Event} event Event object.
		 */
		const onChangeLayout = event => {
			const { value } = event.target;
			const subFieldNames = value.split( '_' );
			const { fieldId } = event.target.dataset;

			/*
			 * Live update form on the form builder.
			 */
			const container = document.querySelector( `#field_${ fieldId }_inner_container .frm_combo_inputs_container` );
			const newColClass = getColClass( subFieldNames.length );

			// Set all sub field elements to cache and hide all of them first.
			allSubFieldNames.forEach( name => {
				const subFieldEl = container.querySelector( `[data-sub-field-name="${ name }"]` );
				if ( subFieldEl ) {
					subFieldEl.classList.add( 'frm_hidden' );
					subFieldEl.classList.remove( ...colClasses );
					setSubFieldElToCache( fieldId, name, subFieldEl );
				}
			} );

			subFieldNames.forEach( subFieldName => {
				const subFieldEl = getSubFieldElFromCache( fieldId, subFieldName );
				if ( ! subFieldEl ) {
					return;
				}

				subFieldEl.classList.remove( 'frm_hidden' );
				subFieldEl.classList.add( newColClass );

				container.append( subFieldEl );
			} );

			/*
			 * Live update subfield options.
			 */
			// Hide all subfield options.
			allSubFieldNames.forEach( name => {
				const optionsEl = document.querySelector( `.frm_sub_field_options-${ name }[data-field-id="${ fieldId }"]` );
				if ( optionsEl ) {
					optionsEl.classList.add( 'frm_hidden' );
					setSubFieldElToCache( fieldId, `${ name }_options`, optionsEl );
				}
			} );

			subFieldNames.forEach( subFieldName => {
				const optionsEl = getSubFieldElFromCache( fieldId, `${ subFieldName }_options` );
				if ( ! optionsEl ) {
					return;
				}
				optionsEl.classList.remove( 'frm_hidden' );
			} );
		};

		const dropdownSelector = '.frm_name_layout_dropdown';
		document.addEventListener( 'change', event => {
			if ( event.target.matches( dropdownSelector ) ) {
				onChangeLayout( event );
			}
		}, false );
	}

	function debounce( func, wait = 100 ) {
		return frmDom.util.debounce( func, wait );
	}

	function addSaveAndDragIconsToOption( fieldId, liObject ) {
		let li;
		let useTag;
		let useTagHref;
		let hasDragIcon = false;
		let hasSaveIcon = false;

		if ( liObject.newOption ) {
			const parser = new DOMParser();
			li = parser.parseFromString( liObject.newOption, 'text/html' ).body.childNodes[ 0 ];
		} else {
			li = liObject;
		}

		const liIcons = li.querySelectorAll( 'svg' );

		liIcons.forEach( svg => {
			useTag = svg.getElementsByTagNameNS( 'http://www.w3.org/2000/svg', 'use' )[ 0 ];
			if ( ! useTag ) {
				return;
			}
			useTagHref = getSVGHref( useTag );

			if ( useTagHref === '#frm_drag_icon' ) {
				hasDragIcon = true;
			}

			if ( useTagHref === '#frm_save_icon' ) {
				hasSaveIcon = true;
			}
		} );

		if ( ! hasDragIcon ) {
			li.prepend( icons.drag.cloneNode( true ) );
		}

		if ( li.querySelector( `[id^=field_key_${ fieldId }-]` ) && ! hasSaveIcon ) {
			li.querySelector( `[id^=field_key_${ fieldId }-]` ).after( icons.save.cloneNode( true ) );
		}

		if ( liObject.newOption ) {
			liObject.newOption = li;
		}
	}

	/**
	 * Fill in the add and remove buttons the server left off this field's options.
	 *
	 * Every option carries the same pair of buttons, so printing them for a whole form means a lot
	 * of markup for something no one sees until they open a field. The server prints one copy, on
	 * the hidden template row, and the real rows get theirs from here the first time the field is
	 * opened.
	 *
	 * @since 6.34
	 *
	 * @param {number|string} fieldId The field whose options need their buttons.
	 * @return {void}
	 */
	function addOptionControlsToField( fieldId ) {
		const optionList = document.getElementById( `frm_field_${ fieldId }_opts` );
		if ( ! optionList ) {
			return;
		}

		const template = optionList.querySelector( '.frm_option_template' );
		const templateRemove = template?.querySelector( '.frm_remove_tag' );
		const templateAdd = template?.querySelector( '.frm_add_opt' );
		if ( ! templateRemove || ! templateAdd ) {
			return;
		}

		const rows = optionList.querySelectorAll( '.frm_single_option:not(.frm_option_template)' );

		// The template row is printed before the option count is known, so it is always marked
		// disabled. Removing an option is only off limits when it is the last one left.
		const removingAllowed = rows.length > 1;

		rows.forEach( row => {
			if ( row.querySelector( '.frm_remove_tag' ) ) {
				// This field has been opened before, or the server printed the buttons already.
				return;
			}

			const optKey = row.dataset.optkey;
			if ( undefined === optKey ) {
				return;
			}

			const remove = templateRemove.cloneNode( true );
			[ 'removeid', 'removemore' ].forEach( name => {
				const value = remove.dataset[ name ];
				if ( value ) {
					remove.dataset[ name ] = value.replace( '-000', `-${ optKey }` );
				}
			} );
			remove.classList.toggle( 'frm_disabled', ! removingAllowed );

			// Keep the order the server uses: label input, remove, add, then the saved value box.
			// Each row is its own parent, so there is nothing for a DocumentFragment to batch here.
			const valueBox = row.querySelector( '.frm_option_key' );
			if ( valueBox ) {
				valueBox.before( remove, templateAdd.cloneNode( true ) );
			} else {
				row.append( remove, templateAdd.cloneNode( true ) );
			}
		} );
	}

	function maybeAddSaveAndDragIcons( fieldId ) {
		const fieldOptions = document.querySelectorAll( `[id^=frm_delete_field_${ fieldId }-]` );
		// return if there are no options.
		if ( fieldOptions.length < 2 ) {
			return;
		}

		const options = [ ...fieldOptions ].slice( 1 );
		options.forEach( ( li, _key ) => {
			if ( li.classList.contains( 'frm_other_option' ) ) {
				return;
			}
			addSaveAndDragIconsToOption( fieldId, li );
		} );
	}

	/**
	 * Moves the focus to the next single option input field in the list and positions the cursor at the end of the text.
	 *
	 * @param {HTMLElement} currentInput The currently focused input element.
	 */
	function focusNextSingleOptionInput( currentInput ) {
		const optionsList = currentInput.closest( '.frm_single_option' ).parentElement;
		const inputs = optionsList.querySelectorAll( '.frm_single_option input[name^="field_options[" ], .frm_single_option input[name^="rows_"]' );
		const inputsArray = Array.from( inputs );

		// Find the index of the currently focused input
		const currentIndex = inputsArray.indexOf( currentInput );

		if ( currentIndex < 0 ) {
			return;
		}

		// Find the next visible input field
		const nextInput = inputsArray.slice( currentIndex + 1 ).find( input => input.offsetParent !== null );

		if ( nextInput ) {
			nextInput.focus();

			// Move the cursor to the end of the text in the next input field
			const textLength = nextInput.value.length;
			nextInput.setSelectionRange( textLength, textLength );
		}
	}

	return {
		addOptionControlsToField,
		addSaveAndDragIconsToOption,
		buildSubmittedNoAjax,
		changeFieldClass,
		changeInputtedValue,
		clickAction,
		closeModalOnOutsideClick,
		debounce,
		dismissInlineModal,
		focusNextSingleOptionInput,
		handleBuilderChangeEvent,
		handleNameFieldOnFormBuilder,
		handleShowPasswordLiveUpdate,
		hideEmptyEle,
		maybeAddSaveAndDragIcons,
		maybeChangeEmbedFormMsg,
		maybeShowInlineModal,
		showFieldOptions,
		showFormSaveFailed,
		slideDown,
		slideUp,
		submitBuild,
		submitNoAjax,
		toggle,
		toggleProductType,
		updateFieldOrder,
		validateProductPriceValue,
	};
};
