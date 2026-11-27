require( './gated-content-action' );

wp.hooks.addAction( 'frmAdminFormSettingsInit', 'formidable', function( {
	addFormNameModalEvents,
	addImageToOption,
	adjustVisibilityValuesForEveryoneValues,
	adminState,
	checkActiveAction,
	clickTab,
	div,
	fieldUpdated,
	frmAdminJs,
	getAppendedLogicRow,
	getNewRowId,
	infoModal,
	initAutocomplete,
	initiateMultiselect,
	isAtLimitForActionType,
	maybeHideShortcodes,
	preFormSave,
	removeImageFromOption,
	showInputIcon,
	showNameYourFormModal,
	triggerChange,
	triggerLogicRowEvent,
	triggerSubmit,
} ) {
	let lastNewActionIdReturned = 0;

	/**
	 * Toggle the visibility of the form actions search no results message.
	 *
	 * @since 6.31
	 */
	function toggleFormActionsNoResultsVisibility() {
		const hasVisibleActions = document.querySelector( '#frm-actions-filter-content .frm-action:not(.frm_hidden)' );
		document.getElementById( 'frm-actions-no-results' )?.classList.toggle( 'frm_hidden', hasVisibleActions );
	}

	function showSuccessOpt() {
		/*jshint validthis:true */
		let c = 'success';
		if ( this.name === 'options[edit_action]' ) {
			c = 'edit';
		}
		const v = jQuery( this ).val();
		jQuery( `.${ c }_action_box` ).hide();
		if ( v === 'redirect' ) {
			jQuery( `.${ c }_action_redirect_box.${ c }_action_box` ).fadeIn( 'slow' );
		} else if ( v === 'page' ) {
			jQuery( `.${ c }_action_page_box.${ c }_action_box` ).fadeIn( 'slow' );
		} else {
			jQuery( `.${ c }_action_message_box.${ c }_action_box` ).fadeIn( 'slow' );
		}
	}

	function copyFormAction( event ) {
		event.stopPropagation();
		if ( waitForActionToLoadBeforeCopy( event.target ) ) {
			return;
		}

		const targetSettings = event.target.closest( '.frm_form_action_settings' );
		const wysiwygs = targetSettings.querySelectorAll( '.wp-editor-area' );
		if ( wysiwygs.length ) {
			// Temporary remove TinyMCE before cloning to avoid TinyMCE conflicts.
			wysiwygs.forEach( wysiwyg => {
				tinymce.EditorManager.execCommand( 'mceRemoveEditor', true, wysiwyg.id );
			} );
		}

		const $action = jQuery( targetSettings ).clone();
		const currentID = $action.attr( 'id' ).replace( 'frm_form_action_', '' );
		const newID = newActionId( currentID );

		const actionType = targetSettings.querySelector( '.frm_action_name' )?.value;
		const sourceTitle = targetSettings.querySelector( '.widget-title h4 span:not(.frm-border-icon)' )?.textContent.trim() ?? '';
		const uniqueTitle = getUniqueActionTitle( sourceTitle.replace( / \(\d+\)$/, '' ), getExistingActionTitles( actionType ) );
		$action[ 0 ].querySelector( '.widget-title h4 span:not(.frm-border-icon)' ).textContent = uniqueTitle;
		$action[ 0 ].querySelector( `input[name$="[${ currentID }][post_title]"]` ).value = uniqueTitle;

		$action.find( '.frm_action_id, .frm-btn-group' ).remove();
		$action.find( `input[name$="[${ currentID }][ID]"]` ).val( '' );
		$action.find( '.widget-inside' ).hide();

		// the .html() gets original values, so they need to be set
		$action.find( 'input[type=text], textarea, input[type=number]' ).prop( 'defaultValue', function() {
			return this.value;
		} );

		$action.find( 'input[type=checkbox], input[type=radio]' ).prop( 'defaultChecked', function() {
			return this.checked;
		} );

		const rename = new RegExp( `\\[${ currentID }\\]`, 'g' );
		const reid = new RegExp( `_${ currentID }"`, 'g' );
		const reidMid = new RegExp( `_${ currentID } `, 'g' );
		const reclass = new RegExp( `-${ currentID }"`, 'g' );
		const revalue = new RegExp( `"${ currentID }"`, 'g' ); // if a field id matches, this could cause trouble

		let html = $action.html().replace( rename, `[${ newID }]` ).replace( reid, `_${ newID }"` );
		html = html.replace( reidMid, `_${ newID } ` );
		html = html.replace( reclass, `-${ newID }"` ).replace( revalue, `"${ newID }"` );

		const newAction = div( {
			id: `frm_form_action_${ newID }`,
			className: $action.get( 0 ).className
		} );
		newAction.setAttribute( 'data-actionkey', newID );
		newAction.innerHTML = html;
		newAction.querySelectorAll( '.wp-editor-wrap, .wp-editor-wrap *' ).forEach(
			element => {
				if ( 'string' === typeof element.className ) {
					element.className = element.className.replace( currentID, newID );
				}
				element.id = element.id.replace( currentID, newID );
			}
		);
		newAction.classList.remove( 'open' );
		document.getElementById( 'frm_notification_settings' ).append( newAction );

		if ( wysiwygs.length ) {
			// Re-initialize the original wysiwyg which was removed before cloning.
			wysiwygs.forEach( wysiwyg => {
				frmDom.wysiwyg.init( wysiwyg );
			} );

			newAction.querySelectorAll( '.wp-editor-area' ).forEach( wysiwyg => {
				frmDom.wysiwyg.init( wysiwyg );
			} );
		}

		if ( newAction.classList.contains( 'frm_single_on_submit_settings' ) ) {
			const autocompleteInput = newAction.querySelector( 'input.frm-page-search' );
			if ( autocompleteInput ) {
				initAutocomplete( newAction );
			}
		}

		initiateMultiselect();

		const hookName = 'frm_after_duplicate_action';
		wp.hooks.doAction( hookName, newAction );
	}

	function waitForActionToLoadBeforeCopy( element ) {
		const $trigger = jQuery( element );
		const $original = $trigger.closest( '.frm_form_action_settings' );
		const $inside = $original.find( '.widget-inside' );

		if ( $inside.find( 'p, div, table' ).length ) {
			return false;
		}

		const $top = $original.find( '.widget-top' );
		$top.one( 'frm-action-loaded', function() {
			$trigger.trigger( 'click' );
			$original.removeClass( 'open' );
			$inside.hide();
		} );
		$top.trigger( 'click' );
		return true;
	}

	function newActionId( currentID ) {
		let newID = parseInt( currentID, 10 ) + 11;
		const exists = document.getElementById( `frm_form_action_${ newID }` );
		if ( exists ) {
			newID++;
			newID = newActionId( newID );
		}
		return newID;
	}

	/**
	 * Gets the visible titles for a given action type from the DOM.
	 *
	 * @since 6.31
	 *
	 * @param {string} actionType The action type slug (e.g. "email").
	 * @return {string[]} Array of trimmed title strings.
	 */
	function getExistingActionTitles( actionType ) {
		return Array.from(
			document.querySelectorAll( `.frm_single_${ actionType }_settings .widget-title h4 span:not(.frm-border-icon)` ),
			el => el.textContent.trim()
		);
	}

	/**
	 * Returns the first available title not already taken, appending " (2)", " (3)", etc. if needed.
	 *
	 * @since 6.31
	 *
	 * @param {string}   baseTitle      The base title without any numeric suffix.
	 * @param {string[]} existingTitles Titles currently in use.
	 * @return {string} The first title not present in existingTitles.
	 */
	function getUniqueActionTitle( baseTitle, existingTitles ) {
		const taken = new Set( existingTitles );
		let title = baseTitle;
		let n = 2;
		while ( taken.has( title ) ) {
			title = `${ baseTitle } (${ n })`;
			n++;
		}

		return title;
	}

	function addFormAction() {
		/*jshint validthis:true */
		const type = jQuery( this ).data( 'actiontype' );

		if ( isAtLimitForActionType( type ) ) {
			return;
		}

		const actionId = getNewActionId();
		const formId = adminState.thisFormId;
		const existingTitles = getExistingActionTitles( type );

		const placeholderSetting = document.createElement( 'div' );
		placeholderSetting.classList.add( `frm_single_${ type }_settings` );

		const actionsList = document.getElementById( 'frm_notification_settings' );
		actionsList.append( placeholderSetting );

		jQuery.ajax( {
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'frm_add_form_action',
				type,
				list_id: actionId,
				form_id: formId,
				nonce: frmGlobal.nonce,
				existing_titles: existingTitles
			},
			success: handleAddFormActionSuccess
		} );

		function handleAddFormActionSuccess( html ) {
			fieldUpdated();
			placeholderSetting.remove();

			document.querySelector( '.frm-no-actions-message' )?.classList.add( 'frm_hidden' );

			closeOpenActions();

			const newActionContainer = div();
			newActionContainer.innerHTML = html;

			const widgetTop = newActionContainer.querySelector( '.widget-top' );
			Array.from( newActionContainer.children ).forEach( child => actionsList.append( child ) );

			jQuery( '.frm_form_action_settings' ).fadeIn( 'slow' );

			const newAction = document.getElementById( `frm_form_action_${ actionId }` );

			newAction.classList.add( 'open' );
			document.getElementById( 'post-body-content' ).scroll( {
				top: newAction.offsetTop + 10,
				left: 0,
				behavior: 'smooth'
			} );

			// Check if icon should be active
			checkActiveAction( type );
			maybeDisableOtherPaymentActions( type );

			showInputIcon( `#frm_form_action_${ actionId }` );

			initiateMultiselect();
			initAutocomplete( newAction );

			if ( widgetTop ) {
				jQuery( widgetTop ).trigger( 'frm-action-loaded' );
			}

			/**
			 * Fires after added a new form action.
			 *
			 * @since 5.5.4
			 *
			 * @param {HTMLElement} formAction Form action element.
			 */
			frmAdminBuild.hooks.doAction( 'frm_added_form_action', newAction );
		}
	}

	/**
	 * @since 6.31
	 *
	 * @param {string} excludedType
	 *
	 * @return {void}
	 */
	function maybeDisableOtherPaymentActions( excludedType ) {
		const paymentActions = [ 'stripe', 'square', 'paypal' ];

		if ( ! paymentActions.includes( excludedType ) ) {
			// Not a payment action so exit early.
			return;
		}

		paymentActions.forEach(
			action => {
				if ( action !== excludedType ) {
					checkActiveAction( action );
				}
			}
		);
	}

	function closeOpenActions() {
		document.querySelectorAll( '.frm_form_action_settings.open' ).forEach(
			setting => setting.classList.remove( 'open' )
		);
	}

	function getNewActionId() {
		const actionSettings = document.querySelectorAll( '.frm_form_action_settings' );
		let len = getNewRowId( actionSettings, 'frm_form_action_' );
		if ( document.getElementById( `frm_form_action_${ len }` ) !== undefined ) {
			len = len + 100;
		}
		if ( lastNewActionIdReturned >= len ) {
			len = lastNewActionIdReturned + 1;
		}
		lastNewActionIdReturned = len;
		return len;
	}

	function showEmailRow() {
		/*jshint validthis:true */
		const actionKey = jQuery( this ).closest( '.frm_form_action_settings' ).data( 'actionkey' );
		const rowType = this.getAttribute( 'data-emailrow' );

		jQuery( `#frm_form_action_${ actionKey } .frm_${ rowType }_row` ).fadeIn( 'slow' );
		jQuery( this ).fadeOut( 'slow' );
	}

	function hideEmailRow() {
		/*jshint validthis:true */
		const actionBox = jQuery( this ).closest( '.frm_form_action_settings' );
		const rowType = this.getAttribute( 'data-emailrow' );
		const emailRowSelector = `.frm_${ rowType }_row`;
		const emailButtonSelector = `.frm_${ rowType }_button`;

		jQuery( actionBox ).find( emailButtonSelector ).fadeIn( 'slow' );
		jQuery( actionBox ).find( emailRowSelector ).fadeOut( 'slow', function() {
			jQuery( actionBox ).find( `${ emailRowSelector } input` ).val( '' );
		} );
	}

	function showEmailWarning() {
		/*jshint validthis:true */
		const actionBox = jQuery( this ).closest( '.frm_form_action_settings' );
		const emailRowSelector = '.frm_from_to_match_row';
		const fromVal = actionBox.find( 'input[name$="[post_content][from]"]' ).val();
		const toVal = actionBox.find( 'input[name$="[post_content][email_to]"]' ).val();

		if ( fromVal === toVal ) {
			jQuery( actionBox ).find( emailRowSelector ).fadeIn( 'slow' );
		} else {
			jQuery( actionBox ).find( emailRowSelector ).fadeOut( 'slow' );
		}
	}

	function actionLimitMessage() {
		let message = frmAdminJs.only_one_action;
		let { limit } = this.dataset;
		const type = this.dataset.actiontype;

		// Use payment-specific message for payment actions
		if ( type && [ 'paypal', 'stripe', 'square', 'payment' ].includes( type ) ) {
			if ( 'stripe' === type ) {
				message = frmAdminJs.only_one_stripe_action;
			} else {
				message = frmAdminJs.only_one_payment_action;
			}
		}

		if ( limit !== undefined ) {
			limit = parseInt( limit );
			if ( limit > 1 ) {
				message = message.replace( 1, limit ).trim();
			} else {
				message += ` ${ frmAdminJs.edit_action_text }`;
			}
		}

		infoModal( message );
	}

	/**
	 * Insert a new conditional logic row into a form action via AJAX.
	 *
	 * @param {number}      id             The ID of the form action.
	 * @param {HTMLElement} contextElement The context element that triggered the insertion.
	 */
	function insertFormLogicRow( id, contextElement ) {
		const type = jQuery( contextElement ).closest( '.frm_form_action_settings' ).find( '.frm_action_name' ).val();
		const formId = document.getElementById( 'form_id' ).value;
		const logicRowsContainer = document.getElementById( `frm_logic_row_${ id }` );
		const logicRows = logicRowsContainer.querySelectorAll( '.frm_logic_row' );
		const newRowID = getNewRowId( logicRows, `frm_logic_${ id }_` );
		const placeholder = div( {
			id: `frm_logic_${ id }_${ newRowID }`,
			className: 'frm_logic_row frm_hidden'
		} );

		// Backwards compat: older Pro uses `#logic_link_{id}` + hidden `.frm_logic_rows`.
		const oldLink = document.getElementById( `logic_link_${ id }` );
		const oldRowsContainer = oldLink ? logicRowsContainer.closest( '.frm_logic_rows' ) : null;

		logicRowsContainer.append( placeholder );
		jQuery.ajax( {
			type: 'POST', url: ajaxurl,
			data: {
				action: 'frm_add_form_logic_row',
				email_id: id,
				form_id: formId,
				meta_name: newRowID,
				type,
				nonce: frmGlobal.nonce
			},
			success( html ) {
				placeholder.insertAdjacentHTML( 'beforebegin', html );
				placeholder.remove();

				const newRow = getAppendedLogicRow( logicRowsContainer );
				const ruleTextEl = newRow ? newRow.querySelector( '.frm-logic-rule-text' ) : null;
				if ( ruleTextEl ) {
					ruleTextEl.textContent = logicRowsContainer.dataset.ruleText || '';
				}

				if ( oldLink ) {
					oldLink.classList.add( 'frm_hidden' );
				}
				if ( oldRowsContainer ) {
					oldRowsContainer.classList.remove( 'frm_hidden' );
					oldRowsContainer.style.display = '';
				}

				if ( newRow ) {
					triggerLogicRowEvent( 'frm_logic_row_added', newRow, logicRowsContainer, contextElement );
				}
			}
		} );
	}

	/**
	 * Handle click on the "+" button to add another logic row to a form action.
	 *
	 * @since 6.31
	 *
	 * @return {boolean} Returns false to prevent default behavior.
	 */
	function addFormLogicRow() {
		/*jshint validthis:true */
		insertFormLogicRow( jQuery( this ).data( 'emailkey' ), this );
		return false;
	}

	/**
	 * Create the first logic row when the conditional logic toggle is turned on.
	 *
	 * @since 6.31
	 *
	 * @return {void}
	 */
	function addInitialLogicRowOnEnable() {
		/*jshint validthis:true */
		if ( ! this.checked ) {
			return;
		}

		const id = this.getAttribute( 'data-emailkey' );
		if ( ! id ) {
			return;
		}

		// Only react to the conditional logic toggle.
		if ( ! this.dataset.toggleclass?.startsWith( 'frm_logic_rows_' ) ) {
			return;
		}

		const logicRowsContainer = document.getElementById( `frm_logic_row_${ id }` );
		if ( logicRowsContainer && ! logicRowsContainer.querySelector( '.frm_logic_row' ) ) {
			insertFormLogicRow( id, this );
		}
	}

	function checkDupPost() {
		/*jshint validthis:true */
		const postField = jQuery( 'select.frm_single_post_field' );
		postField.css( 'border-color', '' );
		const currentField = this;
		const selectedValue = jQuery( currentField ).val();
		if ( selectedValue === '' || selectedValue === 'checkbox' ) {
			return false;
		}
		postField.each( function() {
			if ( jQuery( this ).val() === selectedValue && this.name !== currentField.name ) {
				this.style.borderColor = 'red';
				jQuery( currentField ).val( '' );
				infoModal( frmAdminJs.field_already_used );
				return false;
			}
		} );
	}

	function togglePostContent() {
		/*jshint validthis:true */
		const selectedValue = jQuery( this ).val();
		if ( '' === selectedValue ) {
			jQuery( '.frm_post_content_opt, select.frm_dyncontent_opt' ).hide().val( '' );
			jQuery( '.frm_dyncontent_opt' ).hide();
		} else if ( 'post_content' === selectedValue ) {
			jQuery( '.frm_post_content_opt' ).show();
			jQuery( '.frm_dyncontent_opt' ).hide();
			jQuery( 'select.frm_dyncontent_opt' ).val( '' );
		} else {
			jQuery( '.frm_post_content_opt' ).hide().val( '' );
			jQuery( 'select.frm_dyncontent_opt, .frm_form_field.frm_dyncontent_opt' ).show();
		}
	}

	function fillDyncontent() {
		/*jshint validthis:true */
		const selectedValue = jQuery( this ).val();
		// formidable-views renamed this id from frm_dyncontent to
		// frm_post_action_custom_content in 2021 (formidable-views@70cde392).
		// Older Views installs still render the old id, so check both.
		const $dyn = jQuery(
			document.getElementById( 'frm_post_action_custom_content' ) ||
			document.getElementById( 'frm_dyncontent' )
		);
		if ( '' === selectedValue || 'new' === selectedValue ) {
			$dyn.val( '' );
			jQuery( '.frm_dyncontent_opt' ).show();
		} else {
			jQuery.ajax( {
				type: 'POST', url: ajaxurl,
				data: { action: 'frm_display_get_content', id: selectedValue, nonce: frmGlobal.nonce },
				success( val ) {
					$dyn.val( val );
					jQuery( '.frm_dyncontent_opt' ).show();
				}
			} );
		}
	}

	function switchPostType() {
		/*jshint validthis:true */
		// update all rows of categories/taxonomies
		let curSelect;
		let newSelect;
		const catRows = document.getElementById( 'frm_posttax_rows' ).childNodes;
		const postParentField = document.querySelector( '.frm_post_parent_field' );
		const postMenuOrderField = document.querySelector( '.frm_post_menu_order_field' );
		const postType = this.value;

		// Get new category/taxonomy options
		jQuery.ajax( {
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'frm_replace_posttax_options',
				post_type: postType,
				nonce: frmGlobal.nonce
			},
			success( html ) {
				// Loop through each category row, and replace the first dropdown
				for ( let i = 0; i < catRows.length; i++ ) {
					// Check if current element is a div
					if ( catRows[ i ].tagName !== 'DIV' ) {
						continue;
					}

					// Get current category select
					curSelect = catRows[ i ].getElementsByTagName( 'select' )[ 0 ];

					// Set up new select
					newSelect = document.createElement( 'select' );
					newSelect.innerHTML = html;
					newSelect.className = curSelect.className;
					newSelect.name = curSelect.name;

					// Replace the old select with the new select
					catRows[ i ].replaceChild( newSelect, curSelect );
				}
			}
		} );

		// Get new post parent option.
		if ( postParentField ) {
			getActionOption(
				postParentField,
				postType,
				'frm_get_post_parent_option',
				function( response, optName ) {
					// The replaced string is declared in FrmProFormActionController::ajax_get_post_menu_order_option() in the pro version.
					postParentField.querySelector( '.frm_post_parent_opt_wrapper' ).innerHTML = response.replaceAll( 'REPLACETHISNAME', optName );
					initAutocomplete( postParentField );
				}
			);
		}

		if ( postMenuOrderField ) {
			getActionOption( postMenuOrderField, postType, 'frm_should_use_post_menu_order_option' );
		}
	}

	function getActionOption( field, postType, action, successHandler ) {
		const opt = field.querySelector( '.frm_autocomplete_value_input' ) || field.querySelector( 'select' );
		const optName = opt.getAttribute( 'name' );

		jQuery.ajax( {
			url: ajaxurl,
			method: 'POST',
			data: {
				action,
				post_type: postType,
				_wpnonce: frmGlobal.nonce
			},
			success: response => {
				if ( 'string' !== typeof response ) {
					console.error( response );
					return;
				}

				if ( '0' === response ) {
					// This post type does not support this field.
					field.classList.add( 'frm_hidden' );
					field.value = '';
					return;
				}

				field.classList.remove( 'frm_hidden' );

				if ( 'function' === typeof successHandler ) {
					successHandler( response, optName );
				}
			},
			error: response => console.error( response )
		} );
	}

	function addPosttaxRow() {
		/*jshint validthis:true */
		addPostRow( 'tax', this );
	}

	function addPostmetaRow() {
		/*jshint validthis:true */
		addPostRow( 'meta', this );
	}

	function addPostRow( type, button ) {
		let name;
		const id = jQuery( 'input[name="id"]' ).val();
		const settings = jQuery( button ).closest( '.frm_form_action_settings' );
		const key = settings.data( 'actionkey' );
		const postType = settings.find( '.frm_post_type' ).val();
		let metaName = 0;
		const postTypeRows = document.querySelectorAll( `.frm_post${ type }_row` );

		if ( postTypeRows.length ) {
			name = postTypeRows[ postTypeRows.length - 1 ].id.replace( `frm_post${ type }_`, '' );
			if ( isNumeric( name ) ) {
				metaName = 1 + parseInt( name, 10 );
			} else {
				metaName = 1;
			}
		}

		jQuery.ajax( {
			type: 'POST', url: ajaxurl,
			data: {
				action: `frm_add_post${ type }_row`,
				form_id: id,
				meta_name: metaName,
				tax_key: metaName,
				post_type: postType,
				action_key: key,
				nonce: frmGlobal.nonce
			},
			success( html ) {
				let cfOpts;
				let optIndex;
				jQuery( document.getElementById( `frm_post${ type }_rows` ) ).append( html );
				jQuery( `.frm_add_post${ type }_row.button` ).hide();

				if ( type === 'meta' ) {
					jQuery( '.frm_name_value' ).show();
					cfOpts = document.querySelectorAll( '.frm_toggle_cf_opts' );
					for ( optIndex = 0; optIndex < cfOpts.length - 1; ++optIndex ) {
						cfOpts[ optIndex ].style.display = 'none';
					}
				} else if ( type === 'tax' ) {
					jQuery( '.frm_posttax_labels' ).show();
				}
			}
		} );
	}

	function isNumeric( value ) {
		return ! isNaN( parseFloat( value ) ) && isFinite( value );
	}

	function changePosttaxRow() {
		/*jshint validthis:true */
		if ( ! jQuery( this ).closest( '.frm_posttax_row' ).find( '.frm_posttax_opt_list' ).length ) {
			return;
		}

		jQuery( this ).closest( '.frm_posttax_row' ).find( '.frm_posttax_opt_list' ).html( '<div class="spinner frm_spinner" style="display:block"></div>' );

		const postType = jQuery( this ).closest( '.frm_form_action_settings' ).find( 'select[name$="[post_content][post_type]"]' ).val();
		const actionKey = jQuery( this ).closest( '.frm_form_action_settings' ).data( 'actionkey' );
		const taxKey = jQuery( this ).closest( '.frm_posttax_row' ).attr( 'id' ).replace( 'frm_posttax_', '' );
		const metaName = jQuery( this ).val();
		const showExclude = jQuery( document.getElementById( `${ taxKey }_show_exclude` ) ).is( ':checked' ) ? 1 : 0;
		const fieldId = jQuery( `select[name$="[post_category][${ taxKey }][field_id]"]` ).val();
		const id = jQuery( 'input[name="id"]' ).val();

		jQuery.ajax( {
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'frm_add_posttax_row',
				form_id: id,
				post_type: postType,
				tax_key: taxKey,
				action_key: actionKey,
				meta_name: metaName,
				field_id: fieldId,
				show_exclude: showExclude,
				nonce: frmGlobal.nonce
			},
			success( html ) {
				const $tax = jQuery( document.getElementById( `frm_posttax_${ taxKey }` ) );
				$tax.replaceWith( html );
			}
		} );
	}

	function toggleCfOpts() {
		/*jshint validthis:true */
		const row = jQuery( this ).closest( '.frm_postmeta_row' );
		const cancel = row.find( '.frm_cancelnew' );
		const select = row.find( '.frm_enternew' );
		if ( row.find( 'select.frm_cancelnew' ).is( ':visible' ) ) {
			cancel.hide();
			select.show();
		} else {
			cancel.show();
			select.hide();
		}

		row.find( 'input.frm_enternew, select.frm_cancelnew' ).val( '' );
		return false;
	}

	function submitSettings() {
		if ( showNameYourFormModal() ) {
			return;
		}

		/*jshint validthis:true */
		preFormSave( this );
		triggerSubmit( document.querySelector( '.frm_form_settings' ) );
	}

	function settingsSubmitted() {
		// set fieldsUpdated to 0 to avoid the unsaved changes pop up
		adminState.fieldsUpdated = 0;
	}

	function resetFieldsUpdated() {
		adminState.fieldsUpdated = 0;
	}

	function onActionLoaded( event ) {
		const settings = event.target.closest( '.frm_form_action_settings' );
		if ( settings && ( settings.classList.contains( 'frm_single_email_settings' ) || settings.classList.contains( 'frm_single_on_submit_settings' ) ) ) {
			initWysiwygOnActionLoaded( settings );
		}
	}

	function initWysiwygOnActionLoaded( settings ) {
		settings.querySelectorAll( '.wp-editor-area' ).forEach( wysiwyg => {
			frmDom.wysiwyg.init(
				wysiwyg,
				{ height: 160, addFocusEvents: true }
			);
		} );
	}

	function invisible( classes ) {
		jQuery( classes ).css( 'visibility', 'hidden' );
	}

	function visible( classes ) {
		jQuery( classes ).css( 'visibility', 'visible' );
	}

	function initOnSubmitAction() {
		const onChangeType = event => {
			if ( ! event.target.checked ) {
				return;
			}

			const actionEl = event.target.closest( '.frm_form_action_settings' );
			actionEl.querySelectorAll( '.frm_on_submit_dependent_setting:not(.frm_hidden)' ).forEach( el => {
				el.classList.add( 'frm_hidden' );
			} );

			const activeEls = actionEl.querySelectorAll( `.frm_on_submit_dependent_setting[data-show-if-${ event.target.value }]` );
			activeEls.forEach( activeEl => {
				activeEl.classList.remove( 'frm_hidden' );
			} );

			actionEl.setAttribute( 'data-on-submit-type', event.target.value );
		};

		frmDom.util.documentOn( 'change', '.frm_on_submit_type input[type="radio"]', onChangeType );
	}

	const $formActions = jQuery( document.getElementById( 'frm_notification_settings' ) );

	// BCC, CC, and Reply To button functionality
	$formActions.on( 'click', '.frm_email_buttons', showEmailRow );
	$formActions.on( 'click', '.frm_remove_field', hideEmailRow );
	$formActions.on( 'change', '.frm_to_row, .frm_from_row', showEmailWarning );
	$formActions.on( 'change', '.frm_tax_selector', changePosttaxRow );
	$formActions.on( 'change', 'select.frm_single_post_field', checkDupPost );
	$formActions.on( 'change', 'select.frm_toggle_post_content', togglePostContent );
	$formActions.on( 'change', 'select.frm_dyncontent_opt', fillDyncontent );
	$formActions.on( 'change', '.frm_post_type', switchPostType );
	$formActions.on( 'click', '.frm_add_postmeta_row', addPostmetaRow );
	$formActions.on( 'click', '.frm_add_posttax_row', addPosttaxRow );
	$formActions.on( 'click', '.frm_toggle_cf_opts', toggleCfOpts );
	$formActions.on( 'click', '.frm_duplicate_form_action', copyFormAction );
	jQuery( '.frm_actions_list' ).on( 'click', '.frm_active_action', addFormAction );
	jQuery( document ).on( 'frmAfterSearch', '#actions-search-input', toggleFormActionsNoResultsVisibility );
	initiateMultiselect();

	// Reflect the at-limit state on action triggers when the page loads (e.g. one-per-form actions like Post or Quiz).
	document.querySelectorAll( '.frm_actions_list a[data-actiontype]' ).forEach(
		trigger => checkActiveAction( trigger.dataset.actiontype )
	);

	jQuery( '.frm_submit_settings_btn' ).on( 'click', submitSettings );

	addFormNameModalEvents();

	const formSettings = jQuery( '.frm_form_settings' );
	formSettings.on( 'click', '.frm_add_form_logic', addFormLogicRow );
	formSettings.on( 'change', 'input[data-emailkey][data-toggleclass]', addInitialLogicRowOnEnable );
	formSettings.on( 'click', '.frm_already_used', actionLimitMessage );

	document.addEventListener(
		'click',
		function handleImageUploadClickEvents( event ) {
			const { target } = event;

			if ( ! target.closest( '.frm_image_preview_wrapper' ) ) {
				return;
			}

			if ( target.closest( '.frm_choose_image_box' ) ) {
				addImageToOption.bind( target )( event );
				return;
			}

			if ( target.closest( '.frm_remove_image_option' ) ) {
				removeImageFromOption.bind( target )( event );
			}
		}
	);

	// Close shortcode modal on click.
	formSettings.on( 'mouseup', '*:not(.frm-show-box)', maybeHideShortcodes );

	//Warning when user selects "Do not store entries ..."
	jQuery( document.getElementById( 'no_save' ) ).on( 'change', function() {
		if ( this.checked ) {
			if ( confirm( frmAdminJs.no_save_warning ) !== true ) {
				// Uncheck box if user hits "Cancel"
				jQuery( this ).attr( 'checked', false );
			}
		}
	} );

	// Handle spam settings redirect link
	const spamRedirectLinks = document.querySelectorAll( 'a[href="#spam_settings"]' );
	spamRedirectLinks.forEach( function( link ) {
		link.addEventListener( 'click', function( e ) {
			e.preventDefault();
			const spamTab = document.querySelector( '.frm-category-tabs a[href="#spam_settings"]' );
			if ( spamTab ) {
				clickTab( spamTab );
				// Scroll to top after tab switch
				setTimeout( function() {
					const categoryDiv = document.getElementById( 'frm-categorydiv' );
					if ( categoryDiv ) {
						categoryDiv.scrollIntoView( { block: 'start' } );
					}
				}, 50 );
			}
		} );
	} );

	// Handle Captcha and GDPR checkbox toggles to show/hide warnings
	[ 'captcha', 'gdpr' ].forEach( function( fieldType ) {
		const includeCheckbox = document.getElementById( `frm_include_${ fieldType }` );
		if ( ! includeCheckbox ) {
			return;
		}
		const initialState = includeCheckbox.checked;
		includeCheckbox.addEventListener( 'change', function() {
			const addWarning = document.getElementById( `frm_${ fieldType }_add_warning` );
			const removeWarning = document.getElementById( `frm_${ fieldType }_remove_warning` );
			if ( addWarning && removeWarning ) {
				// Only show warning if current state differs from initial state
				if ( this.checked !== initialState ) {
					if ( this.checked ) {
						addWarning.style.display = 'block';
						removeWarning.style.display = 'none';
					} else {
						addWarning.style.display = 'none';
						removeWarning.style.display = 'block';
					}
				} else {
					// Hide warnings if toggled back to initial state
					addWarning.style.display = 'none';
					removeWarning.style.display = 'none';
				}
			}
		} );
	} );

	jQuery( 'select[name="options[edit_action]"]' ).on( 'change', showSuccessOpt );

	const $loggedIn = document.getElementById( 'logged_in' );
	jQuery( $loggedIn ).on( 'change', function() {
		if ( this.checked ) {
			visible( '.hide_logged_in' );
		} else {
			invisible( '.hide_logged_in' );
		}
	} );

	const $cookieExp = jQuery( document.getElementById( 'frm_cookie_expiration' ) );
	jQuery( document.getElementById( 'frm_single_entry_type' ) ).on( 'change', function() {
		if ( this.value === 'cookie' ) {
			$cookieExp.fadeIn( 'slow' );
		} else {
			$cookieExp.fadeOut( 'slow' );
		}
	} );

	const $singleEntry = document.getElementById( 'single_entry' );
	jQuery( $singleEntry ).on( 'change', function() {
		if ( this.checked ) {
			visible( '.hide_single_entry' );
		} else {
			invisible( '.hide_single_entry' );
		}

		if ( this.checked && jQuery( document.getElementById( 'frm_single_entry_type' ) ).val() === 'cookie' ) {
			$cookieExp.fadeIn( 'slow' );
		} else {
			$cookieExp.fadeOut( 'slow' );
		}
	} );

	jQuery( '.hide_save_draft' ).hide();

	const $saveDraft = jQuery( document.getElementById( 'save_draft' ) );
	$saveDraft.on( 'change', function() {
		if ( this.checked ) {
			jQuery( '.hide_save_draft' ).fadeIn( 'slow' );
		} else {
			jQuery( '.hide_save_draft' ).fadeOut( 'slow' );
		}
	} );
	triggerChange( $saveDraft );

	//If Allow editing is checked/unchecked
	const $editable = document.getElementById( 'editable' );
	jQuery( $editable ).on( 'change', function() {
		if ( this.checked ) {
			jQuery( '.hide_editable' ).fadeIn( 'slow' );
			triggerChange( document.getElementById( 'edit_action' ) );
		} else {
			jQuery( '.hide_editable' ).fadeOut( 'slow' );
			jQuery( '.edit_action_message_box' ).fadeOut( 'slow' );//Hide On Update message box
		}
	} );

	//If File Protection is checked/unchecked
	jQuery( document ).on( 'change', '#protect_files', function() {
		if ( this.checked ) {
			jQuery( '.hide_protect_files' ).fadeIn( 'slow' );
		} else {
			jQuery( '.hide_protect_files' ).fadeOut( 'slow' );
		}
	} );

	jQuery( document ).on( 'frm-multiselect-changed', '#protect_files_role', adjustVisibilityValuesForEveryoneValues );

	jQuery( document ).on( 'submit', '.frm_form_settings', settingsSubmitted );
	jQuery( document ).on( 'change', '#form_settings_page input:not(.frm-search-input), #form_settings_page select, #form_settings_page textarea', fieldUpdated );

	// Page Selection Autocomplete
	initAutocomplete();

	jQuery( document ).on( 'frm-action-loaded', onActionLoaded );

	initOnSubmitAction();

	wp.hooks.addAction( 'frm_reset_fields_updated', 'formidableAdmin', resetFieldsUpdated );
} );
