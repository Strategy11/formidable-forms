module.exports = function createCalculationFields( { ensureFieldSettings } ) {
	const { a, span, tag } = frmDom;

	function isCalcBoxType( box, listClass ) {
		const list = jQuery( box ).find( '.frm_code_list' );
		return 1 === list.length && list.hasClass( listClass );
	}

	function extractExcludedOptions( exclude ) {
		const opts = [];
		if ( ! Array.isArray( exclude ) ) {
			return opts;
		}

		for ( let i = 0; i < exclude.length; i++ ) {
			if ( exclude[ i ].startsWith( '[' ) ) {
				opts.push( exclude[ i ] );
				// remove it
				exclude.splice( i, 1 );
				// https://love2dev.com/blog/javascript-remove-from-array/#remove-from-array-splice-value
				i--;
			}
		}

		return opts;
	}

	function hasExcludedOption( field, excludedOpts ) {
		ensureFieldSettings( field.fieldId );
		let hasOption = false;
		for ( let i = 0; i < excludedOpts.length; i++ ) {
			const inputs = document.getElementsByName( getFieldOptionInputName( excludedOpts[ i ], field.fieldId ) );
			// 2nd condition checks that there's at least one non-empty value
			if ( inputs.length && jQuery( inputs[ 0 ] ).val() ) {
				hasOption = true;
				break;
			}
		}
		return hasOption;
	}

	function getFieldOptionInputName( opt, fieldId ) {
		const at = opt.indexOf( ']' );
		return `field_options${ opt.substring( 0, at ) }_${ fieldId }${ opt.substring( at ) }`;
	}

	/**
	 * Populates the calculation field list.
	 *
	 * @param {HTMLElement} element The calculation box element.
	 * @param {boolean}     force   Whether to force the refresh.
	 */
	function popCalcFields( element, force ) {
		const settingsBox = jQuery( element ).closest( '.frm-single-settings' );
		const calc = settingsBox.find( '.frm-calc-field' );

		if ( ! force && ( ! calc.length || calc.val() === '' || calc.is( ':hidden' ) ) ) {
			return;
		}

		const isSummary = isCalcBoxType( element, 'frm_js_summary_list' );

		const fieldId = settingsBox.find( 'input[name="frm_fields_submitted[]"]' ).val();

		let box;
		if ( force ) {
			box = element;
		} else {
			box = document.getElementById( `frm-calc-box-${ fieldId }` );
		}

		const codeList = box.querySelector( '.frm_code_list' );
		const exclude = getExcludeArray( codeList, isSummary );
		const excludedOpts = extractExcludedOptions( exclude );

		/**
		 * Filters the fields offered in a calculation box.
		 *
		 * Add-ons that own a calculation type can drop fields it can't use. When
		 * nothing is hooked in, every field is offered, matching the behavior
		 * before calculation-type specific filtering existed.
		 *
		 * @param {Array}  fields The candidate fields.
		 * @param {Object} args   Context with the box, code list, and isSummary flag.
		 */
		const fields = wp.hooks.applyFilters( 'frm_calc_field_list', getFieldList(), { box, codeList, isSummary } );
		const list = document.getElementById( `frm-calc-list-${ fieldId }` );
		list.innerHTML = '';

		for ( let i = 0; i < fields.length; i++ ) {
			if ( fields[ i ].fieldId === fieldId ) {
				// A field can't be used in its own calculation, since that would create a loop.
				continue;
			}

			if ( exclude?.includes( fields[ i ].fieldType ) ||
				( excludedOpts.length && hasExcludedOption( fields[ i ], excludedOpts ) ) ) {
				continue;
			}

			addCalcFieldLiToList( list, fieldId, fields[ i ].fieldId, fields[ i ].fieldName, fields[ i ].fieldType );

			if ( ! isSummary ) {
				// The summary field list is a list of fields to exclude, not a list of shortcodes, so field parts don't belong in it.
				addFieldPartShortcodes( fields[ i ], fieldId, list );
			}
		}
	}

	/**
	 * Lets the plugin that owns the calculation type add shortcodes for the individual
	 * parts of a multi-part field, like [25 show=last] for a Name field.
	 *
	 * Nothing in Lite renders a calculation box, so Lite offers the extension point and
	 * leaves the parts themselves to whichever plugin owns the calculation.
	 *
	 * @since 6.35
	 *
	 * @param {Object}      field   Field object containing fieldType, fieldId, and fieldName.
	 * @param {string}      fieldId ID of the field the popup was opened for.
	 * @param {HTMLElement} list    The 'ul' element that contains field shortcodes available for calculation.
	 *
	 * @return {void}
	 */
	function addFieldPartShortcodes( field, fieldId, list ) {
		/**
		 * Allows add-ons to add field part shortcodes to calculation popup.
		 *
		 * @since 6.35
		 *
		 * @param {Object}      hookArgs                      Arguments passed to the hook.
		 * @param {Object}      hookArgs.field                Field object containing fieldType, fieldId, and fieldName.
		 * @param {string}      hookArgs.fieldId              ID of the field triggering the popup.
		 * @param {HTMLElement} hookArgs.list                 The 'ul' element containing field shortcodes.
		 * @param {Function}    hookArgs.addCalcFieldLiToList Helper function: addCalcFieldLiToList(list, fieldId, code, label, fieldType).
		 */
		wp.hooks.doAction( 'frm_add_calc_field_shortcodes', { field, fieldId, list, addCalcFieldLiToList } );
	}

	/**
	 * Adds a row to a calculation box's field shortcode list.
	 *
	 * @since 6.35
	 *
	 * @param {HTMLElement} list      The 'ul' element that contains field shortcodes available for calculation.
	 * @param {string}      fieldId   ID of the field the popup was opened for.
	 * @param {string}      code      The shortcode to insert, without the brackets.
	 * @param {string}      label     The name shown for the row.
	 * @param {string}      fieldType Type of the field the shortcode belongs to.
	 *
	 * @return {void}
	 */
	function addCalcFieldLiToList( list, fieldId, code, label, fieldType ) {
		const anchor = a( {
			className: 'frm_insert_code',
			children: [
				span( label ),
				span( { className: 'frm-text-sm frm-text-grey-500', text: `[${ code }]` } )
			],
			data: { code }
		} );

		list.append(
			tag( 'li', {
				className: `frm-field-list-${ fieldId } frm-field-list-${ fieldType }`,
				child: anchor
			} )
		);
	}

	/**
	 * Gets the exclude array for the calculation box.
	 *
	 * @param {HTMLElement} codeList  The code list element.
	 * @param {boolean}     isSummary Whether the calculation box is for a summary.
	 * @return {Array} The exclude array.
	 */
	function getExcludeArray( codeList, isSummary ) {
		const exclude = JSON.parse( codeList.getAttribute( 'data-exclude' ) );

		if ( isSummary ) {
			// includedExtras are those that are normally excluded from the summary but the form owner can choose to include,
			// when they have been chosen to be included, then they can now be manually excluded in the calc box.
			const includedExtras = getIncludedExtras();
			if ( includedExtras.length ) {
				for ( let i = 0; i < exclude.length; i++ ) {
					if ( includedExtras.includes( exclude[ i ] ) ) {
						// remove it
						exclude.splice( i, 1 );
						// https://love2dev.com/blog/javascript-remove-from-array/#remove-from-array-splice-value
						i--;
					}
				}
			}
		}

		return exclude;
	}

	function getIncludedExtras() {
		const checked = [];
		const checkboxes = document.getElementsByClassName( 'frm_include_extras_field' );

		for ( let i = 0; i < checkboxes.length; i++ ) {
			if ( checkboxes[ i ].checked ) {
				checked.push( checkboxes[ i ].value );
			}
		}

		return checked;
	}

	function getFieldList( fieldType ) {
		let i;
		const fields = [];
		const allFields = document.querySelectorAll( 'li.frm_field_box' );
		const checkType = fieldType !== undefined;

		for ( i = 0; i < allFields.length; i++ ) {
			// data-ftype is better (than data-type) because of fields loaded by AJAX - which might not be ready yet
			if ( checkType && allFields[ i ].getAttribute( 'data-ftype' ) !== fieldType ) {
				continue;
			}

			const fieldId = allFields[ i ].getAttribute( 'data-fid' );
			if ( fieldId ) {
				fields.push( {
					fieldId,
					fieldName: getPossibleValue( `frm_name_${ fieldId }` ),
					fieldType: getPossibleValue( `field_options_type_${ fieldId }` ),
					fieldKey: getPossibleValue( `field_options_field_key_${ fieldId }` )
				} );
			}
		}

		return wp.hooks.applyFilters( 'frm_admin_get_field_list', fields, fieldType, allFields );
	}

	function popProductFields( field ) {
		let i;
		let checked;
		let id;
		const options = [];
		let current = getCurrentProductFields( field );
		const fName = field.getAttribute( 'data-frmfname' );
		const products = getFieldList( 'product' );
		const quantities = getFieldList( 'quantity' );
		const isSelect = field.tagName === 'SELECT'; // for reverse compatibility.
		// whether we have just 1 product and 1 quantity field & should therefore attach the latter to the former
		const auto = 1 === quantities.length && 1 === products.length;

		if ( isSelect ) {
			// This fallback can be removed after 4.05.
			current = field.getAttribute( 'data-frmcurrent' );
		}

		for ( i = 0; i < products.length; i++ ) {
			// let's be double sure it's string, else indexOf will fail
			id = products[ i ].fieldId.toString();
			checked = auto || current.includes( id );
			if ( isSelect ) {
				// This fallback can be removed after 4.05.
				checked = checked ? ' selected' : '';
				options.push( `<option value="${ id }"${ checked }>${ products[ i ].fieldName }</option>` );
			} else {
				checked = checked ? ' checked' : '';
				options.push( '<label class="frm6">' );
				options.push( `<input type="checkbox" name="${ fName }" value="${ id }"${ checked }> ${ products[ i ].fieldName }` );
				options.push( '</label>' );
			}
		}

		field.innerHTML = options.join( '' );
	}

	function getCurrentProductFields( prodFieldOpt ) {
		const products = prodFieldOpt.querySelectorAll( '[type="checkbox"]:checked' );
		const idsArray = [];

		for ( let i = 0; i < products.length; i++ ) {
			idsArray.push( products[ i ].value );
		}

		return idsArray;
	}

	function popAllProductFields() {
		const opts = document.querySelectorAll( '.frmjs_prod_field_opt' );
		for ( let i = 0; i < opts.length; i++ ) {
			popProductFields( opts[ i ] );
		}
	}

	/**
	 * If the element doesn't exist, use a blank value.
	 * @param {string} id The element ID to look up.
	 */
	function getPossibleValue( id ) {
		const field = document.getElementById( id );
		if ( field ) {
			return field.value;
		}
		return '';
	}

	return {
		getFieldList,
		popAllProductFields,
		popCalcFields,
		popProductFields,
	};
};
