module.exports = function createFieldChoices( { builderPage, ensureFieldSettings, frmAdminJs, getChoiceNewValueAndLabel, isProductField, builderScreen, prepareLoadedFieldMarkup, renderDropdownPreview } ) {
	const { div, img, tag } = frmDom;

	/**
	 * Returns an object that has a value and label for new conditional logic option, for a given option value.
	 *
	 * @param {number} fieldId
	 * @param {string} expectedOption The expected option value.
	 * @return {Object} Object with value and label for the new option.
	 */
	function getNewConditionalLogicOption( fieldId, expectedOption ) {
		const optionsContainer = document.getElementById( `frm_field_${ fieldId }_opts` );

		const expectedOptionInput = optionsContainer.querySelector( `input[value="${ expectedOption }"]` );

		if ( expectedOptionInput ) {
			return getChoiceNewValueAndLabel( expectedOptionInput );
		}

		return { newValue: expectedOption, newLabel: expectedOption };
	}

	function adjustConditionalLogicOptionOrders( fieldId, type ) {
		builderScreen?.materializeAllFieldSettings( prepareLoadedFieldMarkup );
		const rows = builderPage.querySelectorAll( '.frm_logic_row' );
		const rowLength = rows.length;

		const fieldOptions = wp.hooks.applyFilters( 'frm_conditional_logic_field_options', getFieldOptions( fieldId ), { type, fieldId } );
		const optionLength = fieldOptions.length;

		for ( let rowIndex = 0; rowIndex < rowLength; rowIndex++ ) {
			const row = rows[ rowIndex ];
			const opts = row.querySelector( '.frm_logic_field_opts' );

			if ( opts.value != fieldId ) {
				continue;
			}

			const logicId = row.id.split( '_' )[ 2 ];
			const valueSelect = row.querySelector( `select[name="field_options[hide_opt_${ logicId }][]"]` );

			for ( let optionIndex = optionLength - 1; optionIndex >= 0; optionIndex-- ) {
				const expectedOption = fieldOptions[ optionIndex ];
				let expectedOptionValue = document.getElementById( `frm_field_${ fieldId }_opts` ).querySelector( '.frm_option_key input[type="text"]' )?.value;
				if ( ! expectedOptionValue ) {
					expectedOptionValue = expectedOption;
				}

				const optionMatch = valueSelect.querySelector( `option[value="${ expectedOptionValue }"]` );

				const { newValue, newLabel } = getNewConditionalLogicOption( fieldId, expectedOption );

				const fieldChoices = document.querySelectorAll( `#frm_field_${ fieldId }_opts input[data-value-on-focus]` );
				const expectedChoiceEl = Array.from( fieldChoices ).find( element => element.value === expectedOption );
				if ( expectedChoiceEl ) {
					const oldValue = expectedChoiceEl.dataset.valueOnFocus;
					const hasMatch = oldValue && valueSelect.querySelector( `option[value="${ oldValue }"]` );
					if ( hasMatch ) {
						continue;
					}
				}
				prependValueSelectWithOptionMatch( valueSelect, optionMatch, newValue, newLabel );
			}

			const optionMatch = valueSelect.querySelector( 'option[value=""]' );
			if ( optionMatch !== null ) {
				valueSelect.prepend( optionMatch );
			}
		}
	}

	function prependValueSelectWithOptionMatch( valueSelect, optionMatch, newValue, newLabel ) {
		if ( optionMatch === null && ! valueSelect.querySelector( `option[value="${ newValue }"]` ) ) {
			optionMatch = frmDom.tag( 'option', { text: newLabel } );
			optionMatch.value = newValue;
		}

		valueSelect.prepend( optionMatch );
	}

	function getFieldOptions( fieldId ) {
		ensureFieldSettings( fieldId );
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

	function fillDropdownOpts( field, atts ) {
		if ( ! field ) {
			return;
		}
		const { sourceID } = atts;
		ensureFieldSettings( sourceID );
		const { placeholder } = atts;
		const isProduct = isProductField( sourceID );
		const showOther = atts.other;

		const opts = getMultipleOpts( sourceID, field.id.includes( 'frm_field_logic_opt' ) );
		if ( field.closest( '#frm-show-fields .frm_form_fields' ) && ! field.closest( '.frm-single-settings' ) ) {
			renderDropdownPreview( field, opts, placeholder );
			return;
		}
		removeDropdownOpts( field );
		let hasPlaceholder = placeholder !== undefined;

		for ( let i = 0; i < opts.length; i++ ) {
			let { label } = opts[ i ];
			const isOther = opts[ i ].key.includes( 'other' );

			if ( hasPlaceholder && label !== '' ) {
				addBlankSelectOption( field, placeholder );
			} else if ( hasPlaceholder ) {
				label = placeholder;
			}
			hasPlaceholder = false;

			if ( ! isOther || showOther ) {
				const opt = document.createElement( 'option' );
				opt.value = opts[ i ].saved;
				opt.innerHTML = purifyHtml( label );

				if ( isProduct ) {
					opt.setAttribute( 'data-price', opts[ i ].price );
				}

				field.append( opt );
			}
		}
	}

	function addBlankSelectOption( field, placeholder ) {
		const opt = document.createElement( 'option' );
		const { firstChild } = field;

		opt.value = '';
		opt.innerHTML = placeholder;
		if ( firstChild !== null ) {
			firstChild.before( opt );
			field.selectedIndex = 0;
		} else {
			field.append( opt );
		}
	}

	/**
	 * Get multiple options for a field.
	 *
	 * @param {string}  fieldId          The field id.
	 * @param {boolean} showValueAsLabel Whether to show the value as label for empty labels.
	 */
	function getMultipleOpts( fieldId, showValueAsLabel = false ) {
		ensureFieldSettings( fieldId );
		let i;
		let saved;
		let labelName;
		let label;
		let key;
		let optObj;
		let checked = false;
		const opts = [];
		let imageUrl = '';

		const optVals = jQuery( `input[name^="field_options[options_${ fieldId }]"]` ).filter( '[name$="[label]"], [name*="[other_"]' );
		const isProduct = isProductField( fieldId );
		const showLabelWithImage = showingLabelWithImage( fieldId );
		const hasImageOptions = imagesAsOptions( fieldId );
		const separateValues = usingSeparateValues( fieldId );

		for ( i = 0; i < optVals.length; i++ ) {
			if ( optVals[ i ].name.indexOf( '[000]' ) > 0 ) {
				continue;
			}

			saved = optVals[ i ].value;
			label = saved;
			key = optVals[ i ].name.replace( `field_options[options_${ fieldId }][`, '' ).replace( '[label]', '' ).replace( ']', '' );

			if ( separateValues ) {
				labelName = optVals[ i ].name.replace( '[label]', '[value]' );
				saved = jQuery( `input[name="${ labelName }"]` ).val();

				if ( showValueAsLabel && '' === label ) {
					label = '' !== saved ? saved : frm_admin_js.no_label;
				}
			}

			if ( hasImageOptions ) {
				imageUrl = getImageUrlFromInput( optVals[ i ] );
				label = getImageLabel( label, showLabelWithImage, imageUrl );
			}

			/**
			 * @since 5.0.04
			 */
			label = frmAdminBuild.hooks.applyFilters( 'frm_choice_field_label', label, fieldId, optVals[ i ], hasImageOptions );

			checked = getChecked( optVals[ i ].id );

			optObj = {
				saved,
				label,
				checked,
				key
			};

			if ( isProduct ) {
				labelName = optVals[ i ].name.replace( '[label]', '[price]' );
				optObj.price = jQuery( `input[name="${ labelName }"]` ).val();
			}

			opts.push( optObj );
		}

		return opts;
	}

	function getImageUrlFromInput( optVal ) {
		const wrapper = jQuery( optVal ).siblings( '.frm_image_preview_wrapper' );

		if ( ! wrapper.length ) {
			return '';
		}

		const img = wrapper.find( 'img' );
		if ( ! img.length ) {
			return '';
		}

		return img.attr( 'src' );
	}

	function purifyHtml( html ) {
		if ( html instanceof Element || html instanceof Document ) {
			html = html.outerHTML;
		}

		const clean = jQuery.parseHTML( html ).reduce(
			( total, currentNode ) => {
				const cleanNode = frmDom.cleanNode( currentNode );

				if ( '#text' === cleanNode.nodeName ) {
					return total + cleanNode.textContent;
				}

				return total + cleanNode.outerHTML;
			},
			''
		);

		if ( clean !== html ) {
			// Clean it until nothing changes, in case the stripped result is now unsafe.
			return purifyHtml( clean );
		}

		return clean;
	}

	function getImageLabel( label, showLabelWithImage, imageUrl ) {
		let originalLabel = label;

		originalLabel = purifyHtml( originalLabel );

		let labelImage;
		if ( imageUrl ) {
			labelImage = img( { src: imageUrl, alt: originalLabel } );
		} else {
			labelImage = div( { className: 'frm_empty_url' } );
			labelImage.innerHTML = frmAdminJs.image_placeholder_icon;
		}

		const imageLabelClass = showLabelWithImage ? ' frm_label_with_image' : '';

		const children = [ labelImage ];

		if ( showLabelWithImage ) {
			const imageLabel = tag( 'span', { className: 'frm_text_label_for_image_inner' } );
			imageLabel.innerHTML = originalLabel;
			children.push( tag( 'span', { className: 'frm_text_label_for_image', child: imageLabel } ) );
		}

		const labelNode = tag(
			'span',
			{
				className: `frm_image_option_container${ imageLabelClass }`,
				children
			}
		);

		return labelNode;
	}

	function getChecked( id ) {
		const field = jQuery( `#${ id }` );

		if ( field.length === 0 ) {
			return false;
		}

		const checkbox = field.siblings( 'input[type=checkbox]' );

		return checkbox.length && checkbox.prop( 'checked' );
	}

	function removeDropdownOpts( field ) {
		let i;
		if ( field.options === undefined ) {
			return;
		}

		for ( i = field.options.length - 1; i >= 0; i-- ) {
			field.remove( i );
		}
	}

	/**
	 * Is the box checked to use separate values?
	 * @param {number} fieldId The field ID.
	 */
	function usingSeparateValues( fieldId ) {
		return isChecked( `separate_value_${ fieldId }` );
	}

	/**
	 * Is the box checked to use images as options?
	 * @param {number} fieldId The field ID.
	 */
	function imagesAsOptions( fieldId ) {
		let checked = false;
		const field = document.getElementsByName( `field_options[image_options_${ fieldId }]` );

		for ( let i = 0; i < field.length; i++ ) {
			if ( field[ i ].checked ) {
				checked = '0' !== field[ i ].value;
			}
		}

		/**
		 * @since 5.0.04
		 */
		return frmAdminBuild.hooks.applyFilters( 'frm_choice_field_images_as_options', checked, fieldId );
	}

	function showingLabelWithImage( fieldId ) {
		const isShowing = ! isChecked( `hide_image_text_${ fieldId }` );

		/**
		 * @since 5.0.04
		 */
		return frmAdminBuild.hooks.applyFilters( 'frm_choice_field_showing_label_with_image', isShowing, fieldId );
	}

	function isChecked( id ) {
		const field = document.getElementById( id );
		if ( ! field ) {
			return false;
		}
		return field.checked;
	}

	return {
		adjustConditionalLogicOptionOrders,
		fillDropdownOpts,
		getImageLabel,
		getMultipleOpts,
		imagesAsOptions,
		purifyHtml,
		showingLabelWithImage,
		usingSeparateValues,
	};
};
