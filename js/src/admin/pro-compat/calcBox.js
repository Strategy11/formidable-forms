/* global frmAdminBuild, frm_admin_js, frmDom */

/**
 * Lite's original calculation and summary box code, for Pro versions that do not register their own.
 * The code was moved here from admin.js.
 *
 * @since x.x
 */

const frmAdminJs = frm_admin_js;
const { a, span, tag } = frmDom;

function checkCalculationCreatedByUser() {
	const calculation = this.value;
	let warningMessage = checkMatchingParens( calculation );
	warningMessage += checkShortcodes( calculation, this );

	if ( warningMessage !== '' ) {
		frmAdminBuild.infoModal( `${ calculation }\n\n${ warningMessage }` );
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
	frmAdminBuild.ensureFieldSettings( field.fieldId );
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
	const fields = wp.hooks.applyFilters( 'frm_calc_field_list', frmAdminBuild.getFieldList(), { box, codeList, isSummary } );
	const list = document.getElementById( `frm-calc-list-${ fieldId }` );
	list.innerHTML = '';

	for ( let i = 0; i < fields.length; i++ ) {
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

function rePopCalcFieldsForSummary() {
	popCalcFields( jQuery( '.frm-inline-modal.postbox:has(.frm_js_summary_list)' )[ 0 ], true );
}

/**
 * @since x.x
 * @return {void}
 */
export function registerCalcBox() {
	const registered = frmAdminBuild.registerBuilderFeature( 'calc_box', {
		popCalcFields,
		rePopCalcFieldsForSummary,
		checkCalculationCreatedByUser,
	} );
	if ( ! registered ) {
		return;
	}

	jQuery( document ).on( 'blur', '#new_fields input[id^="frm_calc"]', checkCalculationCreatedByUser );
	jQuery( document ).on( 'change', '#new_fields .frm_include_extras_field', rePopCalcFieldsForSummary );
}
