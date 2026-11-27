module.exports = function createFieldPreview( {
	adjustConditionalLogicOptionOrders,
	fieldsContainer,
	fillDropdownOpts,
	frmAdminJs,
	getImageLabel,
	getMultipleOpts,
	hydrateBuilderSelect,
	imagesAsOptions,
	infoModal,
	isProductField,
	purifyHtml,
	showingLabelWithImage,
	usingSeparateValues,
}, builder ) {
	const { tag, div } = frmDom;
	const { __, sprintf } = wp.i18n;
	// The fake field works differently than real fields.
	function maybeCollapsePage() {
		/*jshint validthis:true */
		const field = jQuery( this ).closest( '.frm_field_box[data-ftype=break]' );
		if ( field.length ) {
			toggleCollapsePage( field );
		} else {
			toggleCollapseFakePage();
		}
	}

	// Find all fields in a page and hide/show them
	function toggleCollapsePage( field ) {
		const toCollapse = getAllFieldsForPage( field.get( 0 ).parentNode.closest( 'li.frm_field_box' ).nextElementSibling );
		togglePage( field, toCollapse );
	}

	function toggleCollapseFakePage() {
		const topLevel = document.getElementById( 'frm-fake-page' );
		const firstField = fieldsContainer.firstElementChild;
		const toCollapse = getAllFieldsForPage( firstField );

		if ( firstField.getAttribute( 'data-ftype' ) === 'break' ) {
			// Don't collapse if the first field is a page break.
			return;
		}

		togglePage( jQuery( topLevel ), toCollapse );
	}

	function getAllFieldsForPage( firstWrapper ) {
		let $fieldsForPage;
		let currentWrapper;

		$fieldsForPage = jQuery();

		if ( null === firstWrapper ) {
			return $fieldsForPage;
		}

		currentWrapper = firstWrapper;

		do {
			if ( null !== currentWrapper.querySelector( '.edit_field_type_break' ) ) {
				break;
			}
			$fieldsForPage = $fieldsForPage.add( jQuery( currentWrapper ) );
			currentWrapper = currentWrapper.nextElementSibling;
		} while ( null !== currentWrapper );

		return $fieldsForPage;
	}

	function togglePage( field, toCollapse ) {
		let i;
		const fieldCount = toCollapse.length;
		const slide = Math.min( fieldCount, 3 );

		if ( field.hasClass( 'frm-page-collapsed' ) ) {
			field.removeClass( 'frm-page-collapsed' );
			toCollapse.removeClass( 'frm-is-collapsed' );
			for ( i = 0; i < slide; i++ ) {
				if ( i === slide - 1 ) {
					jQuery( toCollapse[ i ] ).slideDown( 150, function() {
						toCollapse.show();
					} );
				} else {
					jQuery( toCollapse[ i ] ).slideDown( 150 );
				}
			}
		} else {
			field.addClass( 'frm-page-collapsed' );
			toCollapse.addClass( 'frm-is-collapsed' );
			for ( i = 0; i < slide; i++ ) {
				if ( i === slide - 1 ) {
					jQuery( toCollapse[ i ] ).slideUp( 150, function() {
						toCollapse.css( 'cssText', 'display:none !important;' );
					} );
				} else {
					jQuery( toCollapse[ i ] ).slideUp( 150 );
				}
			}
		}
	}

	function maybeCollapseSection() {
		/*jshint validthis:true */
		const parentCont = this.parentNode.parentNode.parentNode.parentNode;

		parentCont.classList.toggle( 'frm-section-collapsed' );
	}

	function maybeCollapseSettings() {
		/*jshint validthis:true */

		// Restore animation that may have been disabled by showFieldOptions.
		if ( this.nextElementSibling ) {
			this.nextElementSibling.style.animation = '';
		}

		this.classList.toggle( 'frm-collapsed' );

		// Toggles the "aria-expanded" attribute
		const expanded = this.getAttribute( 'aria-expanded' ) === 'true' || false;
		this.setAttribute( 'aria-expanded', ! expanded );

		addSlideAnimationCssVars( this.nextElementSibling );
	}

	/**
	 * Add slide animation CSS variables to the element
	 *
	 * @param {HTMLElement} element The element to add CSS variables to
	 * @return {void}
	 */
	function addSlideAnimationCssVars( element ) {
		if ( ! element ) {
			return;
		}

		let height = element.scrollHeight;
		if ( height <= 0 ) {
			return;
		}

		height += 250;
		element.style.setProperty( '--slide-height', `${ height }px` );
		element.style.setProperty( '--slide-time', `${ Math.ceil( height * 0.8 ) }ms` );
	}

	function clickLabel() {
		if ( ! this.id ) {
			return;
		}

		/*jshint validthis:true */
		const setting = document.querySelectorAll( `[data-changeme="${ this.id }"]` )[ 0 ];
		const fieldId = this.id.replace( 'field_label_', '' );
		const fieldType = document.getElementById( `field_options_type_${ fieldId }` );
		let fieldTypeName = fieldType.value;

		if ( setting !== undefined ) {
			if ( fieldType.tagName === 'SELECT' ) {
				hydrateBuilderSelect( fieldType );
				fieldTypeName = fieldType.options[ fieldType.selectedIndex ].text.toLowerCase();
			} else {
				fieldTypeName = fieldTypeName.replace( '_', ' ' );
			}

			fieldTypeName = normalizeFieldName( fieldTypeName );

			// The setting can't take focus until its tab is showing.
			setTimeout( () => builder.state.fieldOptionsTabShown.then( () => {
				if ( setting.value.toLowerCase() === fieldTypeName ) {
					setting.select();
				} else {
					setting.focus();
				}
			} ), 50 );
		}
	}

	function clickDescription() {
		/*jshint validthis:true */
		const setting = document.querySelectorAll( `[data-changeme="${ this.id }"]` )[ 0 ];
		if ( setting !== undefined ) {
			setTimeout( () => builder.state.fieldOptionsTabShown.then( () => {
				setting.focus();
				autoExpandSettings( setting );
			} ), 50 );
		}
	}

	function autoExpandSettings( setting ) {
		const inSection = setting.closest( '.frm-collapse-me' );
		if ( inSection !== null ) {
			inSection.previousElementSibling.classList.remove( 'frm-collapsed' );
		}
	}

	function normalizeFieldName( fieldTypeName ) {
		if ( fieldTypeName === 'divider' ) {
			fieldTypeName = 'section';
		} else if ( fieldTypeName === 'range' ) {
			fieldTypeName = 'slider';
		} else if ( fieldTypeName === 'data' ) {
			fieldTypeName = 'dynamic';
		} else if ( fieldTypeName === 'form' ) {
			fieldTypeName = 'embed form';
		}
		return fieldTypeName;
	}

	function clickVis( e ) {
		/*jshint validthis:true */
		const currentClass = e.target.classList;

		if ( currentClass.contains( 'frm-collapse-page' ) || currentClass.contains( 'frm-sub-label' ) || e.target.closest( '.dropdown' ) !== null ) {
			return;
		}

		if ( this.closest( '.start_divider' ) !== null ) {
			e.stopPropagation();
		}

		if ( this.classList.contains( 'edit_field_type_divider' ) ) {
			const originalList = e.originalEvent.target.closest( 'ul.frm_sorting' );
			if ( null !== originalList ) {
				// prevent section click if clicking a field group within a section.
				if ( wp.hooks.applyFilters( 'frm_builder_skip_container_click', originalList.classList.contains( 'edit_field_type_divider' ) || originalList.parentNode.parentNode.classList.contains( 'start_divider' ), originalList ) ) {
					return;
				}
			}
		}

		builder.fieldEditor.clickAction( this );
	}

	/**
	 * Update the format input based on the selected format type.
	 *
	 * @since 6.9
	 *
	 * @param {Event} event The event object from the format type selection.
	 * @return {void}
	 */
	function maybeUpdateFormatInput( event ) {
		const formatElement = event.target;
		const type = formatElement.value;
		const fieldId = formatElement.dataset.fieldId;

		if ( formatElement.classList.contains( 'frm_phone_type_dropdown' ) ) {
			const autocompleteElement = document.getElementById( `field_options_autocomplete_${ fieldId }` );
			// An upgrade-only option snaps back to none below, so it must not clear the tel default.
			const isUpgradeOption = Boolean( formatElement.selectedOptions[ 0 ]?.classList.contains( 'frm_show_upgrade' ) );
			const hasFormat = ! isUpgradeOption && ( 'custom' === type || 'international' === type );
			// Browser autofill would put the same value in every repeater row.
			const isInRepeater = Boolean( document.getElementById( `frm_field_id_${ fieldId }` )?.closest( '.repeat_section' ) );

			if ( autocompleteElement && 'none' === type && ! autocompleteElement.value && ! isInRepeater ) {
				autocompleteElement.value = 'tel';
			} else if ( autocompleteElement && hasFormat && 'tel' === autocompleteElement.value ) {
				autocompleteElement.value = '';
			}
		}

		if ( 'custom' === type ) {
			const formatInput = document.getElementById( `frm-field-format-custom-${ fieldId }` ).querySelector( '.frm_format_opt' );

			if ( 'international' === formatInput.value || 'currency' === formatInput.value || 'number' === formatInput.value ) {
				formatInput.setAttribute( 'value', '' );
			}
		}

		setTimeout(
			() => {
				formatElement.querySelectorAll( 'option' ).forEach(
					option => {
						if ( option.selected && option.classList.contains( 'frm_show_upgrade' ) ) {
							formatElement.value = 'none';
						}
					}
				);
			},
			0
		);
	}

	/**
	 * Open Advanced settings on double click.
	 */
	function openAdvanced() {
		const fieldId = this.getAttribute( 'data-fid' );
		autoExpandSettings( document.getElementById( `field_options_field_key_${ fieldId }` ) );
	}

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
			infoModal( frmAdminJs.repeat_limit_min );
			this.value = '';
		}
	}

	function checkCheckboxSelectionsLimit() {
		/*jshint validthis:true */
		const val = this.value;
		if ( val !== '' && ( val < 1 || val > 200 ) ) {
			infoModal( frmAdminJs.checkbox_limit );
			this.value = '';
		}
	}

	function updateRepeatText( obj, addRemove ) {
		const $thisField = jQuery( obj ).closest( '.frm_field_box' );
		$thisField.find( `.frm_${ addRemove }_form_row .frm_repeat_label` ).text( obj.value );
	}

	function fieldsInSection( id ) {
		const children = [];
		jQuery( document.getElementById( `frm_field_id_${ id }` ) ).find( 'li.frm_field_box:not(.no_repeat_section .edit_field_type_end_divider)' ).each( function() {
			children.push( jQuery( this ).data( 'fid' ) );
		} );
		return wp.hooks.applyFilters( 'frm_builder_child_field_ids', children, id );
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
			builder.dragDrop.empty( $showFields );
		} else {
			$showFields.style.display = 'none';
			$showForms.style.display = 'none';
			getTaxOrFieldSelection( val, id );
		}
	}

	function resetOptOnChange() {
		/*jshint validthis:true */
		const field = getFieldKeyFromOpt( this );
		if ( ! field ) {
			return;
		}

		const thisOpt = jQuery( this ).closest( '.frm_single_option' );

		resetSingleOpt( field.fieldId, field.fieldKey, thisOpt );
	}

	function getFieldKeyFromOpt( object ) {
		const allOpts = jQuery( object ).closest( '.frm_sortable_field_opts' );
		if ( ! allOpts.length ) {
			return false;
		}

		const fieldId = allOpts.attr( 'id' ).replace( 'frm_field_', '' ).replace( '_opts', '' );
		const fieldKey = allOpts.data( 'key' );

		return {
			fieldId,
			fieldKey
		};
	}

	function resetSingleOpt( fieldId, fieldKey, thisOpt ) {
		const optKey = thisOpt.data( 'optkey' );
		const separateValues = usingSeparateValues( fieldId );
		const single = jQuery( `label[for="field_${ fieldKey }-${ optKey }"]` );
		const baseName = `field_options[options_${ fieldId }][${ optKey }]`;
		let label = jQuery( `input[name="${ baseName }[label]"]` );

		if ( single.length < 1 ) {
			resetDisplayedOpts( fieldId );

			return;
		}

		const previewInput = single.children( 'input' );

		let saved;
		if ( label.length < 1 ) {
			// Check for other label.
			label = jQuery( `input[name="${ baseName }"]` );
			saved = label.val();
		} else if ( separateValues ) {
			saved = jQuery( `input[name="${ baseName }[value]"]` ).val();
		} else {
			saved = label.val();
		}

		if ( label.length < 1 ) {
			return;
		}

		// Set the displayed value.
		const text = single[ 0 ].childNodes;

		if ( imagesAsOptions( fieldId ) ) {
			const labelForDisplay = getImageDisplayValue( thisOpt, fieldId, label );
			const optContainer = single.find( '.frm_image_option_container' );

			if ( optContainer.length > 0 ) {
				optContainer.replaceWith( labelForDisplay );
			} else {
				text[ text.length - 1 ].nodeValue = '';
				single.append( labelForDisplay );
			}
		} else {
			let firstInputIndex = false;
			text.forEach( ( node, index ) => {
				if ( firstInputIndex === false ) {
					if ( node.tagName === 'INPUT' ) {
						firstInputIndex = index;
					}
				} else if ( index === firstInputIndex + 1 ) {
					let nodeValue = '';

					if ( buttonsAsOptions( fieldId ) ) {
						nodeValue = div( { className: 'frm_label_button_container', text: ` ${ label.val() }` } );
						single[ 0 ].replaceChild( nodeValue, node );
					} else {
						node.nodeValue = ` ${ label.val() }`;
					}
				} else {
					node.remove();
				}
			} );
		}

		// Set saved value.
		previewInput.val( saved );

		// Set the default value.
		defaultVal = thisOpt.find( 'input[name^="default_value_"]' );
		previewInput.prop( 'checked', defaultVal.is( ':checked' ) ? true : false );
	}

	function buttonsAsOptions( fieldId ) {
		const fields = document.getElementsByName( `field_options[image_options_${ fieldId }]` );
		const result = Array.from( fields ).find( field => field.checked && ( 'buttons' === field.value ) );

		return result !== undefined;
	}

	/**
	 * Set the displayed value for an image option.
	 * @param {HTMLElement} thisOpt The option element.
	 * @param {number}      fieldId The field ID.
	 * @param {string}      label   The option label.
	 */
	function getImageDisplayValue( thisOpt, fieldId, label ) {
		const image = thisOpt.find( 'img' );
		let imageUrl;
		if ( image ) {
			imageUrl = image.attr( 'src' );
		}

		const showLabelWithImage = showingLabelWithImage( fieldId );
		return getImageLabel( label.val(), showLabelWithImage, imageUrl );
	}

	function getImageOptionSize( fieldId ) {
		let val;
		const field = document.getElementById( `field_options_image_size_${ fieldId }` );
		let size = '';

		if ( field ) {
			val = field.value;
			if ( val !== '' ) {
				size = val;
			}
		}

		return size;
	}

	function resetDisplayedOpts( fieldId ) {
		const input = jQuery( `[name^="item_meta[${ fieldId }]"]` );

		if ( input.length < 1 ) {
			return;
		}

		const hookArgs = {
			fieldId,
			metaInput: input[ 0 ],
		};

		if ( false !== wp.hooks.applyFilters( 'frm_custom_reset_displayed_opts', false, hookArgs ) ) {
			adjustConditionalLogicOptionOrders( fieldId );
			// Return early if there is a custom reset displayed opts handler.
			return;
		}

		if ( isSingleProductField( fieldId ) ) {
			updateSingleProductLabel( fieldId );
			adjustConditionalLogicOptionOrders( fieldId );
			return;
		}

		if ( input.is( 'select' ) ) {
			const placeholder = document.getElementById( `frm_placeholder_${ fieldId }` );
			if ( ! placeholder || placeholder.value === '' ) {
				fillDropdownOpts( input[ 0 ], { sourceID: fieldId } );
			} else {
				fillDropdownOpts( input[ 0 ], {
					sourceID: fieldId,
					placeholder: placeholder.value
				} );
			}
		} else {
			const opts = getMultipleOpts( fieldId );
			const container = jQuery( `#field_${ fieldId }_inner_container > .frm_form_fields` );
			const hasImageOptions = imagesAsOptions( fieldId );
			const imageSize = hasImageOptions ? getImageOptionSize( fieldId ) : '';
			const imageOptionClass = hasImageOptions ? `frm_image_option frm_image_${ imageSize } ` : '';

			container.html( '' );
			const fieldInfo = getFieldKeyFromOpt( jQuery( `#frm_delete_field_${ fieldId }-000_container` ) );
			const isProduct = isProductField( fieldId );

			const type = 'hidden' === input.attr( 'type' ) ? input.data( 'field-type' ) : input.attr( 'type' );
			for ( let i = 0; i < opts.length; i++ ) {
				container.append( addRadioCheckboxOpt( type, opts[ i ], fieldId, fieldInfo.fieldKey, isProduct, imageOptionClass ) );
			}
		}

		adjustConditionalLogicOptionOrders( fieldId );
	}

	/**
	 * Normalize a price string using the site's configured currency separators
	 * before it's passed to Number(), mirroring FrmCurrencyHelper::prepare_price()'s
	 * PHP-side logic so a comma-decimal locale (e.g. EUR) parses correctly instead
	 * of producing NaN.
	 *
	 * @since 7.0
	 *
	 * @param {string|number} price    Raw price value.
	 * @param {Object}        currency Currency settings object.
	 * @return {string} Price string using '.' as the decimal separator, safe for Number().
	 */
	function normalizePriceString( price, currency ) {
		price = String( price ).trim();
		if ( ! price ) {
			return '';
		}

		const matches = price.match( /-?[\d.,]+/g );
		price = matches ? matches[ matches.length - 1 ] : '';
		while ( /[.,]$/.test( price ) ) {
			price = price.slice( 0, -1 );
		}
		if ( ! price ) {
			return '';
		}

		const thousandSep = currency.thousand_separator ?? ',';
		const decimalSep = currency.decimal_separator ?? '.';

		// A '.' used as the thousand separator is ambiguous with a plain decimal point;
		// treat it as decimal when it trails exactly 1-2 digits, same as the PHP side.
		if ( thousandSep === '.' ) {
			const parts = price.split( '.' );
			if ( parts.length === 2 && [ 1, 2 ].includes( parts[ 1 ].length ) ) {
				price = parts.join( decimalSep );
			}
		}

		if ( thousandSep ) {
			price = price.split( thousandSep ).join( '' );
		}
		return price.split( decimalSep ).join( '.' );
	}

	/**
	 * Format a product price value for display in the builder preview using the
	 * currency settings from frm_admin_js. Mirrors the logic in FrmCurrencyHelper::format_price().
	 *
	 * @since 7.0
	 *
	 * @param {string|number} price Raw price value.
	 * @return {string} Formatted price string.
	 */
	function formatProductPrice( price ) {
		const currency = frm_admin_js?.currency;
		if ( ! currency ) {
			return String( price );
		}

		const normalized = normalizePriceString( price, currency );
		const num = Number( normalized );
		if ( ! normalized || isNaN( num ) ) {
			return String( price );
		}

		const decimals = Number( currency.decimals ?? 2 );
		const decimalSep = currency.decimal_separator ?? '.';
		const thousandSep = currency.thousand_separator ?? ',';

		let formatted = Math.abs( num ).toFixed( decimals ).replace( '.', decimalSep );

		const parts = decimals > 0 ? formatted.split( decimalSep ) : [ formatted ];
		if ( thousandSep ) {
			let grouped = '';
			for ( let end = parts[ 0 ].length; end > 0; end -= 3 ) {
				grouped = parts[ 0 ].slice( Math.max( 0, end - 3 ), end ) + ( grouped ? thousandSep + grouped : '' );
			}
			parts[ 0 ] = grouped;
		}
		formatted = ( num < 0 ? '-' : '' ) + parts.join( decimalSep );

		const symbolPadding = currency.symbol_padding ?? '';
		const leftSymbol = currency.symbol_left ? ( currency.symbol_left + symbolPadding ) : '';
		const rightSymbol = currency.symbol_right ? ( symbolPadding + currency.symbol_right ) : '';

		return leftSymbol + formatted + rightSymbol;
	}

	/**
	 * @since 7.0
	 *
	 * @param {string|number} fieldId
	 * @return {boolean} True if the field data type is 'single' product.
	 */
	function isSingleProductField( fieldId ) {
		const el = document.querySelector( `select[name="field_options[data_type_${ fieldId }]"]` );
		return Boolean( el ) && el.value === 'single';
	}

	/**
	 * Update the .frm_single_product_label text in the builder preview to reflect
	 * the current name and price values of the first product option.
	 *
	 * @since 7.0
	 *
	 * @param {string|number} fieldId
	 */
	function updateSingleProductLabel( fieldId ) {
		const container = document.querySelector( `#field_${ fieldId }_inner_container > .frm_form_fields` );
		if ( ! container ) {
			return;
		}

		const firstRealOpt = document.querySelector( `#frm_field_${ fieldId }_opts .frm_single_option:not(.frm_option_template)` );
		if ( ! firstRealOpt ) {
			return;
		}

		const firstOptKey = firstRealOpt.dataset.optkey;
		const optWrapper = document.getElementById( `frm_delete_field_${ fieldId }-${ firstOptKey }_container` );
		if ( ! optWrapper ) {
			return;
		}

		const label = optWrapper.querySelector( `.field_${ fieldId }_option:not(.frm_product_price)` )?.value ?? '';
		const price = optWrapper.querySelector( '.frm_product_price' )?.value ?? '';

		let labelEl = container.querySelector( '.frm_single_product_label' );
		let hiddenInput = container.querySelector( 'input[type="hidden"][data-frmprice]' );

		if ( ! labelEl || ! hiddenInput ) {
			// The preview still shows the markup for whatever data type this field had when
			// it was last rendered server-side (e.g. the default dropdown) - switching the
			// "Product Type" setting to Single Product doesn't request a fresh render, so build
			// the single-product preview markup here instead, mirroring product-single.php.
			const existingInput = container.querySelector( `[name^="item_meta[${ fieldId }]"]` );
			// Radio and checkbox inputs carry an option suffix in their id, and checkboxes a [] in their name.
			const fieldName = `item_meta[${ fieldId }]`;
			const fieldKey = document.getElementById( `frm_field_${ fieldId }_opts` )?.dataset.key;
			const htmlId = fieldKey ? `field_${ fieldKey }` : ( existingInput?.getAttribute( 'id' ) ?? `field_${ fieldId }` );
			const fieldVal = existingInput?.value ?? '';

			// Not existingInput.cloneNode(true): the stale markup being replaced here is often
			// a <select> (a fresh field's server-rendered default data type), whose children
			// and tag semantics don't carry over to the hidden input product-single.php expects.
			labelEl = tag( 'p', { className: 'frm_single_product_label' } );

			hiddenInput = tag( 'input', { id: htmlId } );
			frmDom.setAttributes( hiddenInput, {
				type: 'hidden',
				name: fieldName,
				value: fieldVal
			} );

			// Pro renders this wrapper in product-single.php, and its frm_custom_reset_displayed_opts
			// handler skips the preview update when the wrapper is missing.
			container.replaceChildren(
				div( {
					className: 'frm_single_product_wrap',
					children: [ labelEl, hiddenInput ]
				} )
			);
		}

		const parts = [];
		if ( label ) {
			parts.push( label );
		}
		if ( price ) {
			parts.push( formatProductPrice( price ) );
		}
		labelEl.innerHTML = purifyHtml( parts.join( ': ' ) );
		hiddenInput.dataset.frmprice = price;
	}

	/**
	 * When a product field switches away from Single Product, swap the single product preview
	 * for an input of the new type so resetDisplayedOpts rebuilds the options preview.
	 * Without this, the leftover hidden input has no data-field-type and the preview breaks.
	 *
	 * @since 7.0
	 *
	 * @param {string} fieldId
	 * @param {string} productType The new product type: select, radio or checkbox.
	 */
	function maybeReplaceSingleProductPreview( fieldId, productType ) {
		const container = document.querySelector( `#field_${ fieldId }_inner_container > .frm_form_fields` );
		const singleInput = container?.querySelector( 'input[type="hidden"][data-frmprice]' );
		if ( ! singleInput ) {
			return;
		}

		let input;
		if ( 'select' === productType ) {
			input = tag( 'select', { id: singleInput.id } );
		} else {
			input = tag( 'input', { id: singleInput.id } );
			frmDom.setAttributes( input, {
				type: 'hidden',
				'data-field-type': productType
			} );
		}
		input.setAttribute( 'name', singleInput.getAttribute( 'name' ) );

		container.replaceChildren( input );
		resetDisplayedOpts( fieldId );
	}

	function addRadioCheckboxOpt( type, opt, fieldId, fieldKey, isProduct, classes ) {
		let single = '';
		const isOther = opt.key.includes( 'other' );
		const id = `field_${ fieldKey }-${ opt.key }`;
		const inputType = type === 'scale' ? 'radio' : type;

		/*
		 * 'radio' and 'checkbox' wrap the option input in its label with no
		 * `for` attribute -- the wrap alone already associates them, and a
		 * `for` pointing at the same id makes Safari VoiceOver announce the
		 * label twice (radio-field.php, checkbox-field.php,
		 * product-radio.php, the last covering both product data_types).
		 * Keep in sync with those templates -- this preview template drifts
		 * silently from the PHP output otherwise.
		 */
		const labelFor = [ 'radio', 'checkbox' ].includes( type ) ? '' : ` for="${ id }"`;

		const other = `<input type="text" id="field_${ fieldKey }-${ opt.key }-otext" class="frm_other_input frm_pos_none" name="item_meta[other][${ fieldId }][${ opt.key }]" value="" />`;

		this.getSingle = function() {
			/**
			 * Get single option template.
			 *
			 * @param {Object} option  Object containing the option data.
			 * @param {string} type    The field type.
			 * @param {string} fieldId The field id.
			 * @param {string} classes The option clasnames.
			 * @param {string} id      The input id attribute.
			 */
			single = wp.hooks.applyFilters( 'frm_admin.build_single_option_template', single, { opt, type, fieldId, classes, id } );

			if ( '' !== single ) {
				return single;
			}

			return `<div class="frm_${ type } ${ type } ${ classes }" id="frm_${ type }_${ fieldId }-${ opt.key }">
					<label${ labelFor }>
						<input type="${ inputType }" name="item_meta[${ fieldId }]${ type === 'checkbox' ? '[]' : '' }" value="${ purifyHtml( opt.saved ) }" id="${ id }"${ isProduct ? ` data-price="${ opt.price }"` : '' }${ opt.checked ? ' checked="checked"' : '' }>
						${ purifyHtml( opt.label ) }
					</label>
					${ isOther ? other : '' }
				</div>`;
		};

		return this.getSingle();
	}

	function checkUniqueOpt( targetInput ) {
		const settingsContainer = targetInput.closest( '.frm-single-settings' );
		const fieldId = settingsContainer.getAttribute( 'data-fid' );
		// The separate values setting is only there when Pro is active.
		const areValuesSeparate = Boolean( settingsContainer.querySelector( `[name="field_options[separate_value_${ fieldId }]"]` )?.checked );

		if ( areValuesSeparate && ! targetInput.name.endsWith( '[value]' ) ) {
			return;
		}

		const container = document.getElementById( `frm_field_${ fieldId }_opts` );
		const conflicts = Array.from( container.querySelectorAll( 'input[type="text"]' ) ).filter(
			input => input.id !== targetInput.id &&
				areValuesSeparate === input.name.endsWith( '[value]' ) &&
				input.value === targetInput.value
		);

		if ( conflicts.length ) {
			/* translators: %s: The detected option value. */
			infoModal( sprintf( __( 'Duplicate option value "%s" detected', 'formidable' ), purifyHtml( targetInput.value ) ) );
		}
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
				frmGetFieldValues( val, fieldID, metaKey, this.getAttribute( 'data-type' ) );
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
					fillDropdownOpts( fillField, {
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

				frmGetFieldValues( val, fieldID, metaKey, thisType, undefined, callback );
			}
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

	return {
		addRadioCheckboxOpt,
		addSlideAnimationCssVars,
		checkCheckboxSelectionsLimit,
		checkRepeatLimit,
		checkUniqueOpt,
		clickDescription,
		clickLabel,
		clickVis,
		fieldsInSection,
		getFieldSelection,
		getFieldValues,
		getImageOptionSize,
		maybeCollapsePage,
		maybeCollapseSection,
		maybeCollapseSettings,
		maybeReplaceSingleProductPreview,
		maybeUpdateFormatInput,
		openAdvanced,
		resetDisplayedOpts,
		resetOptOnChange,
		toggleFormTax,
		toggleRepeatButtons,
		updateRepeatText,
		updateSingleProductLabel,
	};
};
