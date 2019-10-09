var FrmFormsConnect = window.FrmFormsConnect || ( function( document, window, $ ) {

	/*global jQuery:false, frm_admin_js, frmGlobal, ajaxurl */

	var el = {
		licenseBox: document.getElementById( 'frm_license_top' ),
		messageBox: document.getElementsByClassName( 'frm_pro_license_msg' )[0],
		btn: document.getElementById('frm-settings-connect-btn'),
		reset: document.getElementById( 'frm_reconnect_link' )
	};

	/**
	 * Public functions and properties.
	 *
	 * @since 4.03
	 *
	 * @type {Object}
	 */
	var app = {

		/**
		 * Register connect button event.
		 *
		 * @since 4.03
		 */
		init: function() {
			$( document.getElementById( 'frm_deauthorize_link' ) ).click( app.deauthorize );
			$( '.frm_authorize_link' ).click( app.authorize );
			if ( el.reset !== null ) {
				$( el.reset ).click( app.reauthorize );
			}

			$( el.btn ).on( 'click', function(e) {
				e.preventDefault();
				app.gotoUpgradeUrl();
			} );

			window.addEventListener('message', function(msg) {
				if ( msg.origin.replace(/\/$/, '') !== frmGlobal.app_url.replace(/\/$/, '') ) {
					return;
				}

				if ( ! msg.data || 'object' !== typeof msg.data ) {
					console.error('Messages from "' + frmGlobal.app_url + '" must contain an api key string.');
					return;
				}

				app.updateForm(msg.data);
			});
		},

		/**
		 * Go to upgrade url.
		 *
		 * @since 4.03
		 */
		gotoUpgradeUrl: function() {
			var w = window.open(frmGlobal.app_url + '/api-connect/', '_blank', 'location=no,width=500,height=730,scrollbars=0');
			w.focus();
		},

		updateForm: function(response) {

			// Start spinner.
			var btn = el.btn;
			btn.classList.add('frm_loading_button');

			if ( response.url !== '' ) {
				app.showProgress({
					success:true,
					message:'Installing...'
				});
				var fallback = setTimeout( function() {
					app.showProgress({
						success:true,
						message:'Installing is taking longer than expected. <a class="frm-install-addon button button-primary frm-button-primary" rel="' + response.url + '" aria-label="Install">Install Now</a>'
					});
				}, 10000 );
				$.ajax( {
					type: 'POST',
					url: ajaxurl,
					dataType: 'json',
					data: {
						action: 'frm_connect',
						plugin: response.url,
						nonce: frmGlobal.nonce
					},
					success: function() {
						clearTimeout( fallback );
						app.activateKey( response );
					},
					error: function(xhr, textStatus, e) {
						clearTimeout( fallback );
						btn.classList.remove('frm_loading_button');
						app.showMessage({
							success:false,
							message: e
						});
					}
				});
			} else if ( response.key !== '' ) {
				app.activateKey( response );
			}
		},

		activateKey: function( response ) {
			var btn = el.btn;
			if ( response.key === '' ) {
				btn.classList.remove('frm_loading_button');
			} else {
				app.showProgress({
					success:true,
					message:'Activating...'
				});
				$.ajax( {
					type: 'POST',
					url: ajaxurl,
					dataType: 'json',
					data: {
						action: 'frm_addon_activate',
						license: response.key,
						plugin: 'formidable_pro',
						wpmu: 0,
						nonce: frmGlobal.nonce
					},
					success: function( msg ) {
						btn.classList.remove('frm_loading_button');

						if ( msg.success === true ) {
							el.licenseBox.classList.replace( 'frm_unauthorized_box', 'frm_authorized_box' );
						}

						app.showMessage( msg );
					},
					error: function(xhr, textStatus, e) {
						btn.classList.remove('frm_loading_button');
						app.showMessage({
							success:false,
							message: e
						});
					}
				});
			}
		},

		/* Manual license authorization */
		authorize: function() {
			/*jshint validthis:true */
			var button = this;
			var pluginSlug = this.getAttribute('data-plugin');
			var input = document.getElementById( 'edd_' + pluginSlug + '_license_key' );
			var license = input.value;
			var wpmu = document.getElementById( 'proplug-wpmu' );
			this.classList.add( 'frm_loading_button' );
			if ( wpmu === null ) {
				wpmu = 0;
			} else if ( wpmu.checked ) {
				wpmu = 1;
			} else {
				wpmu = 0;
			}

			$.ajax( {
				type: 'POST', url: ajaxurl, dataType: 'json',
				data: {
					action: 'frm_addon_activate',
					license: license,
					plugin: pluginSlug,
					wpmu: wpmu,
					nonce: frmGlobal.nonce
				},
				success: function( msg ) {
					app.afterAuthorize( msg, input );
					button.classList.remove( 'frm_loading_button' );
				}
			} );
		},

		afterAuthorize: function( msg, input ) {
			if ( msg.success === true ) {
				input.value = '•••••••••••••••••••';
			}

			app.showMessage( msg );
		},

		showProgress: function( msg ) {
			var messageBox = el.messageBox;
			if ( msg.success === true ) {
				messageBox.classList.remove( 'frm_error_style' );
				messageBox.classList.add( 'frm_message', 'frm_updated_message' );
			} else {
				messageBox.classList.add( 'frm_error_style' );
				messageBox.classList.remove( 'frm_message', 'frm_updated_message' );
			}
			messageBox.classList.remove( 'frm_hidden' );
			messageBox.innerHTML = msg.message;
		},

		showMessage: function( msg ) {
			var messageBox = el.messageBox;

			if ( msg.success === true ) {
				var d = el.licenseBox;
				d.className = d.className.replace( 'frm_unauthorized_box', 'frm_authorized_box' );
				messageBox.classList.remove( 'frm_error_style' );
				messageBox.classList.add( 'frm_message', 'frm_updated_message' );
			} else {
				messageBox.classList.add( 'frm_error_style' );
				messageBox.classList.remove( 'frm_message', 'frm_updated_message' );
			}

			messageBox.classList.remove( 'frm_hidden' );
			messageBox.innerHTML = msg.message;
			if ( msg.message !== '' ) {
				setTimeout( function() {
					messageBox.innerHTML = '';
					messageBox.classList.add( 'frm_hidden' );
					messageBox.classList.remove( 'frm_error_style', 'frm_message', 'frm_updated_message' );
				}, 10000 );
			}
		},

		/* Clear the site license cache */
		reauthorize: function() {
			/*jshint validthis:true */
			this.innerHTML = '<span class="frm-wait frm_spinner" style="visibility:visible;float:none"></span>';

			$.ajax( {
				type: 'POST',
				url: ajaxurl,
				dataType: 'json',
				data: {
					action: 'frm_reset_cache',
					plugin: 'formidable_pro',
					nonce: frmGlobal.nonce
				},
				success: function( msg ) {
					el.reset.innerHTML = msg.message;
					if ( el.reset.getAttribute( 'data-refresh' ) === '1' ) {
						window.location.reload();
					}
				}
			} );
			return false;
		},

		deauthorize: function() {
			/*jshint validthis:true */
			if ( !confirm( frmGlobal.deauthorize ) ) {
				return false;
			}
			var pluginSlug = this.getAttribute('data-plugin'),
				input = document.getElementById( 'edd_' + pluginSlug + '_license_key' ),
				license = input.value,
				link = this;

			this.innerHTML = '<span class="frm-wait frm_spinner" style="visibility:visible;"></span>';

			$.ajax( {
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'frm_addon_deactivate',
					license: license,
					plugin: pluginSlug,
					nonce: frmGlobal.nonce
				},
				success: function( msg ) {
					el.licenseBox.className = el.licenseBox.className.replace( 'frm_authorized_box', 'frm_unauthorized_box' );
					input.value = '';
					link.innerHTML = '';
				}
			} );
			return false;
		}
	};

	// Provide access to public functions/properties.
	return app;

}( document, window, jQuery ) );

function frmAdminBuildJS() {
	//'use strict';

	/*global jQuery:false, frm_admin_js, frmGlobal, ajaxurl */

	var $newFields = jQuery( document.getElementById( 'frm-show-fields' ) );
	var builderForm = document.getElementById( 'new_fields' );
	var thisForm = document.getElementById( 'form_id' );
	var cancelSort = false;
	var copyHelper = false;

	var this_form_id = 0;
	if ( thisForm !== null ) {
		this_form_id = thisForm.value;
	}

	// Global settings
	var s;

	function showElement( element ) {
		element[0].style.display = '';
	}

	function hideElement( element ) {
		element[0].style.display = 'none';
	}

	function empty( $obj ) {
		if ( $obj !== null ) {
			while ( $obj.firstChild ) {
				$obj.removeChild( $obj.firstChild );
			}
		}
	}

	function addClass( $obj, className ) {
		if ( $obj.classList ) {
			$obj.classList.add( className );
		} else {
			$obj.className += ' ' + className;
		}
	}

	function confirmClick( e ) {
		/*jshint validthis:true */
		e.stopPropagation();
		e.preventDefault();
		confirmLinkClick( this );
	}

	function confirmLinkClick( link ) {
		var message = link.getAttribute( 'data-frmverify' );

		if ( message === null || link.id === 'frm-confirmed-click' ) {
			return true;
		} else {
			return confirmModal( link );
		}
	}

	function confirmModal( link ) {
		var i, dataAtts,
			$info = initModal( '#frm_confirm_modal', '400px' ),
			continueButton = document.getElementById( 'frm-confirmed-click' );

		if ( $info === false ) {
			return false;
		}

		jQuery('.frm-confirm-msg').html( link.getAttribute( 'data-frmverify' ) );

		removeAtts = continueButton.dataset;
		for ( i in dataAtts ) {
			continueButton.removeAttribute( 'data-' + i );
		}

		dataAtts = link.dataset;
		for ( i in dataAtts ) {
			if ( i !== 'frmverify' ) {
				continueButton.setAttribute( 'data-' + i, dataAtts[ i ] );
			}
		}

		$info.dialog('open');
		continueButton.setAttribute( 'href', link.getAttribute( 'href' ) );
		return false;
	}

	function toggleItem( e ) {
		/*jshint validthis:true */
		var toggle = this.getAttribute( 'data-frmtoggle' ),
			text = this.getAttribute( 'data-toggletext' ),
			items = jQuery( toggle );

		e.preventDefault();

		if ( items.is( ':visible' ) ) {
			items.show();
		} else {
			items.hide();
		}

		if ( text !== null && text !== '' ) {
			this.setAttribute( 'data-toggletext', this.innerHTML );
			this.innerHTML = text;
		}

		return false;
	}

	function hideShowItem( e ) {
		/*jshint validthis:true */
		var hide = this.getAttribute( 'data-frmhide' ),
			show = this.getAttribute( 'data-frmshow' ),
			toggleClass = this.getAttribute( 'data-toggleclass' );

		e.preventDefault();
		if ( toggleClass === null ) {
			toggleClass = 'frm_hidden';
		}

		if ( hide !== null ) {
			jQuery( hide ).addClass( toggleClass );
		}

		if ( show !== null ) {
			jQuery( show ).removeClass( toggleClass );
		}

		var current = this.parentNode.querySelectorAll( 'a.current' );
		if ( current !== null ) {
			for ( var i = 0; i < current.length; i++ ) {
				current[ i ].classList.remove( 'current' );
			}
			this.classList.add( 'current' );
		}

		return false;
	}

	function setupMenuOffset() {
		window.onscroll = document.documentElement.onscroll = setMenuOffset;
		setMenuOffset();
	}

	function setMenuOffset() {
		var fields = document.getElementById( 'frm_adv_info' );
		if ( fields === null ) {
			return;
		}

		var currentOffset = document.documentElement.scrollTop || document.body.scrollTop; // body for Safari
		if ( currentOffset === 0 ) {
			fields.classList.remove( 'frm_fixed' );
			return;
		}

		var posEle = document.getElementById( 'frm_position_ele' );
		if ( posEle === null ) {
			return;
		}

		var eleOffset = jQuery( posEle ).offset();
		var offset = eleOffset.top;
		var desiredOffset = offset - currentOffset;
		var menuHeight = 0;

		var menu = document.getElementById( 'wpadminbar' );
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

	function loadTooltips() {
		var tooltipOpts = {
			template: '<div class="frm_tooltip tooltip"><div class="tooltip-inner"></div></div>',
			placement: 'bottom',
			container: 'body'
		};

		var wrapClass = jQuery( '.wrap, .frm_wrap' ),
			confirmModal = document.getElementById( 'frm_confirm_modal' );

		jQuery( confirmModal ).on( 'click', '[data-deletefield]', deleteFieldConfirmed );
		jQuery( confirmModal ).on( 'click', '[data-removeid]', removeThisTag );
		jQuery( confirmModal ).on( 'click', '[data-trashtemplate]', trashTemplate );

		wrapClass.on( 'click', '.frm_remove_tag, .frm_remove_form_action', removeThisTag );
		wrapClass.on( 'click', 'a[data-frmverify]', confirmClick );
		wrapClass.on( 'click', 'a[data-frmtoggle]', toggleItem );
		wrapClass.on( 'click', 'a[data-frmhide], a[data-frmshow]', hideShowItem );
		wrapClass.on( 'click', '.widget-top,a.widget-action', clickWidget );

		wrapClass.on( 'mouseenter.frm', '.frm_help', function() {
			jQuery( this ).off( 'mouseenter.frm' );
			jQuery( '.frm_help' ).tooltip( tooltipOpts );
			jQuery( this ).tooltip( 'show' );
		} );
		jQuery( '.frm_help' ).tooltip( tooltipOpts );
		wrapClass.on( 'mouseenter.frm', '.frm_bstooltip', function() {
			jQuery( this ).off( 'mouseenter.frm' );
			jQuery( '.frm_bstooltip' ).tooltip();
			jQuery( this ).tooltip( 'show' );
		} );
		jQuery( '.frm_bstooltip' ).tooltip();
	}

	function removeThisTag() {
		/*jshint validthis:true */
		var show, hide, id = '', removeMore,
			deleteButton = jQuery( this ),
			continueRemove = confirmLinkClick( this );

		if ( continueRemove === false ) {
			return;
		} else {
			id = deleteButton.attr( 'data-removeid' );
			show = deleteButton.attr( 'data-showlast' );
			removeMore = deleteButton.attr( 'data-removemore' );
			if ( typeof show === 'undefined' ) {
				show = '';
			}
			hide = deleteButton.attr( 'data-hidelast' );
			if ( typeof hide === 'undefined' ) {
				hide = '';
			}
		}

		if ( show !== '' ) {
			if ( deleteButton.closest( '.frm_add_remove' ).find( '.frm_remove_tag:visible' ).length > 1 ) {
				show = '';
				hide = '';
			}
		} else if ( id.indexOf( 'frm_postmeta_' ) === 0 ) {
			if ( jQuery( '#frm_postmeta_rows .frm_postmeta_row' ).length < 2 ) {
				show = '.frm_add_postmeta_row.button';
			}
			if ( jQuery( '.frm_toggle_cf_opts' ).length && jQuery( '#frm_postmeta_rows .frm_postmeta_row:not(#' + id + ')' ).last().length ) {
				if ( show !== '' ) {
					show += ',';
				}
				show += '#' + jQuery( '#frm_postmeta_rows .frm_postmeta_row:not(#' + id + ')' ).last().attr( 'id' ) + ' .frm_toggle_cf_opts';
			}
		}

		var $fadeEle = jQuery( document.getElementById( id ) );
		$fadeEle.fadeOut( 400, function() {
			$fadeEle.remove();

			if ( hide !== '' ) {
				jQuery( hide ).hide();
			}

			if ( show !== '' ) {
				jQuery( show + ' a,' + show ).removeClass( 'frm_hidden' ).fadeIn( 'slow' );
			}

			var action = jQuery( this ).closest( '.frm_form_action_settings' );
			if ( typeof action !== 'undefined' ) {
				var type = jQuery( this ).closest( '.frm_form_action_settings' ).find( '.frm_action_name' ).val();
				checkActiveAction( type );
			}
		} );

		if ( typeof removeMore !== 'undefined' ) {
			removeMore = jQuery( removeMore );
			removeMore.fadeOut( 400, function() {
				removeMore.remove();
			} );
		}

		if ( show !== '' ) {
			jQuery( this ).closest( '.frm_logic_rows' ).fadeOut( 'slow' );
		}

		return false;
	}

	function clickWidget( event, b ) {
		/*jshint validthis:true */
		var target = event.target;
		if ( typeof b === 'undefined' ) {
			b = this;
		}

		popCalcFields( b, false );

		var cont = jQuery( b ).closest( '.frm_form_action_settings' );
		if ( cont.length && typeof target !== 'undefined' && ( target.parentElement.className.indexOf( 'frm_email_icons' ) > -1 || target.parentElement.className.indexOf( 'frm_toggle' ) > -1 ) ) {
			// clicking on delete icon shouldn't open it
			event.stopPropagation();
			return;
		}

		var inside = cont.children( '.widget-inside' );

		if ( cont.length && inside.find( 'p, div, table' ).length < 1 ) {
			var action_id = cont.find( 'input[name$="[ID]"]' ).val();
			var action_type = cont.find( 'input[name$="[post_excerpt]"]' ).val();
			if ( action_type ) {
				inside.html( '<span class="frm-wait frm_spinner"></span>' );
				cont.find( '.spinner' ).fadeIn( 'slow' );
				jQuery.ajax( {
					type: 'POST',
					url: ajaxurl,
					data: {
						action: 'frm_form_action_fill',
						action_id: action_id,
						action_type: action_type,
						nonce: frmGlobal.nonce
					},
					success: function( html ) {
						inside.html( html );
						initiateMultiselect();
						showInputIcon( '#' + cont.attr('id') );
					}
				} );
			}
		}

		jQuery( b ).closest( '.frm_field_box' ).siblings().find( '.widget-inside' ).slideUp( 'fast' );
		if ( ( typeof b.className !== 'undefined' && b.className.indexOf( 'widget-action' ) !== -1 ) || jQuery( b ).closest( '.start_divider' ).length < 1 ) {
			return;
		}

		inside = jQuery( b ).closest( 'div.widget' ).children( '.widget-inside' );
		if ( inside.is( ':hidden' ) ) {
			inside.slideDown( 'fast' );
		} else {
			inside.slideUp( 'fast' );
		}
	}

	function clickNewTab() {
		/*jshint validthis:true */
		var t = this.getAttribute( 'href' ),
			c = t.replace( '#', '.' ),
			$link = jQuery( this );

		if ( typeof t === 'undefined' ) {
			return false;
		}

		$link.closest( 'li' ).addClass( 'frm-tabs active' ).siblings( 'li' ).removeClass( 'frm-tabs active starttab' );
		$link.closest( 'div' ).children( '.tabs-panel' ).not( t ).not( c ).hide();
		document.getElementById( t.replace( '#', '' ) ).style.display = 'block';

		if ( this.id === 'frm_insert_fields_tab' ) {
			clearSettingsBox();
		}
		return false;
	}

	function clickTab( link, auto ) {
		link = jQuery( link );
		var t = link.attr( 'href' );
		if ( typeof t === 'undefined' ) {
			return;
		}

		var c = t.replace( '#', '.' );
		var pro = jQuery( '.frm-category-tabs li' ).length > 2;
		link.closest( 'li' ).addClass( 'frm-tabs active' ).siblings( 'li' ).removeClass( 'frm-tabs active starttab' );
		if ( link.closest( 'div' ).find( '.tabs-panel' ).length ) {
			link.closest( 'div' ).children( '.tabs-panel' ).not( t ).not( c ).hide();
		} else {
			if ( document.getElementById( 'form_global_settings' ) !== null ) {
				/* global settings */
				var ajax = link.data( 'frmajax' );
				link.closest( '.frm_wrap' ).find( '.tabs-panel, .hide_with_tabs' ).hide();
				if ( typeof ajax !== 'undefined' && ajax == '1' ) {
					loadSettingsTab( t );
				}
			} else {
				/* form settings page */
				jQuery( '#frm-categorydiv .tabs-panel, .hide_with_tabs' ).hide();
			}
		}
		jQuery( t ).show();
		jQuery( c ).show();

		hideShortcodes();

		if ( auto !== 'auto' ) {
			// Hide success message on tab change.
			jQuery( '.frm_updated_message' ).hide();
		}

		if ( jQuery( link ).closest( '#frm_adv_info' ).length ) {
			return;
		}

		if ( jQuery( '.frm_form_settings' ).length ) {
			jQuery( '.frm_form_settings' ).attr( 'action', '?page=formidable&frm_action=settings&id=' + jQuery( '.frm_form_settings input[name="id"]' ).val() + '&t=' + t.replace( '#', '' ) );
		} else {
			jQuery( '.frm_settings_form' ).attr( 'action', '?page=formidable-settings&t=' + t.replace( '#', '' ) );
		}
	}

	/* Form Builder */
	function setupSortable( sort ) {
		var startSort = false,
			container = jQuery( '#post-body-content' );

		var opts = {
			connectWith: 'ul.frm_sorting',
			items: '> li.frm_field_box',
			placeholder: 'sortable-placeholder',
			axis: 'y',
			cancel: '.widget,.frm_field_opts_list,input,textarea,select,.edit_field_type_end_divider,.frm_sortable_field_opts,.frm_noallow',
			accepts: 'field_type_list',
			forcePlaceholderSize: false,
			tolerance: 'pointer',
			handle: '.frm-move',
			over : function(){
				this.classList.add( 'drop-me' );
			},
			out : function(){
				this.classList.remove( 'drop-me' );
			},
			receive: function( event, ui ) {
				// Receive event occurs when an item in one sortable list is dragged into another sortable list

				if ( cancelSort ) {
					ui.item.addClass( 'frm_cancel_sort' );
					return;
				}

				if ( typeof ui.item.attr( 'id' ) !== 'undefined' ) {
					if ( ui.item.attr( 'id' ).indexOf( 'frm_field_id' ) > -1 ) {
						// An existing field was dragged and dropped into, out of, or between sections
						updateFieldAfterMovingBetweenSections( ui.item );
					} else {
						// A new field was dragged into the form
						insertNewFieldByDragging( this, ui.item, opts );
					}
				}
			},
			change: function( event, ui ) {
				// don't allow some field types inside section
				if ( allowDrop( ui ) ) {
					ui.placeholder.addClass( 'sortable-placeholder' ).removeClass( 'no-drop-placeholder' );
					cancelSort = false;
				} else {
					ui.placeholder.addClass( 'no-drop-placeholder' ).removeClass( 'sortable-placeholder' );
					cancelSort = true;
				}
			},
			start: function( event, ui ) {
				if ( ui.item[0].offsetHeight > 120 ) {
					jQuery( sort ).sortable( 'refreshPositions' );
				}
				if ( ui.item[0].classList.contains( 'frm-page-collapsed' ) ) {
					// If a page if collapsed, expand it before dragging since only the page break will move.
					toggleCollapsePage( jQuery( ui.item[0] ) );
				}
			},
			helper: function( e, li ) {
				copyHelper = li.clone().insertAfter( li );
				return li.clone();
			},
			beforeStop: function( event, ui ) {
				// If this was dropped at the beginning of a collpased page, open it.
				var previous = ui.item[0].previousElementSibling;
				if ( previous !== null && previous.classList.contains( 'frm-page-collapsed' ) ) {
					toggleCollapsePage( jQuery( previous ) );
				}
			},
			stop: function( event, ui ) {
				var moving = jQuery( this );
				copyHelper && copyHelper.remove();
				if ( cancelSort ) {
					moving.sortable( 'cancel' );
				} else {
					updateFieldOrder();
				}
				moving.children( '.edit_field_type_end_divider' ).appendTo( this );
			},
			sort: function( event ) {
				container.scrollTop( function( i, v ) {
					if ( startSort === false ) {
						startSort = event.clientY;
						return v;
					}

					var moved = event.clientY - startSort;
					var h = this.offsetHeight;
					var relativePos = event.clientY - this.offsetTop;
					var y = relativePos - h / 2;
					if ( relativePos > ( h - 50 ) && moved > 5 ) {
						// scrolling down
						return v + y * 0.1;
					} else if ( relativePos < 50 && moved < -5 ) {
						//scrolling up
						return v - Math.abs( y * 0.1 );
					}
				} );
			}
		};

		jQuery( sort ).sortable( opts );

		setupFieldOptionSorting( jQuery( '#frm_builder_page' ) );
	}

	function setupFieldOptionSorting( sort ) {
		var opts = {
			items: '.frm_sortable_field_opts li',
			axis: 'y',
			opacity: 0.65,
			forcePlaceholderSize: false,
			handle: '.frm-drag',
			helper: function( e, li ) {
				copyHelper = li.clone().insertAfter( li );
				return li.clone();
			},
			stop: function( e, ui ) {
				copyHelper && copyHelper.remove();
				var fieldId = ui.item.attr( 'id' ).replace( 'frm_delete_field_', '' ).replace( '-' + ui.item.data( 'optkey' ) + '_container', '' );
				resetDisplayedOpts( fieldId );
			}
		};

		jQuery( sort ).sortable( opts );
	}

	// Get the section where a field is dropped
	function getSectionForFieldPlacement( currentItem ) {
		var section = '';
		if ( typeof currentItem !== 'undefined' ) {
			section = currentItem.closest( '.edit_field_type_divider' );
		}

		return section;
	}

	// Get the form ID where a field is dropped
	function getFormIdForFieldPlacement( section ) {
		var form_id = '';

		if ( typeof section[0] !== 'undefined' ) {
			var sDivide = section.children( '.start_divider' );
			sDivide.children( '.edit_field_type_end_divider' ).appendTo( sDivide );
			if ( typeof section.attr( 'data-formid' ) !== 'undefined' ) {
				var fieldId = section.attr( 'data-fid' );
				form_id = jQuery( 'input[name="field_options[form_select_' + fieldId + ']"]' ).val();
			}
		}

		if ( typeof form_id === 'undefined' || form_id === '' ) {
			form_id = this_form_id;
		}

		return form_id;
	}

	// Get the section ID where a field is dropped
	function getSectionIdForFieldPlacement( section ) {
		var sectionId = 0;
		if ( typeof section[0] !== 'undefined' ) {
			sectionId = section.attr( 'id' ).replace( 'frm_field_id_', '' );
		}

		return sectionId;
	}

	/**
	 * Update a field after it is dragged and dropped into, out of, or between sections
	 *
	 * @param {object} currentItem
	 */
	function updateFieldAfterMovingBetweenSections( currentItem ) {
		var fieldId = currentItem.attr( 'id' ).replace( 'frm_field_id_', '' );
		var section = getSectionForFieldPlacement( currentItem );
		var formId = getFormIdForFieldPlacement( section );
		var sectionId = getSectionIdForFieldPlacement( section );

		jQuery.ajax( {
			type: 'POST', url: ajaxurl,
			data: {
				action: 'frm_update_field_after_move',
				form_id: formId,
				field: fieldId,
				section_id: sectionId,
				nonce: frmGlobal.nonce
			},
			success: function() {
				toggleSectionHolder();
				updateInSectionValue( fieldId, sectionId );
			}
		} );
	}

	// Update the in_section field value
	function updateInSectionValue( fieldId, sectionId ) {
		document.getElementById( 'frm_in_section_' + fieldId ).value = sectionId;
	}

	/**
	 * Add a new field by dragging and dropping it from the Fields sidebar
	 *
	 * @param {object} selectedItem
	 * @param {object} fieldButton
	 * @param {object} opts
	 */
	function insertNewFieldByDragging( selectedItem, fieldButton, opts ) {
		var fieldType = fieldButton.attr( 'id' );

		// We'll optimistically disable the button now. We'll re-enable if AJAX fails
		if ( 'summary' === fieldType ) {
			var addBtn = fieldButton.children( '.frm_add_field' );
			disableSummaryBtnBeforeAJAX( addBtn, fieldButton );
		}

		var currentItem = jQuery( selectedItem ).data().uiSortable.currentItem;
		var section = getSectionForFieldPlacement( currentItem );
		var formId = getFormIdForFieldPlacement( section );
		var sectionId = getSectionIdForFieldPlacement( section );

		var loadingID = fieldType.replace( '|', '-' );
		currentItem.replaceWith( '<li class="frm-wait frmbutton_loadingnow" id="' + loadingID + '" ></li>' );

		var hasBreak = 0;
		if ( 'summary' === fieldType ) {
			// see if we need to insert a page break before this newly-added summary field. Check for at least 1 page break
			hasBreak = jQuery( '.frmbutton_loadingnow#' + loadingID ).prevAll( 'li[data-type="break"]:first' ).length > 0 ? 1 : 0;
		}

		jQuery.ajax( {
			type: 'POST', url: ajaxurl,
			data: {
				action: 'frm_insert_field',
				form_id: formId,
				field_type: fieldType,
				section_id: sectionId,
				nonce: frmGlobal.nonce,
				has_break: hasBreak,
			},
			success: function( msg ) {
				document.getElementById( 'frm_form_editor_container' ).classList.add( 'frm-has-fields' );
				jQuery( '.frmbutton_loadingnow#' + loadingID ).replaceWith( msg );
				updateFieldOrder();

				afterAddField( msg, false );
			},
			error: function( jqXHR, textStatus, errorThrown ) {
				maybeReenableSummaryBtnAfterAJAX( fieldType, addBtn, fieldButton, errorThrown );
			},
		} );
	}

	// don't allow page break, embed form, captcha, summary, or section inside section field
	function allowDrop( ui ) {
		if ( ! ui.placeholder.parent().hasClass( 'start_divider' ) ) {
			return true;
		}

		// new field
		if ( ui.item.hasClass( 'frmbutton' ) ) {
			if ( ui.item.hasClass( 'frm_tbreak' ) || ui.item.hasClass( 'frm_tform' ) || ui.item.hasClass( 'frm_tdivider' ) || ui.item.hasClass( 'frm_tdivider-repeat' ) ) {
				return false;
			}
			return true;
		}

		// moving an existing field
		return ! ( ui.item.hasClass( 'edit_field_type_break' ) || ui.item.hasClass( 'edit_field_type_form' ) ||
			ui.item.hasClass( 'edit_field_type_divider' ) );
	}

	function loadFields( field_id ) {
		var $thisField = jQuery( document.getElementById( field_id ) );
		var fields;
		if ( jQuery.isFunction( jQuery.fn.addBack ) ) {
			fields = $thisField.nextAll( "*:lt(14)" ).addBack();
		} else {
			fields = $thisField.nextAll( "*:lt(14)" ).andSelf();
		}
		fields.addClass( 'frm_load_now' );

		var h = [];
		jQuery.each( fields, function( k, v ) {
			h.push( jQuery( v ).find( '.frm_hidden_fdata' ).html() );
		} );

		jQuery.ajax( {
			type: 'POST', url: ajaxurl,
			data: {action: 'frm_load_field', field: h, form_id: this_form_id, nonce: frmGlobal.nonce},
			success: function( html ) {
				html = html.replace( /^\s+|\s+$/g, '' );
				if ( html.indexOf( '{' ) !== 0 ) {
					jQuery( '.frm_load_now' ).removeClass( '.frm_load_now' ).html( 'Error' );
					return;
				}
				html = jQuery.parseJSON( html );

				for ( var key in html ) {
					jQuery( '#frm_field_id_' + key ).replaceWith( html[key] );
					setupSortable( '#frm_field_id_' + key + '.edit_field_type_divider ul.frm_sorting' );
				}

				var $nextSet = $thisField.nextAll( '.frm_field_loading:not(.frm_load_now)' );
				if ( $nextSet.length ) {
					loadFields( $nextSet.attr( 'id' ) );
				} else {
					// go up a level
					$nextSet = jQuery( document.getElementById( 'frm-show-fields' ) ).find( '.frm_field_loading:not(.frm_load_now)' );
					if ( $nextSet.length ) {
						loadFields( $nextSet.attr( 'id' ) );
					}
				}

				initiateMultiselect();
				renumberPageBreaks();
			},
		} );
	}

	function addFieldClick() {
		/*jshint validthis:true */
		var $thisObj = jQuery( this );
		// there is no real way to disable a <a> (with a valid href attribute) in HTML - https://css-tricks.com/how-to-disable-links/
		if ( $thisObj.hasClass( 'disabled' ) ) {
			return false;
		}

		var $button = $thisObj.closest( '.frmbutton' );
		var fieldType = $button.attr( 'id' );

		var hasBreak = 0;
		if ( 'summary' === fieldType ) {
			// We'll optimistically disable $button now. We'll re-enable if AJAX fails
			disableSummaryBtnBeforeAJAX( $thisObj, $button );

			hasBreak = $newFields.children( 'li[data-type="break"]' ).length > 0 ? 1 : 0;
		}

		var form_id = this_form_id;

		jQuery.ajax( {
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'frm_insert_field',
				form_id: form_id,
				field_type: fieldType,
				section_id: 0,
				nonce: frmGlobal.nonce,
				has_break: hasBreak,
			},
			success: function( msg ) {
				document.getElementById( 'frm_form_editor_container' ).classList.add( 'frm-has-fields' );
				$newFields.append( msg );
				afterAddField( msg, true );
			},
			error: function( jqXHR, textStatus, errorThrown ) {
				maybeReenableSummaryBtnAfterAJAX( fieldType, $thisObj, $button, errorThrown );
			},
		} );
		return false;
	}

	function disableSummaryBtnBeforeAJAX( addBtn, fieldButton ) {
		addBtn.addClass( 'disabled' );
		fieldButton.draggable( 'disable' );
	}

	function reenableAddSummaryBtn() {
		var frmBtn = jQuery( 'li#summary' );
		var addFieldLink = frmBtn.children( '.frm_add_field' );
		frmBtn.draggable( 'enable' );
		addFieldLink.removeClass( 'disabled' );
	}

	function maybeDisableAddSummaryBtn() {
		if ( formHasSummaryField() ) {
			disableAddSummaryBtn();
		}
	}

	function disableAddSummaryBtn() {
		var frmBtn = jQuery( 'li#summary' );
		var addFieldLink = frmBtn.children( '.frm_add_field' );
		frmBtn.draggable( 'disable' );
		addFieldLink.addClass( 'disabled' );
	}

	function maybeReenableSummaryBtnAfterAJAX( fieldType, addBtn, fieldButton, errorThrown ) {
		alert( errorThrown + '. Please try again.' );
		if ( 'summary' === fieldType ) {
			addBtn.removeClass( 'disabled' );
			fieldButton.draggable( 'enable' );
		}
	}

	function formHasSummaryField() {
		// .edit_field_type_summary is a better selector here in order to also cover fields loaded by AJAX
		return $newFields.children( 'li.edit_field_type_summary' ).length > 0;
	}

	function duplicateField() {
		/*jshint validthis:true */
		var thisField = jQuery( this ).closest( 'li' );
		var field_id = thisField.data( 'fid' );
		var children = fieldsInSection( field_id );

		if ( thisField.hasClass( 'frm-section-collapsed' ) || thisField.hasClass( 'frm-page-collapsed' ) ) {
			return false;
		}

		jQuery.ajax( {
			type: 'POST', url: ajaxurl,
			data: {
				action: 'frm_duplicate_field',
				field_id: field_id,
				form_id: this_form_id,
				children: children,
				nonce: frmGlobal.nonce,
			},
			success: function( msg ) {
				thisField.after( msg );
				updateFieldOrder();
				afterAddField( msg, false );
			},
		} );
		return false;
	}

	function afterAddField( msg, addFocus ) {
		var regex = /id="(\S+)"/,
			match = regex.exec( msg ),
			field = document.getElementById( match[1] ),
			section = '#' + match[1] + '.edit_field_type_divider ul.frm_sorting',
			$thisSection = jQuery( section ),
			toggled = false;

		setupSortable( section );

		if ( $thisSection.length ) {
			$thisSection.parent( '.frm_field_box' ).children( '.frm_no_section_fields' ).addClass( 'frm_block' );
		} else {
			var $parentSection = jQuery( field ).closest( 'ul.frm_sorting' );
			if ( $parentSection.length ) {
				toggleOneSectionHolder( $parentSection );
				toggled = true;
			}
		}

		if ( msg.indexOf( 'frm-collapse-page' ) !== -1 ) {
			renumberPageBreaks();
		}

		addClass( field, 'frm-newly-added' );
		setTimeout( function() {
			field.classList.remove( 'frm-newly-added' );
		}, 1000 );

		if ( addFocus ) {
			var bounding = field.getBoundingClientRect(),
				container = document.getElementById( 'post-body-content' ),
				inView = ( bounding.top >= 0 &&
					bounding.left >= 0 &&
					bounding.right <= ( window.innerWidth || document.documentElement.clientWidth ) &&
					bounding.bottom <= ( window.innerHeight || document.documentElement.clientHeight )
				);

			if ( ! inView ) {
				container.scroll( {
					top: container.scrollHeight,
					left: 0,
					behavior: 'smooth',
				} );
			}

			if ( toggled === false ) {
				toggleOneSectionHolder( $thisSection );
			}
		}

		deselectFields();
		initiateMultiselect();
	}

	function clearSettingsBox() {
		jQuery( '#new_fields .frm-single-settings' ).addClass( 'frm_hidden' );
		jQuery( '#frm-options-panel > .frm-single-settings' ).removeClass( 'frm_hidden' );
		deselectFields();
	}

	function deselectFields() {
		jQuery( 'li.ui-state-default.selected' ).removeClass( 'selected' );
	}

	function scrollToField( field ) {
		var newPos = field.getBoundingClientRect().top,
			container = document.getElementById( 'post-body-content' ),
			pos = container.getBoundingClientRect(),
			screenTop = pos.top;

		if ( typeof animate === 'undefined' ) {
			jQuery( container ).scrollTop(newPos);
		} else {
			// TODO: smooth scroll
			jQuery( container ).animate({scrollTop: newPos}, 500);
		}
	}

	function checkCalculationCreatedByUser() {
		var calculation = this.value;
		var warningMessage = checkMatchingParens( calculation );
		warningMessage += checkShortcodes( calculation, this );

		if ( warningMessage !== '' ) {
			alert( calculation + "\n\n" + warningMessage );
		}
	}

	/**
	 * Checks a string for parens, brackets, and curly braces and returns a message if any unmatched are found.
	 * @param formula
	 * @returns {string}
	 */
	function checkMatchingParens( formula ) {

		var stack = [],
			formula_array = formula.split( '' ),
			length = formula_array.length,
			opening = ["{", "[", "("],
			closing = {
				"}": "{",
				")": "(",
				"]": "[",
			},
			unmatchedClosing = [],
			msg = '',
			i, next, top;

		for ( i = 0; i < length; i++ ) {
			if ( opening.includes( formula_array[i] ) ) {
				stack.push( formula_array[i] );
				continue;
			}
			if ( closing.hasOwnProperty( formula_array[i] ) ) {
				top = stack.pop();
				if ( top !== closing[formula_array[i]] ) {
					unmatchedClosing.push( formula_array[i] );
				}
			}
		}

		if ( stack.length > 0 || unmatchedClosing.length > 0 ) {
			msg = frm_admin_js.unmatched_parens + '\n\n';
			return msg;
		}

		return '';
	}

	/**
	 * Checks a calculation for shortcodes that shouldn't be in it and returns a message if found.
	 * @param calculation
	 * @param inputElement
	 * @returns {string}
	 */
	function checkShortcodes( calculation, inputElement ) {
		var msg = checkNonNumericShortcodes( calculation, inputElement );
		msg += checkNonFormShortcodes( calculation );

		return msg;
	}

	/**
	 * Checks if a numeric calculation has shortcodes that output non-numeric strings and returns a message if found.
	 * @param calculation
	 *
	 * @param inputElement
	 * @returns {string}
	 */
	function checkNonNumericShortcodes( calculation, inputElement ) {

		var msg = '';

		if ( isTextCalculation( inputElement ) ) {
			return msg;
		}

		var nonNumericShortcodes = getNonNumericShortcodes();

		if ( nonNumericShortcodes.test( calculation ) ) {
			msg = frm_admin_js.text_shortcodes + "\n\n";
		}

		return msg;
	}

	/**
	 * Determines if the calculation input is from a text calculation.
	 *
	 * @param inputElement
	 */
	function isTextCalculation( inputElement ) {
		return jQuery( inputElement ).siblings( "label[for^='calc_type']" ).children( "input" ).prop( "checked" );
	}

	/**
	 * Returns a regular expression of shortcodes that can't be used in numeric calculations.
	 * @returns {RegExp}
	 */
	function getNonNumericShortcodes() {
		return /\[(date|time|email|ip)\]/;
	}

	/**
	 * Checks if a string has any shortcodes that do not belong in forms and returns a message if any are found.
	 * @param formula
	 * @returns {string}
	 */
	function checkNonFormShortcodes( formula ) {
		var nonFormShortcodes = getNonFormShortcodes(),
			msg = '';

		if ( nonFormShortcodes.test( formula ) ) {
			msg += frm_admin_js.view_shortcodes + "\n\n";
		}

		return msg;
	}

	/**
	 * Returns a regular expression of shortcodes that can't be used in forms but can be used in Views, Email
	 * Notifications, and other Formidable areas.
	 *
	 * @returns {RegExp}
	 */
	function getNonFormShortcodes() {
		return /\[id\]|\[key\]|\[if\s\w+\]|\[foreach\s\w+\]|\[created-at(\s*)?/g;
	}

	function isSummaryCalcBox( box ) {
		var list = jQuery( box ).find( '.frm_code_list' );
		return 1 === list.length && list.hasClass( 'frm_js_summary_list' );
	}

	function extractExcludedOptions( exclude ) {
		var opts = [];
		for ( var i = 0; i < exclude.length; i++ ) {
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
		var hasOption = false;
		for ( var i = 0; i < excludedOpts.length; i++ ) {
			var inputs = document.getElementsByName( getFieldOptionInputName( excludedOpts[ i ], field.fieldId ) );
			// 2nd condition checks that there's at least one non-empty value
			if ( inputs.length && jQuery( inputs[0] ).val() ) {
				hasOption = true;
				break;
			}
		}
		return hasOption;
	}

	function getFieldOptionInputName( opt, fieldId ) {
		var at = opt.indexOf( ']' );
		return 'field_options' + opt.substring( 0, at ) + '_' + fieldId + opt.substring( at );
	}

	function popCalcFields( v, force ) {
		var box, exclude, fields, i, list,
			p = jQuery( v ).closest( '.frm-single-settings' ),
			calc = p.find( '.frm-calc-field' );

		if ( ! force && ( ! calc.length || calc.val() === '' || calc.is( ':hidden' ) ) ) {
			return;
		}

		var isSummary = isSummaryCalcBox( v );

		var form_id = jQuery( 'input[name="id"]' ).val();
		var fieldId = p.find( 'input[name="frm_fields_submitted[]"]' ).val();

		if ( force ) {
			box = v;
		} else {
			box = document.getElementById( 'frm-calc-box-' + fieldId );
		}

		exclude = getExcludeArray( box, isSummary );
		var excludedOpts = extractExcludedOptions( exclude );

		fields = getFieldList();
		list = document.getElementById( 'frm-calc-list-' + fieldId );
		list.innerHTML = '';

		for ( i = 0; i < fields.length; i++ ) {
			if ( exclude.includes( fields[ i ].fieldType ) ||
				( excludedOpts.length && hasExcludedOption( fields[ i ], excludedOpts ) ) ) {
				continue;
			}

			var span = document.createElement( 'span' );
			span.appendChild( document.createTextNode( '[' + fields[i].fieldId + ']' ) );

			var a = document.createElement( 'a' );
			a.setAttribute( 'href', '#' );
			a.setAttribute( 'data-code', fields[i].fieldId );
			a.classList.add( 'frm_insert_code' );
			a.appendChild( span );
			a.appendChild( document.createTextNode( fields[i].fieldName ) );

			var li = document.createElement( 'li' );
			li.classList.add( 'frm-field-list-' + fieldId );
			li.classList.add( 'frm-field-list-' + fields[i].fieldType );
			li.appendChild( a );
			list.appendChild( li );
		}
	}

	function getExcludeArray( calcBox, isSummary ) {
		var exclude = JSON.parse( calcBox.getElementsByClassName( 'frm_code_list' )[0].getAttribute( 'data-exclude' ) );

		if ( isSummary ) {
			// includedExtras are those that are normally excluded from the summary but the form owner can choose to include,
			// when they have been chosen to be included, then they can now be manually excluded in the calc box.
			var includedExtras = getIncludedExtras();
			if ( includedExtras.length ) {
				for ( var i = 0; i < exclude.length; i++ ) {
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
		var checked = [];
		var checkboxes = document.getElementsByClassName( 'frm_include_extras_field' );

		for ( var i = 0; i < checkboxes.length; i++ ) {
			if ( checkboxes[i].checked ) {
				checked.push( checkboxes[i].value );
			}
		}

		return checked;
	}

	function rePopCalcFieldsForSummary() {
		popCalcFields( jQuery( '.frm-inline-modal.postbox:has(.frm_js_summary_list)' )[0], true );
	}

	function getFieldList() {
		var i, fields = [],
			allFields = document.querySelectorAll( 'li.frm_field_box' );

		for ( i = 0; i < allFields.length; i++ ) {
			var fieldId = allFields[ i ].getAttribute( 'data-fid' );
			if ( typeof fieldId !== 'undefined' && fieldId ) {
				fields.push( {
					'fieldId': fieldId,
					'fieldName': getPossibleValue( 'frm_name_' + fieldId ),
					'fieldType': getPossibleValue( 'field_options_type_' + fieldId ),
					'fieldKey': getPossibleValue( 'field_options_field_key_' + fieldId )
				} );
			}

			if ( i === allFields.length - 1 ) {
				return fields;
			}
		}
	}

	/**
	 * If the element doesn't exist, use a blank value.
	 */
	function getPossibleValue( id ) {
		field = document.getElementById( id );
		if ( field !== null ) {
			return field.value;
		} else {
			return '';
		}
	}

	function liveChanges() {
		/*jshint validthis:true */
		var option,
			newValue = this.value,
			changes = document.getElementById( this.getAttribute( 'data-changeme' ) ),
			att = this.getAttribute( 'data-changeatt' );

		if ( changes === null ) {
			return;
		}

		if ( att !== null ) {
			if ( changes.tagName === 'SELECT' && att === 'placeholder' ) {
				option = changes.options[0];
				if ( option.value === '' ) {
					option.innerHTML = newValue;
				} else {
					// Create a placeholder option if there are no blank values.
					addBlankSelectOption( changes, newValue );
				}
			} else if ( att === 'class' ) {
				changeFieldClass( changes, this );
			} else {
				changes.setAttribute( att, newValue );
			}
		} else if ( changes.id.indexOf( 'setup-message' ) === 0 ) {
			if ( newValue !== '' ) {
				changes.innerHTML = '<input type="text" value="" disabled />';
			}
		} else {
			changes.innerHTML = newValue;
		}
	}

	function toggleInvalidMsg() {
		/*jshint validthis:true */
		var typeDropdown, fieldType,
			fieldId = this.id.replace( 'frm_format_', '' ),
			hasValue = this.value !== '';

		typeDropdown = document.getElementsByName( 'field_options[type_' + fieldId + ']' )[0];
		fieldType = typeDropdown.options[typeDropdown.selectedIndex].value;

		if ( fieldType === 'text' ) {
			toggleValidationBox( hasValue, '.frm_invalid_msg' + fieldId );
		}
	}

	function markRequired() {
		/*jshint validthis:true */
		var thisid = this.id.replace( 'frm_', '' ),
			fieldId = thisid.replace( 'req_field_', '' ),
			checked = this.checked,
			label = jQuery( '#field_label_' + fieldId + ' .frm_required' );

		toggleValidationBox( checked, '.frm_required_details' + fieldId );

		if ( checked ) {
			var $reqBox = jQuery( 'input[name="field_options[required_indicator_' + fieldId + ']"]' );
			if ( $reqBox.val() === '' ) {
				$reqBox.val( '*' );
			}
			label.removeClass( 'frm_hidden' );
		} else {
			label.addClass( 'frm_hidden' );
		}
	}

	function toggleValidationBox( hasValue, messageClass ) {
		$msg = jQuery( messageClass );
		if ( hasValue ) {
			$msg.fadeIn( 'fast' ).closest( '.frm_validation_msg' ).fadeIn( 'fast' );
		} else {
			//Fade out validation options
			var v = $msg.fadeOut( 'fast' ).closest( '.frm_validation_box' ).children( ':not(' + messageClass + '):visible' ).length;
			if ( v === 0 ) {
				$msg.closest( '.frm_validation_msg' ).fadeOut( 'fast' );
			}
		}
	}

	function markUnique() {
		/*jshint validthis:true */
		var field_id = jQuery( this ).closest( '.frm-single-settings' ).data( 'fid' );
		var $thisField = jQuery( '.frm_unique_details' + field_id );
		if ( this.checked ) {
			$thisField.fadeIn( 'fast' ).closest( '.frm_validation_msg' ).fadeIn( 'fast' );
			$unqDetail = jQuery( '.frm_unique_details' + field_id + ' input' );
			if ( $unqDetail.val() === '' ) {
				$unqDetail.val( frm_admin_js.default_unique );
			}
		} else {
			var v = $thisField.fadeOut( 'fast' ).closest( '.frm_validation_box' ).children( ':not(.frm_unique_details' + field_id + '):visible' ).length;
			if ( v === 0 ) {
				$thisField.closest( '.frm_validation_msg' ).fadeOut( 'fast' );
			}
		}
	}

	//Fade confirmation field and validation option in or out
	function addConf() {
		/*jshint validthis:true */
		var field_id = jQuery( this ).closest( '.frm-single-settings' ).data( 'fid' );
		var val = jQuery( this ).val();
		var $thisField = jQuery( document.getElementById( 'frm_field_id_' + field_id ) );

		toggleValidationBox( val !== '', '.frm_conf_details' + field_id );

		if ( val !== '' ) {
			//Add default validation message if empty
			var valMsg = jQuery( '.frm_validation_box .frm_conf_details' + field_id + ' input' );
			if ( valMsg.val() === '' ) {
				valMsg.val( frm_admin_js.default_conf );
			}

			setConfirmationFieldDescriptions( field_id );

			//Add or remove class for confirmation field styling
			if ( val === 'inline' ) {
				$thisField.removeClass( 'frm_conf_below' ).addClass( 'frm_conf_inline' );
			} else if ( val === 'below' ) {
				$thisField.removeClass( 'frm_conf_inline' ).addClass( 'frm_conf_below' );
			}
			jQuery( '.frm-conf-box-' + field_id ).removeClass( 'frm_hidden' );
		} else {
			jQuery( '.frm-conf-box-' + field_id ).addClass( 'frm_hidden' );
			setTimeout( function() {
				$thisField.removeClass( 'frm_conf_inline frm_conf_below' );
			}, 200 );
		}
	}

	function setConfirmationFieldDescriptions( field_id ) {
		var fieldType = document.getElementsByName( 'field_options[type_' + field_id + ']' )[0].value;

		var fieldDescription = document.getElementById( 'field_description_' + field_id );
		var hiddenDescName = 'field_options[description_' + field_id + ']';
		var newValue = frm_admin_js['enter_' + fieldType];
		maybeSetNewDescription( fieldDescription, hiddenDescName, newValue );

		var confFieldDescription = document.getElementById( 'conf_field_description_' + field_id );
		var hiddenConfName = 'field_options[conf_desc_' + field_id + ']';
		var newConfValue = frm_admin_js['confirm_' + fieldType];
		maybeSetNewDescription( confFieldDescription, hiddenConfName, newConfValue );
	}

	function maybeSetNewDescription( descriptionDiv, hiddenName, newValue ) {
		if ( descriptionDiv.innerHTML === frm_admin_js.desc ) {

			// Set the visible description value and the hidden description value
			descriptionDiv.innerHTML = newValue;
			document.getElementsByName( hiddenName )[0].value = newValue;
		}
	}

	function initBulkOptionsOverlay() {
		/*jshint validthis:true */
		var $info = initModal( '#frm-bulk-modal', '700px' );
		if ( $info === false ) {
			return;
		}

		jQuery( '.frm-insert-preset' ).click( insertBulkPreset );

		jQuery( builderForm ).on( 'click', 'a.frm-bulk-edit-link', function( event ) {
			event.preventDefault();
			var i, key, label, content = '',
				fieldId = jQuery( this ).closest( '[data-fid]' ).data( 'fid' ),
				separate = usingSeparateValues( fieldId ),
				optList = document.getElementById( 'frm_field_' + fieldId + '_opts' ),
				opts = optList.getElementsByTagName( 'li' );

			document.getElementById( 'bulk-field-id' ).value = fieldId;

			for ( i = 0; i < opts.length; i++ ) {
				key = opts[i].getAttribute( 'data-optkey' );
				if ( key !== '000' ) {
					label = document.getElementsByName( 'field_options[options_' + fieldId + '][' + key + '][label]' )[0];
					if ( typeof label !== 'undefined' ) {
						content += label.value;
						if ( separate ) {
							content += '|' + document.getElementsByName( 'field_options[options_' + fieldId + '][' + key + '][value]' )[0].value;
						}
						content += "\r\n";
					}
				}

				if ( i >= opts.length - 1 ) {
					document.getElementById( 'frm_bulk_options' ).value = content;
				}
			}

			$info.dialog('open');

			return false;
		} );

		jQuery( '#frm-update-bulk-opts' ).click( function() {
			var fieldId = document.getElementById( 'bulk-field-id' ).value;
			this.classList.add( 'frm_loading_button' );
			frmAdminBuild.updateOpts( fieldId, document.getElementById( 'frm_bulk_options' ).value, $info );
		} );
	}

	function insertBulkPreset( event ) {
		/*jshint validthis:true */
		var opts = JSON.parse( this.getAttribute( 'data-opts' ) );
		event.preventDefault();
		document.getElementById( 'frm_bulk_options' ).value = opts.join( "\n" );
		return false;
	}

	//Add new option or "Other" option to radio/checkbox/dropdown
	function addFieldOption() {
		/*jshint validthis:true */
		var field_id = jQuery( this ).closest( '.frm-single-settings' ).data( 'fid' ),
			newOption = jQuery( '#frm_field_' + field_id + '_opts .frm_option_template' ).prop('outerHTML'),
			opt_type = jQuery( this ).data( 'opttype' ),
			optKey = 0,
			lastKey = 0,
			oldKey = '000',
			lastOpt = jQuery( '#frm_field_' + field_id + '_opts li:last' );

		if ( lastOpt.length ) {
			optKey = lastOpt.data( 'optkey');
			lastKey = parseInt( optKey );
			if ( isNaN( lastKey ) ) {
				lastKey = jQuery( '#frm_field_' + field_id + '_opts li' ).length;
				if ( document.getElementById( 'frm_delete_field_' + field_id + '-' + ( lastKey + 1 ) + '_container' ) !== null ) {
					lastKey = lastKey + 2;
				}
			}
			optKey = lastKey + 1;
		}

		//Update hidden field
		if ( opt_type === 'other' ) {
			document.getElementById( 'other_input_' + field_id ).value = 1;

			//Hide "Add Other" option now if this is radio field
			var ftype = jQuery( this ).data( 'ftype' );
			if ( ftype === 'radio' || ftype === 'select' ) {
				jQuery( this ).fadeOut( 'slow' );
			}

			var data = {
				action: 'frm_add_field_option', field_id: field_id,
				opt_key: optKey,
				opt_type: opt_type, nonce: frmGlobal.nonce
			};
			jQuery.post( ajaxurl, data, function( msg ) {
				jQuery( document.getElementById( 'frm_field_' + field_id + '_opts' ) ).append( msg );
				resetDisplayedOpts( field_id );
			} );
		} else {
			newOption = newOption.replace( new RegExp( 'optkey="' + oldKey + '"', 'g' ), 'optkey="' + optKey + '"' );
			newOption = newOption.replace( new RegExp( '-' + oldKey + '_', 'g' ), '-' + optKey + '_' );
			newOption = newOption.replace( new RegExp( '-' + oldKey + '"', 'g' ), '-' + optKey + '"' );
			newOption = newOption.replace( new RegExp( '\\[' + oldKey + '\\]', 'g' ), '[' + optKey + ']' );
			newOption = newOption.replace( 'frm_hidden frm_option_template', '' );
			jQuery( document.getElementById( 'frm_field_' + field_id + '_opts' ) ).append( newOption );
			resetDisplayedOpts( field_id );
		}
	}

	function toggleMultSel() {
		/*jshint validthis:true */
		var field_id = jQuery( this ).closest( '.frm-single-settings' ).data( 'fid' );
		toggleMultiSelect( field_id, this.value );
	}

	function toggleMultiSelect( fieldId, value ) {
		var setting = jQuery( '.frm_multiple_cont_' + fieldId );
		if ( value === 'select' ) {
			setting.fadeIn( 'fast' );
		} else {
			setting.fadeOut( 'fast' );
		}
	}

	function toggleSepValues() {
		/*jshint validthis:true */
		var field_id = jQuery( this ).closest( '.frm-single-settings' ).data( 'fid' );
		toggle( jQuery( '.field_' + field_id + '_option_key' ) );
		jQuery( '.field_' + field_id + '_option' ).toggleClass( 'frm_with_key' );
	}

	function toggleMultiselect() {
		/*jshint validthis:true */
		var dropdown = jQuery( this ).closest( 'li' ).find( '.frm_form_fields select' );
		if ( this.checked ) {
			dropdown.attr( 'multiple', 'multiple' );
		} else {
			dropdown.removeAttr( 'multiple' );
		}
	}

	/**
	 * Allow typing on form switcher click without an extra click to search.
	 */
	function focusSearchBox() {
		var searchBox = document.getElementById( 'dropform-search-input' );
		if ( searchBox !== null ) {
			setTimeout( function() {
				searchBox.focus();
			}, 100 );
		}
	}

	/**
	 * If a field is clicked in the builder, prevent inputs from changing.
	 */
	function stopFieldFocus( e ) {
		e.preventDefault();
	}

	function deleteFieldOption() {
		/*jshint validthis:true */
		var parentLi = this.parentNode;
		var parentUl = parentLi.parentNode;
		var field_id = this.getAttribute( 'data-fid' );

		jQuery( parentLi ).fadeOut( 'slow', function() {
			jQuery( parentLi ).remove();

			var hasOther = jQuery( parentUl ).find( '.frm_other_option' );
			if ( hasOther.length < 1 ) {
				document.getElementById( 'other_input_' + field_id ).value = 0;
				jQuery( '#other_button_' + field_id ).fadeIn( 'slow' );
			}
		} );
	}

	/**
	 * If a radio button is set as default, allow a click to
	 * deselect it.
	 */
	function maybeUncheckRadio( e ) {
		/*jshint validthis:true */
		var $self = jQuery( this );
		if ( $self.is( ':checked' ) ) {
			var uncheck = function() {
				setTimeout( function(){ $self.removeAttr( 'checked' ); },0 );
			};
			var unbind = function() {
				$self.unbind( 'mouseup', up );
			};
			var up = function() {
				uncheck();
				unbind();
			};
			$self.bind( 'mouseup', up );
			$self.one( 'mouseout', unbind );
		}
	}

	/**
	 * If the field option has the default text, clear it out on click.
	 */
	function maybeClearOptText() {
		/*jshint validthis:true */
		if ( this.value === frm_admin_js.new_option ) {
			this.value = '';
		}
	}

	function clickDeleteField() {
		/*jshint validthis:true */
		var confirm_msg = frm_admin_js.conf_delete,
			maybeDivider = this.parentNode.parentNode.parentNode,
			li = maybeDivider.parentNode,
			field = jQuery( this ).closest( 'li' ),
			fieldId = field.data( 'fid' );

		if ( li.classList.contains( 'frm-section-collapsed' ) || li.classList.contains( 'frm-page-collapsed' ) ) {
			return false;
		}

		// If deleting a section, add an extra message.
		if ( maybeDivider.className === 'divider_section_only' ) {
			confirm_msg += '\n\n' + frm_admin_js.conf_delete_sec;
		}

		this.setAttribute( 'data-frmverify', confirm_msg );
		this.setAttribute( 'data-deletefield', fieldId );

		confirmLinkClick( this );
		return false;
	}

	function deleteFieldConfirmed() {
		/*jshint validthis:true */
		deleteFields( this.getAttribute( 'data-deletefield' ) );
	}

	function deleteFields( fieldId ) {
		var field = jQuery( '#frm_field_id_' + fieldId );

		deleteField( fieldId );

		if ( field.hasClass( 'edit_field_type_divider' ) ) {
			field.find( 'li.frm_field_box' ).each( function() {
				//TODO: maybe delete only end section
				//if(n.hasClass('edit_field_type_end_divider')){
				deleteField( this.getAttribute( 'data-fid' ) );
				//}
			} );
		}
		toggleSectionHolder();
	}

	function deleteField( field_id ) {
		jQuery.ajax( {
			type: 'POST',
			url: ajaxurl,
			data: {action: 'frm_delete_field', field_id: field_id, nonce: frmGlobal.nonce},
			success: function( msg ) {
				var $thisField = jQuery( document.getElementById( 'frm_field_id_' + field_id ) ),
					settings = jQuery( '#frm-single-settings-' + field_id );

				// Remove settings from sidebar.
				if ( settings.is( ':visible' ) ) {
					document.getElementById( 'frm_insert_fields_tab' ).click();
				}
				settings.remove();

				$thisField.fadeOut( 'slow', function() {
					var $section = $thisField.closest( '.start_divider' );
					$thisField.remove();
					if ( $thisField.data( 'type' ) === 'break' ) {
						renumberPageBreaks();
					}
					if ( $thisField.data( 'type' ) === 'summary' ) {
						reenableAddSummaryBtn();
					}
					if ( jQuery( '#frm-show-fields li' ).length === 0 ) {
						document.getElementById( 'frm_form_editor_container' ).classList.remove( 'frm-has-fields' );
					} else if ( $section.length ) {
						toggleOneSectionHolder( $section );
					}
				} );
			}
		} );
	}

	function addFieldLogicRow() {
		/*jshint validthis:true */
		var id = jQuery( this ).closest( '.frm-single-settings' ).data( 'fid' ),
			form_id = this_form_id,
			meta_name = 0;

		if ( jQuery( '#frm_logic_row_' + id + ' .frm_logic_row' ).length > 0 ) {
			meta_name = 1 + parseInt( jQuery( '#frm_logic_row_' + id + ' .frm_logic_row:last' ).attr( 'id' ).replace( 'frm_logic_' + id + '_', '' ) );
		}
		jQuery.ajax( {
			type: 'POST', url: ajaxurl,
			data: {
				action: 'frm_add_logic_row',
				form_id: form_id,
				field_id: id,
				nonce: frmGlobal.nonce,
				meta_name: meta_name,
				fields: getFieldList()
			},
			success: function( html ) {
				jQuery( document.getElementById( 'logic_' + id ) ).fadeOut( 'slow', function() {
					var logicRow = jQuery( document.getElementById( 'frm_logic_row_' + id ) );
					logicRow.append( html );
					logicRow.closest( '.frm_logic_rows' ).fadeIn( 'slow' );
				} );
			}
		} );
		return false;
	}

	function addWatchLookupRow() {
		/*jshint validthis:true */
		var id = jQuery( this ).closest( '.frm-single-settings' ).data( 'fid' );
		var form_id = this_form_id;
		var row_key = 0;
		var lookupBlockRows = document.getElementById( 'frm_watch_lookup_block_' + id ).children;
		if ( lookupBlockRows.length > 0 ) {
			var lastRowId = lookupBlockRows[lookupBlockRows.length - 1].id;
			row_key = 1 + parseInt( lastRowId.replace( 'frm_watch_lookup_' + id + '_', '' ) );
		}

		jQuery.ajax( {
			type: 'POST', url: ajaxurl,
			data: {
				action: 'frm_add_watch_lookup_row',
				form_id: form_id,
				field_id: id,
				row_key: row_key,
				nonce: frmGlobal.nonce
			},
			success: function( newRow ) {
				var watchRowBlock = jQuery( document.getElementById( 'frm_watch_lookup_block_' + id ) );
				watchRowBlock.append( newRow );
				watchRowBlock.fadeIn( 'slow' );
			}
		} );
		return false;
	}

	function updateGetValueFieldSelection() {
		/*jshint validthis:true */
		var fieldID = this.id.replace( 'get_values_form_', '' );
		var fieldSelect = document.getElementById( 'get_values_field_' + fieldID );
		var fieldType = this.getAttribute( 'data-fieldtype' );

		if ( this.value === '' ) {
			fieldSelect.options.length = 1;
		} else {
			var formID = this.value;
			jQuery.ajax( {
				type: 'POST', url: ajaxurl,
				data: {
					action: 'frm_get_options_for_get_values_field',
					form_id: formID,
					field_type: fieldType,
					nonce: frmGlobal.nonce
				},
				success: function( fields ) {
					fieldSelect.innerHTML = fields;
				}
			} );
		}
	}

	// Clear the Watch Fields option when Lookup field switches to "Text" option
	function maybeClearWatchFields() {
		/*jshint validthis:true */
		var link, lookupBlock,
			fieldID = this.name.replace( 'field_options[data_type_', '' ).replace( ']', '' );

		if ( this.value === 'text' ) {
			lookupBlock = document.getElementById( 'frm_watch_lookup_block_' + fieldID );
			if ( lookupBlock !== null ) {
				// Clear the Watch Fields option
				lookupBlock.innerHTML = '';

				// Hide the Watch Fields row
				link = document.getElementById( 'frm_add_watch_lookup_link_' + fieldID ).parentNode;
				link.style.display = 'none';
				link.previousElementSibling.style.display = 'none';
				link.previousElementSibling.previousElementSibling.style.display = 'none';
				link.previousElementSibling.previousElementSibling.previousElementSibling.style.display = 'none';
			}
		}

		toggleMultiSelect( fieldID, this.value );
	}

	// Number the pages and hide/show the first page as needed.
	function renumberPageBreaks() {
		var i, containerClass,
			pages = document.getElementsByClassName( 'frm-page-num' );

		if ( pages.length > 1 ) {
			document.getElementById( 'frm-fake-page' ).style.display = 'block';
			for ( i = 0; i < pages.length; i++ ) {
				containerClass = pages[i].parentNode.parentNode.parentNode.classList;
				if ( i === 1 ) {
					// Hide previous button on page 1
					containerClass.add( 'frm-first-page' );
				} else {
					containerClass.remove( 'frm-first-page' );
				}
				pages[i].innerHTML = ( i + 1 );
			}
		} else {
			document.getElementById( 'frm-fake-page' ).style.display = 'none';
		}
	}

	// The fake field works differently than real fields.
	function maybeCollapsePage() {
		/*jshint validthis:true */
		var field = jQuery( this ).closest( '.frm_field_box[data-ftype=break]' );
		if ( field.length ) {
			toggleCollapsePage( field );
		} else {
			toggleCollapseFakePage();
		}
	}

	// Find all fields in a page and hide/show them
	function toggleCollapsePage( field ) {
		var toCollapse = field.nextUntil( '.frm_field_box[data-ftype=break]' );
		togglePage( field, toCollapse );
	}

	function toggleCollapseFakePage() {
		var topLevel = document.getElementById( 'frm-fake-page' ),
			firstField = document.getElementById( 'frm-show-fields' ).firstElementChild,
			toCollapse = jQuery( firstField ).nextUntil( '.frm_field_box[data-ftype=break]' ).andSelf();

		if ( firstField.getAttribute( 'data-ftype' ) === 'break' ) {
			// Don't collapse if the first field is a page break.
			return;
		}

		togglePage( jQuery( topLevel ), toCollapse );
	}

	function togglePage( field, toCollapse ) {
		var i,
			fieldCount = toCollapse.length,
			slide = Math.min( fieldCount, 3 );

		if ( field.hasClass( 'frm-page-collapsed' ) ) {
			field.removeClass( 'frm-page-collapsed' );
			toCollapse.removeClass( 'frm-is-collapsed' );
			for ( i = 0; i < slide; i++ ) {
				if ( i == slide - 1 ) {
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
				if ( i == slide - 1 ) {
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
		var parentCont = this.parentNode.parentNode.parentNode.parentNode;

		parentCont.classList.toggle( 'frm-section-collapsed' );
	}

	function maybeCollapseSettings() {
		/*jshint validthis:true */
		this.classList.toggle( 'frm-collapsed' );
	}

	function clickLabel() {
		/*jshint validthis:true */
		var setting = document.querySelectorAll( '[data-changeme="' + this.id + '"]' )[0],
			fieldId = this.id.replace( 'field_label_', '' ),
			fieldType = document.getElementById( 'field_options_type_' + fieldId ),
			fieldTypeName = fieldType.value;

		if ( typeof setting !== 'undefined' ) {
			if ( fieldType.tagName === 'SELECT' ) {
				fieldTypeName = fieldType.options[ fieldType.selectedIndex ].text.toLowerCase();
			} else {
				fieldTypeName = fieldTypeName.replace( '_', ' ' );
			}

			fieldTypeName = normalizeFieldName( fieldTypeName );

			setTimeout( function() {
				if ( setting.value.toLowerCase() === fieldTypeName ) {
					setting.select();
				} else {
					setting.focus();
				}
			}, 50 );
		}
	}

	function clickDescription() {
		/*jshint validthis:true */
		var setting = document.querySelectorAll( '[data-changeme="' + this.id + '"]' )[0];
		if ( typeof setting !== 'undefined' ) {
			setTimeout( function() {
				setting.focus();
				autoExpandSettings( setting );
			}, 50 );
		}
	}

	function autoExpandSettings( setting ) {
		var inSection = setting.closest( '.frm-collapse-me' );
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
		var currentClass = e.target.classList;
		if ( currentClass.contains( 'frm-collapse-page' ) || currentClass.contains( 'frm-sub-label' ) ) {
			return;
		}

		if ( this.closest( '.start_divider' ) !== null ) {
			e.stopPropagation();
		}
		clickAction( this );
	}

	/**
	 * Open Advanced settings on double click.
	 */
	function openAdvanced() {
		var fieldId = this.getAttribute( 'data-fid' );
		autoExpandSettings( document.getElementById( 'field_options_field_key_' + fieldId ) );
	}

	function toggleRepeatButtons() {
		/*jshint validthis:true */
		var $thisField = jQuery( this ).closest( '.frm_field_box' );
		$thisField.find( '.repeat_icon_links' ).removeClass( 'repeat_format repeat_formatboth repeat_formattext' ).addClass( 'repeat_format' + this.value );
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
		var val = this.value;
		if ( val !== '' && ( val < 2 || val > 200 ) ) {
			alert( frm_admin_js.repeat_limit_min );
			this.value = '';
		}
	}

	function checkCheckboxSelectionsLimit() {
		/*jshint validthis:true */
		var val = this.value;
		if ( val !== '' && ( val < 1 || val > 200 ) ) {
			alert( frm_admin_js.checkbox_limit );
			this.value = '';
		}
	}

	function updateRepeatText( obj, addRemove ) {
		var $thisField = jQuery( obj ).closest( '.frm_field_box' );
		$thisField.find( '.frm_' + addRemove + '_form_row .frm_repeat_label' ).text( obj.value );
	}

	function fieldsInSection( id ) {
		var children = [];
		jQuery( document.getElementById( 'frm_field_id_' + id ) ).find( 'li.frm_field_box:not(.no_repeat_section .edit_field_type_end_divider)' ).each( function() {
			children.push( jQuery( this ).data( 'fid' ) );
		} );
		return children;
	}

	function toggleFormTax() {
		/*jshint validthis:true */
		var id = jQuery( this ).closest( '.frm-single-settings' ).data( 'fid' );
		var val = this.value;
		var $showFields = document.getElementById( 'frm_show_selected_fields_' + id );
		var $showForms = document.getElementById( 'frm_show_selected_forms_' + id );

		jQuery( $showForms ).find( 'select' ).val( '' );
		if ( val === 'form' ) {
			$showForms.style.display = 'inline';
			empty( $showFields );
		} else {
			$showFields.style.display = 'none';
			$showForms.style.display = 'none';
			getTaxOrFieldSelection( val, id );
		}

	}

	function resetOptOnChange() {
		/*jshint validthis:true */
		var field = getFieldKeyFromOpt( this ),
			thisOpt = jQuery( this ).closest( '.frm_single_option' );

		resetSingleOpt( field.fieldId, field.fieldKey, thisOpt );
	}

	function getFieldKeyFromOpt( object ) {
		var allOpts = jQuery( object ).closest( '.frm_sortable_field_opts' ),
			fieldId = allOpts.attr( 'id' ).replace( 'frm_field_', '' ).replace( '_opts', '' ),
			fieldKey = allOpts.data( 'key' );

		return {
			fieldId: fieldId,
			fieldKey: fieldKey
		};
	}

	function resetSingleOpt( fieldId, fieldKey, thisOpt ) {
		var saved, text, defaultVal, previewInput,
			optKey = thisOpt.data( 'optkey' ),
			separateValues = usingSeparateValues( fieldId ),
			single = jQuery( 'label[for="field_' + fieldKey + '-' + optKey + '"]' ),
			baseName = 'field_options[options_' + fieldId + '][' + optKey + ']';
			label = jQuery( 'input[name="' + baseName + '[label]"]' );

		if ( single.length < 1 ) {
			resetDisplayedOpts( fieldId );

			// Set the default value.
			defaultVal = thisOpt.find( 'input[name^="default_value_"]' );
			if ( defaultVal.is( ':checked' ) && label.length > 0 ) {
				jQuery( 'select[name^="item_meta[' + fieldId + ']"]' ).val( label.val() );
			}
			return;
		}

		previewInput = single.children( 'input' );

		if ( label.length < 1 ) {
			// Check for other label.
			label = jQuery( 'input[name="' + baseName + '"]' );
			saved = label.val();
		} else if ( separateValues ) {
			saved = jQuery( 'input[name="' + baseName + '[value]"]' ).val();
		} else {
			saved = label.val();
		}

		if ( label.length ) {
			// Set the displayed value.
			text = single[0].childNodes;
			text[ text.length - 1 ].nodeValue = ' ' + label.val();

			// Set saved value.
			previewInput.val( saved );

			// Set the default value.
			defaultVal = thisOpt.find( 'input[name^="default_value_"]' );
			previewInput.prop( 'checked', defaultVal.is( ':checked' ) ? true : false );
		}
	}

	function resetDisplayedOpts( fieldId ) {
		var i, opt, opts, type, placeholder, fieldInfo,
			input = jQuery( '[name^="item_meta[' + fieldId + ']"]' );

		if ( input.length < 1 ) {
			return;
		}

		if ( input.is( 'select' ) ) {
			placeholder = document.getElementById( 'frm_placeholder_' + fieldId );
			if ( placeholder !== null && placeholder.value === '' ) {
				fillDropdownOpts( input[0], { sourceID: fieldId } );
			} else {
				fillDropdownOpts( input[0], {
					sourceID: fieldId,
					placeholder: placeholder.value
				} );
			}
		} else {
			opts = getMultipleOpts( fieldId );
			type = input.attr( 'type' );
			jQuery( '#field_' + fieldId + '_inner_container > .frm_form_fields' ).html( '' );
			fieldInfo = getFieldKeyFromOpt( jQuery( '#frm_delete_field_' + fieldId + '-000_container' ) );

			for ( i = 0; i < opts.length; i++ ) {
				addRadioCheckboxOpt( type, opts[ i ], fieldId, fieldInfo.fieldKey );
			}
		}
	}

	function addRadioCheckboxOpt( type, opt, fieldId, fieldKey ) {
		var other, single,
			isOther = opt.key.indexOf( 'other' ) !== -1,
			id = 'field_' + fieldKey + '-' + opt.key,
			container = jQuery( '#field_' + fieldId + '_inner_container > .frm_form_fields' );

		other = '<input type="text" id="field_' + fieldKey + '-' + opt.key + '-otext" class="frm_other_input frm_pos_none" name="item_meta[other][' + fieldId + '][' + opt.key + ']" value="" />';

		single = '<div class="frm_' + type + ' ' + type + '" id="frm_' + type + '_' + fieldId + '-' + opt.key + '"><label for="' + id +
			'"><input type="' + type +
			'" name="item_meta[' + fieldId + ']' + ( type === 'checkbox' ? '[]' : '' ) +
			'" value="' + opt.saved + '" id="' + id + '"> ' + opt.label + '</label>' +
			( isOther ? other : '' ) +
			'</div>';

		container.append( single );
	}

	function fillDropdownOpts( field, atts ) {
		if ( field === null ) {
			return;
		}
		var sourceID = atts.sourceID,
			placeholder = atts.placeholder,
			showOther = atts.other;

		removeDropdownOpts( field );
		var opts = getMultipleOpts( sourceID ),
		hasPlaceholder = ( typeof placeholder !== 'undefined' );

		for ( var i = 0; i < opts.length; i++ ) {
			var label = opts[ i ].label,
			isOther = opts[ i ].key.indexOf( 'other' ) !== -1;

			if ( hasPlaceholder && label !== '' ) {
				addBlankSelectOption( field, placeholder );
			} else if ( hasPlaceholder ) {
				label = placeholder;
			}
			hasPlaceholder = false;

			if ( ! isOther || showOther ) {
				var opt = document.createElement( 'option' );
				opt.value = opts[ i ].saved;
				opt.innerHTML = label;
				field.appendChild( opt );
			}
		}
	}

	function addBlankSelectOption( field, placeholder ) {
		var opt = document.createElement( 'option' ),
			firstChild = field.firstChild;

		opt.value = '';
		opt.innerHTML = placeholder;
		if ( firstChild !== null ) {
			field.insertBefore( opt, firstChild );
			field.selectedIndex = 0;
		} else {
			field.appendChild( opt );
		}
	}

	function getMultipleOpts( fieldId ) {
		var i, saved, labelName, label, key, opts = [],
			optVals = jQuery( 'input[name^="field_options[options_' + fieldId + ']"]' ),
			separateValues = usingSeparateValues( fieldId );

		for ( i = 0; i < optVals.length; i++ ) {
			if ( optVals[ i ].name.indexOf( '[000]' ) > 0 || optVals[ i ].name.indexOf( '[value]' ) > 0 ) {
				continue;
			}
			saved = optVals[ i ].value;
			label = saved;
			key = optVals[ i ].name.replace( 'field_options[options_' + fieldId + '][', '' ).replace( '[label]', '' ).replace( ']', '' );

			if ( separateValues ) {
				labelName = optVals[ i ].name.replace( '[label]', '[value]' );
				saved = jQuery( 'input[name="' + labelName + '"]' ).val();
			}

			opts.push( {
				saved: saved,
				label: label,
				key: key
			} );
		}

		return opts;
	}

	function removeDropdownOpts( field ) {
		var i;
		if ( typeof field.options === 'undefined' ) {
			return;
		}

		for ( i = field.options.length - 1; i >= 0; i-- ) {
			field.remove( i );
		}
	}

	/**
	 * Is the box checked to use separate values?
	 */
	function usingSeparateValues( fieldId ) {
		var field = document.getElementById( 'separate_value_' + fieldId );
		if ( field === null ) {
			return false;
		} else {
			return field.checked;
		}
	}

	/* TODO: Is this still used? */
	function checkUniqueOpt( id, text ) {
		if ( id.indexOf( 'field_key_' ) === 0 ) {
			var a = id.split( '-' );
			jQuery.each( jQuery( 'label[id^="' + a[0] + '"]' ), function( k, v ) {
				var c = false;
				if ( !c && jQuery( v ).attr( 'id' ) != id && jQuery( v ).html() == text ) {
					c = true;
					alert( 'Saved values cannot be identical.' );
				}
			} );
		}
	}

	function setStarValues() {
		/*jshint validthis:true */
		var fieldID = this.id.replace( 'radio_maxnum_', '' );
		var container = jQuery( '#field_' + fieldID + '_inner_container .frm-star-group' );
		var fieldKey = document.getElementsByName( 'field_options[field_key_' + fieldID + ']' )[0].value;
		container.html( '' );

		var min = 1;
		var max = this.value;
		if ( min > max ) {
			max = min;
		}

		for ( var i = min; i <= max; i++ ) {
			container.append( '<input type="hidden" name="field_options[options_' + fieldID + '][' + i + ']" value="' + i + '"><input type="radio" name="item_meta[' + fieldID + ']" id="field_' + fieldKey + '-' + i + '" value="' + i + '" /><label for="field_' + fieldKey + '-' + i + '" class="star-rating"></label>' );
		}
	}

	function setScaleValues() {
		/*jshint validthis:true */
		var isMin = this.id.indexOf( 'minnum' ) !== -1;
		var fieldID = this.id.replace( 'scale_maxnum_', '' ).replace( 'scale_minnum_', '' );
		var min = this.value;
		var max = this.value;
		if ( isMin ) {
			max = document.getElementById( 'scale_maxnum_' + fieldID ).value;
		} else {
			min = document.getElementById( 'scale_minnum_' + fieldID ).value;
		}

		updateScaleValues( parseInt( min ), parseInt( max ), fieldID );
	}

	function updateScaleValues( min, max, fieldID ) {
		var container = jQuery( '#field_' + fieldID + '_inner_container .frm_form_fields' );
		container.html( '' );

		if ( min >= max ) {
			max = min + 1;
		}

		for ( var i = min; i <= max; i++ ) {
			container.append( '<div class="frm_scale"><label><input type="hidden" name="field_options[options_' + fieldID + '][' + i + ']" value="' + i + '"> <input type="radio" name="item_meta[' + fieldID + ']" value="' + i + '"> ' + i + ' </label></div>' );
		}
		container.append( '<div class="clear"></div>' );
	}

	function getFieldValues() {
		/*jshint validthis:true */
		var is_taxonomy,
			val = this.value;

		if ( val ) {
			var parentIDs = this.parentNode.id.replace( 'frm_logic_', '' ).split( '_' );
			var fieldID = parentIDs[0];
			var metaKey = parentIDs[1];
			var valueField = document.getElementById( 'frm_field_id_' + val );
			var valueFieldType = valueField.getAttribute( 'data-ftype' );
			var fill = document.getElementById( 'frm_show_selected_values_' + fieldID + '_' + metaKey );
			var optionName = 'field_options[hide_opt_' + fieldID + '][]';
			var optionID = 'frm_field_logic_opt_' + fieldID;
			var input = false;
			var showSelect = (valueFieldType == 'select' || valueFieldType == 'checkbox' || valueFieldType == 'radio' );
			var showText = ( valueFieldType == 'text' || valueFieldType == 'email' || valueFieldType == 'phone' || valueFieldType == 'url' || valueFieldType == 'number' );

			if ( showSelect ) {
				is_taxonomy = document.getElementById( 'frm_has_hidden_options_' + val );
				if ( is_taxonomy !== null ) {
					// get the category options with ajax
					showSelect = false;
				}
			}

			if ( showSelect || showText ) {
				fill.innerHTML = '';
				if ( showSelect ) {
					input = document.createElement( 'select' );
				} else {
					input = document.createElement( 'input' );
					input.type = 'text';
				}
				input.name = optionName;
				input.id = optionID + '_' + metaKey;
				fill.appendChild( input );

				if ( showSelect ) {
					var fillField = document.getElementById( input.id );
					fillDropdownOpts( fillField, {
						sourceID: val,
						placeholder: '',
						other: true
					} );
				}
			} else {
				var thisType = this.getAttribute( 'data-type' );
				frmGetFieldValues( val, fieldID, metaKey, thisType );
			}
		}
	}

	function getFieldSelection() {
		/*jshint validthis:true */
		var form_id = this.value;
		if ( form_id ) {
			var field_id = jQuery( this ).closest( '.frm-single-settings' ).data( 'fid' );
			getTaxOrFieldSelection( form_id, field_id );
		}
	}

	function getTaxOrFieldSelection( form_id, field_id ) {
		if ( form_id ) {
			jQuery.ajax( {
				type: 'POST', url: ajaxurl,
				data: {action: 'frm_get_field_selection', field_id: field_id, form_id: form_id, nonce: frmGlobal.nonce},
				success: function( msg ) {
					jQuery( "#frm_show_selected_fields_" + field_id ).html( msg ).show();
				}
			} );
		}
	}

	function updateFieldOrder() {
		var i;
		renumberPageBreaks();
		jQuery( '#frm-show-fields' ).each( function( i ) {
			var fields = jQuery( 'li.frm_field_box', this );
			for ( i = 0; i < fields.length; i ++ ) {
				var fieldId = fields[ i ].getAttribute( 'data-fid' ),
					field = jQuery( 'input[name="field_options[field_order_' + fieldId + ']"]' ),
					currentOrder = field.val(),
					newOrder = ( i + 1 );

				if ( currentOrder != newOrder ) {
					field.val( newOrder );
					singleField = document.getElementById( 'frm-single-settings-' + fieldId );

					moveFieldSettings( singleField );
				}
			}
		} );
	}

	function toggleSectionHolder() {
		jQuery( '.start_divider' ).each( function() {
			toggleOneSectionHolder( jQuery( this ) );
		} );
	}

	function toggleOneSectionHolder( $section ) {
		if ( $section.length === 0 ) {
			return;
		}

		var sectionFields = $section.parent( '.frm_field_box' ).children( '.frm_no_section_fields' );
		if ( $section.children( 'li' ).length < 2 ) {
			sectionFields.addClass( 'frm_block' );
		} else {
			sectionFields.removeClass( 'frm_block' );
		}
	}

	function slideDown() {
		/*jshint validthis:true */
		var id = jQuery( this ).data( 'slidedown' );
		var $thisId = jQuery( document.getElementById( id ) );
		if ( $thisId.is( ":hidden" ) ) {
			$thisId.slideDown( 'fast' );
			this.style.display = 'none';
		}
		return false;
	}

	function slideUp() {
		/*jshint validthis:true */
		var id = jQuery( this ).data( 'slideup' );
		var $thisId = jQuery( document.getElementById( id ) );
		$thisId.slideUp( 'fast' );
		$thisId.siblings( 'a' ).show();
		return false;
	}

	/**
	 * Get rid of empty container that inserts extra space.
	 */
	function hideEmptyEle() {
		jQuery( '.frm-hide-empty' ).each( function() {
		    if ( jQuery( this ).text().trim().length == 0 ) {
		        jQuery( this ).remove();
		    }
		});
	}

	/* Change the classes in the builder */
	function changeFieldClass( field, setting ) {
		var classes, replace, alignField,
			replaceWith = ' ' + setting.value,
			fieldId = field.getAttribute( 'data-fid' );

		// Include classes from multiple settings.
		if ( typeof fieldId !== 'undefined' ) {
			if ( setting.classList.contains( 'field_options_align' ) ) {
				replaceWith += ' ' + document.getElementById( 'frm_classes_' + fieldId ).value;
			} else if ( setting.classList.contains( 'frm_classes' ) ) {
				alignField = document.getElementById( 'field_options_align_' + fieldId );
				if ( alignField !== null ) {
					replaceWith += ' ' + alignField.value;
				}
			}
		}
		replaceWith += ' ';

		// Allow for the column number dropdown.
		replaceWith = replaceWith.replace( ' block ', ' ' ).replace( ' inline ', ' horizontal_radio ' ).replace( ' frm_alignright ', ' ' );

		classes = field.className.split( ' frmstart ' )[1].split( ' frmend ' )[0];
		if ( classes.trim() === '' ) {
			replace = ' frmstart  frmend ';
			replaceWith = ' frmstart ' + replaceWith.trim() + ' frmend ';
		} else {
			replace = classes.trim();
			replaceWith = replaceWith.trim();
		}
		field.className = field.className.replace( replace, replaceWith );
	}

	function maybeShowInlineModal( e ) {
		/*jshint validthis:true */
		e.preventDefault();
		showInlineModal( this );
	}

	function showInlineModal( icon, input ) {
		var box = document.getElementById( icon.getAttribute( 'data-open' ) ),
			container = jQuery( icon ).closest( 'p' ),
			pos = icon.getBoundingClientRect(),
			parentPos = box.parentNode.getBoundingClientRect(),
			inputTrigger = ( typeof input !== 'undefined' );

		if ( container.hasClass( 'frm-open' ) ) {
			container.removeClass( 'frm-open' );
			box.classList.add( 'frm_hidden' );
		} else {
			if ( ! inputTrigger ) {
				input = getInputForIcon( icon );
			}
			if ( input !== null ) {
				if ( ! inputTrigger ) {
					input.focus();
				}
				container.after( box );
				box.setAttribute( 'data-fills', input.id );

				if ( box.id.indexOf( 'frm-calc-box' ) === 0 ) {
					popCalcFields( box, true );
				}
			}

			container.addClass( 'frm-open' );
			box.classList.remove( 'frm_hidden' );
		}
	}

	function dismissInlineModal( e ) {
		/*jshint validthis:true */
		e.preventDefault();
		this.parentNode.classList.add( 'frm_hidden' );
		jQuery('.frm-open [data-open="' + this.parentNode.id + '"]').closest( '.frm-open' ).removeClass( 'frm-open' );
	}

	function changeInputtedValue() {
		/*jshint validthis:true */
		var action = this.getAttribute( 'data-frmchange' );
		this.value = this.value[ action ]();
	}

	function submitBuild() {
		/*jshint validthis:true */
		var $thisEle = jQuery( this );
		var p = $thisEle.html();

		preFormSave( this );

		var $form = jQuery( builderForm );
		var v = JSON.stringify( $form.serializeArray() );

		jQuery( document.getElementById( 'frm_compact_fields' ) ).val( v );
		jQuery.ajax( {
			type: 'POST',
			url: ajaxurl,
			data: {action: 'frm_save_form', 'frm_compact_fields': v, nonce: frmGlobal.nonce},
			success: function( msg ) {
				afterFormSave( $thisEle, p );

				var $postStuff = document.getElementById( 'post-body-content' );
				var $html = document.createElement( 'div' );
				$html.setAttribute( 'class', 'frm_updated_message' );
				$html.innerHTML = msg;
				$postStuff.insertBefore( $html, $postStuff.firstChild );
			},
			error: function( html ) {
				jQuery( document.getElementById( 'frm_js_build_form' ) ).submit();
			}
		} );
	}

	function submitNoAjax() {
		/*jshint validthis:true */
		preFormSave( this );

		var form = jQuery( builderForm );
		jQuery( document.getElementById( 'frm_compact_fields' ) ).val( JSON.stringify( form.serializeArray() ) );
		jQuery( document.getElementById( 'frm_js_build_form' ) ).submit();
	}

	function preFormSave( b ) {
		removeWPUnload();
		if ( jQuery( 'form.inplace_form' ).length ) {
			jQuery( '.inplace_save, .postbox' ).click();
		}

		$button = jQuery( b );

		if ( $button.hasClass( 'frm_button_submit' ) ) {
			$button.addClass( 'frm_loading_form' );
			$button.html( frm_admin_js.saving );
		} else {
			$button.addClass( 'frm_loading_button' );
			$button.val( frm_admin_js.saving );
		}
	}

	function afterFormSave( $button, buttonVal ) {
		$button.removeClass( 'frm_loading_form' ).removeClass( 'frm_loading_button' );
		$button.html( frm_admin_js.saved );

		setTimeout( function() {
			jQuery( '.frm_updated_message' ).fadeOut( 'slow', function() {
				this.parentNode.removeChild( this );
			} );
			$button.fadeOut( 'slow', function() {
				$button.html( buttonVal );
				$button.show();
			} );
		}, 5000 );
	}

	function initUpgradeModal() {
		var $info = initModal( '#frm_upgrade_modal' );
		if ( $info === false ) {
			return;
		}

		jQuery( document ).on( 'click', '[data-upgrade]', function( event ) {
			event.preventDefault();
			jQuery('.frm_feature_label').html( this.getAttribute( 'data-upgrade' ) );
			jQuery( '#frm_upgrade_modal h2' ).show();
			jQuery( '#frm_upgrade_modal .frm_lock_icon' ).removeClass( 'frm_lock_open_icon' );
			jQuery( '#frm_upgrade_modal .frm_lock_icon use' ).attr( 'xlink:href', '#frm_lock_icon' );

			var requires = this.getAttribute( 'data-requires' );
			if ( requires === undefined ) {
				requires = 'Pro';
			}
			jQuery( '.license-level' ).html( requires );

			// If one click upgrade, hide other content
			addOneClickModal( this );

			$info.dialog('open');

			// set the utm medium
			var button = $info.find('.button-primary:not(#frm-oneclick-button)');
			var link = button.attr('href').replace( /(medium=)[a-z_-]+/ig, '$1' + this.getAttribute( 'data-medium' ) );
			var content = this.getAttribute( 'data-content' );
			if ( content === undefined ) {
				content = '';
			}
			link = link.replace( /(content=)[a-z_-]+/ig, '$1' + content );
			button.attr( 'href', link );
			return false;
		} );
	}

	/**
	 * Allow addons to be installed from the upgrade modal.
	 */
	function addOneClickModal( link ) {
		// If one click upgrade, hide other content
		var oneclickMessage = document.getElementById( 'frm-oneclick' ),
			oneclick = link.getAttribute( 'data-oneclick' ),
			button = document.getElementById( 'frm-oneclick-button' ),
			showIt = 'block',
			hideIt = 'none';

		if ( oneclickMessage !== null && typeof oneclick !== 'undefined' && oneclick ) {
			showIt = 'none';
			hideIt = 'block';
			oneclick = JSON.parse( oneclick );

			button.className = button.className.replace( ' frm-install-addon', '' ).replace( ' frm-activate-addon', '' );
			button.className = button.className + ' ' + oneclick.class;
			button.rel = oneclick.url;
		}

		document.getElementById( 'frm-addon-status' ).style.display = 'none';
		oneclickMessage.style.display = hideIt;
		button.style.display = hideIt == 'block' ? 'inline-block' : hideIt;
		document.getElementById( 'frm-upgrade-message' ).style.display = showIt;
		document.getElementById( 'frm-upgrade-modal-link' ).style.display = showIt == 'block' ? 'inline-block' : showIt;
	}

	/* Form settings */

	function showInputIcon( parentClass ) {
		if ( typeof parentClass === 'undefined' ) {
			parentClass = '';
		}
		maybeAddFieldSelection( parentClass );
		jQuery( parentClass + ' .frm_has_shortcodes:not(.frm-with-right-icon) input,' + parentClass + ' .frm_has_shortcodes:not(.frm-with-right-icon) textarea' ).wrap( '<span class="frm-with-right-icon"></span>' ).before( '<svg class="frmsvg frm-show-box"><use xlink:href="#frm_more_horiz_solid_icon"/></svg>' );
	}

	/**
	 * For reverse compatibility. Check for fields that were
	 * using the old sidebar.
	 */
	function maybeAddFieldSelection( parentClass ) {
		var i, missingClass = jQuery( parentClass + ' :not(.frm_has_shortcodes) .frm_not_email_message, ' + parentClass + ' :not(.frm_has_shortcodes) .frm_not_email_to, ' + parentClass + ' :not(.frm_has_shortcodes) .frm_not_email_subject' );
		for ( i = 0; i < missingClass.length; i ++ ) {
			missingClass[i].parentNode.classList.add( 'frm_has_shortcodes' );
		}
	}

	function showSuccessOpt() {
		/*jshint validthis:true */
		var c = 'success';
		if ( this.name === 'options[edit_action]' ) {
			c = 'edit';
		}
		var v = jQuery( this ).val();
		jQuery( '.' + c + '_action_box' ).hide();
		if ( v === 'redirect' ) {
			jQuery( '.' + c + '_action_redirect_box.' + c + '_action_box' ).fadeIn( 'slow' );
		} else if ( v === 'page' ) {
			jQuery( '.' + c + '_action_page_box.' + c + '_action_box' ).fadeIn( 'slow' );
		} else {
			jQuery( '.' + c + '_action_message_box.' + c + '_action_box' ).fadeIn( 'slow' );
		}
	}

	function copyFormAction() {
		/*jshint validthis:true */
		var action = jQuery( this ).closest( '.frm_form_action_settings' ).clone();
		var currentID = action.attr( 'id' ).replace( 'frm_form_action_', '' );
		var newID = newActionId( currentID );
		action.find( '.frm_action_id, .frm-btn-group' ).remove();
		action.find( 'input[name$="[' + currentID + '][ID]"]' ).val( '' );
		action.find( '.widget-inside' ).hide();

		// the .html() gets original values, so they need to be set
		action.find( 'input[type=text], textarea, input[type=number]' ).prop( 'defaultValue', function() {
			return this.value;
		} );

		action.find( 'input[type=checkbox], input[type=radio]' ).prop( 'defaultChecked', function() {
			return this.checked;
		} );

		var rename = new RegExp( '\\[' + currentID + '\\]', 'g' );
		var reid = new RegExp( '_' + currentID + '"', 'g' );
		var reclass = new RegExp( '-' + currentID + '"', 'g' );
		var revalue = new RegExp( '"' + currentID + '"', 'g' ); // if a field id matches, this could cause trouble

		var html = action.html().replace( rename, '[' + newID + ']' ).replace( reid, '_' + newID + '"' );
		html = html.replace( reclass, '-' + newID + '"' ).replace( revalue, '"' + newID + '"' );
		var div = '<div id="frm_form_action_' + newID + '" class="widget frm_form_action_settings frm_single_email_settings" data-actionkey="' + newID + '">';

		jQuery( '#frm_notification_settings' ).append( div + html + '</div>' );
		initiateMultiselect();
	}

	function newActionId( currentID ) {
		var newID = parseInt( currentID ) + 11;
		var exists = document.getElementById( 'frm_form_action_' + newID );
		if ( exists !== null ) {
			newID++;
			newID = newActionId( newID );
		}
		return newID;
	}

	function addFormAction() {
		/*jshint validthis:true */
		var actionId = getNewActionId();
		var type = jQuery( this ).data( 'actiontype' );
		var formId = this_form_id;

		jQuery.ajax( {
			type: 'POST', url: ajaxurl,
			data: {
				action: 'frm_add_form_action',
				type: type,
				list_id: actionId,
				form_id: formId,
				nonce: frmGlobal.nonce
			},
			success: function( html ) {
				// Close any open actions first.
				jQuery( '.frm_form_action_settings.open' ).removeClass( 'open' );

				jQuery( '#frm_notification_settings' ).append( html );
				jQuery( '.frm_form_action_settings' ).fadeIn( 'slow' );

				var newAction = document.getElementById( 'frm_form_action_' + actionId );

				newAction.classList.add( 'open' );
				document.getElementById( 'post-body-content' ).scroll( {
					top: newAction.offsetTop + 10,
					left: 0,
					behavior: 'smooth'
				} );

				//check if icon should be active
				checkActiveAction( type );
				initiateMultiselect();
				showInputIcon( '#frm_form_action_' + actionId );
			}
		} );
	}

	function toggleActionGroups() {
		/*jshint validthis:true */
		var actions = document.getElementById( 'frm_email_addon_menu' ).classList,
			search = document.getElementById( 'actions-search-input' );

		if ( actions.contains( 'frm-all-actions' ) ) {
			actions.remove( 'frm-all-actions' );
			actions.add( 'frm-limited-actions' );
		} else {
			actions.add( 'frm-all-actions' );
			actions.remove( 'frm-limited-actions' );
		}

		// Reset search.
		search.value = '';
		triggerEvent( search, 'input' );
	}

	function getNewActionId() {
		var len = 0;
		if ( jQuery( '.frm_form_action_settings:last' ).length ) {
			//Get number of previous action
			len = jQuery( '.frm_form_action_settings:last' ).attr( 'id' ).replace( 'frm_form_action_', '' );
		}
		len = parseInt( len ) + 1;
		if ( typeof document.getElementById( 'frm_form_action_' + len ) !== 'undefined' ) {
			len = len + 100;
		}
		return len;
	}

	function clickAction( obj ) {
		var $thisobj = jQuery( obj );

		if ( obj.className.indexOf( 'selected' ) !== -1 ) {
			return;
		}
		if ( obj.className.indexOf( 'edit_field_type_end_divider' ) !== -1 && $thisobj.closest( '.edit_field_type_divider' ).hasClass( 'no_repeat_section' ) ) {
			return;
		}

		deselectFields();
		$thisobj.addClass( 'selected' );

		showFieldOptions( obj );
	}

	/**
	 * When a field is selected, show the field settings in the sidebar.
	 */
	function showFieldOptions( obj ) {
		var i, singleField,
			fieldId = obj.getAttribute( 'data-fid' ),
			allFieldSettings = document.querySelectorAll( '.frm-single-settings:not(.frm_hidden)' );

		for ( i = 0; i < allFieldSettings.length; i++ ) {
			allFieldSettings[i].classList.add( 'frm_hidden' );
		}

		singleField = document.getElementById( 'frm-single-settings-' + fieldId );
		moveFieldSettings( singleField );

		singleField.classList.remove( 'frm_hidden' );
		document.getElementById( 'frm-options-panel-tab' ).click();
	}

	/**
	 * Move the settings to the sidebar the first time they are changed or selected.
	 * Keep the end marker at the end of the form.
	 */
	function moveFieldSettings( singleField ) {
		if ( singleField === null ) {
			// The field may have not been loaded yet via ajax.
			return;
		}

		var classes = singleField.parentElement.classList;
		if ( classes.contains( 'frm_field_box' ) || classes.contains( 'divider_section_only' ) ) {
			var endMarker = document.getElementById( 'frm-end-form-marker' );
			builderForm.insertBefore( singleField, endMarker );
		}
	}

	function showEmailRow() {
		/*jshint validthis:true */
		var actionKey = jQuery( this ).closest( '.frm_form_action_settings' ).data( 'actionkey' );
		var rowType = this.getAttribute( 'data-emailrow' );

		jQuery( '#frm_form_action_' + actionKey + ' .frm_' + rowType + '_row' ).fadeIn( 'slow' );
		jQuery( this ).fadeOut( 'slow' );
	}

	function hideEmailRow() {
		/*jshint validthis:true */
		var action_box = jQuery( this ).closest( '.frm_form_action_settings' );
		var rowType = this.getAttribute( 'data-emailrow' );

		var emailRowSelector = '.frm_' + rowType + '_row';
		var emailButtonSelector = '.frm_' + rowType + '_button';

		jQuery( action_box ).find( emailButtonSelector ).fadeIn( 'slow' );
		jQuery( action_box ).find( emailRowSelector ).fadeOut( 'slow', function() {
			jQuery( action_box ).find( emailRowSelector + ' input' ).val( '' );
		} );
	}

	function checkActiveAction( type ) {
		var limit = parseInt( jQuery( '.frm_' + type + '_action' ).data( 'limit' ) );
		var len = jQuery( '.frm_single_' + type + '_settings' ).length;
		if ( len >= limit ) {
			jQuery( '.frm_' + type + '_action' ).removeClass( 'frm_active_action' ).addClass( 'frm_inactive_action' );
		} else {
			jQuery( '.frm_' + type + '_action' ).removeClass( 'frm_inactive_action' ).addClass( 'frm_active_action' );
		}
	}

	function addFormLogicRow() {
		/*jshint validthis:true */
		var id = jQuery( this ).data( 'emailkey' );
		var type = jQuery( this ).closest( '.frm_form_action_settings' ).find( '.frm_action_name' ).val();
		var meta_name = 0;
		var form_id = document.getElementById( 'form_id' ).value;
		if ( jQuery( '#frm_form_action_' + id + ' .frm_logic_row' ).length ) {
			meta_name = 1 + parseInt( jQuery( '#frm_form_action_' + id + ' .frm_logic_row:last' ).attr( 'id' ).replace( 'frm_logic_' + id + '_', '' ) );
		}
		jQuery.ajax( {
			type: 'POST', url: ajaxurl,
			data: {
				action: 'frm_add_form_logic_row',
				email_id: id,
				form_id: form_id,
				meta_name: meta_name,
				type: type,
				nonce: frmGlobal.nonce
			},
			success: function( html ) {
				jQuery( document.getElementById( 'logic_link_' + id ) ).fadeOut( 'slow', function() {
					var $logicRow = jQuery( document.getElementById( 'frm_logic_row_' + id ) );
					$logicRow.append( html );
					$logicRow.parent( '.frm_logic_rows' ).fadeIn( 'slow' );
				} );
			}
		} );
		return false;
	}

	function toggleSubmitLogic() {
		/*jshint validthis:true */
		if ( this.checked ) {
			addSubmitLogic();
		} else {
			jQuery( '.frm_logic_row_submit' ).remove();
			document.getElementById( 'frm_submit_logic_rows' ).style.display = 'none';
		}
	}

	/**
	 * Adds submit button Conditional Logic row and reveals submit button Conditional Logic
	 *
	 * @returns {boolean}
	 */
	function addSubmitLogic() {
		/*jshint validthis:true */
		var form_id = this_form_id;
		var meta_name = 0;
		if ( jQuery( '#frm_submit_logic_row .frm_logic_row' ).length > 0 ) {
			var last = jQuery( '#frm_submit_logic_row .frm_logic_row:last' );
			var submitRowID = last.attr( 'id' );
			var idFromSubmitRow = submitRowID.replace( 'frm_logic_submit_', '' );

			meta_name = 1 + parseInt( last.attr( 'id' ).replace( 'frm_logic_submit_', '' ) );
		}
		jQuery.ajax( {
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'frm_add_submit_logic_row',
				form_id: form_id,
				meta_name: meta_name,
				nonce: frmGlobal.nonce
			},
			success: function( html ) {
				var $logicRow = jQuery( document.getElementById( 'frm_submit_logic_row' ) );
				$logicRow.append( html );
				$logicRow.parent( '.frm_submit_logic_rows' ).fadeIn( 'slow' );
			}
		} );
		return false;
	}

	/**
	 *  When the user selects a field for a submit condition, update corresponding options field accordingly.
	 */
	function addSubmitLogicOpts() {
		var fieldOpt = jQuery( this );
		var field_id = fieldOpt.find( ':selected' ).val();

		if ( field_id ) {
			var row = fieldOpt.data( 'row' );
			frmGetFieldValues( field_id, 'submit', row, '', 'options[submit_conditions][hide_opt][]' );
		}
	}

	function formatEmailSetting() {
		/*jshint validthis:true */
		var val = jQuery( this ).val();
		var email = val.match( /(\s[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z0-9._-]+)/gi );
		/*if(email !== null && email.length) {
			//has email
			//TODO: add < > if they aren't there
		}*/
	}

	function maybeShowFormMessages() {
		var header = document.getElementById( 'frm_messages_header' );
		if ( showFormMessages() ) {
			header.style.display = 'block';
		} else {
			header.style.display = 'none';
		}
	}

	function showFormMessages() {
		var action = document.getElementById( 'success_action' );
		var selectedAction = action.options[action.selectedIndex].value;
		if ( selectedAction === 'message' ) {
			return true;
		}

		var show = false;
		var editable = document.getElementById( 'editable' );
		if ( editable !== null ) {
			show = editable.checked && jQuery( document.getElementById( 'edit_action' ) ).val() === 'message';
			if ( !show ) {
				show = document.getElementById( 'save_draft' ).checked;
			}
		}
		return show;
	}

	function checkDupPost() {
		/*jshint validthis:true */
		var postField = jQuery( 'select.frm_single_post_field' );
		postField.css( 'border-color', '' );
		var $t = this;
		var v = jQuery( $t ).val();
		if ( v === '' || v === 'checkbox' ) {
			return false;
		}
		postField.each( function() {
			if ( jQuery( this ).val() === v && this.name !== $t.name ) {
				this.style.borderColor = 'red';
				jQuery( $t ).val( '' );
				alert( 'Oops. You have already used that field.' );
				return false;
			}
		} );
	}

	function togglePostContent() {
		/*jshint validthis:true */
		var v = jQuery( this ).val();
		if ( '' === v ) {
			jQuery( '.frm_post_content_opt, select.frm_dyncontent_opt' ).hide().val( '' );
			jQuery( '.frm_dyncontent_opt' ).hide();
		} else if ( 'post_content' === v ) {
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
		var v = jQuery( this ).val();
		var $dyn = jQuery( document.getElementById( 'frm_dyncontent' ) );
		if ( '' === v || 'new' === v ) {
			$dyn.val( '' );
			jQuery( '.frm_dyncontent_opt' ).show();
		} else {
			jQuery.ajax( {
				type: 'POST', url: ajaxurl,
				data: {action: 'frm_display_get_content', id: v, nonce: frmGlobal.nonce},
				success: function( val ) {
					$dyn.val( val );
					jQuery( '.frm_dyncontent_opt' ).show();
				}
			} );
		}
	}

	function switchPostType() {
		/*jshint validthis:true */
		// update all rows of categories/taxonomies
		var cat_rows = document.getElementById( 'frm_posttax_rows' ).childNodes;
		var post_type = this.value;
		var cur_select;
		var new_select;

		// Get new category/taxonomy options
		jQuery.ajax( {
			type: 'POST', url: ajaxurl,
			data: {action: 'frm_replace_posttax_options', post_type: post_type, nonce: frmGlobal.nonce},
			success: function( html ) {

				// Loop through each category row, and replace the first dropdown
				for ( i = 0; i < cat_rows.length; i++ ) {
					// Check if current element is a div
					if ( cat_rows[i].tagName != 'DIV' ) {
						continue;
					}

					// Get current category select
					cur_select = cat_rows[i].getElementsByTagName( 'select' )[0];

					// Set up new select
					new_select = document.createElement( "select" );
					new_select.innerHTML = html;
					new_select.className = cur_select.className;
					new_select.name = cur_select.name;

					// Replace the old select with the new select
					cat_rows[i].replaceChild( new_select, cur_select );
				}
			}
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
		var id = jQuery( 'input[name="id"]' ).val();
		var settings = jQuery( button ).closest( '.frm_form_action_settings' );
		var key = settings.data( 'actionkey' );
		var post_type = settings.find( '.frm_post_type' ).val();

		var meta_name = 0;
		if ( jQuery( '.frm_post' + type + '_row' ).length ) {
			var name = jQuery( '.frm_post' + type + '_row:last' ).attr( 'id' ).replace( 'frm_post' + type + '_', '' );
			if ( jQuery.isNumeric( name ) ) {
				meta_name = 1 + parseInt( name );
			} else {
				meta_name = 1;
			}
		}
		jQuery.ajax( {
			type: 'POST', url: ajaxurl,
			data: {
				action: 'frm_add_post' + type + '_row', form_id: id,
				meta_name: meta_name, tax_key: meta_name,
				post_type: post_type, action_key: key, nonce: frmGlobal.nonce
			},
			success: function( html ) {
				jQuery( document.getElementById( 'frm_post' + type + '_rows' ) ).append( html );
				jQuery( '.frm_add_post' + type + '_row.button' ).hide();

				if ( type === 'meta' ) {
					jQuery( '.frm_name_value' ).show();
					jQuery( '.frm_toggle_cf_opts' ).not( ':last' ).hide();
				} else if ( type === 'tax' ) {
					jQuery( '.frm_posttax_labels' ).show();
				}
			}
		} );
	}

	function getMetaValue( id, meta_name ) {
		var new_meta = meta_name;
		if ( jQuery( document.getElementById( id + meta_name ) ).length > 0 ) {
			new_meta = getMetaValue( id, meta_name + 1 );
		}
		return new_meta;
	}

	function changePosttaxRow() {
		/*jshint validthis:true */
		if ( !jQuery( this ).closest( '.frm_posttax_row' ).find( '.frm_posttax_opt_list' ).length ) {
			return;
		}

		jQuery( this ).closest( '.frm_posttax_row' ).find( '.frm_posttax_opt_list' ).html( '<div class="spinner frm_spinner" style="display:block"></div>' );

		var post_type = jQuery( this ).closest( '.frm_form_action_settings' ).find( 'select[name$="[post_content][post_type]"]' ).val();
		var action_key = jQuery( this ).closest( '.frm_form_action_settings' ).data( 'actionkey' );
		var tax_key = jQuery( this ).closest( '.frm_posttax_row' ).attr( 'id' ).replace( 'frm_posttax_', '' );
		var meta_name = jQuery( this ).val();
		var show_exclude = jQuery( document.getElementById( tax_key + '_show_exclude' ) ).is( ':checked' ) ? 1 : 0;
		var field_id = jQuery( 'select[name$="[post_category][' + tax_key + '][field_id]"]' ).val();
		var id = jQuery( 'input[name="id"]' ).val();

		jQuery.ajax( {
			type: 'POST', url: ajaxurl,
			data: {
				action: 'frm_add_posttax_row',
				form_id: id,
				post_type: post_type,
				tax_key: tax_key,
				action_key: action_key,
				meta_name: meta_name,
				field_id: field_id,
				show_exclude: show_exclude,
				nonce: frmGlobal.nonce
			},
			success: function( html ) {
				var $tax = jQuery( document.getElementById( 'frm_posttax_' + tax_key ) );
				$tax.replaceWith( html );
			}
		} );
	}

	function toggleCfOpts() {
		/*jshint validthis:true */
		var row = jQuery( this ).closest( '.frm_postmeta_row' );
		var cancel = row.find( '.frm_cancelnew' );
		var select = row.find( '.frm_enternew' );
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

	function toggleFormOpts() {
		/*jshint validthis:true */
		var changedOpt = jQuery( this );
		var val = changedOpt.val();
		if ( changedOpt.attr( 'type' ) === 'checkbox' ) {
			if ( this.checked === false ) {
				val = '';
			}
		}

		var toggleClass = changedOpt.data( 'toggleclass' );
		if ( val === '' ) {
			jQuery( '.' + toggleClass ).hide();
		} else {
			jQuery( '.' + toggleClass ).show();
			jQuery( '.hide_' + toggleClass + '_' + val ).hide();
		}
	}

	function submitSettings() {
		/*jshint validthis:true */
		preFormSave( this );
		jQuery( '.frm_form_settings' ).submit();
	}

	/* View Functions */
	function showCount() {
		/*jshint validthis:true */
		var value = jQuery( this ).val();

		var $cont = document.getElementById( 'date_select_container' );
		var tab = document.getElementById( 'frm_listing_tab' );
		var label = tab.getAttribute( 'data-label' );
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
		var form_id = jQuery( this ).val();
		this_form_id = form_id; // set the global form id
		if ( form_id === '' ) {
			return;
		}

		jQuery.ajax( {
			type: 'POST', url: ajaxurl,
			data: {action: 'frm_get_cd_tags_box', form_id: form_id, nonce: frmGlobal.nonce},
			success: function( html ) {
				jQuery( '#frm_adv_info .categorydiv' ).html( html );
			}
		} );

		jQuery.ajax( {
			type: 'POST', url: ajaxurl,
			data: {action: 'frm_get_date_field_select', form_id: form_id, nonce: frmGlobal.nonce},
			success: function( html ) {
				jQuery( document.getElementById( 'date_select_container' ) ).html( html );
			}
		} );
	}

	function clickTabsAfterAjax() {
		/*jshint validthis:true */
		var t = jQuery( this ).attr( 'href' );
		jQuery( this ).parent().addClass( 'tabs' ).siblings( 'li' ).removeClass( 'tabs' );
		jQuery( t ).show().siblings( '.tabs-panel' ).hide();
		return false;
	}

	function clickContentTab() {
		/*jshint validthis:true */
		link = jQuery( this );
		var t = link.attr( 'href' );
		if ( typeof t === 'undefined' ) {
			return false;
		}

		var c = t.replace( '#', '.' );
		link.closest( '.nav-tab-wrapper' ).find( 'a' ).removeClass( 'nav-tab-active' );
		link.addClass( 'nav-tab-active' );
		jQuery( '.nav-menu-content' ).not( t ).not( c ).hide();
		jQuery( t + ',' + c ).show();

		return false;
	}

	function addOrderRow() {
		var l = 0;
		if ( jQuery( '#frm_order_options .frm_logic_rows div:last' ).length > 0 ) {
			l = jQuery( '#frm_order_options .frm_logic_rows div:last' ).attr( 'id' ).replace( 'frm_order_field_', '' );
		}
		jQuery.ajax( {
			type: 'POST', url: ajaxurl,
			data: {
				action: 'frm_add_order_row',
				form_id: this_form_id,
				order_key: (parseInt( l ) + 1),
				nonce: frmGlobal.nonce
			},
			success: function( html ) {
				jQuery( '#frm_order_options .frm_logic_rows' ).append( html ).show().prev( '.frm_add_order_row' ).hide();
			}
		} );
	}

	function addWhereRow() {
		var l = 0;
		if ( jQuery( '#frm_where_options .frm_logic_rows div:last' ).length ) {
			l = jQuery( '#frm_where_options .frm_logic_rows div:last' ).attr( 'id' ).replace( 'frm_where_field_', '' );
		}
		jQuery.ajax( {
			type: 'POST', url: ajaxurl,
			data: {
				action: 'frm_add_where_row',
				form_id: this_form_id,
				where_key: (parseInt( l ) + 1),
				nonce: frmGlobal.nonce
			},
			success: function( html ) {
				jQuery( '#frm_where_options .frm_logic_rows' ).append( html ).show().prev( '.frm_add_where_row' ).hide();
			}
		} );
	}

	function insertWhereOptions() {
		/*jshint validthis:true */
		var value = this.value;
		var where_key = jQuery( this ).closest( '.frm_where_row' ).attr( 'id' ).replace( 'frm_where_field_', '' );
		jQuery.ajax( {
			type: 'POST', url: ajaxurl,
			data: {action: 'frm_add_where_options', where_key: where_key, field_id: value, nonce: frmGlobal.nonce},
			success: function( html ) {
				jQuery( document.getElementById( 'where_field_options_' + where_key ) ).html( html );
			}
		} );
	}

	function hideWhereOptions() {
		/*jshint validthis:true */
		var value = this.value;
		var where_key = jQuery( this ).closest( '.frm_where_row' ).attr( 'id' ).replace( 'frm_where_field_', '' );
		if ( value === 'group_by' || value === 'group_by_newest' ) {
			document.getElementById( 'where_field_options_' + where_key ).style.display = 'none';
		} else {
			document.getElementById( 'where_field_options_' + where_key ).style.display = 'inline-block';
		}
	}

	function setDefaultPostStatus() {
		var urlQuery = window.location.search.substring( 1 );
		if ( urlQuery.indexOf( 'action=edit' ) === -1 ) {
			document.getElementById( 'post-visibility-display' ).innerHTML = frm_admin_js.private;
			document.getElementById( 'hidden-post-visibility' ).value = 'private';
			document.getElementById( 'visibility-radio-private' ).checked = true;
		}
	}

	/* Customization Panel */
	function insertCode( e ) {
		/*jshint validthis:true */
		e.preventDefault();
		insertFieldCode( jQuery( this ), this.getAttribute( 'data-code' ) );
		return false;
	}

	function insertFieldCode( element, variable ) {
		var rich = false,
			element_id = element;
		if ( typeof element === 'object' ) {
			if ( element.hasClass( 'frm_noallow' ) ) {
				return;
			}

			element_id = jQuery( element ).closest( '[data-fills]' ).attr( 'data-fills' );
			if ( typeof element_id === 'undefined' ) {
				element_id = element.closest( 'div' ).attr( 'class' );
				if ( typeof element_id !== 'undefined' ) {
					element_id = element_id.split( ' ' )[1];
				}
			}
		}

		if ( typeof element_id === 'undefined' ) {
			var active = document.activeElement;
			if ( active.type === 'search' ) {
				// If the search field has focus, find the correct field.
				element_id = active.id.replace( '-search-input', '' );
				if ( element_id.match( /\d/gi ) === null ) {
					active = jQuery( '.frm-single-settings:visible .' + element_id );
					element_id = active.attr( 'id' );
				}
			} else {
				element_id = active.id;
			}
		}

		if ( element_id ) {
			rich = jQuery( '#wp-' + element_id + '-wrap.wp-editor-wrap' ).length > 0;
		}

		var content_box = jQuery( document.getElementById( element_id ) );
		if ( typeof element.attr('data-shortcode') === 'undefined' && ( ! content_box.length || typeof content_box.attr('data-shortcode') === 'undefined' ) ) {
			// this helps to exclude those that don't want shortcode-like inserted content e.g. frm-pro's summary field
			var doShortcode = element.parents( 'ul.frm_code_list' ).attr( 'data-shortcode' );
			if ( doShortcode === 'undefined' || doShortcode !== 'no' ) {
				variable = '[' + variable + ']';
			}
		}

		if ( rich ) {
			wpActiveEditor = element_id;
			send_to_editor( variable );
			return;
		}

		if ( ! content_box.length ) {
			return false;
		}

		if ( variable === '[default-html]' || variable === '[default-plain]' ) {
			var p = 0;
			if ( variable === '[default-plain]' ) {
				p = 1;
			}
			jQuery.ajax( {
				type: 'POST', url: ajaxurl,
				data: {
					action: 'frm_get_default_html',
					form_id: jQuery( 'input[name="id"]' ).val(),
					plain_text: p,
					nonce: frmGlobal.nonce
				},
				success: function( msg ) {
					insertContent( content_box, msg );
				}
			} );
		} else {
			insertContent( content_box, variable );
		}
		return false;
	}

	function insertContent( content_box, variable ) {
		if ( document.selection ) {
			content_box[0].focus();
			document.selection.createRange().text = variable;
		} else {
			obj = content_box[0];
			var e = obj.selectionEnd;

			variable = maybeFormatInsertedContent( content_box, variable, obj.selectionStart, e );

			obj.value = obj.value.substr( 0, obj.selectionStart ) + variable + obj.value.substr( obj.selectionEnd, obj.value.length );
			var s = e + variable.length;
			obj.focus();
			obj.setSelectionRange( s, s );
		}
		content_box.change(); //trigger change
	}

	function maybeFormatInsertedContent( input, textToInsert, selectionStart, selectionEnd ) {
		var separator = input.data( 'sep' );
		if ( undefined === separator ) {
			return textToInsert;
		}

		var value = input.val();

		if ( ! value.trim().length ) {
			return textToInsert;
		}

		var startPattern = new RegExp( separator + "\\s*$" );
		var endPattern = new RegExp( "^\\s*" + separator );

		if ( value.substr( 0, selectionStart ).trim().length && false === startPattern.test( value.substr( 0, selectionStart ) ) ) {
			textToInsert = separator + textToInsert;
		}

		if ( value.substr( selectionEnd, value.length ).trim().length && false === endPattern.test( value.substr( selectionEnd, value.length ) ) ) {
			textToInsert += separator;
		}

		return textToInsert;
	}

	function resetLogicBuilder() {
		/*jshint validthis:true */
		var id = document.getElementById( 'frm-id-condition' ),
			key = document.getElementById( 'frm-key-condition' );

		if ( this.checked ) {
			id.classList.remove( 'frm_hidden' );
			key.classList.add( 'frm_hidden' );
			triggerEvent( key, 'change' );
		} else {
			id.classList.add( 'frm_hidden' );
			key.classList.remove( 'frm_hidden' );
			triggerEvent( id, 'change' );
		}
	}

	function setLogicExample() {
		var field, code,
			idKey = document.getElementById( 'frm-id-key-condition' ).checked ? 'frm-id-condition' : 'frm-key-condition',
			is = document.getElementById( 'frm-is-condition' ).value,
			text = document.getElementById( 'frm-text-condition' ).value,
			result = document.getElementById( 'frm-insert-condition' );

		idKey = document.getElementById( idKey );
		field = idKey.options[idKey.selectedIndex].value;
		code = 'if ' + field + ' ' + is + '="' + text + '"]';
		result.setAttribute( 'data-code', code + frm_admin_js.conditional_text + '[/if ' + field );
		result.innerHTML = '[' + code + '[/if ' + field + ']';
	}

	function showBuilderModal( e ) {
		/*jshint validthis:true */
		var moreIcon = getIconForInput( this );
		showInlineModal( moreIcon, this );
	}

	function maybeShowModal( input ) {
		var moreIcon;
		if ( input.parentNode.parentNode.classList.contains( 'frm_has_shortcodes' ) ) {
			hideShortcodes();
			moreIcon = getIconForInput( input );
			if ( moreIcon.tagName === 'use' ) {
				moreIcon = moreIcon.firstElementChild;
				if ( moreIcon.getAttributeNS( 'http://www.w3.org/1999/xlink', 'href' ).indexOf( 'frm_close_icon' ) === -1 ) {
					showShortcodeBox( moreIcon, 'nofocus' );
				}
			} else if ( ! moreIcon.classList.contains( 'frm_close_icon' ) ) {
				showShortcodeBox( moreIcon, 'nofocus' );
			}
		}
	}

	function showShortcodes( e ) {
		/*jshint validthis:true */
		e.preventDefault();
		e.stopPropagation();

		showShortcodeBox( this );
	}

	function showShortcodeBox( moreIcon, shouldFocus ) {
		var pos = moreIcon.getBoundingClientRect(),
			input = getInputForIcon( moreIcon ),
			box = document.getElementById( 'frm_adv_info' ),
			classes = moreIcon.className,
			parentPos = box.parentElement.getBoundingClientRect();

		if ( moreIcon.tagName === 'svg' ) {
			moreIcon = moreIcon.firstElementChild;
		}
		if ( moreIcon.tagName === 'use' ) {
			classes = moreIcon.getAttributeNS( 'http://www.w3.org/1999/xlink', 'href' );
		}

		if ( classes.indexOf( 'frm_close_icon' ) !== -1 ) {
			hideShortcodes( box );
		} else {
			box.style.top = ( pos.top - parentPos.top + 32 ) + 'px';
			box.style.left = ( pos.left - parentPos.left - 257 ) + 'px';

			jQuery( '.frm_code_list a' ).removeClass( 'frm_noallow' );
			if ( input.classList.contains( 'frm_not_email_to' ) ) {
				jQuery( '#frm-insert-fields-box .frm_code_list li:not(.show_frm_not_email_to) a' ).addClass( 'frm_noallow' );
			} else if ( input.classList.contains( 'frm_not_email_subject' ) ) {
				jQuery( '.frm_code_list li.hide_frm_not_email_subject a' ).addClass( 'frm_noallow' );
			}

			box.setAttribute( 'data-fills', input.id );
			box.style.display = 'block';

			if ( moreIcon.tagName === 'use' ) {
				moreIcon.setAttributeNS( 'http://www.w3.org/1999/xlink', 'href', '#frm_close_icon' );
			} else {
				moreIcon.className = classes.replace( 'frm_more_horiz_solid_icon', 'frm_close_icon' );
			}

			if ( shouldFocus !== 'nofocus' ) {
				input.focus();
			}
		}
	}

	/**
	 * Get the input box for the selected ... icon.
	 */
	function getInputForIcon( moreIcon ) {
		var input = moreIcon.nextElementSibling;
		if ( input !== null && input.tagName !== 'INPUT' && input.tagName !== 'TEXTAREA' ) {
			// Workaround for 1Password.
			input = input.nextElementSibling;
		}
		return input;
	}

	/**
	 * Get the ... icon for the selected input box.
	 */
	function getIconForInput( input ) {
		var moreIcon = input.previousElementSibling;
		if ( moreIcon !== null && moreIcon.tagName !== 'I' && moreIcon.tagName !== 'svg' ) {
			moreIcon = moreIcon.previousElementSibling;
		}
		return moreIcon;
	}

	function hideShortcodes( box ) {
		var i, u, closeIcons, closeSvg;
		if ( typeof box === 'undefined' ) {
			box = document.getElementById( 'frm_adv_info' );
			if ( box === null ) {
				return;
			}
		}

		if ( document.getElementById( 'frm_dyncontent' ) !== null ) {
			// Don't run when in the sidebar.
			return;
		}

		box.style.display = 'none';

		closeIcons = document.querySelectorAll( '.frm-show-box.frm_close_icon' );
		for ( i = 0; i < closeIcons.length; i++ ) {
			closeIcons[i].classList.remove( 'frm_close_icon' );
			closeIcons[i].classList.add( 'frm_more_horiz_solid_icon' );
		}

		closeSvg = document.querySelectorAll( '.frm_has_shortcodes use' );
		for ( u = 0; u < closeSvg.length; u++ ) {
			if ( closeSvg[u].getAttributeNS( 'http://www.w3.org/1999/xlink', 'href' ) === '#frm_close_icon' ) {
				closeSvg[u].setAttributeNS( 'http://www.w3.org/1999/xlink', 'href', '#frm_more_horiz_solid_icon' );
			}
		}
	}

	function initToggleShortcodes() {
		if ( typeof tinymce !== 'object' ) {
			return;
		}

		DOM = tinymce.DOM;
		if ( typeof(DOM.events) !== 'undefined' && typeof(DOM.events.add) !== 'undefined' ) {
			DOM.events.add( DOM.select( '.wp-editor-wrap' ), 'mouseover', function( e ) {
				if ( jQuery( '*:focus' ).length > 0 ) {
					return;
				}
				if ( this.id ) {
					toggleAllowedShortcodes( this.id.slice( 3, -5 ), 'focusin' );
				}
			} );
			DOM.events.add( DOM.select( '.wp-editor-wrap' ), 'mouseout', function( e ) {
				if ( jQuery( '*:focus' ).length > 0 ) {
					return;
				}
				if ( this.id ) {
					toggleAllowedShortcodes( this.id.slice( 3, -5 ), 'focusin' );
				}
			} );
		} else {
			jQuery( '#frm_dyncontent' ).on( 'mouseover mouseout', '.wp-editor-wrap', function( e ) {
				if ( jQuery( '*:focus' ).length > 0 ) {
					return;
				}
				if ( this.id ) {
					toggleAllowedShortcodes( this.id.slice( 3, -5 ), 'focusin' );
				}
			} );
		}
	}

	function toggleAllowedShortcodes( id, f ) {
		var c, clickedID;
		if ( typeof(id) === 'undefined' ) {
			id = '';
		}
		c = id;

		if ( id.indexOf( '-search-input' ) !== -1 ) {
			return;
		}

		if ( id !== '' ) {
			var $ele = jQuery( document.getElementById( id ) );
			if ( $ele.attr( 'class' ) && id !== 'wpbody-content' && id !== 'content' && id !== 'dyncontent' && id !== 'success_msg' ) {
				var d = $ele.attr( 'class' ).split( ' ' )[0];
				if ( d === 'frm_long_input' || d === 'frm_98_width' || typeof d === 'undefined' ) {
					d = '';
				} else {
					id = jQuery.trim( d );
				}
				c = c + ' ' + d;
				c = c.replace( 'widefat', '' ).replace( 'frm_with_left_label', '' );
			}
		}

		jQuery( '#frm-insert-fields-box,#frm-conditionals,#frm-adv-info-tab,#frm-dynamic-values' ).attr( 'data-fills', jQuery.trim( c ) );
		var a = [
			'content', 'wpbody-content', 'dyncontent', 'success_url',
			'success_msg', 'edit_msg', 'frm_dyncontent', 'frm_not_email_message',
			'frm_not_email_subject'
		];
		var b = [
			'before_content', 'after_content', 'frm_not_email_to',
			'dyn_default_value',
		];

		if ( jQuery.inArray( id, a ) >= 0 ) {
			jQuery( '.frm_code_list a' ).removeClass( 'frm_noallow' ).addClass( 'frm_allow' );
			jQuery( '.frm_code_list a.hide_' + id ).addClass( 'frm_noallow' ).removeClass( 'frm_allow' );
		} else if ( jQuery.inArray( id, b ) >= 0 ) {
			jQuery( '.frm_code_list:not(.frm-dropdown-menu) a:not(.show_' + id + ')' ).addClass( 'frm_noallow' ).removeClass( 'frm_allow' );
			jQuery( '.frm_code_list a.show_' + id ).removeClass('frm_noallow').addClass( 'frm_allow' );
		} else {
			jQuery( '.frm_code_list:not(.frm-dropdown-menu) a' ).addClass( 'frm_noallow' ).removeClass( 'frm_allow' );
		}

		// Automatically select a tab.
		if ( id === 'dyn_default_value' ) {
			clickedID = 'frm_dynamic_values';
			jQuery( document.getElementById( clickedID + '_tab' ) ).click();
			jQuery( '#' + clickedID.replace( /_/g, '-' ) + ' .frm_show_inactive' ).addClass( 'frm_hidden' );
			jQuery( '#' + clickedID.replace( /_/g, '-' ) + ' .frm_show_active' ).removeClass( 'frm_hidden' );
		}
	}

	function toggleAllowedHTML( input, f ) {
		var b, id = input.id;
		if ( typeof id === 'undefined' || id.indexOf( '-search-input' ) !== -1 ) {
			return;
		}

		jQuery( '#frm-adv-info-tab' ).attr( 'data-fills', jQuery.trim( id ) );
		if ( input.classList.contains( 'field_custom_html' ) ) {
			id = 'field_custom_html';
		}

		b = [ 'after_html', 'before_html', 'submit_html', 'field_custom_html' ];
		if ( jQuery.inArray( id, b ) >= 0 ) {
			jQuery( '.frm_code_list li:not(.show_' + id + ')' ).addClass( 'frm_hidden' );
			jQuery( '.frm_code_list li.show_' + id ).removeClass( 'frm_hidden' );
		}
	}

	function toggleKeyID( switch_to, e ) {
		e.stopPropagation();
		jQuery( '.frm_code_list .frmids, .frm_code_list .frmkeys' ).addClass( 'frm_hidden' );
		jQuery( '.frm_code_list .' + switch_to ).removeClass( 'frm_hidden' );
		jQuery( '.frmids, .frmkeys' ).removeClass( 'current' );
		jQuery( '.' + switch_to ).addClass( 'current' );
	}

	/* Styling */

	//function to append a new theme stylesheet with the new style changes
	function updateUICSS( locStr ) {
		if ( locStr == -1 ) {
			jQuery( 'link.ui-theme' ).remove();
			return false;
		}
		var cssLink = jQuery( '<link href="' + locStr + '" type="text/css" rel="Stylesheet" class="ui-theme" />' );
		jQuery( 'head' ).append( cssLink );

		if ( jQuery( 'link.ui-theme' ).length > 1 ) {
			jQuery( 'link.ui-theme:first' ).remove();
		}
	}

	function setPosClass() {
		/*jshint validthis:true */
		var value = this.value;
		if ( value === 'none' ) {
			value = 'top';
		} else if ( value === 'no_label' ) {
			value = 'none';
		}
		jQuery( '.frm_pos_container' ).removeClass( 'frm_top_container frm_left_container frm_right_container frm_none_container frm_inside_container' ).addClass( 'frm_' + value + '_container' );
	}

	function collapseAllSections() {
		jQuery( '.control-section.accordion-section.open' ).removeClass( 'open' );
	}

	function textSquishCheck() {
		var size = document.getElementById( 'frm_field_font_size' ).value.replace( /\D/g, '' );
		var height = document.getElementById( 'frm_field_height' ).value.replace( /\D/g, '' );
		var paddingEntered = document.getElementById( 'frm_field_pad' ).value.split( ' ' );
		var paddingCount = paddingEntered.length;

		// If too many or too few padding entries, leave now
		if ( paddingCount === 0 || paddingCount > 4 || height === '' ) {
			return;
		}

		// Get the top and bottom padding from entered values
		var paddingTop = paddingEntered[0].replace( /\D/g, '' );
		var paddingBottom = paddingTop;
		if ( paddingCount >= 3 ) {
			paddingBottom = paddingEntered[2].replace( /\D/g, '' );
		}

		// Check if there is enough space for text
		var textSpace = height - size - paddingTop - paddingBottom - 3;
		if ( textSpace < 0 ) {
			alert( frm_admin_js.css_invalid_size );
		}
	}

	/* Global settings page */
	function loadSettingsTab( anchor ) {
		var holder = anchor.replace( '#', '' );
		var holderContainer = jQuery( '.frm_' + holder + '_ajax' );
		if ( holderContainer.length ) {
			jQuery.ajax( {
				type: 'POST', url: ajaxurl,
				data: {
					'action': 'frm_settings_tab',
					'tab': holder.replace( '_settings', '' ),
					'nonce': frmGlobal.nonce
				},
				success: function( html ) {
					holderContainer.replaceWith( html );
				}
			} );
		}
	}

	function uninstallNow() {
		/*jshint validthis:true */
		if ( confirmLinkClick( this ) === true ) {
			jQuery( '.frm_uninstall .frm-wait' ).css( 'visibility', 'visible' );
			jQuery.ajax( {
				type: 'POST',
				url: ajaxurl,
				data: 'action=frm_uninstall&nonce=' + frmGlobal.nonce,
				success: function( msg ) {
					jQuery( '.frm_uninstall' ).fadeOut( 'slow' );
					window.location = msg;
				}
			} );
		}
		return false;
	}

	function saveAddonLicense() {
		/*jshint validthis:true */
		var button = jQuery( this );
		var buttonName = this.name;
		var pluginSlug = this.getAttribute( 'data-plugin' );
		var action = buttonName.replace( 'edd_' + pluginSlug + '_license_', '' );
		var license = document.getElementById( 'edd_' + pluginSlug + '_license_key' ).value;
		jQuery.ajax( {
			type: 'POST', url: ajaxurl, dataType: 'json',
			data: {action: 'frm_addon_' + action, license: license, plugin: pluginSlug, nonce: frmGlobal.nonce},
			success: function( msg ) {
				var thisRow = button.closest( '.edd_frm_license_row' );
				if ( action === 'deactivate' ) {
					license = '';
					document.getElementById( 'edd_' + pluginSlug + '_license_key' ).value = '';
				}
				thisRow.find( '.edd_frm_license' ).html( license );
				if ( msg.success === true ) {
					thisRow.find( '.frm_icon_font' ).removeClass( 'frm_hidden' );
					thisRow.find( 'div.alignleft' ).toggleClass( 'frm_hidden', 1000 );
				}

				var messageBox = thisRow.find( '.frm_license_msg' );
				messageBox.html( msg.message );
				if ( msg.message !== '' ) {
					setTimeout( function() {
						messageBox.html( '' );
					}, 15000 );
				}
			}
		} );
	}

	/* Import/Export page */

	function startFormMigration( event ) {
		event.preventDefault();

		var checkedBoxes = jQuery( '#frm_form_importer input:checked' );
		if ( checkedBoxes.length ) {

			var ids = [];
			checkedBoxes.each( function( i ) {
				ids[i] = this.value;
			} );

			// Begin the import process.
			importForms( ids );
		}
	}

	/**
	 * Begins the process of importing the forms.
	 */
	function importForms( forms ) {

		var $processSettings = jQuery( '#frm-importer-process' );

		// Display total number of forms we have to import.
		$processSettings.find( '.form-total' ).text( forms.length );
		$processSettings.find( '.form-current' ).text( '1' );

		// Hide the form select section.
		jQuery( '#frm_form_importer' ).hide();

		// Show processing status.
		$processSettings.show();
		$processSettings.find( '.process-completed' ).hide();

		// Create global import queue.
		s.importQueue = forms;
		s.imported = 0;

		// Import the first form in the queue.
		importForm();
	}

	/**
	 * Imports a single form from the import queue.
	 */
	function importForm() {
		var $processSettings = jQuery( '#frm-importer-process' ),
			formID = s.importQueue[0],
			provider = jQuery( 'input[name="slug"]' ).val(),
			data = {
				action: 'frm_import_' + provider,
				form_id: formID,
				nonce: frmGlobal.nonce
			};

		// Trigger AJAX import for this form.
		jQuery.post( ajaxurl, data, function( res ) {

			if ( res.success ) {
				var statusUpdate;

				if ( res.data.error ) {
					statusUpdate = '<p>' + res.data.name + ': ' + res.data.msg + '</p>';
				} else {
					statusUpdate = '<p>Imported <a href="' + res.data.link + '" target="_blank">' + res.data.name + '</a></p>';
				}

				$processSettings.find( '.status' ).prepend( statusUpdate );
				$processSettings.find( '.status' ).show();

				// Remove this form ID from the queue.
				s.importQueue = jQuery.grep( s.importQueue, function( value ) {
					return value != formID;
				} );
				s.imported++;

				if ( s.importQueue.length === 0 ) {
					$processSettings.find( '.process-count' ).hide();
					$processSettings.find( '.forms-completed' ).text( s.imported );
					$processSettings.find( '.process-completed' ).show();
				} else {
					// Import next form in the queue.
					$processSettings.find( '.form-current' ).text( s.imported + 1 );
					importForm();
				}
			}
		} );
	}

	function validateExport( e ) {
		/*jshint validthis:true */
		e.preventDefault();

		var s = false;
		var $exportForms = jQuery( 'input[name="frm_export_forms[]"]' );

		if ( ! jQuery( 'input[name="frm_export_forms[]"]:checked' ).val() ) {
			$exportForms.closest( '.frm-table-box' ).addClass( 'frm_blank_field' );
			s = 'stop';
		}

		var $exportType = jQuery( 'input[name="type[]"]' );
		if ( ! jQuery( 'input[name="type[]"]:checked' ).val() && $exportType.attr( 'type' ) === 'checkbox' ) {
			$exportType.closest( 'p' ).addClass( 'frm_blank_field' );
			s = 'stop';
		}

		if ( s === 'stop' ) {
			return false;
		}

		e.stopPropagation();
		this.submit();
	}

	function removeExportError() {
		/*jshint validthis:true */
		var t = jQuery( this ).closest( '.frm_blank_field' );
		if ( typeof(t) === 'undefined' ) {
			return;
		}

		var $thisName = this.name;
		if ( $thisName === 'type[]' && jQuery( 'input[name="type[]"]:checked' ).val() ) {
			t.removeClass( 'frm_blank_field' );
		} else if ( $thisName === 'frm_export_forms[]' && jQuery( this ).val() ) {
			t.removeClass( 'frm_blank_field' );
		}

	}

	function checkCSVExtension() {
		/*jshint validthis:true */
		var f = jQuery( this ).val();
		var re = /\.csv$/i;
		if ( f.match( re ) !== null ) {
			jQuery( '.show_csv' ).fadeIn();
		} else {
			jQuery( '.show_csv' ).fadeOut();
		}
	}

	function checkExportTypes() {
		/*jshint validthis:true */
		var $dropdown = jQuery( this );
		var $selected = $dropdown.find( ':selected' );
		var s = $selected.data( 'support' );

		var multiple = s.indexOf( '|' );
		jQuery( 'input[name="type[]"]' ).each( function() {
			this.checked = false;
			if ( s.indexOf( this.value ) >= 0 ) {
				this.disabled = false;
				if ( multiple == -1 ) {
					this.checked = true;
				}
			} else {
				this.disabled = true;
			}
		} );

		if ( $dropdown.val() === 'csv' ) {
			jQuery( '.csv_opts' ).show();
			jQuery( '.xml_opts' ).hide();
		} else {
			jQuery( '.csv_opts' ).hide();
			jQuery( '.xml_opts' ).show();
		}

		var c = $selected.data( 'count' );
		var exportField = jQuery( 'input[name="frm_export_forms[]"]' );
		if ( c === 'single' ) {
			exportField.prop( 'multiple', false );
			exportField.removeAttr( 'checked' );
		} else {
			exportField.prop( 'multiple', true );
			exportField.removeAttr( 'disabled' );
		}
	}

	function preventMultipleExport() {
		var type = jQuery( 'select[name=format]' ),
			selected = type.find( ':selected' ),
			count = selected.data( 'count' ),
			exportField = jQuery( 'input[name="frm_export_forms[]"]' );

		if ( count === 'single' ) {
			// Disable all other fields to prevent multiple selections.
			if ( this.checked ) {
				exportField.attr( 'disabled', true );
				this.removeAttribute( 'disabled' );
			} else {
				exportField.removeAttr( 'disabled' );
			}
		} else {
			exportField.removeAttr( 'disabled' );
		}
	}

	function initiateMultiselect() {
		jQuery( '.frm_multiselect' ).multiselect( {
			templates: {ul: '<ul class="multiselect-container frm-dropdown-menu"></ul>'},
			buttonContainer: '<div class="btn-group frm-btn-group dropdown" />',
			nonSelectedText: frm_admin_js['default'],
			onDropdownShown: function( event ) {
				var action = jQuery( event.currentTarget.closest( '.frm_form_action_settings, #frm-show-fields' ) );
				if ( action.length ) {
					jQuery( '#wpcontent' ).click( function() {
						if ( jQuery( '.multiselect-container.frm-dropdown-menu' ).is( ':visible' ) ) {
							jQuery( event.currentTarget ).removeClass( 'open' );
						}
					} );
				}
			}
		} );
	}

	/* Addons page */
	function activateAddon( e ) {
		e.preventDefault();
		installOrActivate( this, 'frm_activate_addon' );
	}

	function installAddon( e ) {
		e.preventDefault();
		installOrActivate( this, 'frm_install_addon' );
	}

	function installOrActivate( clicked, action ) {
		// Remove any leftover error messages, output an icon and get the plugin basename that needs to be activated.
		jQuery( '.frm-addon-error' ).remove();
		var button = jQuery( clicked );
		var plugin = button.attr( 'rel' );
		var el = button.parent();
		var message = el.parent().find( '.addon-status-label' );

		button.addClass('frm_loading_button');

		// Process the Ajax to perform the activation.
		jQuery.ajax( {
			url: ajaxurl,
			type: 'POST',
			async: true,
			cache: false,
			dataType: 'json',
			data: {
				action: action,
				nonce:  frmGlobal.nonce,
				plugin: plugin
			},
			success: function( response ) {
				// If there is a WP Error instance, output it here and quit the script.
				if ( response.error ) {
					addonError( response, el, button );
					return;
				}

				// If we need more credentials, output the form sent back to us.
				if ( response.form ) {
					// Display the form to gather the users credentials.

					button.append( '<div class="frm-addon-error frm_error_style">' + response.form + '</div>' );
					loader.hide();

					// Add a disabled attribute the install button if the creds are needed.
					button.attr( 'disabled', true );

					el.on( 'click', '#upgrade', 'installAddonWithCreds' );

					// No need to move further if we need to enter our creds.
					return;
				}

				afterAddonInstall( response, button, message, el );
			},
			error: function(xhr, textStatus, e) {
				button.removeClass('frm_loading_button');
			}
		} );
	}

	function installAddonWithCreds( e ) {
		// Prevent the default action, let the user know we are attempting to install again and go with it.
		e.preventDefault();

		// Now let's make another Ajax request once the user has submitted their credentials.
		var proceed = jQuery( this );
		var el = proceed.parent().parent();

		proceed.addClass( 'frm_loading_button' );

		jQuery.ajax( {
			url: ajaxurl,
			type: 'POST',
			async: true,
			cache: false,
			dataType: 'json',
			data: {
				action: 'frm_install_addon',
				nonce: frm_admin_js.nonce,
				plugin: plugin,
				hostname: el.find( '#hostname' ).val(),
				username: el.find( '#username' ).val(),
				password: el.find( '#password' ).val()
			},
			success: function( response ) {
				// If there is a WP Error instance, output it here and quit the script.
				if ( response.error ) {
					addonError( response, el, button );
					return;
				}

				if ( response.form ) {
					loader.hide();
					jQuery( '.frm-inline-error' ).remove();
					//proceed.val(monsterinsights_admin.proceed);
					//proceed.after('<span class="frm-inline-error">' + monsterinsights_admin.connect_error + '</span>');
					return;
				}

				afterAddonInstall( response, proceed, message, el );
			},
			error: function(xhr, textStatus ,e) {
				proceed.removeClass( 'frm_loading_button' );
			}
		} );
	}

	function afterAddonInstall( response, button, message, el ) {
		// The Ajax request was successful, so let's update the output.
		button.css({ 'opacity': '0' });
		message.text( frm_admin_js.active );
		jQuery( '#frm-oneclick' ).hide();
		jQuery( '#frm-addon-status' ).text( response ).show();
		jQuery( '#frm_upgrade_modal h2' ).hide();
		jQuery( '#frm_upgrade_modal .frm_lock_icon' ).addClass( 'frm_lock_open_icon' );
		jQuery( '#frm_upgrade_modal .frm_lock_icon use' ).attr( 'xlink:href', '#frm_lock_open_icon' );

		// Proceed with CSS changes
		el.parent().removeClass('frm-addon-not-installed frm-addon-installed').addClass('frm-addon-active');
		button.removeClass('frm_loading_button');
	}

	function addonError( response, el, button ) {
		el.append( '<div class="frm-addon-error frm_error_style"><p><strong>' + response.error + '</strong></p></div>' );
		button.removeClass( 'frm_loading_button' );
		jQuery( '.frm-addon-error' ).delay( 4000 ).fadeOut();
	}

	/* Templates */

	function initNewFormModal() {
		var $info = initModal( '#frm_form_modal', '650px' );
		if ( $info === false ) {
			return;
		}

		jQuery( '.frm-new-form-button' ).click( function( event ) {
			event.preventDefault();
			$info.dialog( 'open' );
		} );

		jQuery( document ).on( 'submit', '#frm-new-form', installTemplate );
	}

	function initTemplateModal() {
		var $preview = initModal( '#frm_preview_template_modal', '700px' );
		if ( $preview !== false ) {
			jQuery( '.frm-preview-template' ).click( function( event ) {
				event.preventDefault();
				var link = this.attributes.rel.value,
					cont = document.getElementById( 'frm-preview-block' );

				if ( link.indexOf( ajaxurl ) > -1 ) {
					var iframe = document.createElement( 'iframe' );
					iframe.src = link;
					iframe.height = '400';
					iframe.width = '100%';
					cont.innerHTML = '';
					cont.appendChild( iframe );
				} else {
					frmApiPreview( cont, link );
				}
				$preview.dialog( 'open' );
			} );
		}

		var $info = initModal( '#frm_template_modal', '650px' );
		if ( $info === false ) {
			return;
		}

		jQuery( '.frm-install-template' ).click( function( event ) {
			event.preventDefault();
			var oldName = jQuery( this ).closest( 'li, td' ).find( 'h3' ).html(),
				nameLabel = document.getElementById( 'frm_new_name' ),
				descLabel = document.getElementById( 'frm_new_desc' );

			document.getElementById( 'frm_template_name' ).value = oldName;
			document.getElementById( 'frm_link' ).value = this.attributes.rel.value;
			document.getElementById( 'frm_action_type' ).value = 'frm_install_template';
			nameLabel.innerHTML = nameLabel.getAttribute( 'data-form' );
			descLabel.innerHTML = descLabel.getAttribute( 'data-form' );
			$info.dialog( 'open' );
		} );

		jQuery( '.frm-build-template' ).click( function( event ) {
			event.preventDefault();
			var nameLabel = document.getElementById( 'frm_new_name' ),
				descLabel = document.getElementById( 'frm_new_desc' );

			nameLabel.innerHTML = nameLabel.getAttribute( 'data-template' );
			descLabel.innerHTML = descLabel.getAttribute( 'data-template' );
			document.getElementById( 'frm_template_name' ).value = this.getAttribute( 'data-fullname' );
			document.getElementById( 'frm_link' ).value = this.getAttribute( 'data-formid' );
			document.getElementById( 'frm_action_type' ).value = 'frm_build_template';
			$info.dialog( 'open' );
		} );

		jQuery( '.frm-new-form-button' ).click( function( event ) {
			event.preventDefault();
			var nameLabel = document.getElementById( 'frm_new_name' ),
				descLabel = document.getElementById( 'frm_new_desc' );

			nameLabel.innerHTML = nameLabel.getAttribute( 'data-form' );
			descLabel.innerHTML = descLabel.getAttribute( 'data-form' );
			document.getElementById( 'frm_template_name' ).value = '';
			document.getElementById( 'frm_link' ).value = '';
			document.getElementById( 'frm_action_type' ).value = 'frm_install_form';
			$info.dialog( 'open' );
		} );

		jQuery( document ).on( 'submit', '#frm-new-template', installTemplate );
	}

	function frmApiPreview( cont, link ) {
		cont.innerHTML = '<div class="frm-wait"></div>';
		jQuery.ajax( {
			dataType: 'json',
			url: link,
			success: function( json ) {
				var form = json.renderedHtml;
				form = form.replace( /<script\b[^<]*(js\/jquery\/jquery)[^<]*><\/script>/gi, '' );
				form = form.replace( /<link\b[^>]*(jquery-ui.min.css)[^>]*>/gi, '' );
				form = form.replace( ' frm_logic_form ', ' ' );
				form = form.replace( '<form ', '<form onsubmit="event.preventDefault();" ' );
				cont.innerHTML = '<div class="frm-wait" id="frm-remove-me"></div><div class="frm-fade" id="frm-show-me">' +
				form + '</div>';
				setTimeout( function(){
					document.getElementById( 'frm-remove-me' ).style.display = 'none';
					document.getElementById( 'frm-show-me' ).style.opacity = '1';
				}, 300 );
			}
		} );
	}

	function installTemplate( e ) {
		/*jshint validthis:true */
		var action = this.elements['type'].value,
			button = this.querySelector( 'button' );
		e.preventDefault();
		button.classList.add( 'frm_loading_button' );
		installNewForm( this, action );
	}

	function installNewForm( form, action ) {
		var data,
			formName = form.elements['template_name'].value,
			formDesc = form.elements['template_desc'].value,
			link = form.elements['link'].value;

		data = {
			action: action,
			xml: link,
			name: formName,
			desc: formDesc,
			nonce: frmGlobal.nonce
		};
		postAjax( data, function( response ) {
			if ( typeof response.redirect !== 'undefined' ) {
				window.location = response.redirect;
			} else {
				jQuery( '.spinner' ).css( 'visibility', 'hidden' );
				// TODO: show response.message
			}
		} );
	}

	function trashTemplate( e ) {
		/*jshint validthis:true */
		var id = this.getAttribute( 'data-id' );
		e.preventDefault();

		data = {
			action: 'frm_forms_trash',
			id: id,
			nonce: frmGlobal.nonce
		};
		postAjax( data, function() {
			var card = document.getElementById( 'frm-template-custom-' + id );
			fadeOut( card, function() {
				card.parentNode.removeChild( card );
			} );
		} );
	}

	function searchContent() {
		/*jshint validthis:true */
		var i,
			regEx = false,
			searchText = this.value.toLowerCase(),
			toSearch = this.getAttribute( 'data-tosearch' ),
			items = document.getElementsByClassName( toSearch );

		if ( this.tagName === 'SELECT' ) {
			searchText = selectedOptions( this );
			searchText = searchText.join('|').toLowerCase();
			regEx = true;
		}

		if ( toSearch === 'frm-action' && searchText !== '' ) {
			var addons = document.getElementById( 'frm_email_addon_menu' ).classList;
			addons.remove( 'frm-all-actions' );
			addons.add( 'frm-limited-actions' );
		}

		for ( i = 0; i < items.length; i++ ) {
			var innerText = items[i].innerText.toLowerCase();
			if ( searchText === '' ) {
				items[i].classList.remove( 'frm_hidden' );
				items[i].classList.remove( 'frm-search-result' );
			} else if ( ( regEx && new RegExp( searchText ).test( innerText ) ) || innerText.indexOf( searchText ) >= 0 ) {
				items[i].classList.remove( 'frm_hidden' );
				items[i].classList.add( 'frm-search-result' );
			} else {
				items[i].classList.add( 'frm_hidden' );
				items[i].classList.remove( 'frm-search-result' );
			}
		}
	}

	function stopPropagation( e ) {
		e.stopPropagation();
	}

	/* Helpers */

	function selectedOptions( select ) {
		var opt,
			result = [],
			options = select && select.options;

		for ( var i = 0, iLen = options.length; i < iLen; i++ ) {
			opt = options[i];

			if ( opt.selected ) {
				result.push( opt.value );
			}
		}
		return result;
	}

	function triggerEvent( element, event ) {
		var evt = document.createEvent( 'HTMLEvents' );
		evt.initEvent( event, false, true );
		element.dispatchEvent( evt );
	}

	function postAjax( data, success ) {
		var xmlHttp = new XMLHttpRequest();
		var params = typeof data == 'string' ? data : Object.keys( data ).map(
			function( k ) {
				return encodeURIComponent( k ) + '=' + encodeURIComponent( data[k] );
			}
		).join( '&' );

		xmlHttp.open( 'post', ajaxurl, true );
		xmlHttp.onreadystatechange = function() {
			if ( xmlHttp.readyState > 3 && xmlHttp.status == 200 ) {
				var response = xmlHttp.responseText;
				if ( response !== '' ) {
					response = JSON.parse( response );
				}
				success( response );
			}
		};
		xmlHttp.setRequestHeader( 'X-Requested-With', 'XMLHttpRequest' );
		xmlHttp.setRequestHeader( 'Content-type', 'application/x-www-form-urlencoded' );
		xmlHttp.send( params );
		return xmlHttp;
	}

	function fadeOut( element, success ) {
		element.classList.add( 'frm-fade' );
		setTimeout( success, 1000 );
	}

	function initModal( id, width ) {
		var $info = jQuery( id );
		if ( $info.length < 1 ) {
			return false;
		}

		if ( typeof width === 'undefined' ) {
			width = '550px';
		}
		$info.dialog( {
			dialogClass: 'frm-dialog',
			modal: true,
			autoOpen: false,
			closeOnEscape: true,
			width: width,
			resizable: false,
			draggable: false,
			open: function( event ) {
				jQuery( '.ui-dialog-titlebar' ).addClass( 'frm_hidden' ).removeClass( 'ui-helper-clearfix' );
				jQuery( '#wpwrap' ).addClass( 'frm_overlay' );
				jQuery( '.frm-dialog' ).removeClass( 'ui-widget ui-widget-content ui-corner-all' );
				jQuery( id ).removeClass( 'ui-dialog-content ui-widget-content' );

				// close dialog by clicking the overlay behind it
				jQuery( '.ui-widget-overlay, a.dismiss' ).bind( 'click', function() {
					$info.dialog( 'close' );
				} );
			},
			close: function() {
				jQuery( '#wpwrap' ).removeClass( 'frm_overlay' );
				jQuery( '.spinner' ).css( 'visibility', 'hidden' );
			}
		} );

		return $info;
	}

	function toggle( cname, id ) {
		if ( id === '#' ) {
			var cont = document.getElementById( cname );
			var hidden = cont.style.display;
			if ( hidden === 'none' ) {
				cont.style.display = 'block';
			} else {
				cont.style.display = 'none';
			}
		} else {
			var vis = cname.is( ':visible' );
			if ( vis ) {
				cname.hide();
			} else {
				cname.show();
			}
		}
	}

	function removeWPUnload() {
		window.onbeforeunload = null;
		var w = jQuery( window );
		w.off( 'beforeunload.widgets' );
		w.off( 'beforeunload.edit-post' );
	}

	function maybeChangeEmbedFormMsg() {
		var fieldId = jQuery( this ).closest( '.frm-single-settings' ).data( 'fid' );
		var fieldItem = document.getElementById( 'frm_field_id_' + fieldId );
		if ( null === fieldItem || 'form' !== fieldItem.dataset['type'] ) {
			return;
		}

		fieldItem = jQuery( fieldItem );

		if ( this.options[ this.selectedIndex ].value ) {
			fieldItem.find( '.frm-not-set' )[0].classList.add( 'frm_hidden' );
			var embedMsg = fieldItem.find( '.frm-embed-message' );
			embedMsg.html( embedMsg.data( 'embedmsg' ) + this.options[ this.selectedIndex ].text );
			fieldItem.find( '.frm-embed-field-placeholder' )[0].classList.remove( 'frm_hidden' );
		} else {
			fieldItem.find( '.frm-not-set' )[0].classList.remove( 'frm_hidden' );
			fieldItem.find( '.frm-embed-field-placeholder' )[0].classList.add( 'frm_hidden' );
		}
	}

	return {
		init: function() {
			s = {};

			// Bootstrap dropdown button
			jQuery( '.wp-admin' ).click( function( e ) {
				var t = jQuery( e.target );
				var $openDrop = jQuery( '.dropdown.open' );
				if ( $openDrop.length && ! t.hasClass( 'dropdown' ) && !t.closest( '.dropdown' ).length ) {
					$openDrop.removeClass( 'open' );
				}
			} );
			jQuery( '#frm_bs_dropdown:not(.open) a' ).click( focusSearchBox );

			if ( typeof this_form_id === 'undefined' ) {
				this_form_id = jQuery( document.getElementById( 'form_id' ) ).val();
			}

			if ( $newFields.length > 0 ) {
				// only load this on the form builder page
				frmAdminBuild.buildInit();
			} else if ( document.getElementById( 'frm_notification_settings' ) !== null ) {
				// only load on form settings page
				frmAdminBuild.settingsInit();
			} else if ( document.getElementById( 'frm_styling_form' ) !== null ) {
				// load styling settings js
				frmAdminBuild.styleInit();
			} else if ( document.getElementById( 'frm_custom_css_box' ) !== null ) {
				// load styling settings js
				frmAdminBuild.customCSSInit();
			} else if ( document.getElementById( 'form_global_settings' ) !== null ) {
				// global settings page
				frmAdminBuild.globalSettingsInit();
			} else if ( document.getElementById( 'frm_export_xml' ) !== null ) {
				// import/export page
				frmAdminBuild.exportInit();
			} else if ( document.getElementById( 'frm-templates-page' ) !== null ) {
				frmAdminBuild.templateInit();
			} else if ( document.getElementById( 'frm_dyncontent' ) !== null ) {
				// only load on views settings page
				frmAdminBuild.viewInit();
			} else {
				// New form selection page
				initNewFormModal();

				jQuery( '[data-frmprint]' ).click( function() {
					window.print();
					return false;
				} );
			}

			var $advInfo = jQuery( document.getElementById( 'frm_adv_info' ) );
			if ( $advInfo.length > 0 || jQuery( '.frm_field_list' ).length > 0 ) {
				// only load on the form, form settings, and view settings pages
				frmAdminBuild.panelInit();
			}

			loadTooltips();
			initUpgradeModal();

			// used on build, form settings, and view settings
			var $shortCodeDiv = jQuery( document.getElementById( 'frm_shortcodediv' ) );
			if ( $shortCodeDiv.length > 0 ) {
				jQuery( 'a.edit-frm_shortcode' ).click( function() {
					if ( $shortCodeDiv.is( ':hidden' ) ) {
						$shortCodeDiv.slideDown( 'fast' );
						this.style.display = 'none';
					}
					return false;
				} );

				jQuery( '.cancel-frm_shortcode', '#frm_shortcodediv' ).click( function() {
					$shortCodeDiv.slideUp( 'fast' );
					$shortCodeDiv.siblings( 'a.edit-frm_shortcode' ).show();
					return false;
				} );
			}

			// tabs
			jQuery( document ).on( 'click', '#frm-nav-tabs a', clickNewTab );
			jQuery( '.post-type-frm_display .frm-nav-tabs a, .frm-category-tabs a, #frm-templates-page .frm-nav-tabs a' ).click( function() {
				if ( ! this.classList.contains( 'frm_noallow' ) ) {
					clickTab( this );
					return false;
				}
			} );
			clickTab( jQuery( '.starttab a' ), 'auto' );

			// submit the search form with dropdown
			jQuery( '#frm-fid-search-menu a' ).click( function() {
				var val = this.id.replace( 'fid-', '' );
				jQuery( 'select[name="fid"]' ).val( val );
				jQuery( document.getElementById( 'posts-filter' ) ).submit();
				return false;
			} );

			jQuery( '.frm_select_box' ).on( 'click focus', function() {
				this.select();
			} );

			jQuery( document ).on( 'input search change', '.frm-auto-search', searchContent );
			jQuery( document ).on( 'focusin click', '.frm-auto-search', stopPropagation );
			var autoSearch = jQuery( '.frm-auto-search' );
			if ( autoSearch.val() !== '' ) {
				autoSearch.keyup();
			}

			// Initialize Formidable Connection.
			FrmFormsConnect.init();

			jQuery( document ).on( 'click', '.frm-install-addon', installAddon );
			jQuery( document ).on( 'click', '.frm-activate-addon', activateAddon );

			// prevent annoying confirmation message from WordPress
			jQuery( 'button, input[type=submit]' ).on( 'click', removeWPUnload );
		},

		buildInit: function() {
			if ( jQuery( '.frm_field_loading' ).length ) {
				var load_field_id = jQuery( '.frm_field_loading' ).first().attr( 'id' );
				loadFields( load_field_id );
			}

			setupSortable( 'ul.frm_sorting' );

			// Show message if section has no fields inside
			var frm_sorting = jQuery( '.start_divider.frm_sorting' );
			for ( i = 0; i < frm_sorting.length; i++ ) {
				if ( frm_sorting[i].children.length < 2 ) {
					jQuery( frm_sorting[i] ).parent().children( '.frm_no_section_fields' ).addClass( 'frm_block' );
				}
			}

			jQuery( '.field_type_list > li:not(.frm_noallow)' ).draggable( {
				connectToSortable: '#frm-show-fields',
				helper: 'clone',
				revert: 'invalid',
				delay: 10,
				cancel: '.frm-dropdown-menu'
			} );
			jQuery( 'ul.field_type_list, .field_type_list li, ul.frm_code_list, .frm_code_list li, .frm_code_list li a, #frm_adv_info #category-tabs li, #frm_adv_info #category-tabs li a' ).disableSelection();

			jQuery( '.frm_submit_ajax' ).click( submitBuild );
			jQuery( '.frm_submit_no_ajax' ).click( submitNoAjax );

			jQuery( 'a.edit-form-status' ).click( slideDown );
			jQuery( '.cancel-form-status' ).click( slideUp );
			jQuery( '.save-form-status' ).click( function() {
				var newStatus = jQuery( document.getElementById( 'form_change_status' ) ).val();
				jQuery( 'input[name="new_status"]' ).val( newStatus );
				jQuery( document.getElementById( 'form-status-display' ) ).html( newStatus );
				jQuery( '.cancel-form-status' ).click();
				return false;
			} );

			jQuery( '.frm_form_builder form:first' ).submit( function() {
				jQuery( '.inplace_field' ).blur();
			} );

			initiateMultiselect();
			renumberPageBreaks();

			var $builderForm = jQuery( builderForm );
			var builderArea = document.getElementById( 'frm_form_editor_container' );
			$builderForm.on( 'click', '.frm_add_logic_row', addFieldLogicRow );
			$builderForm.on( 'click', '.frm_add_watch_lookup_row', addWatchLookupRow );
			$builderForm.on( 'change', '.frm_get_values_form', updateGetValueFieldSelection );
			$builderForm.on( 'change', '.frm_logic_field_opts', getFieldValues );
			$builderForm.on( 'change', '.scale_maxnum, .scale_minnum', setScaleValues );
			$builderForm.on( 'change', '.radio_maxnum', setStarValues );

			jQuery( document.getElementById( 'frm-insert-fields' ) ).on( 'click', '.frm_add_field', addFieldClick );
			$newFields.on( 'click', '.frm_clone_field', duplicateField );
			$builderForm.on( 'change', 'input[id^="frm_calc"]', checkCalculationCreatedByUser );
			$builderForm.on( 'change', 'input.frm_format_opt', toggleInvalidMsg );
			$builderForm.on( 'change click', '[data-changeme]', liveChanges );
			$builderForm.on( 'click', 'input.frm_req_field', markRequired );
			$builderForm.on( 'click', '.frm_mark_unique', markUnique );

			$builderForm.on( 'change', '.frm_repeat_format', toggleRepeatButtons );
			$builderForm.on( 'change', '.frm_repeat_limit', checkRepeatLimit );
			$builderForm.on( 'change', '.frm_js_checkbox_limit', checkCheckboxSelectionsLimit );
			$builderForm.on( 'input', 'input[name^="field_options[add_label_"]', function() {
				updateRepeatText( this, 'add' );
			} );
			$builderForm.on( 'input', 'input[name^="field_options[remove_label_"]', function() {
				updateRepeatText( this, 'remove' );
			} );
			$builderForm.on( 'change', 'select[name^="field_options[data_type_"]', maybeClearWatchFields );
			jQuery( builderArea ).on( 'click', '.frm-collapse-page', maybeCollapsePage );
			jQuery( builderArea ).on( 'click', '.frm-collapse-section', maybeCollapseSection );
			$builderForm.on( 'click', '.frm-single-settings h3', maybeCollapseSettings );

			$builderForm.on( 'click', '.frm_toggle_sep_values', toggleSepValues );
			$builderForm.on( 'click', '.frm_multiselect_opt', toggleMultiselect );
			$newFields.on( 'mousedown', 'input, textarea, select', stopFieldFocus );
			$newFields.on( 'click', 'input[type=radio], input[type=checkbox]', stopFieldFocus );
			$newFields.on( 'click', '.frm_delete_field', clickDeleteField );
			$builderForm.on( 'click', '.frm_single_option a[data-removeid]', deleteFieldOption );
			$builderForm.on( 'mousedown', '.frm_single_option input[type=radio]', maybeUncheckRadio );
			$builderForm.on( 'focusin', '.frm_single_option input[type=text]', maybeClearOptText );
			$builderForm.on( 'click', '.frm_add_opt', addFieldOption );
			$builderForm.on( 'change', '.frm_single_option input', resetOptOnChange );
			$builderForm.on( 'change', '.frm_toggle_mult_sel', toggleMultSel );
			$builderForm.on( 'focusin', '.frm_classes', showBuilderModal );

			$newFields.on( 'click', '.frm_primary_label', clickLabel );
			$newFields.on( 'click', '.frm_description', clickDescription );
			$newFields.on( 'click', 'li.ui-state-default', clickVis );
			$newFields.on( 'dblclick', 'li.ui-state-default', openAdvanced );
			$builderForm.on( 'change', '.frm_tax_form_select', toggleFormTax );
			$builderForm.on( 'change', 'select.conf_field', addConf );

			$builderForm.on( 'change', '.frm_get_field_selection', getFieldSelection );

			$builderForm.on( 'click', '.frm-show-inline-modal', maybeShowInlineModal );

			$builderForm.on( 'click', '.frm-inline-modal .dismiss', dismissInlineModal );
			jQuery( document ).on( 'change', '[data-frmchange]', changeInputtedValue );

			$builderForm.on( 'change', '.frm_include_extras_field', rePopCalcFieldsForSummary );
			$builderForm.on( 'change', 'select[name^="field_options[form_select_"]', maybeChangeEmbedFormMsg );

			initBulkOptionsOverlay();
			hideEmptyEle();
			maybeDisableAddSummaryBtn();
		},

		settingsInit: function() {
			var $formActions = jQuery( document.getElementById( 'frm_notification_settings' ) );
			//BCC, CC, and Reply To button functionality
			$formActions.on( 'click', '.frm_email_buttons', showEmailRow );
			$formActions.on( 'click', '.frm_remove_field', hideEmailRow );
			$formActions.on( 'change', '.frm_tax_selector', changePosttaxRow );
			$formActions.on( 'change', 'select.frm_single_post_field', checkDupPost );
			$formActions.on( 'change', 'select.frm_toggle_post_content', togglePostContent );
			$formActions.on( 'change', 'select.frm_dyncontent_opt', fillDyncontent );
			$formActions.on( 'change', '.frm_post_type', switchPostType );
			$formActions.on( 'click', '.frm_add_postmeta_row', addPostmetaRow );
			$formActions.on( 'click', '.frm_add_posttax_row', addPosttaxRow );
			$formActions.on( 'click', '.frm_toggle_cf_opts', toggleCfOpts );
			$formActions.on( 'click', '.frm_duplicate_form_action', copyFormAction );
			jQuery( 'select[data-toggleclass], input[data-toggleclass]' ).change( toggleFormOpts );
			jQuery( '.frm_actions_list' ).on( 'click', '.frm_active_action', addFormAction );
			jQuery( '#frm-show-groups, #frm-hide-groups').click( toggleActionGroups );
			initiateMultiselect();

			//set actions icons to inactive
			jQuery( 'ul.frm_actions_list li' ).each( function() {
				checkActiveAction( jQuery( this ).children( 'a' ).data( 'actiontype' ) );

				// If the icon is a background image, don't add BG color.
				var icon = jQuery( this ).find( 'i' );
				if ( icon.css('background-image') !== 'none' ) {
					icon.addClass( 'frm-inverse' );
				}
			} );

			jQuery( '.frm_submit_settings_btn' ).click( submitSettings );

			var formSettings = jQuery( '.frm_form_settings' );
			formSettings.on( 'click', '.frm_add_form_logic', addFormLogicRow );
			formSettings.on( 'blur', '.frm_email_blur', formatEmailSetting );

			formSettings.on( 'change', '#logic_link_submit', toggleSubmitLogic );
			formSettings.on( 'click', '.frm_add_submit_logic', addSubmitLogic );
			formSettings.on( 'change', '.frm_submit_logic_field_opts', addSubmitLogicOpts );


			// Close shortcode modal on click.
			formSettings.on( 'mouseup', '*:not(.frm-show-box)', function( e ) {
				e.stopPropagation();
				if ( e.target.classList.contains( 'frm-show-box' ) ) {
					return;
				}
				var sidebar = document.getElementById( 'frm_adv_info' ),
					isChild = jQuery( e.target ).closest( '#frm_adv_info' ).length > 0;

				if ( sidebar.getAttribute( 'data-fills' ) === e.target.id && typeof e.target.id !== 'undefined' ) {
					return;
				}

				if ( sidebar !== null && ! isChild && sidebar.display !== 'none' ) {
					hideShortcodes( sidebar );
				}
			} );

			//Warning when user selects "Do not store entries ..."
			jQuery( document.getElementById( 'no_save' ) ).change( function() {
				if ( this.checked ) {
					if ( confirm( frm_admin_js.no_save_warning ) !== true ) {
						// Uncheck box if user hits "Cancel"
						jQuery( this ).attr( 'checked', false );
					}
				}
			} );

			//Show/hide Messages header
			jQuery( '#editable, #edit_action, #save_draft, #success_action' ).change( function() {
				maybeShowFormMessages();
			} );
			jQuery( "select[name='options[success_action]'], select[name='options[edit_action]']" ).change( showSuccessOpt );

			var $loggedIn = document.getElementById( 'logged_in' );
			jQuery( $loggedIn ).change( function() {
				if ( this.checked ) {
					frmFrontForm.visible( '.hide_logged_in' );
				} else {
					frmFrontForm.invisible( '.hide_logged_in' );
				}
			} );

			var $cookieExp = jQuery( document.getElementById( 'frm_cookie_expiration' ) );
			jQuery( document.getElementById( 'frm_single_entry_type' ) ).change( function() {
				if ( this.value === 'cookie' ) {
					$cookieExp.fadeIn( 'slow' );
				} else {
					$cookieExp.fadeOut( 'slow' );
				}
			} );

			var $singleEntry = document.getElementById( 'single_entry' );
			jQuery( $singleEntry ).change( function() {
				if ( this.checked ) {
					frmFrontForm.visible( '.hide_single_entry' );
				} else {
					frmFrontForm.invisible( '.hide_single_entry' );
				}

				if ( this.checked && jQuery( document.getElementById( 'frm_single_entry_type' ) ).val() === 'cookie' ) {
					$cookieExp.fadeIn( 'slow' );
				} else {
					$cookieExp.fadeOut( 'slow' );
				}
			} );

			jQuery( '.hide_save_draft' ).hide();

			var $saveDraft = jQuery( document.getElementById( 'save_draft' ) );
			$saveDraft.change( function() {
				if ( this.checked ) {
					jQuery( '.hide_save_draft' ).fadeIn( 'slow' );
				} else {
					jQuery( '.hide_save_draft' ).fadeOut( 'slow' );
				}
			} );
			$saveDraft.change();

			//If Allow editing is checked/unchecked
			var $editable = document.getElementById( 'editable' );
			jQuery( $editable ).change( function() {
				if ( this.checked ) {
					jQuery( '.hide_editable' ).fadeIn( 'slow' );
					jQuery( '#edit_action' ).change();
				} else {
					jQuery( '.hide_editable' ).fadeOut( 'slow' );
					jQuery( '.edit_action_message_box' ).fadeOut( 'slow' );//Hide On Update message box
				}
			} );
		},

		panelInit: function() {
			jQuery( '.frm_wrap, #postbox-container-1' ).on( 'click', '.frm_insert_code', insertCode );
			jQuery( document ).on( 'change', '.frm_insert_val', function() {
				insertFieldCode( jQuery( this ).data( 'target' ), jQuery( this ).val() );
				jQuery( this ).val( '' );
			} );

			jQuery( document ).on( 'click change', '#frm-id-key-condition', resetLogicBuilder );
			jQuery( document ).on( 'keyup change', '.frm-build-logic', setLogicExample );

			showInputIcon();
			jQuery( document ).on( 'frmElementAdded', function( event, parentEle ) {
				/* This is here for add-ons to trigger */
				showInputIcon( parentEle );
			});
			jQuery( document ).on( 'mousedown', '.frm-show-box', showShortcodes );

			var settingsPage = document.getElementById( 'form_settings_page' ),
				viewPage = document.body.classList.contains( 'post-type-frm_display' ),
				htmlTab = document.getElementById( 'frm_html_tags_tab' ),
				insertFieldsTab = document.getElementById( 'frm_insert_fields_tab' );

			if ( settingsPage !== null || viewPage ) {
			jQuery( document ).on( 'focusin', 'form input, form textarea', function( e ) {
				e.stopPropagation();
				maybeShowModal( this );

				if ( jQuery( this ).is( ':not(:submit, input[type=button], .frm-search-input, input[type=checkbox])' ) ) {
					if ( jQuery( e.target ).closest( '#frm_adv_info' ).length ) {
						// Don't trigger for fields inside of the modal.
						return;
					}

					if ( settingsPage !== null ) {
						/* form settings page */
						var htmlTab = jQuery( '#frm_html_tab' );
						if ( jQuery( this ).closest( '#html_settings' ).length > 0 ) {
							htmlTab.show();
							htmlTab.siblings().hide();
							jQuery( '#frm_html_tab a' ).click();
							toggleAllowedHTML( this, e.type );
						} else {
							showElement( jQuery( '.frm-category-tabs li' ) );
							insertFieldsTab.click();
							htmlTab.hide();
							htmlTab.siblings().show();
						}
					} else if ( viewPage ) {
						// Run on view page.
						toggleAllowedShortcodes( this.id, e.type );
					}
				}
			} );
			}

			jQuery( '.frm_wrap, #postbox-container-1' ).on( 'mousedown', '#frm_adv_info a, .frm_field_list a', function( e ) {
				e.preventDefault();
			} );

			var customPanel = jQuery( '#frm_adv_info' );
			customPanel.on( 'click', '.subsubsub a.frmids', function( e ) {
				toggleKeyID( 'frmids', e );
			} );
			customPanel.on( 'click', '.subsubsub a.frmkeys', function( e ) {
				toggleKeyID( 'frmkeys', e );
			} );
		},

		templateInit: function() {
			initTemplateModal();
			initiateMultiselect();
		},

		viewInit: function() {
			var $advInfo = jQuery( document.getElementById( 'frm_adv_info' ) );
			$advInfo.before( '<div id="frm_position_ele"></div>' );
			setupMenuOffset();

			// Show loading indicator.
			jQuery( '#publish' ).mousedown( function() {
				this.classList.add( 'frm_loading_button' );
			});

			// move content tabs
			jQuery( '#frm_dyncontent .handlediv' ).before( jQuery( '#frm_dyncontent .nav-menus-php' ) );

			// click content tabs
			jQuery( '.nav-tab-wrapper a' ).click( clickContentTab );

			// click tabs after panel is replaced with ajax
			jQuery( '#side-sortables' ).on( 'click', '.frm_doing_ajax.categorydiv .category-tabs a', clickTabsAfterAjax );

			initToggleShortcodes();
			jQuery( '.frm_code_list:not(.frm-dropdown-menu) a' ).addClass( 'frm_noallow' );

			jQuery( 'input[name="show_count"]' ).change( showCount );

			jQuery( document.getElementById( 'form_id' ) ).change( displayFormSelected );

			var $addRemove = jQuery( '.frm_repeat_rows' );
			$addRemove.on( 'click', '.frm_add_order_row', addOrderRow );
			$addRemove.on( 'click', '.frm_add_where_row', addWhereRow );
			$addRemove.on( 'change', '.frm_insert_where_options', insertWhereOptions );
			$addRemove.on( 'change', '.frm_where_is_options', hideWhereOptions );

			setDefaultPostStatus();
		},

		styleInit: function() {
			collapseAllSections();

			document.getElementById( 'frm_field_height' ).addEventListener( 'change', textSquishCheck );
			document.getElementById( 'frm_field_font_size' ).addEventListener( 'change', textSquishCheck );
			document.getElementById( 'frm_field_pad' ).addEventListener( 'change', textSquishCheck );

			jQuery( 'input.hex' ).wpColorPicker( {
				change: function( event, ui ) {
					var hexcolor = jQuery( this ).wpColorPicker( 'color' );
					jQuery( event.target ).val( hexcolor ).change();
				}
			} );
			jQuery( '.wp-color-result-text' ).text( function( i, oldText ) {
				return oldText === 'Select Color' ? 'Select' : oldText;
			} );

			// update styling on change
			jQuery( '#frm_styling_form .styling_settings' ).change( function() {
				var locStr = jQuery( 'input[name^="frm_style_setting[post_content]"], select[name^="frm_style_setting[post_content]"], textarea[name^="frm_style_setting[post_content]"], input[name="style_name"]' ).serializeArray();
				locStr = JSON.stringify( locStr );
				jQuery.ajax( {
					type: 'POST', url: ajaxurl,
					data: {
						action: 'frm_change_styling',
						nonce: frmGlobal.nonce,
						frm_style_setting: locStr,
					},
					success: function( css ) {
						document.getElementById( 'this_css' ).innerHTML = css;
					}
				} );
			} );

			// menu tabs
			jQuery( '#menu-settings-column' ).bind( 'click', function( e ) {
				var selectAreaMatch, panelId, wrapper, items,
					target = jQuery( e.target );

				if ( e.target.className.indexOf( 'nav-tab-link' ) !== -1 ) {

					panelId = target.data( 'type' );

					wrapper = target.parents( '.accordion-section-content' ).first();


					jQuery( '.tabs-panel-active', wrapper ).removeClass( 'tabs-panel-active' ).addClass( 'tabs-panel-inactive' );
					jQuery( '#' + panelId, wrapper ).removeClass( 'tabs-panel-inactive' ).addClass( 'tabs-panel-active' );

					jQuery( '.tabs', wrapper ).removeClass( 'tabs' );
					target.parent().addClass( 'tabs' );

					// select the search bar
					jQuery( '.quick-search', wrapper ).focus();

					e.preventDefault();
				}
			} );

			jQuery( '.multiselect-container.frm-dropdown-menu li a' ).click( function() {
				var radio = this.children[0].children[0];
				var btnGrp = jQuery( this ).closest( '.btn-group' );
				var btnId = btnGrp.attr( 'id' );
				document.getElementById( btnId.replace( '_select', '' ) ).value = radio.value;
				btnGrp.children( 'button' ).html( radio.nextElementSibling.innerHTML + ' <b class="caret"></b>' );

				// set active class
				btnGrp.find( 'li.active' ).removeClass( 'active' );
				jQuery( this ).closest( 'li' ).addClass( 'active' );
			} );

			jQuery( '#frm_confirm_modal' ).on( 'click', '[data-resetstyle]', function( e ) {
				var button = document.getElementById( 'frm_reset_style' );

				button.classList.add( 'frm_loading_button' );
				e.stopPropagation();

				jQuery.ajax( {
					type: 'POST', url: ajaxurl,
					data: {action: 'frm_settings_reset', nonce: frmGlobal.nonce},
					success: function( errObj ) {
						errObj = errObj.replace( /^\s+|\s+$/g, '' );
						if ( errObj.indexOf( '{' ) === 0 ) {
							errObj = jQuery.parseJSON( errObj );
						}
						for ( var key in errObj ) {
							jQuery( 'input[name$="[' + key + ']"], select[name$="[' + key + ']"]' ).val( errObj[key] );
						}
						jQuery( '#frm_submit_style, #frm_auto_width' ).prop( 'checked', false );
						jQuery( document.getElementById( 'frm_fieldset' ) ).change();
						button.classList.remove( 'frm_loading_button' );
					}
				} );
			} );

			jQuery( '.frm_pro_form #datepicker_sample' ).datepicker( {changeMonth: true, changeYear: true} );

			jQuery( document.getElementById( 'frm_position' ) ).change( setPosClass );

			jQuery( 'select[name$="[theme_selector]"]' ).change( function() {
				var themeVal = jQuery( this ).val();
				var themeName = themeVal;
				var css = themeVal;
				if ( themeVal !== -1 ) {
					if ( themeVal === 'ui-lightness' && frm_admin_js.pro_url !== '' ) {
						css = frm_admin_js.pro_url + '/css/ui-lightness/jquery-ui.css';
						jQuery( '.frm_date_color' ).show();
					} else {
						css = frm_admin_js.jquery_ui_url + '/themes/' + themeVal + '/jquery-ui.css';
						jQuery( '.frm_date_color' ).hide();
					}
				}

				updateUICSS( css );
				document.getElementById( 'frm_theme_css' ).value = themeVal;
				return false;
			} ).change();
		},

		customCSSInit: function() {
			/* deprecated since WP 4.9 */
			var customCSS = document.getElementById( 'frm_custom_css_box' );
			if ( customCSS !== null ) {
				var editor = CodeMirror.fromTextArea( customCSS, {
					lineNumbers: true
				} );
			}
		},

		globalSettingsInit: function() {
			jQuery( document).on( 'click', '[data-frmuninstall]', uninstallNow );

			initiateMultiselect();

			// activate addon licenses
			var licenseTab = document.getElementById( 'licenses_settings' );
			if ( licenseTab !== null ) {
				jQuery( licenseTab ).on( 'click', '.edd_frm_save_license', saveAddonLicense );
			}

			jQuery( '#frm-dismissable-cta .dismiss' ).click( function( event ) {
				event.preventDefault();
				jQuery.post( ajaxurl, {
					action: 'frm_lite_settings_upgrade'
				} );
				jQuery( '.settings-lite-cta' ).remove();
			} );
		},

		exportInit: function() {
			jQuery( '#frm_form_importer' ).submit( startFormMigration );
			jQuery( document.getElementById( 'frm_export_xml' ) ).submit( validateExport );
			jQuery( '#frm_export_xml input, #frm_export_xml select' ).change( removeExportError );
			jQuery( 'input[name="frm_import_file"]' ).change( checkCSVExtension );
			jQuery( 'select[name="format"]' ).change( checkExportTypes ).change();
			jQuery( 'input[name="frm_export_forms[]"]' ).click( preventMultipleExport );
			initiateMultiselect();
		},

		updateOpts: function( field_id, opts, modal ) {
			var separate = usingSeparateValues( field_id );
			$fieldOpts = document.getElementById( 'frm_field_' + field_id + '_opts' );
			empty( $fieldOpts );
			jQuery.ajax( {
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'frm_import_options',
					field_id: field_id,
					opts: opts,
					separate: separate,
					nonce: frmGlobal.nonce
				},
				success: function( html ) {
					document.getElementById( 'frm_field_' + field_id + '_opts' ).innerHTML = html;
					resetDisplayedOpts( field_id );

					if ( typeof modal !== 'undefined' ) {
						modal.dialog( 'close' );
						document.getElementById( 'frm-update-bulk-opts' ).classList.remove( 'frm_loading_button' );
					}
				}
			} );
		},

		/* remove conditional logic if the field doesn't exist */
		triggerRemoveLogic: function( fieldID, metaName ) {
			jQuery( '#frm_logic_' + fieldID + '_' + metaName + ' .frm_remove_tag' ).click();
		},

		downloadXML: function( controller, ids, isTemplate ) {
			var url = ajaxurl + '?action=frm_' + controller + '_xml&ids=' + ids;
			if ( isTemplate !== null ) {
				url = url + '&is_template=' + isTemplate;
			}
			location.href = url;
		}
	};
}

var frmAdminBuild = frmAdminBuildJS();

jQuery( document ).ready( function( $ ) {
	frmAdminBuild.init();
} );

function frm_remove_tag( html_tag ) {
	console.warn( 'DEPRECATED: function frm_remove_tag in v2.0' );
	jQuery( html_tag ).remove();
}

function frm_show_div( div, value, show_if, class_id ) {
	if ( value == show_if ) {
		jQuery( class_id + div ).fadeIn( 'slow' ).css( 'visibility', 'visible' );
	} else {
		jQuery( class_id + div ).fadeOut( 'slow' );
	}
}

function frmCheckAll( checked, n ) {
	if ( checked ) {
		jQuery( "input[name^='" + n + "']" ).attr( 'checked', 'checked' );
	} else {
		jQuery( "input[name^='" + n + "']" ).removeAttr( 'checked' );
	}
}

function frmCheckAllLevel( checked, n, level ) {
	var $kids = jQuery( ".frm_catlevel_" + level ).children( ".frm_checkbox" ).children( 'label' );
	if ( checked ) {
		$kids.children( "input[name^='" + n + "']" ).attr( "checked", "checked" );
	} else {
		$kids.children( "input[name^='" + n + "']" ).removeAttr( "checked" );
	}
}

function frm_add_logic_row( id, form_id ) {
	console.warn( 'DEPRECATED: function frm_add_logic_row in v2.0' );
	jQuery.ajax( {
		type: "POST", url: ajaxurl,
		data: {
			action: 'frm_add_logic_row',
			form_id: form_id,
			field_id: id,
			meta_name: jQuery( '#frm_logic_row_' + id + ' > div' ).size(),
			nonce: frmGlobal.nonce
		},
		success: function( html ) {
			jQuery( '#frm_logic_row_' + id ).append( html );
		}
	} );
	return false;
}

function frmGetFieldValues( field_id, cur, row_number, field_type, html_name ) {

	if ( field_id ) {
		jQuery.ajax( {
			type: 'POST', url: ajaxurl,
			data: 'action=frm_get_field_values&current_field=' + cur + '&field_id=' + field_id + '&name=' + html_name + '&t=' + field_type + '&form_action=' + jQuery( 'input[name="frm_action"]' ).val() + '&nonce=' + frmGlobal.nonce,
			success: function( msg ) {
				document.getElementById( 'frm_show_selected_values_' + cur + '_' + row_number ).innerHTML = msg;
			}
		} );
	}
}

function frmImportCsv( formID ) {
	var urlVars = '';
	if ( typeof __FRMURLVARS != 'undefined' ) {
		urlVars = __FRMURLVARS;
	}

	jQuery.ajax( {
		type: "POST", url: ajaxurl,
		data: 'action=frm_import_csv&nonce=' + frmGlobal.nonce + '&frm_skip_cookie=1' + urlVars,
		success: function( count ) {
			var max = jQuery( '.frm_admin_progress_bar' ).attr( 'aria-valuemax' );
			var imported = max - count;
			var percent = (imported / max) * 100;
			jQuery( '.frm_admin_progress_bar' ).css( 'width', percent + '%' ).attr( 'aria-valuenow', imported );

			if ( parseInt( count ) > 0 ) {
				jQuery( '.frm_csv_remaining' ).html( count );
				frmImportCsv( formID );
			} else {
				jQuery( document.getElementById( 'frm_import_message' ) ).html( frm_admin_js.import_complete );
				setTimeout( function() {
					location.href = '?page=formidable-entries&frm_action=list&form=' + formID + '&import-message=1';
				}, 2000 );
			}
		}
	} );
}

// https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String/Trim#Polyfill
if ( ! String.prototype.trim ) {
  String.prototype.trim = function () {
    return this.replace( /^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, '' );
  };
}
// https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String/startsWith#Polyfill
if (!String.prototype.startsWith) {
    Object.defineProperty(String.prototype, 'startsWith', {
        value: function(search, pos) {
            pos = !pos || pos < 0 ? 0 : +pos;
            return this.substring(pos, pos + search.length) === search;
        }
    });
}