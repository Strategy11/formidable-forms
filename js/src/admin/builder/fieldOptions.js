const { updateComboSelectPreview } = require( '../fieldPreview' );
const { materializeAllFieldSettings } = require( '../deferredFieldSettings' );
module.exports = function createFieldOptions( {
	builderForm,
	confirmLinkClick,
	fieldUpdated,
	frmAdminJs,
	getFieldIdsToDelete,
	getFieldsInRow,
	imagesAsOptions,
	infoModal,
	initModal,
	isProductField,
	moveFieldSettings,
	popCalcFields,
	popProductFields,
	prepareLoadedFieldMarkup,
	purifyHtml,
	syncLayoutClasses,
	usingSeparateValues,
}, builder ) {
	const { __, sprintf } = wp.i18n;
	function checkCalculationCreatedByUser() {
		const calculation = this.value;
		let warningMessage = checkMatchingParens( calculation );
		warningMessage += checkShortcodes( calculation, this );

		if ( warningMessage !== '' ) {
			infoModal( `${ calculation }\n\n${ warningMessage }` );
		}
	}

	/**
	 * Checks a string for parens, brackets, and curly braces and returns a message if any unmatched are found.
	 *
	 * @param {string} formula The formula string to check.
	 * @return {string} Warning message if unmatched delimiters found, empty string otherwise.
	 */
	function checkMatchingParens( formula ) {
		const stack = [];
		const formulaArray = formula.split( '' );
		const { length } = formulaArray;
		const opening = [ '{', '[', '(' ];
		const closing = {
			'}': '{',
			')': '(',
			']': '['
		};
		let hasUnmatchedClosing = false;
		let msg = '';
		let i;
		let top;

		for ( i = 0; i < length; i++ ) {
			if ( opening.includes( formulaArray[ i ] ) ) {
				stack.push( formulaArray[ i ] );
				continue;
			}
			if ( Object.hasOwn( closing, formulaArray[ i ] ) ) {
				top = stack.pop();
				if ( top !== closing[ formulaArray[ i ] ] ) {
					hasUnmatchedClosing = true;
				}
			}
		}

		if ( stack.length > 0 || hasUnmatchedClosing ) {
			msg = `${ frmAdminJs.unmatched_parens }\n\n`;
			return msg;
		}

		return '';
	}

	/**
	 * Checks a calculation for shortcodes that shouldn't be in it and returns a message if found.
	 *
	 * @param {string}      calculation  The calculation formula.
	 * @param {HTMLElement} inputElement The input element for the calculation.
	 * @return {string}                  Warning message if invalid shortcodes found, empty string otherwise.
	 */
	function checkShortcodes( calculation, inputElement ) {
		let msg = checkNonNumericShortcodes( calculation, inputElement );
		msg += checkNonFormShortcodes( calculation );

		return msg;
	}

	/**
	 * Checks if a numeric calculation has shortcodes that output non-numeric strings and returns a message if found.
	 *
	 * @param {string}      calculation  The calculation formula.
	 * @param {HTMLElement} inputElement The input element for the calculation.
	 * @return {string} Warning message if non-numeric shortcodes found, empty string otherwise.
	 */
	function checkNonNumericShortcodes( calculation, inputElement ) {
		let msg = '';

		if ( isTextCalculation( inputElement ) ) {
			return msg;
		}

		const nonNumericShortcodes = getNonNumericShortcodes();

		if ( nonNumericShortcodes.test( calculation ) ) {
			msg = `${ frmAdminJs.text_shortcodes }\n\n`;
		}

		return msg;
	}

	/**
	 * Determines if the calculation input is from a text calculation.
	 *
	 * @param {HTMLElement} inputElement The calculation input element.
	 */
	function isTextCalculation( inputElement ) {
		return jQuery( inputElement ).siblings( 'label[for^="calc_type"]' ).children( 'input' ).prop( 'checked' );
	}

	/**
	 * Returns a regular expression of shortcodes that can't be used in numeric calculations.
	 *
	 * @return {RegExp} Pattern matching non-numeric shortcodes.
	 */
	function getNonNumericShortcodes() {
		return /\[(date|time|email|ip)\]/;
	}

	/**
	 * Checks if a string has any shortcodes that do not belong in forms and returns a message if any are found.
	 *
	 * @param {string} formula The formula string to check.
	 * @return {string} Warning message if non-form shortcodes found, empty string otherwise.
	 */
	function checkNonFormShortcodes( formula ) {
		const nonFormShortcodes = getNonFormShortcodes();
		let msg = '';

		if ( nonFormShortcodes.test( formula ) ) {
			msg += `${ frmAdminJs.view_shortcodes }\n\n`;
		}

		return msg;
	}

	/**
	 * Returns a regular expression of shortcodes that can't be used in forms but can be used in Views, Email
	 * Notifications, and other Formidable areas.
	 *
	 * @return {RegExp} Pattern matching shortcodes not valid in forms.
	 */
	function getNonFormShortcodes() {
		return /\[id\]|\[key\]|\[if\s\w+\]|\[foreach\s\w+\]|\[created-at(\s*)?/g;
	}

	function rePopCalcFieldsForSummary() {
		popCalcFields( jQuery( '.frm-inline-modal.postbox:has(.frm_js_summary_list)' )[ 0 ], true );
	}

	function maybeSetProductField( field ) {
		const fieldId = field.getAttribute( 'data-fid' );
		const productFieldOpt = document.getElementById( `field_options[product_field_${ fieldId }]` );

		if ( ! productFieldOpt ) {
			return;
		}

		popProductFields( productFieldOpt );
		// in order to move its settings to that LHS panel where
		// the update form resides, else it'll lose this setting
		moveFieldSettings( document.getElementById( `frm-single-settings-${ fieldId }` ) );
	}

	function liveChanges() {
		/*jshint validthis:true */
		const newValue = this.value;
		const changes = document.getElementById( this.getAttribute( 'data-changeme' ) );
		const att = this.getAttribute( 'data-changeatt' );

		if ( ! changes ) {
			return;
		}

		if ( att !== null ) {
			if ( changes.tagName === 'SELECT' && changes.hasAttribute( 'data-frm-preview-choices' ) &&
				( att === 'value' || att === 'placeholder' ) ) {
				updateComboSelectPreview( changes, this.closest( '.frm-single-settings' ) );
			} else if ( changes.tagName === 'SELECT' && att === 'placeholder' ) {
				builder.fieldPreview.resetDisplayedOpts( this.closest( '.frm-single-settings' ).dataset.fid );
			} else if ( att === 'class' ) {
				builder.fieldEditor.changeFieldClass( changes, this );
			} else if ( isSliderField( changes ) ) {
				updateSliderFieldPreview( changes, att, newValue );
			} else if ( att === 'value' || att === 'textContent' ) {
				changes[ att ] = newValue;
			} else if ( att === 'rows' && newValue === '' ) {
				changes.removeAttribute( att );
			} else {
				changes.setAttribute( att, newValue );
			}
		} else if ( changes.id.indexOf( 'setup-message' ) === 0 ) {
			if ( newValue !== '' ) {
				changes.innerHTML = '<input type="text" value="" disabled />';
			}
		} else if ( changes.tagName === 'TEXTAREA' && ! changes.classList.contains( 'wp-editor-area' ) ) {
			changes.value = newValue;
		} else {
			const content = changes.classList.contains( 'frm_primary_label' )
				? changes.querySelector( '.frm_builder_field_name' ) || changes
				: changes;
			content.innerHTML = purifyHtml( newValue );
			if ( 'TEXTAREA' === changes.nodeName && changes.classList.contains( 'wp-editor-area' ) ) {
				// Trigger change events on wysiwyg textareas so we can also sync default values in the visual tab.
				jQuery( changes ).trigger( 'change' );
			}

			if ( changes.classList.contains( 'frm_primary_label' ) && 'break' === changes.nextElementSibling?.getAttribute( 'data-ftype' ) ) {
				changes.nextElementSibling.querySelector( '.frm_button_submit' ).textContent = newValue;
			}
		}
	}

	function updateSliderFieldPreview( field, att, newValue ) {
		if ( frmGlobal.proIncludesSliderJs ) {
			const hookName = 'frm_update_slider_field_preview';
			const hookArgs = { field, att, newValue };
			wp.hooks.doAction( hookName, hookArgs );
			return;
		}

		// This functionality has been moved to pro since v5.4.3. This code should be removed eventually.
		if ( 'value' === att ) {
			if ( '' === newValue ) {
				newValue = getSliderMidpoint( field );
			}
			field.value = newValue;
		} else {
			field.setAttribute( att, newValue );
		}

		if ( ! [ 'value', 'min', 'max' ].includes( att ) ) {
			return;
		}

		if ( ( 'max' === att || 'min' === att ) && '' === getSliderDefaultValueInput( field.id ) ) {
			field.value = getSliderMidpoint( field );
		}

		field.parentNode.querySelector( '.frm_range_value' ).textContent = field.value;
	}

	function getSliderDefaultValueInput( previewInputId ) {
		return document.querySelector( `input[data-changeme="${ previewInputId }"][data-changeatt="value"]` ).value;
	}

	function getSliderMidpoint( sliderInput ) {
		const max = parseFloat( sliderInput.getAttribute( 'max' ) );
		const min = parseFloat( sliderInput.getAttribute( 'min' ) );
		return ( ( max - min ) / 2 ) + min;
	}

	function isSliderField( previewInput ) {
		return 'range' === previewInput.type && previewInput.parentNode.classList.contains( 'frm_range_container' );
	}

	function toggleInvalidMsg() {
		/*jshint validthis:true */
		const fieldId = this.getAttribute( 'data-fid' );
		let value = '';

		[ 'field_options_max_', 'frm_format_' ].forEach( function( id ) {
			const input = document.getElementById( id + fieldId );
			if ( ! input ) {
				return;
			}

			value += input.value;
		} );

		const typeDropdown = document.getElementsByName( `field_options[type_${ fieldId }]` )[ 0 ];
		const fieldType = typeDropdown.options[ typeDropdown.selectedIndex ].value;

		if ( fieldType === 'text' ) {
			toggleValidationBox( '' !== value, `.frm_invalid_msg${ fieldId }` );
		}
	}

	function markRequired() {
		/*jshint validthis:true */
		const thisid = this.id.replace( 'frm_', '' );
		const fieldId = thisid.replace( 'req_field_', '' );
		const { checked } = this;
		const label = jQuery( `#field_label_${ fieldId } .frm_required` );

		toggleValidationBox( checked, `.frm_required_details${ fieldId }` );

		if ( checked ) {
			const $reqBox = jQuery( `input[name="field_options[required_indicator_${ fieldId }]"]` );
			if ( $reqBox.val() === '' ) {
				$reqBox.val( '*' );
				liveChanges.call( $reqBox[ 0 ] );
			}
			label.removeClass( 'frm_hidden' );
		} else {
			label.addClass( 'frm_hidden' );
		}
	}

	function toggleValidationBox( hasValue, messageClass ) {
		const $msg = jQuery( messageClass );
		if ( hasValue ) {
			$msg.fadeIn( 'fast' ).closest( '.frm_validation_msg' ).fadeIn( 'fast' );
		} else {
			// Fade out validation options
			const $validationBox = $msg.fadeOut( 'fast' ).closest( '.frm_validation_box' );
			const visibleCount = $validationBox.css( 'display', 'block' ).children( `:not(${ messageClass }):visible` ).length;
			$validationBox.css( 'display', '' );
			if ( visibleCount === 0 ) {
				$msg.closest( '.frm_validation_msg' ).fadeOut( 'fast' );
			}
		}
	}

	function markUnique() {
		/*jshint validthis:true */
		const fieldId = jQuery( this ).closest( '.frm-single-settings' ).data( 'fid' );
		const $thisField = jQuery( `.frm_unique_details${ fieldId }` );
		if ( this.checked ) {
			$thisField.fadeIn( 'fast' ).closest( '.frm_validation_msg' ).fadeIn( 'fast' );
			const $unqDetail = jQuery( `.frm_unique_details${ fieldId } input` );
			if ( $unqDetail.val() === '' ) {
				$unqDetail.val( frmAdminJs.default_unique );
			}
		} else {
			const $validationBox = $thisField.fadeOut( 'fast' ).closest( '.frm_validation_box' );
			const visibleCount = $validationBox.css( 'display', 'block' ).children( `:not(.frm_unique_details${ fieldId }):visible` ).length;
			$validationBox.css( 'display', '' );
			if ( visibleCount === 0 ) {
				$thisField.closest( '.frm_validation_msg' ).fadeOut( 'fast' );
			}
		}
	}

	//Fade confirmation field and validation option in or out
	function addConf() {
		/*jshint validthis:true */
		const fieldId = jQuery( this ).closest( '.frm-single-settings' ).data( 'fid' );
		const val = jQuery( this ).val();
		const $thisField = jQuery( document.getElementById( `frm_field_id_${ fieldId }` ) );

		toggleValidationBox( val !== '', `.frm_conf_details${ fieldId }` );

		if ( val !== '' ) {
			//Add default validation message if empty
			const valMsg = jQuery( `.frm_validation_box .frm_conf_details${ fieldId } input` );
			if ( valMsg.val() === '' ) {
				valMsg.val( frmAdminJs.default_conf );
			}

			setConfirmationFieldDescriptions( fieldId );

			//Add or remove class for confirmation field styling
			if ( val === 'inline' ) {
				$thisField.removeClass( 'frm_conf_below' ).addClass( 'frm_conf_inline' );
			} else if ( val === 'below' ) {
				$thisField.removeClass( 'frm_conf_inline' ).addClass( 'frm_conf_below' );
			}
			jQuery( `.frm-conf-box-${ fieldId }` ).removeClass( 'frm_hidden' );
		} else {
			jQuery( `.frm-conf-box-${ fieldId }` ).addClass( 'frm_hidden' );
			setTimeout( function() {
				$thisField.removeClass( 'frm_conf_inline frm_conf_below' );
			}, 200 );
		}
	}

	function setConfirmationFieldDescriptions( fieldId ) {
		const fieldType = document.getElementsByName( `field_options[type_${ fieldId }]` )[ 0 ].value;

		const fieldDescription = document.getElementById( `field_description_${ fieldId }` );
		const hiddenDescName = `field_options[description_${ fieldId }]`;
		const newValue = frmAdminJs[ `enter_${ fieldType }` ];
		maybeSetNewDescription( fieldDescription, hiddenDescName, newValue );

		const confFieldDescription = document.getElementById( `conf_field_description_${ fieldId }` );
		const hiddenConfName = `field_options[conf_desc_${ fieldId }]`;
		const newConfValue = frmAdminJs[ `confirm_${ fieldType }` ];
		maybeSetNewDescription( confFieldDescription, hiddenConfName, newConfValue );
	}

	function maybeSetNewDescription( descriptionDiv, hiddenName, newValue ) {
		if ( descriptionDiv.innerHTML === frmAdminJs.desc ) {
			// Set the visible description value and the hidden description value
			descriptionDiv.innerHTML = newValue;
			document.getElementsByName( hiddenName )[ 0 ].value = newValue;
		}
	}

	let bulkOptionsOverlay;

	let bulkOptionsOverlayScheduled = false;

	/**
	 * Create the dialog once, either during idle time or on its first click.
	 *
	 * @since 7.0
	 * @return {jQuery|boolean} The dialog, or false when its markup is absent.
	 */
	function initializeBulkOptionsOverlay() {
		if ( bulkOptionsOverlay === undefined ) {
			bulkOptionsOverlay = initModal( '#frm-bulk-modal', '700px' );
		}
		return bulkOptionsOverlay;
	}

	/**
	 * Wait for page assets and field rendering before scheduling optional dialog work.
	 *
	 * @since 7.0
	 * @return {void}
	 */
	function scheduleBulkOptionsOverlay() {
		if ( bulkOptionsOverlayScheduled ) {
			return;
		}
		bulkOptionsOverlayScheduled = true;

		const schedule = () => {
			if ( window.requestIdleCallback ) {
				window.requestIdleCallback( initializeBulkOptionsOverlay );
			} else {
				setTimeout( initializeBulkOptionsOverlay, 0 );
			}
		};
		if ( document.readyState === 'complete' ) {
			schedule();
		} else {
			window.addEventListener( 'load', schedule, { once: true } );
		}
	}

	function initBulkOptionsOverlay() {
		/*jshint validthis:true */

		jQuery( '.frm-insert-preset' ).on( 'click', insertBulkPreset );

		jQuery( builderForm ).on( 'click', 'a.frm-bulk-edit-link', function( event ) {
			event.preventDefault();
			const $info = initializeBulkOptionsOverlay();
			if ( $info === false ) {
				return;
			}
			let content = '';
			const fieldId = jQuery( this ).closest( '[data-fid]' ).data( 'fid' );
			const separate = usingSeparateValues( fieldId );
			const product = isProductField( fieldId );

			const optList = document.getElementById( `frm_field_${ fieldId }_opts` );
			if ( ! optList ) {
				return;
			}

			const opts = optList.getElementsByTagName( 'li' );

			document.getElementById( 'bulk-field-id' ).value = fieldId;

			for ( let i = 0; i < opts.length; i++ ) {
				const key = opts[ i ].getAttribute( 'data-optkey' );
				if ( key !== '000' ) {
					const label = document.getElementsByName( `field_options[options_${ fieldId }][${ key }][label]` )[ 0 ];
					if ( label !== undefined ) {
						content += label.value;
						if ( separate ) {
							content += `|${ document.getElementsByName( `field_options[options_${ fieldId }][${ key }][value]` )[ 0 ].value }`;
						}
						if ( product ) {
							content += `|${ document.getElementsByName( `field_options[options_${ fieldId }][${ key }][price]` )[ 0 ].value }`;
						}
						content += '\r\n';
					}
				}

				if ( i >= opts.length - 1 ) {
					document.getElementById( 'frm_bulk_options' ).value = content;
				}
			}

			$info.dialog( 'open' );

			return false;
		} );

		jQuery( '#frm-update-bulk-opts' ).on( 'click', function() {
			const fieldId = document.getElementById( 'bulk-field-id' ).value;
			const optionType = document.getElementById( 'bulk-option-type' ).value;

			if ( optionType ) {
				// Use custom handler for custom option type.
				return;
			}

			this.classList.add( 'frm_loading_button' );
			frmAdminBuild.updateOpts( fieldId, document.getElementById( 'frm_bulk_options' ).value, initializeBulkOptionsOverlay() );
			fieldUpdated();
		} );
	}

	function insertBulkPreset( event ) {
		/*jshint validthis:true */
		const opts = JSON.parse( this.getAttribute( 'data-opts' ) );
		event.preventDefault();
		document.getElementById( 'frm_bulk_options' ).value = opts.join( '\n' );
		return false;
	}

	//Add new option or "Other" option to radio/checkbox/dropdown
	function addFieldOption() {
		/*jshint validthis:true */
		const fieldId = jQuery( this ).closest( '.frm-single-settings' ).data( 'fid' );
		let newOption = jQuery( `#frm_field_${ fieldId }_opts .frm_option_template` ).prop( 'outerHTML' );
		const optType = jQuery( this ).data( 'opttype' );
		let optKey = 0;
		const oldKey = '000';
		const lastKey = getHighestOptKey( fieldId );

		if ( lastKey !== oldKey ) {
			optKey = lastKey + 1;
		}

		//Update hidden field
		if ( optType === 'other' ) {
			document.getElementById( `other_input_${ fieldId }` ).value = 1;

			//Hide "Add Other" option now if this is radio field
			const ftype = jQuery( this ).data( 'ftype' );
			if ( ftype === 'radio' || ftype === 'select' ) {
				jQuery( this ).fadeOut( 'slow' );
			}

			const data = {
				action: 'frm_add_field_option',
				field_id: fieldId,
				opt_key: optKey,
				opt_type: optType,
				nonce: frmGlobal.nonce
			};
			jQuery.post( ajaxurl, data, function( msg ) {
				jQuery( document.getElementById( `frm_field_${ fieldId }_opts` ) ).append( msg );
				builder.fieldPreview.resetDisplayedOpts( fieldId );
			} );
		} else {
			newOption = newOption.replace( new RegExp( `optkey="${ oldKey }"`, 'g' ), `optkey="${ optKey }"` );
			newOption = newOption.replace( new RegExp( `-${ oldKey }_`, 'g' ), `-${ optKey }_` );
			newOption = newOption.replace( new RegExp( `-${ oldKey }"`, 'g' ), `-${ optKey }"` );
			newOption = newOption.replace( new RegExp( `\\[${ oldKey }\\]`, 'g' ), `[${ optKey }]` );
			newOption = newOption.replace( 'frm_hidden frm_option_template', '' );
			newOption = { newOption };
			builder.fieldEditor.addSaveAndDragIconsToOption( fieldId, newOption );

			const $thisOption = this.closest( '.frm_single_option' );
			if ( $thisOption ) {
				$thisOption.after( newOption.newOption );
			} else {
				// Backwards compatibility "@since 6.24"
				// Note: Keep it jQuery since some events are attached to the element
				jQuery( `#frm_field_${ fieldId }_opts` ).append( newOption.newOption );
			}

			builder.fieldPreview.resetDisplayedOpts( fieldId );
		}

		fieldOptionEnableAllRemoveButtons( this );
		fieldUpdated();
	}

	/**
	 * Enable all remove buttons for field options.
	 *
	 * @param {HTMLElement} element The add option button element.
	 */
	function fieldOptionEnableAllRemoveButtons( element ) {
		// Make sure all remove buttons are enabled
		const parentEl = element.classList.contains( 'frm-add-option-legacy' ) // Backwards compatibility "@since 6.24"
			? element.closest( '.frm-collapse-me' )?.querySelector( '.frm_sortable_field_opts' )
			: element.closest( '.frm_sortable_field_opts' );

		parentEl?.querySelectorAll( '.frm_remove_tag.frm_disabled' ).forEach( button => button.classList.remove( 'frm_disabled' ) );
	}

	function getHighestOptKey( fieldId ) {
		let i = 0;
		let optKey = 0;
		const opts = jQuery( `#frm_field_${ fieldId }_opts li` );
		let lastKey = 0;

		for ( i; i < opts.length; i++ ) {
			optKey = opts[ i ].getAttribute( 'data-optkey' );
			if ( opts.length === 1 ) {
				return optKey;
			}
			if ( optKey !== '000' ) {
				optKey = optKey.replace( 'other_', '' );
				optKey = parseInt( optKey, 10 );
			}

			if ( ! isNaN( lastKey ) && ( optKey > lastKey || lastKey === '000' ) ) {
				lastKey = optKey;
			}
		}

		return lastKey;
	}

	function toggleMultSel() {
		/*jshint validthis:true */
		const fieldId = jQuery( this ).closest( '.frm-single-settings' ).data( 'fid' );
		toggleMultiSelect( fieldId, this.value );
	}

	function toggleMultiSelect( fieldId, value ) {
		const setting = jQuery( `.frm_multiple_cont_${ fieldId }` );
		if ( value === 'select' ) {
			setting.fadeIn( 'fast' );
		} else {
			setting.fadeOut( 'fast' );
		}
	}

	function toggleSepValues() {
		/*jshint validthis:true */
		const fieldId = jQuery( this ).closest( '.frm-single-settings' ).data( 'fid' );
		builder.fieldEditor.toggle( jQuery( `.field_${ fieldId }_option_key` ), '', this.checked );
		jQuery( `.field_${ fieldId }_option` ).toggleClass( 'frm_with_key' );
	}

	function toggleImageOptions() {
		/*jshint validthis:true */
		const $field = jQuery( this ).closest( '.frm-single-settings' );
		const fieldId = $field.data( 'fid' );
		const displayField = document.getElementById( `frm_field_id_${ fieldId }` );

		refreshOptionDisplayNow( jQuery( this ) );

		const hasImageOptions = imagesAsOptions( fieldId );
		builder.fieldEditor.toggle( $field.find( `.field_${ fieldId }_image_id` ), '', hasImageOptions );
		builder.fieldEditor.toggle( $field.find( `.frm_toggle_image_options_${ fieldId }` ), '', hasImageOptions );
		builder.fieldEditor.toggle( $field.find( `.frm_image_size_${ fieldId }` ), '', hasImageOptions );
		builder.fieldEditor.toggle( $field.find( `.frm_alignment_${ fieldId }` ), '', hasImageOptions );
		builder.fieldEditor.toggle( $field.find( `.frm-add-other#frm_add_field_${ fieldId }` ), '', hasImageOptions );

		removeImageSizeClasses( displayField );

		if ( hasImageOptions ) {
			setAlignment( fieldId, 'inline' );
			const imageSize = builder.fieldPreview.getImageOptionSize( fieldId );
			displayField.classList.add( 'frm_image_options' );
			displayField.classList.add( `frm_image_size_${ imageSize }` );
			$field.find( '.frm-bulk-edit-link' ).hide();
		} else {
			displayField.classList.remove( 'frm_image_options' );
			setAlignment( fieldId, 'block' );
			$field.find( '.frm-bulk-edit-link' ).show();
		}

		/**
		 * Fires when image options are toggled for a field.
		 *
		 * @param {HTMLElement} field           The field element.
		 * @param {boolean}     hasImageOptions Whether the field has image options enabled.
		 */
		wp.hooks.doAction( 'frm_image_options_toggled', $field[ 0 ], hasImageOptions );
	}

	function removeImageSizeClasses( field ) {
		field.classList.remove( 'frm_image_size_', 'frm_image_size_small', 'frm_image_size_medium', 'frm_image_size_large', 'frm_image_size_xlarge' );
	}

	function setAlignment( fieldId, alignment ) {
		jQuery( `#field_options_align_${ fieldId }` ).val( alignment ).trigger( 'change' );
	}

	function setImageSize() {
		const $field = jQuery( this ).closest( '.frm-single-settings' );
		const fieldId = $field.data( 'fid' );
		const displayField = document.getElementById( `frm_field_id_${ fieldId }` );

		refreshOptionDisplay();

		if ( imagesAsOptions( fieldId ) ) {
			removeImageSizeClasses( displayField );
			displayField.classList.add( 'frm_image_options' );
			displayField.classList.add( `frm_image_size_${ builder.fieldPreview.getImageOptionSize( fieldId ) }` );
		}
	}

	function refreshOptionDisplayNow( object ) {
		const $field = object.closest( '.frm-single-settings' );
		const fieldID = $field.data( 'fid' );
		jQuery( `.field_${ fieldID }_option` ).trigger( 'change' );
	}

	function refreshOptionDisplay() {
		/*jshint validthis:true */
		refreshOptionDisplayNow( jQuery( this ) );
	}

	function toggleMultiselect() {
		/*jshint validthis:true */
		const dropdown = jQuery( this ).closest( 'li' ).find( '.frm_form_fields select' );
		if ( this.checked ) {
			dropdown.attr( 'multiple', 'multiple' );
		} else {
			dropdown.removeAttr( 'multiple' );
		}
	}

	/**
	 * If a field is clicked in the builder, prevent inputs from changing.
	 * @param {Event} e The event to prevent.
	 */
	function stopFieldFocus( e ) {
		e.preventDefault();
	}

	/**
	 * Delete a field option.
	 */
	function deleteFieldOption() {
		materializeAllFieldSettings( prepareLoadedFieldMarkup );
		const parentLi = this.parentNode;
		const parentUl = parentLi.parentNode;

		// If only 2 visible options, add disabled class to the other delete button
		const visibleOptions = parentUl.querySelectorAll( 'li:not(.frm_hidden)' );
		if ( visibleOptions.length === 2 ) {
			Array.from( visibleOptions )
				.find( li => li !== parentLi )
				.querySelector( '.frm_remove_tag' )?.classList.add( 'frm_disabled' );
		}

		/*jshint validthis:true */
		let otherInput;
		const fieldId = this.getAttribute( 'data-fid' );

		jQuery( parentLi ).fadeOut( 'fast', function() {
			wp.hooks.doAction( 'frm_before_delete_field_option', this );
			jQuery( parentLi ).remove();

			const hasOther = jQuery( parentUl ).find( '.frm_other_option' );
			if ( hasOther.length < 1 ) {
				otherInput = document.getElementById( `other_input_${ fieldId }` );
				if ( otherInput ) {
					otherInput.value = 0;
				}
				jQuery( `#other_button_${ fieldId }` ).fadeIn( 'fast' );
			}
		} );
		fieldUpdated();
	}

	/**
	 * If a radio button is set as default, allow a click to
	 * deselect it.
	 */
	function maybeUncheckRadio() {
		/*jshint validthis:true */
		const $self = jQuery( this );
		if ( $self.is( ':checked' ) ) {
			const uncheck = function() {
				setTimeout( function() {
					$self.prop( 'checked', false );
				}, 0 );
			};
			let up; // eslint-disable-line prefer-const
			const unbind = function() {
				$self.off( 'mouseup', up );
			};
			up = function() {
				uncheck();
				unbind();
			};
			$self.on( 'mouseup', up );
			$self.one( 'mouseout', unbind );
		}
	}

	/**
	 * If the field option has the default text, clear it out on click.
	 */
	function maybeClearOptText() {
		/*jshint validthis:true */
		if ( this.value === frmAdminJs.new_option ) {
			this.setAttribute( 'data-value-on-focus', this.value );
			this.value = '';
		}
	}

	function confirmFieldsDeleteMessage( numberOfFields ) {
		/* translators: %1$s: Number of fields that are selected to be deleted. */
		return sprintf( __( 'Are you sure you want to delete these %1$s selected field(s)?', 'formidable' ), numberOfFields );
	}

	function clickDeleteField() {
		/*jshint validthis:true */
		let confirmMsg = frmAdminJs.conf_delete;
		const maybeDivider = this.parentNode.parentNode.parentNode.parentNode.parentNode;
		const li = maybeDivider.parentNode;
		const field = jQuery( this ).closest( 'li.form-field' );
		const fieldId = field.data( 'fid' );

		if ( field.data( 'ftype' ) === 'divider' ) {
			const fieldBoxes = document.querySelectorAll( '.frm-field-group-hover-target .start_divider .frm_field_box' );
			let fieldIdsToDelete = 0;
			fieldBoxes.forEach( fieldBox => {
				const fieldsInsideFieldBox = fieldBox.querySelectorAll( 'li.form-field' );
				if ( fieldsInsideFieldBox ) {
					fieldIdsToDelete += fieldsInsideFieldBox.length;
				}
			} );
			if ( fieldIdsToDelete ) {
				confirmMsg = confirmFieldsDeleteMessage( ++fieldIdsToDelete );
			}
		}

		if ( li.classList.contains( 'frm-section-collapsed' ) || li.classList.contains( 'frm-page-collapsed' ) ) {
			return false;
		}

		// If deleting a section, use a special message.
		if ( maybeDivider.className === 'divider_section_only' ) {
			confirmMsg = frmAdminJs.conf_delete_sec;
		}

		const fieldIds = getFieldIdsToDelete( [ String( fieldId ) ] );
		confirmMsg = wp.hooks.applyFilters( 'frm_delete_fields_confirmation', confirmMsg, fieldIds );

		this.setAttribute( 'data-frmverify', confirmMsg );
		this.setAttribute( 'data-frmverify-btn', 'frm-button-red' );
		this.setAttribute( 'data-deletefield', fieldId );

		builder.fieldActions.closeOpenFieldDropdowns();

		confirmLinkClick( this );
		return false;
	}

	function clickSelectField() {
		this.closest( 'li.form-field' ).click();
	}

	function clickDeleteFieldGroup() {
		const hoverTarget = builder.fieldActions.getActiveHoverRow();
		if ( ! hoverTarget ) {
			return;
		}

		hoverTarget.classList.add( 'frm-selected-field-group' );

		const decoy = document.createElement( 'div' );
		decoy.classList.add( 'frm-delete-field-groups', 'frm_hidden' );
		document.body.append( decoy );
		decoy.click();
	}

	function duplicateFieldGroup() {
		const hoverTarget = builder.fieldActions.getActiveHoverRow();
		if ( ! hoverTarget ) {
			return;
		}

		const newRowId = `frm_field_group_${ builder.dragDrop.getAutoId() }`;
		const placeholderUlChild = document.createTextNode( '' );
		builder.fieldActions.wrapFieldLiInPlace( placeholderUlChild );

		const newRow = jQuery( placeholderUlChild ).closest( 'li' ).get( 0 );
		newRow.classList.add( 'frm_hidden' );

		const newRowUl = newRow.querySelector( 'ul' );
		newRowUl.id = newRowId;

		jQuery( hoverTarget.closest( 'li.frm_field_box' ) ).after( newRow );

		const $fields = getFieldsInRow( jQuery( hoverTarget ) );
		const syncDetails = [];
		const injectedCloneOptions = [];

		const expectedLength = $fields.length;

		let duplicatedCount = 0;

		jQuery( newRow ).on(
			'frm_added_duplicated_field_to_row',
			function() {
				if ( expectedLength > ++duplicatedCount ) {
					return;
				}

				const $newRowUl = jQuery( newRowUl );
				const $duplicatedFields = getFieldsInRow( $newRowUl );

				injectedCloneOptions.forEach(
					function( cloneOption ) {
						cloneOption.remove();
					}
				);

				for ( let index = 0; index < expectedLength; ++index ) {
					$newRowUl.append( $newRowUl.children( `li.form-field[frm-field-order="${ index }"]` ) );
				}

				syncLayoutClasses( $duplicatedFields.first(), syncDetails );
				newRow.classList.remove( 'frm_hidden' );
				builder.fieldEditor.updateFieldOrder();
			}
		);

		$fields.each(
			function( index ) {
				const cloneOption = document.createElement( 'li' );
				cloneOption.classList.add( 'frm_clone_field' );
				cloneOption.setAttribute( 'frm-target-row-id', newRowId );
				cloneOption.setAttribute( 'frm-field-order', index );
				this.append( cloneOption );
				cloneOption.click();
				injectedCloneOptions.push( cloneOption );
				syncDetails.push( builder.fieldGroups.getSizeOfLayoutClass( builder.fieldGroups.getLayoutClassName( this.classList ) ) );
			}
		);
	}

	return {
		addConf,
		addFieldOption,
		checkCalculationCreatedByUser,
		clickDeleteField,
		clickDeleteFieldGroup,
		clickSelectField,
		confirmFieldsDeleteMessage,
		deleteFieldOption,
		duplicateFieldGroup,
		initBulkOptionsOverlay,
		liveChanges,
		markRequired,
		markUnique,
		maybeClearOptText,
		maybeSetProductField,
		maybeUncheckRadio,
		rePopCalcFieldsForSummary,
		refreshOptionDisplay,
		scheduleBulkOptionsOverlay,
		setImageSize,
		stopFieldFocus,
		toggleImageOptions,
		toggleInvalidMsg,
		toggleMultSel,
		toggleMultiSelect,
		toggleMultiselect,
		toggleSepValues,
		toggleValidationBox,
	};
};
