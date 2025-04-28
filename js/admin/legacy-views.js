( function() {
	let thisFormId = 0;

	jQuery( document ).ready( function() {
		const thisForm = document.getElementById( 'form_id' );
		if ( thisForm !== null ) {
			thisFormId = thisForm.value;
		}

		// Only load on views settings page.
		if ( document.getElementById( 'frm_dyncontent' ) !== null ) {
			viewInit();
		}

		document.addEventListener( 'frm_legacy_views_handle_field_focus', function( event ) {
			const { idAttrValue } = event.frmData;
			toggleAllowedShortcodes( idAttrValue );
		});
	});

	function viewInit() {
		let $addRemove,
			$advInfo = jQuery( document.getElementById( 'frm_adv_info' ) );

		$advInfo.before( '<div id="frm_position_ele"></div>' );
		setupMenuOffset();

		jQuery( document ).on( 'blur', '#param', checkDetailPageSlug );
		jQuery( document ).on( 'blur', 'input[name^="options[where_val]"]', checkFilterParamNames );

		// Show loading indicator.
		jQuery( '#publish' ).on( 'mousedown', function() {
			this.classList.add( 'frm_loading_button' );
		});

		// move content tabs
		jQuery( '#frm_dyncontent .handlediv' ).before( jQuery( '#frm_dyncontent .nav-menus-php' ) );

		// click content tabs
		jQuery( '.nav-tab-wrapper a' ).on( 'click', clickContentTab );

		// click tabs after panel is replaced with ajax
		jQuery( '#side-sortables' ).on( 'click', '.frm_doing_ajax.categorydiv .category-tabs a', clickTabsAfterAjax );

		initToggleShortcodes();
		jQuery( '.frm_code_list:not(.frm-dropdown-menu) a' ).addClass( 'frm_noallow' );

		jQuery( 'input[name="show_count"]' ).on( 'change', showCount );

		jQuery( document.getElementById( 'form_id' ) ).on( 'change', displayFormSelected );

		$addRemove = jQuery( '.frm_repeat_rows' );
		$addRemove.on( 'click', '.frm_add_order_row', addOrderRow );
		$addRemove.on( 'click', '.frm_add_where_row', addWhereRow );
		$addRemove.on( 'change', '.frm_insert_where_options', insertWhereOptions );
		$addRemove.on( 'change', '.frm_where_is_options', hideWhereOptions );

		setDefaultPostStatus();
	}

	/**
	 * Checks the Detail Page slug to see if it's a reserved word and displays a message if it is.
	 */
	function checkDetailPageSlug() {
		let slug = jQuery( '#param' ).val(),
			msg;
		slug = slug.trim().toLowerCase();
		if ( Array.isArray( frmAdminJs.unsafe_params ) && frmAdminJs.unsafe_params.includes( slug ) ) {
			msg = frmAdminJs.slug_is_reserved;
			msg =  msg.replace( '****', addHtmlTags( slug, 'strong' ) );
			msg += '<br /><br />';
			msg += addHtmlTags( '<a href="https://codex.wordpress.org/WordPress_Query_Vars" target="_blank" class="frm-standard-link">' + frmAdminJs.reserved_words + '</a>', 'div' );
			infoModal( msg );
		}
	}
	
	/**
	 * Checks View filter value for params named with reserved words and displays a message if any are found.
	 */
	function checkFilterParamNames() {
		let regEx = /\[\s*get\s*param\s*=\s*['"]?([a-zA-Z-_]+)['"]?/ig,
			filterValue = jQuery( this ).val(),
			match = regEx.exec( filterValue ),
			unsafeParams = '';

		while ( match !== null ) {
			if ( Array.isArray( frmAdminJs.unsafe_params ) && frmAdminJs.unsafe_params.includes( match[1]) ) {
				if ( unsafeParams !== '' ) {
					unsafeParams += '", "' + match[ 1 ];
				} else {
					unsafeParams = match[ 1 ];
				}
			}
			match = regEx.exec( filterValue );
		}

		if ( unsafeParams !== '' ) {
			let msg =  frmAdminJs.param_is_reserved;
			msg =  msg.replace( '****', addHtmlTags( unsafeParams, 'strong' ) );
			msg += '<br /><br />';
			msg += ' <a href="https://codex.wordpress.org/WordPress_Query_Vars" target="_blank" class="frm-standard-link">' + frmAdminJs.reserved_words + '</a>';

			infoModal( msg );
		}
	}

	function addHtmlTags( text, tag ) {
		tag = tag ? tag : 'p';
		return '<' + tag + '>' + text + '</' + tag + '>';
	}

	function initToggleShortcodes() {
		if ( typeof tinymce !== 'object' ) {
			return;
		}

		DOM = tinymce.DOM;
		if ( typeof DOM.events !== 'undefined' && typeof DOM.events.add !== 'undefined' ) {
			DOM.events.add( DOM.select( '.wp-editor-wrap' ), 'mouseover', function() {
				if ( jQuery( '*:focus' ).length > 0 ) {
					return;
				}
				if ( this.id ) {
					toggleAllowedShortcodes( this.id.slice( 3, -5 ) );
				}
			});
			DOM.events.add( DOM.select( '.wp-editor-wrap' ), 'mouseout', function() {
				if ( jQuery( '*:focus' ).length > 0 ) {
					return;
				}
				if ( this.id ) {
					toggleAllowedShortcodes( this.id.slice( 3, -5 ) );
				}
			});
		} else {
			jQuery( '#frm_dyncontent' ).on( 'mouseover mouseout', '.wp-editor-wrap', function() {
				if ( jQuery( '*:focus' ).length > 0 ) {
					return;
				}
				if ( this.id ) {
					toggleAllowedShortcodes( this.id.slice( 3, -5 ) );
				}
			});
		}
	}

	function setDefaultPostStatus() {
		const urlQuery = window.location.search.substring( 1 );
		if ( urlQuery.indexOf( 'action=edit' ) === -1 ) {
			document.getElementById( 'post-visibility-display' ).textContent = frmAdminJs.private_label;
			document.getElementById( 'hidden-post-visibility' ).value        = 'private';
			document.getElementById( 'visibility-radio-private' ).checked    = true;
		}
	}

	function setupMenuOffset() {
		window.onscroll = document.documentElement.onscroll = setMenuOffset;
		setMenuOffset();
	}

	function setMenuOffset() {
		const fields = document.getElementById( 'frm_adv_info' );
		if ( fields === null ) {
			return;
		}

		const currentOffset = document.documentElement.scrollTop || document.body.scrollTop; // body for Safari
		if ( currentOffset === 0 ) {
			fields.classList.remove( 'frm_fixed' );
			return;
		}

		const posEle = document.getElementById( 'frm_position_ele' );
		if ( posEle === null ) {
			return;
		}

		const eleOffset = jQuery( posEle ).offset();
		const offset = eleOffset.top;
		let desiredOffset = offset - currentOffset;
		let menuHeight = 0;

		const menu = document.getElementById( 'wpadminbar' );
		if ( menu !== null ) {
			menuHeight = menu.offsetHeight;
		}

		if ( desiredOffset < menuHeight ) {
			desiredOffset = menuHeight;
		}

		if ( desiredOffset > menuHeight ) {
			fields.classList.remove( 'frm_fixed' );
		} else {
			fields.classList.add( 'frm_fixed' );
			if ( desiredOffset !== 32 ) {
				fields.style.top = desiredOffset + 'px';
			}
		}
	}

	function showCount() {
		/*jshint validthis:true */
		const value = jQuery( this ).val();

		const $cont = document.getElementById( 'date_select_container' );
		const tab = document.getElementById( 'frm_listing_tab' );
		let label = tab.getAttribute( 'data-label' );
		if ( value === 'calendar' ) {
			jQuery( '.hide_dyncontent, .hide_single_content' ).removeClass( 'frm_hidden' );
			jQuery( '.limit_container' ).addClass( 'frm_hidden' );
			$cont.style.display = 'block';
		} else if ( value === 'dynamic' ) {
			jQuery( '.hide_dyncontent, .limit_container, .hide_single_content' ).removeClass( 'frm_hidden' );
		} else if ( value === 'one' ) {
			label = tab.getAttribute( 'data-one' );
			jQuery( '.hide_dyncontent, .limit_container, .hide_single_content' ).addClass( 'frm_hidden' );
		} else {
			jQuery( '.hide_dyncontent' ).addClass( 'frm_hidden' );
			jQuery( '.limit_container, .hide_single_content' ).removeClass( 'frm_hidden' );
		}

		if ( value !== 'calendar' ) {
			$cont.style.display = 'none';
		}
		tab.innerHTML = label;
	}

	function displayFormSelected() {
		/*jshint validthis:true */
		const formId = jQuery( this ).val();
		thisFormId = formId; // set the global form id
		if ( formId === '' ) {
			return;
		}

		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'frm_get_cd_tags_box',
				form_id: formId,
				nonce: frmGlobal.nonce
			},
			success: function( html ) {
				jQuery( '#frm_adv_info .categorydiv' ).html( html );
			}
		});

		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'frm_get_date_field_select',
				form_id: formId,
				nonce: frmGlobal.nonce
			},
			success: function( html ) {
				jQuery( document.getElementById( 'date_select_container' ) ).html( html );
			}
		});
	}

	function clickTabsAfterAjax() {
		/*jshint validthis:true */
		const t = jQuery( this ).attr( 'href' );
		jQuery( this ).parent().addClass( 'tabs' ).siblings( 'li' ).removeClass( 'tabs' );
		jQuery( t ).show().siblings( '.tabs-panel' ).hide();
		return false;
	}

	function clickContentTab() {
		/*jshint validthis:true */
		const link = jQuery( this );
		const t = link.attr( 'href' );
		if ( typeof t === 'undefined' ) {
			return false;
		}

		const c = t.replace( '#', '.' );
		link.closest( '.nav-tab-wrapper' ).find( 'a' ).removeClass( 'nav-tab-active' );
		link.addClass( 'nav-tab-active' );
		jQuery( '.nav-menu-content' ).not( t ).not( c ).hide();
		jQuery( t + ',' + c ).show();

		return false;
	}

	function addOrderRow() {
		const logicRows = document.getElementById( 'frm_order_options' ).querySelectorAll( '.frm_logic_rows div' );
		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'frm_add_order_row',
				form_id: thisFormId,
				order_key: getNewRowId( logicRows, 'frm_order_field_', 1 ),
				nonce: frmGlobal.nonce
			},
			success: function( html ) {
				jQuery( '#frm_order_options .frm_logic_rows' ).append( html ).show().prev( '.frm_add_order_row' ).hide();
			}
		});
	}

	function addWhereRow() {
		const rowDivs = document.getElementById( 'frm_where_options' ).querySelectorAll( '.frm_logic_rows div' );
		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'frm_add_where_row',
				form_id: thisFormId,
				where_key: getNewRowId( rowDivs, 'frm_where_field_', 1 ),
				nonce: frmGlobal.nonce
			},
			success: function( html ) {
				jQuery( '#frm_where_options .frm_logic_rows' ).append( html ).show().prev( '.frm_add_where_row' ).hide();
			}
		});
	}

	function insertWhereOptions() {
		/*jshint validthis:true */
		const value = this.value,
			whereKey = jQuery( this ).closest( '.frm_where_row' ).attr( 'id' ).replace( 'frm_where_field_', '' );

		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'frm_add_where_options',
				where_key: whereKey,
				field_id: value,
				nonce: frmGlobal.nonce
			},
			success: function( html ) {
				jQuery( document.getElementById( 'where_field_options_' + whereKey ) ).html( html );
			}
		});
	}

	function hideWhereOptions() {
		/*jshint validthis:true */
		const value = this.value,
			whereKey = jQuery( this ).closest( '.frm_where_row' ).attr( 'id' ).replace( 'frm_where_field_', '' );

		if ( value === 'group_by' || value === 'group_by_newest' ) {
			document.getElementById( 'where_field_options_' + whereKey ).style.display = 'none';
		} else {
			document.getElementById( 'where_field_options_' + whereKey ).style.display = 'inline-block';
		}
	}

	function getNewRowId( rows, replace, defaultValue ) {
		if ( ! rows.length ) {
			return 'undefined' !== typeof defaultValue ? defaultValue : 0;
		}
		return parseInt( rows[ rows.length - 1 ].id.replace( replace, '' ), 10 ) + 1;
	}

	function toggleAllowedShortcodes( id ) {
		let c, clickedID;
		if ( typeof id === 'undefined' ) {
			id = '';
		}
		c = id;

		if ( id.indexOf( '-search-input' ) !== -1 ) {
			return;
		}

		if ( id !== '' ) {
			const $ele = jQuery( document.getElementById( id ) );
			if ( $ele.attr( 'class' ) && id !== 'wpbody-content' && id !== 'content' && id !== 'dyncontent' && id !== 'success_msg' ) {
				let d = $ele.attr( 'class' ).split( ' ' )[0];
				if ( d === 'frm_long_input' || d === 'frm_98_width' || typeof d === 'undefined' ) {
					d = '';
				} else {
					id = d.trim();
				}
				c = c + ' ' + d;
				c = c.replace( 'widefat', '' ).replace( 'frm_with_left_label', '' );
			}
		}

		jQuery( '#frm-insert-fields-box,#frm-conditionals,#frm-adv-info-tab,#frm-dynamic-values' ).attr( 'data-fills', c.trim() );
		const a = [
			'content', 'wpbody-content', 'dyncontent', 'success_url',
			'success_msg', 'edit_msg', 'frm_dyncontent', 'frm_not_email_message',
			'frm_not_email_subject'
		];
		const b = [
			'before_content', 'after_content', 'frm_not_email_to',
			'dyn_default_value'
		];

		if ( jQuery.inArray( id, a ) >= 0 ) {
			jQuery( '.frm_code_list a' ).removeClass( 'frm_noallow' ).addClass( 'frm_allow' );
			jQuery( '.frm_code_list a.hide_' + id ).addClass( 'frm_noallow' ).removeClass( 'frm_allow' );
		} else if ( jQuery.inArray( id, b ) >= 0 ) {
			jQuery( '.frm_code_list:not(.frm-dropdown-menu) a:not(.show_' + id + ')' ).addClass( 'frm_noallow' ).removeClass( 'frm_allow' );
			jQuery( '.frm_code_list a.show_' + id ).removeClass( 'frm_noallow' ).addClass( 'frm_allow' );
		} else {
			jQuery( '.frm_code_list:not(.frm-dropdown-menu) a' ).addClass( 'frm_noallow' ).removeClass( 'frm_allow' );
		}

		// Automatically select a tab.
		if ( id === 'dyn_default_value' ) {
			clickedID = 'frm_dynamic_values';
			document.getElementById( clickedID + '_tab' ).click();
			jQuery( '#' + clickedID.replace( /_/g, '-' ) + ' .frm_show_inactive' ).addClass( 'frm_hidden' );
			jQuery( '#' + clickedID.replace( /_/g, '-' ) + ' .frm_show_active' ).removeClass( 'frm_hidden' );
		}
	}
}() );