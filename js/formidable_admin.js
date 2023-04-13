/* exported frm_add_logic_row, frm_remove_tag, frm_show_div, frmCheckAll, frmCheckAllLevel */

var frmAdminBuild;

var FrmFormsConnect = window.FrmFormsConnect || ( function( document, window, $ ) {

	/*global jQuery:false, frm_admin_js, frmGlobal, ajaxurl */

	var el = {
		licenseBox: document.getElementById( 'frm_license_top' ),
		messageBox: document.getElementsByClassName( 'frm_pro_license_msg' )[0],
		btn: document.getElementById( 'frm-settings-connect-btn' ),
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
			$( document.getElementById( 'frm_deauthorize_link' ) ).on( 'click', app.deauthorize );
			$( '.frm_authorize_link' ).on( 'click', app.authorize );
			if ( el.reset !== null ) {
				$( el.reset ).on( 'click', app.reauthorize );
			}

			$( el.btn ).on( 'click', function( e ) {
				e.preventDefault();
				app.gotoUpgradeUrl();
			});

			window.addEventListener( 'message', function( msg ) {
				if ( msg.origin.replace( /\/$/, '' ) !== frmGlobal.app_url.replace( /\/$/, '' ) ) {
					return;
				}

				if ( ! msg.data || 'object' !== typeof msg.data ) {
					console.error( 'Messages from "' + frmGlobal.app_url + '" must contain an api key string.' );
					return;
				}

				app.updateForm( msg.data );
			});

			jQuery( document ).on( 'mouseover', '#frm_new_form_modal .frm-selectable', function() {
				var $item = jQuery( this ),
					$icons = $item.find( '.frm-hover-icons' ),
					$clone;

				if ( ! $icons.length ) {
					$clone = jQuery( '#frm-hover-icons-template' ).clone();
					$clone.removeAttr( 'id' );
					$item.append( $clone );
				}

				$icons.show();
			});

			jQuery( document ).on( 'mouseout', '#frm_new_form_modal .frm-selectable', function() {
				var $item = jQuery( this ),
					$icons = $item.find( '.frm-hover-icons' );

				if ( $icons.length ) {
					$icons.hide();
				}
			});
		},

		/**
		 * Go to upgrade url.
		 *
		 * @since 4.03
		 */
		gotoUpgradeUrl: function() {
			var w = window.open( frmGlobal.app_url + '/api-connect/', '_blank', 'location=no,width=500,height=730,scrollbars=0' );
			w.focus();
		},

		updateForm: function( response ) {

			// Start spinner.
			var btn = el.btn;
			btn.classList.add( 'frm_loading_button' );

			if ( response.url !== '' ) {
				app.showProgress({
					success: true,
					message: 'Installing...'
				});
				var fallback = setTimeout( function() {
					app.showProgress({
						success: true,
						message: 'Installing is taking longer than expected. <a class="frm-install-addon button button-primary frm-button-primary" rel="' + response.url + '" aria-label="Install">Install Now</a>'
					});
				}, 10000 );
				$.ajax({
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
					error: function( xhr, textStatus, e ) {
						clearTimeout( fallback );
						btn.classList.remove( 'frm_loading_button' );
						app.showMessage({
							success: false,
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
				btn.classList.remove( 'frm_loading_button' );
			} else {
				app.showProgress({
					success: true,
					message: 'Activating...'
				});
				$.ajax({
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
						btn.classList.remove( 'frm_loading_button' );

						if ( msg.success === true ) {
							el.licenseBox.classList.replace( 'frm_unauthorized_box', 'frm_authorized_box' );
						}

						app.showMessage( msg );
					},
					error: function( xhr, textStatus, e ) {
						btn.classList.remove( 'frm_loading_button' );
						app.showMessage({
							success: false,
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
			var pluginSlug = this.getAttribute( 'data-plugin' );
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

			$.ajax({
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
			});
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
				var refreshPage = document.querySelectorAll( '#frm-welcome' );
				if ( refreshPage.length > 0 ) {
					window.location.reload();
				}
			}
		},

		/* Clear the site license cache */
		reauthorize: function() {
			/*jshint validthis:true */
			this.innerHTML = '<span class="frm-wait frm_spinner" style="visibility:visible;float:none"></span>';

			$.ajax({
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
			});
			return false;
		},

		deauthorize: function() {
			/*jshint validthis:true */
			if ( ! confirm( frmGlobal.deauthorize ) ) {
				return false;
			}
			var pluginSlug = this.getAttribute( 'data-plugin' ),
				input = document.getElementById( 'edd_' + pluginSlug + '_license_key' ),
				license = input.value,
				link = this;

			this.innerHTML = '<span class="frm-wait frm_spinner" style="visibility:visible;"></span>';

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'frm_addon_deactivate',
					license: license,
					plugin: pluginSlug,
					nonce: frmGlobal.nonce
				},
				success: function() {
					el.licenseBox.className = el.licenseBox.className.replace( 'frm_authorized_box', 'frm_unauthorized_box' );
					input.value = '';
					link.innerHTML = '';
				}
			});
			return false;
		}
	};

	// Provide access to public functions/properties.
	return app;

}( document, window, jQuery ) );

function frmAdminBuildJS() {
	//'use strict';

	/*global jQuery:false, frm_admin_js, frmGlobal, ajaxurl, fromDom */

	const { tag, div, span, a, svg, img } = frmDom;
	const { doJsonFetch } = frmDom.ajax;
	const icons = {
		save: svg({ href: '#frm_save_icon' }),
		drag: svg({ href: '#frm_drag_icon', classList: [ 'frm_drag_icon', 'frm-drag' ] })
	};

	let $newFields = jQuery( document.getElementById( 'frm-show-fields' ) ),
		builderForm = document.getElementById( 'new_fields' ),
		thisForm = document.getElementById( 'form_id' ),
		copyHelper = false,
		fieldsUpdated = 0,
		thisFormId = 0,
		autoId = 0,
		optionMap = {},
		lastNewActionIdReturned = 0;

	const { __ } = wp.i18n;
	let debouncedSyncAfterDragAndDrop, postBodyContent, $postBodyContent;

	if ( thisForm !== null ) {
		thisFormId = thisForm.value;
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
		var verify, $confirmMessage, i, dataAtts, btnClass,
			$info = initModal( '#frm_confirm_modal', '400px' ),
			continueButton = document.getElementById( 'frm-confirmed-click' );

		if ( $info === false ) {
			return false;
		}

		verify = link.getAttribute( 'data-frmverify' );
		btnClass = verify ? link.getAttribute( 'data-frmverify-btn' ) : '';
		$confirmMessage = jQuery( '.frm-confirm-msg' );
		$confirmMessage.empty();

		if ( verify ) {
			$confirmMessage.append( document.createTextNode( verify ) );
			if ( btnClass ) {
				continueButton.classList.add( btnClass );
			}
		}

		removeAtts = continueButton.dataset;
		for ( i in dataAtts ) {
			continueButton.removeAttribute( 'data-' + i );
		}

		dataAtts = link.dataset;
		for ( i in dataAtts ) {
			if ( i !== 'frmverify' ) {
				continueButton.setAttribute( 'data-' + i, dataAtts[i]);
			}
		}

		$info.dialog( 'open' );
		continueButton.setAttribute( 'href', link.getAttribute( 'href' ) );
		return false;
	}

	function infoModal( msg ) {
		var $info = initModal( '#frm_info_modal', '400px' );

		if ( $info === false ) {
			return false;
		}

		jQuery( '.frm-info-msg' ).html( msg );

		$info.dialog( 'open' );
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

	/**
	 * Toggle a class on target elements when an anchor is clicked, or when a radio or checkbox has been selected.
	 *
	 * @param {Event} e Event with either the change or click type.
	 * @returns {false}
	 */
	function hideShowItem( e ) {
		/*jshint validthis:true */
		let hide = this.getAttribute( 'data-frmhide' );
		let show = this.getAttribute( 'data-frmshow' );

		// Flip unchecked checkboxes so an off value undoes the on value.
		if ( isUncheckedCheckbox( this ) ) {
			if ( hide !== null ) {
				show = hide;
				hide = null;
			} else if ( show !== null ) {
				hide = show;
				show = null;
			}
		}

		e.preventDefault();

		const toggleClass = this.getAttribute( 'data-toggleclass' ) || 'frm_hidden';

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

	function isUncheckedCheckbox( element ) {
		return 'INPUT' === element.nodeName && 'checkbox' === element.type && ! element.checked;
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
		var wrapClass = jQuery( '.wrap, .frm_wrap' ),
			confirmModal = document.getElementById( 'frm_confirm_modal' ),
			doAction = false,
			confirmedBulkDelete = false;

		jQuery( confirmModal ).on( 'click', '[data-deletefield]', deleteFieldConfirmed );
		jQuery( confirmModal ).on( 'click', '[data-removeid]', removeThisTag );
		jQuery( confirmModal ).on( 'click', '[data-trashtemplate]', trashTemplate );

		wrapClass.on( 'click', '.frm_remove_tag, .frm_remove_form_action', removeThisTag );
		wrapClass.on( 'click', 'a[data-frmverify]', confirmClick );
		wrapClass.on( 'click', 'a[data-frmtoggle]', toggleItem );
		wrapClass.on( 'click', 'a[data-frmhide], a[data-frmshow]', hideShowItem );
		wrapClass.on( 'change', 'input[data-frmhide], input[data-frmshow]', hideShowItem );
		wrapClass.on( 'click', '.widget-top,a.widget-action', clickWidget );

		wrapClass.on( 'mouseenter.frm', '.frm_bstooltip, .frm_help', function() {
			jQuery( this ).off( 'mouseenter.frm' );

			jQuery( '.frm_bstooltip, .frm_help' ).tooltip( );
			jQuery( this ).tooltip( 'show' );
		});

		jQuery( '.frm_bstooltip, .frm_help' ).tooltip( );

		jQuery( document ).on( 'click', '#doaction, #doaction2', function( event ) {
			var isTop = this.id === 'doaction',
				suffix = isTop ? 'top' : 'bottom',
				bulkActionSelector = document.getElementById( 'bulk-action-selector-' + suffix ),
				confirmBulkDelete = document.getElementById( 'confirm-bulk-delete-' + suffix );

			if ( bulkActionSelector !== null && confirmBulkDelete !== null ) {
				doAction = this;

				if ( ! confirmedBulkDelete && bulkActionSelector.value === 'bulk_delete' ) {
					event.preventDefault();
					confirmLinkClick( confirmBulkDelete );
					return false;
				}
			} else {
				doAction = false;
			}
		});

		jQuery( document ).on( 'click', '#frm-confirmed-click', function( event ) {
			if ( doAction === false ) {
				return;
			}

			if ( this.getAttribute( 'href' ) === 'confirm-bulk-delete' ) {
				event.preventDefault();
				confirmedBulkDelete = true;
				doAction.click();
				return false;
			}
		});
	}

	function deleteTooltips() {
		document.querySelectorAll( '.tooltip' ).forEach(
			function( tooltip ) {
				tooltip.remove();
			}
		);
	}

	function removeThisTag() {
		/*jshint validthis:true */
		let show, hide, removeMore;

		if ( parseInt( this.getAttribute( 'data-skip-frm-js' ) ) || confirmLinkClick( this ) === false ) {
			return;
		}

		const deleteButton = jQuery( this );
		const id = deleteButton.attr( 'data-removeid' );

		show = deleteButton.attr( 'data-showlast' );
		if ( typeof show === 'undefined' ) {
			show = '';
		}

		hide = deleteButton.attr( 'data-hidelast' );
		if ( typeof hide === 'undefined' ) {
			hide = '';
		}

		removeMore = deleteButton.attr( 'data-removemore' );

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

		const $fadeEle = jQuery( document.getElementById( id ) );
		$fadeEle.fadeOut( 400, function() {
			$fadeEle.remove();
			fieldUpdated();

			if ( hide !== '' ) {
				jQuery( hide ).hide();
			}

			if ( show !== '' ) {
				jQuery( show + ' a,' + show ).removeClass( 'frm_hidden' ).fadeIn( 'slow' );
			}

			if ( this.closest( '.frm_form_action_settings' ) ) {
				const type = this.closest( '.frm_form_action_settings' ).querySelector( '.frm_action_name' ).value;
				afterActionRemoved( type );
			}
		});

		if ( typeof removeMore !== 'undefined' ) {
			removeMore = jQuery( removeMore );
			removeMore.fadeOut( 400, function() {
				removeMore.remove();
			});
		}

		if ( show !== '' ) {
			jQuery( this ).closest( '.frm_logic_rows' ).fadeOut( 'slow' );
		}

		return false;
	}

	function afterActionRemoved( type ) {
		checkActiveAction( type );

		const hookName = 'frm_after_action_removed';
		const hookArgs = { type };
		wp.hooks.doAction( hookName, hookArgs );
	}

	function clickWidget( event, b ) {
		/*jshint validthis:true */
		if ( typeof b === 'undefined' ) {
			b = this;
		}

		popCalcFields( b, false );

		const cont   = jQuery( b ).closest( '.frm_form_action_settings' );
		const target = event.target;

		if ( cont.length && typeof target !== 'undefined' ) {
			const className = target.parentElement.className;
			if ( 'string' === typeof className ) {
				if ( className.indexOf( 'frm_email_icons' ) > -1 || className.indexOf( 'frm_toggle' ) > -1 ) {
					// clicking on delete icon shouldn't open it
					event.stopPropagation();
					return;
				}
			}
		}

		let inside = cont.children( '.widget-inside' );

		if ( cont.length && inside.find( 'p, div, table' ).length < 1 ) {
			const actionId = cont.find( 'input[name$="[ID]"]' ).val();
			const actionType = cont.find( 'input[name$="[post_excerpt]"]' ).val();
			if ( actionType ) {
				inside.html( '<span class="frm-wait frm_spinner"></span>' );
				cont.find( '.spinner' ).fadeIn( 'slow' );
				jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					data: {
						action: 'frm_form_action_fill',
						action_id: actionId,
						action_type: actionType,
						nonce: frmGlobal.nonce
					},
					success: function( html ) {
						inside.html( html );
						initiateMultiselect();
						showInputIcon( '#' + cont.attr( 'id' ) );
						frmDom.autocomplete.initAutocomplete( 'page', inside );
						jQuery( b ).trigger( 'frm-action-loaded' );

						/**
						 * Fires after filling form action content when opening.
						 *
						 * @since 5.5.4
						 *
						 * @param {Object} insideElement JQuery object of form action inside element.
						 */
						wp.hooks.doAction( 'frm_filled_form_action', inside );
					}
				});
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
			jQuery( '.frm_warning_style' ).hide();
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

	function setupSortable( sortableSelector ) {
		document.querySelectorAll( sortableSelector ).forEach(
			list => {
				makeDroppable( list );
				Array.from( list.children ).forEach( child => makeDraggable( child, '.frm-move' ) );

				const $sectionTitle = jQuery( list ).children( '[data-type="divider"]' ).children( '.divider_section_only' );
				if ( $sectionTitle.length ) {
					makeDroppable( $sectionTitle );
				}
			}
		);
		setupFieldOptionSorting( jQuery( '#frm_builder_page' ) );
	}

	function makeDroppable( list ) {
		jQuery( list ).droppable({
			accept: '.frmbutton, li.frm_field_box',
			deactivate: handleFieldDrop,
			over: onDragOverDroppable,
			out: onDraggableLeavesDroppable,
			tolerance: 'pointer'
		});
	}

	function onDragOverDroppable( event, ui ) {
		const droppable = getDroppableForOnDragOver( event.target );
		const draggable = ui.draggable[0];

		if ( ! allowDrop( draggable, droppable ) ) {
			droppable.classList.remove( 'frm-over-droppable' );
			jQuery( droppable ).parents( 'ul.frm_sorting' ).addClass( 'frm-over-droppable' );
			return;
		}

		document.querySelectorAll( '.frm-over-droppable' ).forEach( droppable => droppable.classList.remove( 'frm-over-droppable' ) );
		droppable.classList.add( 'frm-over-droppable' );
		jQuery( droppable ).parents( 'ul.frm_sorting' ).addClass( 'frm-over-droppable' );
	}

	/**
	 * Maybe change the droppable.
	 * Section titles are made droppable, but are not a list, so we need to change the droppable to the section's list instead.
	 *
	 * @param {Element} droppable
	 * @returns {Element}
	 */
	function getDroppableForOnDragOver( droppable ) {
		if ( droppable.classList.contains( 'divider_section_only' ) ) {
			droppable = jQuery( droppable ).nextAll( '.start_divider.frm_sorting' ).get( 0 );
		}
		return droppable;
	}

	function onDraggableLeavesDroppable( event ) {
		const droppable = event.target;
		droppable.classList.remove( 'frm-over-droppable' );
	}

	function makeDraggable( draggable, handle ) {
		const settings = {
			helper: getDraggableHelper,
			revert: 'invalid',
			delay: 10,
			start: handleDragStart,
			stop: handleDragStop,
			drag: handleDrag,
			cursor: 'grabbing',
			refreshPositions: true,
			cursorAt: {
				top: 0,
				left: 90 // The width of draggable button is 180. 90 should center the draggable on the cursor.
			}
		};
		if ( 'string' === typeof handle ) {
			settings.handle = handle;
		}
		jQuery( draggable ).draggable( settings );
	}

	function getDraggableHelper( event ) {
		const draggable = event.delegateTarget;

		if ( isFieldGroup( draggable ) ) {
			const newTextFieldClone = document.getElementById( 'frm-insert-fields' ).querySelector( '.frm_ttext' ).cloneNode( true );
			newTextFieldClone.querySelector( 'use' ).setAttributeNS( 'http://www.w3.org/1999/xlink', 'href', '#frm_field_group_layout_icon' );
			newTextFieldClone.querySelector( 'span' ).textContent = __( 'Field Group' );
			newTextFieldClone.classList.add( 'frm_field_box' );
			newTextFieldClone.classList.add( 'ui-sortable-helper' );
			return newTextFieldClone;
		}

		let copyTarget;
		const isNewField = draggable.classList.contains( 'frmbutton' );
		if ( isNewField ) {
			copyTarget = draggable.cloneNode( true );
			copyTarget.classList.add( 'ui-sortable-helper' );
			draggable.classList.add( 'frm-new-field' );
			return copyTarget;
		}

		if ( draggable.hasAttribute( 'data-ftype' ) ) {
			const fieldType = draggable.getAttribute( 'data-ftype' );
			copyTarget = document.getElementById( 'frm-insert-fields' ).querySelector( '.frm_t' + fieldType );
			copyTarget = copyTarget.cloneNode( true );
			copyTarget.classList.add( 'form-field' );

			copyTarget.classList.add( 'ui-sortable-helper' );

			if ( copyTarget ) {
				return copyTarget.cloneNode( true );
			}
		}

		return div({ className: 'frmbutton' });
	}

	function handleDragStart( event, ui ) {
		const container = postBodyContent;
		container.classList.add( 'frm-dragging-field' );

		document.body.classList.add( 'frm-dragging' );
		ui.helper.addClass( 'frm-sortable-helper' );
		ui.helper.initialOffset = container.scrollTop;

		event.target.classList.add( 'frm-drag-fade' );

		unselectFieldGroups();
		deleteEmptyDividerWrappers();
		maybeRemoveGroupHoverTarget();
		closeOpenFieldDropdowns();
		deleteTooltips();
	}

	function handleDragStop() {
		const container = postBodyContent;
		container.classList.remove( 'frm-dragging-field' );
		document.body.classList.remove( 'frm-dragging' );

		const fade = document.querySelector( '.frm-drag-fade' );
		if ( fade ) {
			fade.classList.remove( 'frm-drag-fade' );
		}
	}

	function handleDrag( event, ui ) {
		maybeScrollBuilder( event );
		const draggable = event.target;
		const droppable = getDroppableTarget();

		let placeholder = document.getElementById( 'frm_drag_placeholder' );

		if ( ! allowDrop( draggable, droppable ) ) {
			if ( placeholder ) {
				placeholder.remove();
			}
			return;
		}

		if ( ! placeholder ) {
			placeholder = tag( 'li', {
				id: 'frm_drag_placeholder',
				className: 'sortable-placeholder'
			});
		}
		const frmSortableHelper = ui.helper.get( 0 );
		if ( frmSortableHelper.classList.contains( 'form-field' ) || frmSortableHelper.classList.contains( 'frm_field_box' ) ) {
			// Sync the y position of the draggable so it still follows the cursor after scrolling up and down the field list.
			frmSortableHelper.style.transform = 'translateY(' + getDragOffset( ui.helper ) + 'px)';
		}

		if ( 'frm-show-fields' === droppable.id || droppable.classList.contains( 'start_divider' ) ) {
			placeholder.style.left = 0;
			handleDragOverYAxis({ droppable, y: event.clientY, placeholder });
			return;
		}

		placeholder.style.top = '';
		handleDragOverFieldGroup({ droppable, x: event.clientX, placeholder });
	}

	function maybeScrollBuilder( event ) {
		$postBodyContent.scrollTop(
			( _, v ) => {
				const moved       = event.clientY;
				const h           = postBodyContent.offsetHeight;
				const relativePos = event.clientY - postBodyContent.offsetTop;
				const y           = relativePos - h / 2;

				if ( relativePos > ( h - 50 ) && moved > 5 ) {
					// Scrolling down.
					return v + y * 0.1;
				}

				if ( relativePos < 70 && moved < 130 ) {
					// Scrolling up.
					return v - Math.abs( y * 0.1 );
				}

				return v;
			}
		);
	}

	function getDragOffset( $helper ) {
		return postBodyContent.scrollTop - $helper.initialOffset;
	}

	function getDroppableTarget() {
		let droppable = document.getElementById( 'frm-show-fields' );
		while ( droppable.querySelector( '.frm-over-droppable' ) ) {
			droppable = droppable.querySelector( '.frm-over-droppable' );
		}
		if ( 'frm-show-fields' === droppable.id && ! droppable.classList.contains( 'frm-over-droppable' ) ) {
			droppable = false;
		}
		return droppable;
	}

	function handleFieldDrop( _, ui ) {
		const draggable = ui.draggable[0];
		const placeholder = document.getElementById( 'frm_drag_placeholder' );

		if ( ! placeholder ) {
			ui.helper.remove();
			debouncedSyncAfterDragAndDrop();
			return;
		}

		maybeOpenCollapsedPage( placeholder );

		const $previousFieldContainer = ui.helper.parent();
		const previousSection         = ui.helper.get( 0 ).closest( 'ul.start_divider' );
		const newSection              = placeholder.closest( 'ul.frm_sorting' );

		if ( draggable.classList.contains( 'frm-new-field' ) ) {
			insertNewFieldByDragging( draggable.id );
		} else {
			moveFieldThatAlreadyExists( draggable, placeholder );
		}

		const previousSectionId = previousSection ? parseInt( previousSection.closest( '.edit_field_type_divider' ).getAttribute( 'data-fid' ) ) : 0;
		const newSectionId      = newSection.classList.contains( 'start_divider' ) ? parseInt( newSection.closest( '.edit_field_type_divider' ).getAttribute( 'data-fid' ) ) : 0;

		placeholder.remove();
		ui.helper.remove();

		const $previousContainerFields = $previousFieldContainer.length ? getFieldsInRow( $previousFieldContainer ) : [];
		maybeUpdatePreviousFieldContainerAfterDrop( $previousFieldContainer, $previousContainerFields );
		maybeUpdateDraggableClassAfterDrop( draggable, $previousContainerFields );

		if ( previousSectionId !== newSectionId ) {
			updateFieldAfterMovingBetweenSections( jQuery( draggable ), previousSection );
		}

		debouncedSyncAfterDragAndDrop();
	}

	/**
	 * If a page if collapsed, expand it before dragging since only the page break will move.
	 *
	 * @param {Element} placeholder
	 * @returns {void}
	 */
	function maybeOpenCollapsedPage( placeholder ) {
		if ( ! placeholder.previousElementSibling || ! placeholder.previousElementSibling.classList.contains( 'frm-is-collapsed' ) ) {
			return;
		}

		const $pageBreakField = jQuery( placeholder ).prevUntil( '[data-type="break"]' );
		if ( ! $pageBreakField.length ) {
			return;
		}

		const collapseButton = $pageBreakField.find( '.frm-collapse-page' ).get( 0 );
		if ( collapseButton ) {
			collapseButton.click();
		}
	}

	function maybeUpdatePreviousFieldContainerAfterDrop( $previousFieldContainer, $previousContainerFields ) {
		if ( ! $previousFieldContainer.length ) {
			return;
		}

		if ( $previousContainerFields.length ) {
			syncLayoutClasses( $previousContainerFields.first() );
		} else {
			maybeDeleteAnEmptyFieldGroup( $previousFieldContainer.get( 0 ) );
		}
	}

	function maybeUpdateDraggableClassAfterDrop( draggable, $previousContainerFields ) {
		if ( 0 !== $previousContainerFields.length || 1 !== getFieldsInRow( jQuery( draggable.parentNode ) ).length ) {
			syncLayoutClasses( jQuery( draggable ) );
		}
	}

	/**
	 * Remove an empty field group, but don't remove an empty section.
	 *
	 * @param {Element} previousFieldContainer
	 * @returns {void}
	 */
	function maybeDeleteAnEmptyFieldGroup( previousFieldContainer ) {
		const closestFieldBox = previousFieldContainer.closest( 'li.frm_field_box' );
		if ( closestFieldBox && ! closestFieldBox.classList.contains( 'edit_field_type_divider' ) ) {
			closestFieldBox.remove();
		}
	}

	function handleDragOverYAxis({ droppable, y, placeholder }) {
		const $list = jQuery( droppable );

		let top;

		$children = $list.children().not( '.edit_field_type_end_divider' );
		if ( 0 === $children.length ) {
			$list.prepend( placeholder );
			top = 0;
		} else {
			const insertAtIndex = determineIndexBasedOffOfMousePositionInList( $list, y );

			if ( insertAtIndex === $children.length ) {
				const $lastChild = jQuery( $children.get( insertAtIndex - 1 ) );
				top = $lastChild.offset().top + $lastChild.outerHeight();
				$list.append( placeholder );

				// Make sure nothing gets inserted after the end divider.
				const $endDivider = $list.children( '.edit_field_type_end_divider' );
				if ( $endDivider.length ) {
					$list.append( $endDivider );
				}
			} else {
				top = jQuery( $children.get( insertAtIndex ) ).offset().top;
				jQuery( $children.get( insertAtIndex ) ).before( placeholder );
			}
		}

		top -= $list.offset().top;
		placeholder.style.top = top + 'px';
	}

	function determineIndexBasedOffOfMousePositionInList( $list, y ) {
		const $items = $list.children().not( '.edit_field_type_end_divider' );
		const length = $items.length;

		let index, item, itemTop, returnIndex;

		returnIndex = 0;
		for ( index = length - 1; index >= 0; --index ) {
			item    = $items.get( index );
			itemTop = jQuery( item ).offset().top;
			if ( y > itemTop ) {
				returnIndex = index;
				if ( y > itemTop + ( jQuery( item ).outerHeight() / 2 ) ) {
					returnIndex = index + 1;
				}
				break;
			}
		}

		return returnIndex;
	}

	function handleDragOverFieldGroup({ droppable, x, placeholder }) {
		const $row = jQuery( droppable );
		const $children = getFieldsInRow( $row );

		if ( ! $children.length ) {
			return;
		}

		let left;
		const insertAtIndex = determineIndexBasedOffOfMousePositionInRow( $row, x );

		if ( insertAtIndex === $children.length ) {
			const $lastChild = jQuery( $children.get( insertAtIndex - 1 ) );
			left = $lastChild.offset().left + $lastChild.outerWidth();
			$row.append( placeholder );
		} else {
			left = jQuery( $children.get( insertAtIndex ) ).offset().left;
			jQuery( $children.get( insertAtIndex ) ).before( placeholder );

			const amountToOffsetLeftBy = 0 === insertAtIndex ? 4 : 8; // Offset by 8 in between rows, but only 4 for the first item in a group.
			left -= amountToOffsetLeftBy; // Offset the placeholder slightly so it appears between two fields.
		}

		left -= $row.offset().left;

		placeholder.style.left = left + 'px';
	}

	function syncAfterDragAndDrop() {
		fixUnwrappedListItems();
		toggleSectionHolder();
		maybeFixEndDividers();
		maybeDeleteEmptyFieldGroups();
		updateFieldOrder();

		const event = new Event( 'frm_sync_after_drag_and_drop', { bubbles: false });
		document.dispatchEvent( event );
	}

	function maybeFixEndDividers() {
		document.querySelectorAll( '.edit_field_type_end_divider' ).forEach(
			endDivider => endDivider.parentNode.appendChild( endDivider )
		);
	}

	function maybeDeleteEmptyFieldGroups() {
		document.querySelectorAll( 'li.form_field_box:not(.form-field)' ).forEach(
			fieldGroup => ! fieldGroup.children.length && fieldGroup.remove()
		);
	}

	function fixUnwrappedListItems() {
		const lists = document.querySelectorAll( 'ul#frm-show-fields, ul.start_divider' );
		lists.forEach(
			list => {
				list.childNodes.forEach(
					child => {
						if ( 'undefined' === typeof child.classList ) {
							return;
						}

						if ( child.classList.contains( 'edit_field_type_end_divider' ) ) {
							// Never wrap end divider in place.
							return;
						}

						if ( 'undefined' !== typeof child.classList && child.classList.contains( 'form-field' ) ) {
							wrapFieldLiInPlace( child );
						}
					}
				);
			}
		);
	}

	function deleteEmptyDividerWrappers() {
		const dividers = document.querySelectorAll( 'ul.start_divider' );
		if ( ! dividers.length ) {
			return;
		}
		dividers.forEach(
			function( divider ) {
				const children = [].slice.call( divider.children );
				children.forEach(
					function( child ) {
						if ( 0 === child.children.length ) {
							child.remove();
						} else if ( 1 === child.children.length && 'ul' === child.firstElementChild.nodeName.toLowerCase() && 0 === child.firstElementChild.children.length ) {
							child.remove();
						}
					}
				);
			}
		);
	}

	function getFieldsInRow( $row ) {
		let $fields = jQuery();

		const row = $row.get( 0 );
		if ( ! row.children ) {
			return $fields;
		}

		Array.from( row.children ).forEach(
			child => {
				if ( 'none' === child.style.display ) {
					return;
				}

				const classes = child.classList;
				if ( ! classes.contains( 'form-field' ) || classes.contains( 'edit_field_type_end_divider' ) || classes.contains( 'frm-sortable-helper' ) ) {
					return;
				}

				$fields = $fields.add( child );
			}
		);
		return $fields;
	}

	function determineIndexBasedOffOfMousePositionInRow( $row, x ) {
		var $inputs = getFieldsInRow( $row ),
			length = $inputs.length,
			index, input, inputLeft, returnIndex;

		returnIndex = 0;
		for ( index = length - 1; index >= 0; --index ) {
			input = $inputs.get( index );
			inputLeft = jQuery( input ).offset().left;
			if ( x > inputLeft ) {
				returnIndex = index;
				if ( x > inputLeft + ( jQuery( input ).outerWidth() / 2 ) ) {
					returnIndex = index + 1;
				}
				break;
			}
		}

		return returnIndex;
	}

	function syncLayoutClasses( $item, type ) {
		var $fields, size, layoutClasses, classToAddFunction;

		if ( 'undefined' === typeof type ) {
			type = 'even';
		}

		$fields = $item.parent().children( 'li.form-field, li.frmbutton_loadingnow' ).not( '.edit_field_type_end_divider' );
		size = $fields.length;
		layoutClasses = getLayoutClasses();

		if ( 'even' === type && 5 !== size ) {
			$fields.each( getSyncLayoutClass( layoutClasses, getEvenClassForSize( size ) ) );
		} else if ( 'clear' === type ) {
			$fields.each( getSyncLayoutClass( layoutClasses, '' ) );
		} else {
			if ( -1 !== [ 'left', 'right', 'middle', 'even' ].indexOf( type ) ) {
				classToAddFunction = function( index ) {
					return getClassForBlock( size, type, index );
				};
			} else {
				classToAddFunction = function( index ) {
					var size = type[ index ];
					return getLayoutClassForSize( size );
				};
			}

			$fields.each( getSyncLayoutClass( layoutClasses, classToAddFunction ) );
		}

		updateFieldGroupControls( $item.parent(), $fields.length );
	}

	function updateFieldGroupControls( $row, count ) {
		var rowOffset, shouldShowControls, controls;

		rowOffset = $row.offset();

		if ( 'undefined' === typeof rowOffset ) {
			return;
		}

		shouldShowControls = count >= 2;

		controls = document.getElementById( 'frm_field_group_controls' );
		if ( null === controls ) {
			if ( ! shouldShowControls ) {
				// exit early. if we do not need controls and they do not exist, do nothing.
				return;
			}

			controls = div();
			controls.id = 'frm_field_group_controls';
			controls.setAttribute( 'role', 'group' );
			controls.setAttribute( 'tabindex', 0 );
			setFieldControlsHtml( controls );
			document.getElementById( 'frm_builder_page' ).appendChild( controls );
		}

		$row.append( controls );
		controls.style.display = shouldShowControls ? 'block' : 'none';
	}

	function setFieldControlsHtml( controls ) {
		var layoutOption, moveOption;

		layoutOption = document.createElement( 'span' );
		layoutOption.innerHTML = '<svg class="frmsvg"><use xlink:href="#frm_field_group_layout_icon"></use></svg>';
		const layoutOptionLabel = __( 'Set Row Layout', 'formidable' );
		addTooltip( layoutOption, layoutOptionLabel );
		makeTabbable( layoutOption, layoutOptionLabel );

		moveOption = document.createElement( 'span' );
		moveOption.innerHTML = '<svg class="frmsvg"><use xlink:href="#frm_thick_move_icon"></use></svg>';
		moveOption.classList.add( 'frm-move' );
		const moveOptionLabel = __( 'Move Field Group', 'formidable' );
		addTooltip( moveOption, moveOptionLabel );
		makeTabbable( moveOption, moveOptionLabel );

		controls.innerHTML = '';
		controls.appendChild( layoutOption );
		controls.appendChild( moveOption );
		controls.appendChild( getFieldControlsDropdown() );
	}

	function addTooltip( element, title ) {
		element.setAttribute( 'data-toggle', 'tooltip' );
		element.setAttribute( 'data-container', 'body' );
		element.setAttribute( 'title', title );
		element.addEventListener(
			'mouseover',
			function() {
				if ( null === element.getAttribute( 'data-original-title' ) ) {
					jQuery( element ).tooltip();
				}
			}
		);
	}

	function getFieldControlsDropdown() {
		var dropdown, trigger, ul;

		dropdown = document.createElement( 'span' );
		dropdown.classList.add( 'dropdown' );

		trigger = document.createElement( 'a' );
		trigger.classList.add( 'frm_bstooltip', 'frm-hover-icon', 'frm-dropdown-toggle', 'dropdown-toggle' );
		trigger.setAttribute( 'title', __( 'More Options', 'formidable' ) );
		trigger.setAttribute( 'data-toggle', 'dropdown' );
		trigger.setAttribute( 'data-container', 'body' );
		makeTabbable( trigger, __( 'More Options', 'formidable' ) );
		trigger.innerHTML = '<span><svg class="frmsvg"><use xlink:href="#frm_thick_more_vert_icon"></use></svg></span>';
		dropdown.appendChild( trigger );

		ul = document.createElement( 'div' );
		ul.classList.add( 'frm-dropdown-menu', 'dropdown-menu', 'dropdown-menu-right' );
		ul.setAttribute( 'role', 'menu' );
		dropdown.appendChild( ul );

		return dropdown;
	}

	function getSyncLayoutClass( layoutClasses, classToAdd ) {
		return function( itemIndex ) {
			var currentClassToAdd, length, layoutClassIndex, currentClass, activeLayoutClass, fieldId, layoutClassesInput;

			currentClassToAdd = 'function' === typeof classToAdd ? classToAdd( itemIndex ) : classToAdd;
			length = layoutClasses.length;
			activeLayoutClass = false;
			for ( layoutClassIndex = 0; layoutClassIndex < length; ++layoutClassIndex ) {
				currentClass = layoutClasses[ layoutClassIndex ];
				if ( this.classList.contains( currentClass ) ) {
					activeLayoutClass = currentClass;
					break;
				}
			}

			fieldId = this.dataset.fid;

			if ( 'undefined' === typeof fieldId ) {
				// we are syncing the drag/drop placeholder before the actual field has loaded.
				// this will get called again afterward and the input will exist then.
				this.classList.add( currentClassToAdd );
				return;
			}

			moveFieldSettings( document.getElementById( 'frm-single-settings-' + fieldId ) );
			layoutClassesInput = document.getElementById( 'frm_classes_' + fieldId );

			if ( null === layoutClassesInput ) {
				// not every field type has a layout class input.
				return;
			}

			if ( false === activeLayoutClass ) {
				if ( '' !== currentClassToAdd ) {
					layoutClassesInput.value = layoutClassesInput.value.concat( ' ' + currentClassToAdd );
				}
			} else {
				this.classList.remove( activeLayoutClass );
				layoutClassesInput.value = layoutClassesInput.value.replace( activeLayoutClass, currentClassToAdd );
			}

			if ( this.classList.contains( 'frm_first' ) ) {
				this.classList.remove( 'frm_first' );
				layoutClassesInput.value = layoutClassesInput.value.replace( 'frm_first', '' ).trim();
			}

			if ( 0 === itemIndex ) {
				this.classList.add( 'frm_first' );
				layoutClassesInput.value = layoutClassesInput.value.concat( ' frm_first' );
			}

			jQuery( layoutClassesInput ).trigger( 'change' );
		};
	}

	function getLayoutClasses() {
		return [ 'frm_full', 'frm_half', 'frm_third', 'frm_fourth', 'frm_sixth', 'frm_two_thirds', 'frm_three_fourths', 'frm1', 'frm2', 'frm3', 'frm4', 'frm5', 'frm6', 'frm7', 'frm8', 'frm9', 'frm10', 'frm11', 'frm12' ];
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
		if ( typeof currentItem !== 'undefined' && ! currentItem.hasClass( 'edit_field_type_divider' ) ) {
			section = currentItem.closest( '.edit_field_type_divider' );
		}
		return section;
	}

	// Get the form ID where a field is dropped
	function getFormIdForFieldPlacement( section ) {
		var formId = '';

		if ( typeof section[0] !== 'undefined' ) {
			var sDivide = section.children( '.start_divider' );
			sDivide.children( '.edit_field_type_end_divider' ).appendTo( sDivide );
			if ( typeof section.attr( 'data-formid' ) !== 'undefined' ) {
				var fieldId = section.attr( 'data-fid' );
				formId = jQuery( 'input[name="field_options[form_select_' + fieldId + ']"]' ).val();
			}
		}

		if ( typeof formId === 'undefined' || formId === '' ) {
			formId = thisFormId;
		}

		return formId;
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
	 * @param {Object} currentItem
	 * @param {Object} previousSection
	 * @returns {void}
	 */
	function updateFieldAfterMovingBetweenSections( currentItem, previousSection ) {
		if ( ! currentItem.hasClass( 'form-field' ) ) {
			// currentItem is a field group. Call for children recursively.
			getFieldsInRow( jQuery( currentItem.get( 0 ).firstChild ) ).each(
				function() {
					updateFieldAfterMovingBetweenSections( jQuery( this ), previousSection );
				}
			);
			return;
		}

		const fieldId        = currentItem.attr( 'id' ).replace( 'frm_field_id_', '' );
		const section        = getSectionForFieldPlacement( currentItem );
		const formId         = getFormIdForFieldPlacement( section );
		const sectionId      = getSectionIdForFieldPlacement( section );
		const previousFormId = previousSection ? getFormIdForFieldPlacement( jQuery( previousSection.parentNode ) ) : 0;

		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'frm_update_field_after_move',
				form_id: formId,
				field: fieldId,
				section_id: sectionId,
				previous_form_id: previousFormId,
				nonce: frmGlobal.nonce
			},
			success: function() {
				toggleSectionHolder();
				updateInSectionValue( fieldId, sectionId );
			}
		});
	}

	// Update the in_section field value
	function updateInSectionValue( fieldId, sectionId ) {
		document.getElementById( 'frm_in_section_' + fieldId ).value = sectionId;
	}

	/**
	 * Add a new field by dragging and dropping it from the Fields sidebar
	 *
	 * @param {string} fieldType
	 */
	function insertNewFieldByDragging( fieldType ) {
		const placeholder  = document.getElementById( 'frm_drag_placeholder' );
		const loadingID    = fieldType.replace( '|', '-' ) + '_' + getAutoId();
		const loading      = tag(
			'li',
			{
				id: loadingID,
				className: 'frm-wait frmbutton_loadingnow'
			}
		);
		const $placeholder = jQuery( loading );
		const currentItem  = jQuery( placeholder );
		const section      = getSectionForFieldPlacement( currentItem );
		const formId       = getFormIdForFieldPlacement( section );
		const sectionId    = getSectionIdForFieldPlacement( section );

		placeholder.parentNode.insertBefore( loading, placeholder );
		placeholder.remove();
		syncLayoutClasses( $placeholder );

		let hasBreak = 0;
		if ( 'summary' === fieldType ) {
			// see if we need to insert a page break before this newly-added summary field. Check for at least 1 page break
			hasBreak = jQuery( '.frmbutton_loadingnow#' + loadingID ).prevAll( 'li[data-type="break"]' ).length ? 1 : 0;
		}

		jQuery.ajax({
			type: 'POST', url: ajaxurl,
			data: {
				action: 'frm_insert_field',
				form_id: formId,
				field_type: fieldType,
				section_id: sectionId,
				nonce: frmGlobal.nonce,
				has_break: hasBreak
			},
			success: function( msg ) {
				let replaceWith;
				document.getElementById( 'frm_form_editor_container' ).classList.add( 'frm-has-fields' );
				const $siblings = $placeholder.siblings( 'li.form-field' ).not( '.edit_field_type_end_divider' );
				if ( ! $siblings.length ) {
					// if dragging into a new row, we need to wrap the li first.
					replaceWith = wrapFieldLi( msg );
				} else {
					replaceWith = msgAsjQueryObject( msg );
					if ( ! $placeholder.get( 0 ).parentNode.parentNode.classList.contains( 'ui-draggable' ) ) {
						// If a field group wasn't draggable because it only had a single field, make it draggable.
						makeDraggable( $placeholder.get( 0 ).parentNode.parentNode, '.frm-move' );
					}
				}
				$placeholder.replaceWith( replaceWith );
				updateFieldOrder();
				afterAddField( msg, false );
				if ( $siblings.length ) {
					syncLayoutClasses( $siblings.first() );
				}
				toggleSectionHolder();

				if ( ! $siblings.length ) {
					makeDroppable( replaceWith.get( 0 ).querySelector( 'ul.frm_sorting' ) );
					makeDraggable( replaceWith.get( 0 ).querySelector( 'li.form-field' ), '.frm-move' );
				} else {
					makeDraggable( replaceWith.get( 0 ), '.frm-move' );
				}

			},
			error: handleInsertFieldError
		});
	}

	function moveFieldThatAlreadyExists( draggable, placeholder ) {
		placeholder.parentNode.insertBefore( draggable, placeholder );
	}

	function msgAsjQueryObject( msg ) {
		const element = div();
		element.innerHTML = msg;
		return jQuery( element.firstChild );
	}

	function handleInsertFieldError( jqXHR, _, errorThrown ) {
		maybeShowInsertFieldError( errorThrown, jqXHR );
	}

	function maybeShowInsertFieldError( errorThrown, jqXHR ) {
		if ( ! jqXHRAborted( jqXHR ) ) {
			infoModal( errorThrown + '. Please try again.' );
		}
	}

	function jqXHRAborted( jqXHR ) {
		return jqXHR.status === 0 || jqXHR.readyState === 0;
	}

	/**
	 * Get a unique id that automatically increments with every function call.
	 * Can be used for any UI that requires a unique id.
	 * Not to be used in data.
	 *
	 * @returns {integer}
	 */
	function getAutoId() {
		return ++autoId;
	}

	// Don't allow page break, embed form, or section inside section field
	// Don't allow page breaks inside of field groups.
	// Don't allow field groups with sections inside of sections.
	// Don't allow field groups in field groups.
	// Don't allow hidden fields inside of field groups but allow them in sections.
	function allowDrop( draggable, droppable ) {
		if ( false === droppable ) {
			// Don't show drop placeholder if dragging somewhere off of the droppable area.
			return false;
		}

		if ( droppable.closest( '.frm-sortable-helper' ) ) {
			// Do not allow drop into draggable.
			return false;
		}

		if ( 'frm-show-fields' === droppable.id ) {
			// Everything can be dropped into the main list of fields.
			return true;
		}

		if ( ! droppable.classList.contains( 'start_divider' ) ) {
			const $fieldsInRow = getFieldsInRow( jQuery( droppable ) );
			if ( ! groupCanFitAnotherField( $fieldsInRow, jQuery( draggable ) ) ) {
				// Field group is full and cannot accept another field.
				return false;
			}
		}

		const isNewField = draggable.classList.contains( 'frm-new-field' );
		if ( isNewField ) {
			return allowNewFieldDrop( draggable, droppable );
		}

		return allowMoveField( draggable, droppable );
	}

	// Don't allow a new page break or hidden field in a field group.
	// Don't allow a new field into a field group that includes a page break or hidden field.
	// Don't allow a new section inside of a section.
	// Don't allow an embedded form in a section.
	function allowNewFieldDrop( draggable, droppable ) {
		const classes           = draggable.classList;
		const newPageBreakField = classes.contains( 'frm_tbreak' );
		const newHiddenField    = classes.contains( 'frm_thidden' );
		const newSectionField   = classes.contains( 'frm_tdivider' );
		const newEmbedField     = classes.contains( 'frm_tform' );

		const newFieldWillBeAddedToAGroup = ! ( 'frm-show-fields' === droppable.id || droppable.classList.contains( 'start_divider' ) );
		if ( newFieldWillBeAddedToAGroup ) {
			if ( groupIncludesBreakOrHidden( droppable ) ) {
				// Never allow any field beside a page break or a hidden field.
				return false;
			}

			return ! newHiddenField && ! newPageBreakField;
		}

		const fieldTypeIsAlwaysAllowed = ! newPageBreakField && ! newHiddenField && ! newSectionField && ! newEmbedField;
		if ( fieldTypeIsAlwaysAllowed ) {
			return true;
		}

		const newFieldWillBeAddedToASection = droppable.classList.contains( 'start_divider' ) || null !== droppable.closest( '.start_divider' );
		if ( newFieldWillBeAddedToASection ) {
			// Don't allow a section or an embedded form in a section.
			return ! newEmbedField && ! newSectionField;
		}

		return true;
	}

	function allowMoveField( draggable, droppable ) {
		if ( isFieldGroup( draggable ) ) {
			return allowMoveFieldGroup( draggable, droppable );
		}

		const isPageBreak = draggable.classList.contains( 'edit_field_type_break' );
		if ( isPageBreak ) {
			// Page breaks are only allowed in the main list of fields, not in sections or in field groups.
			return false;
		}

		if ( droppable.classList.contains( 'start_divider' ) ) {
			return allowMoveFieldToSection( draggable );
		}

		const isHiddenField = draggable.classList.contains( 'edit_field_type_hidden' );
		if ( isHiddenField ) {
			// Hidden fields should not be added to field groups since they're not shown and don't make sense with the grid distribution.
			return false;
		}

		return allowMoveFieldToGroup( draggable, droppable );
	}

	function isFieldGroup( draggable ) {
		return draggable.classList.contains( 'frm_field_box' ) && ! draggable.classList.contains( 'form-field' );
	}

	function allowMoveFieldGroup( fieldGroup, droppable ) {
		if ( droppable.classList.contains( 'start_divider' ) && null === fieldGroup.querySelector( '.start_divider' ) ) {
			// Allow a field group with no section inside of a section.
			return true;
		}
		return false;
	}

	function allowMoveFieldToSection( draggable ) {
		const draggableIncludeEmbedForm = draggable.classList.contains( 'edit_field_type_form' ) || draggable.querySelector( '.edit_field_type_form' );
		if ( draggableIncludeEmbedForm ) {
			// Do not allow an embedded form inside of a section.
			return false;
		}

		const draggableIncludesSection = draggable.classList.contains( 'edit_field_type_divider' ) || draggable.querySelector( '.edit_field_type_divider' );
		if ( draggableIncludesSection ) {
			// Do not allow a section inside of a section.
			return false;
		}

		return true;
	}

	function allowMoveFieldToGroup( draggable, group ) {
		if ( groupIncludesBreakOrHidden( group ) ) {
			// Never allow any field beside a page break or a hidden field.
			return false;
		}

		const isFieldGroup = jQuery( draggable ).children( 'ul.frm_sorting' ).not( '.start_divider' ).length > 0;
		if ( isFieldGroup ) {
			// Do not allow a field group directly inside of a field group unless it's in a section.
			return false;
		}

		const draggableIncludesASection = draggable.classList.contains( 'edit_field_type_divider' ) || draggable.querySelector( '.edit_field_type_divider' );
		const draggableIsEmbedField     = draggable.classList.contains( 'edit_field_type_form' );
		const groupIsInASection         = null !== group.closest( '.start_divider' );
		if ( groupIsInASection && ( draggableIncludesASection || draggableIsEmbedField ) ) {
			// Do not allow a section or an embed field inside of a section.
			return false;
		}

		return true;
	}

	function groupIncludesBreakOrHidden( group ) {
		return null !== group.querySelector( '.edit_field_type_break, .edit_field_type_hidden' );
	}

	function groupCanFitAnotherField( fieldsInRow, $field ) {
		var fieldId;
		if ( fieldsInRow.length < 6 ) {
			return true;
		}
		if ( fieldsInRow.length > 6 ) {
			return false;
		}
		fieldId = $field.attr( 'data-fid' );
		// allow 6 if we're not changing field groups.
		return 1 === jQuery( fieldsInRow ).filter( '[data-fid="' + fieldId + '"]' ).length;
	}

	function loadFields( fieldId ) {
		const thisField      = document.getElementById( fieldId );
		const $thisField     = jQuery( thisField );
		const field          = [];
		const addHtmlToField = element => {
			const frmHiddenFdata = element.querySelector( '.frm_hidden_fdata' );
			element.classList.add( 'frm_load_now' );
			if ( frmHiddenFdata !== null ) {
				field.push( frmHiddenFdata.innerHTML );
			}
		};

		let nextElement = thisField;
		addHtmlToField( nextElement );
		while ( nextElement.nextElementSibling && field.length < 15 ) {
			addHtmlToField( nextElement.nextElementSibling );
			nextElement = nextElement.nextElementSibling;
		}

		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'frm_load_field',
				field: field,
				form_id: thisFormId,
				nonce: frmGlobal.nonce
			},
			success: html => handleAjaxLoadFieldSuccess( html, $thisField, field )
		});
	}

	function handleAjaxLoadFieldSuccess( html, $thisField, field ) {
		let key, $nextSet;

		html = html.replace( /^\s+|\s+$/g, '' );
		if ( html.indexOf( '{' ) !== 0 ) {
			jQuery( '.frm_load_now' ).removeClass( '.frm_load_now' ).html( 'Error' );
			return;
		}

		html = JSON.parse( html );
		for ( key in html ) {
			jQuery( '#frm_field_id_' + key ).replaceWith( html[key]);
			setupSortable( '#frm_field_id_' + key + '.edit_field_type_divider ul.frm_sorting' );
			makeDraggable( document.getElementById( 'frm_field_id_' + key ) );
		}

		$nextSet = $thisField.nextAll( '.frm_field_loading:not(.frm_load_now)' );
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
		maybeHideQuantityProductFieldOption();

		const loadedEvent     = new Event( 'frm_ajax_loaded_field', { bubbles: false });
		loadedEvent.frmFields = field.map( f => JSON.parse( f ) );
		document.dispatchEvent( loadedEvent );
	}

	function addFieldClick() {
		/*jshint validthis:true */
		const $thisObj = jQuery( this );
		// there is no real way to disable a <a> (with a valid href attribute) in HTML - https://css-tricks.com/how-to-disable-links/
		if ( $thisObj.hasClass( 'disabled' ) ) {
			return false;
		}

		const $button = $thisObj.closest( '.frmbutton' );
		const fieldType = $button.attr( 'id' );

		let hasBreak = 0;
		if ( 'summary' === fieldType ) {
			hasBreak = $newFields.children( 'li[data-type="break"]' ).length > 0 ? 1 : 0;
		}

		const formId = thisFormId;
		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'frm_insert_field',
				form_id: formId,
				field_type: fieldType,
				section_id: 0,
				nonce: frmGlobal.nonce,
				has_break: hasBreak
			},
			success: function( msg ) {
				document.getElementById( 'frm_form_editor_container' ).classList.add( 'frm-has-fields' );
				const replaceWith = wrapFieldLi( msg );
				$newFields.append( replaceWith );
				afterAddField( msg, true );

				replaceWith.each(
					function() {
						makeDroppable( this.querySelector( 'ul.frm_sorting' ) );
						makeDraggable( this.querySelector( '.form-field' ), '.frm-move' );
					}
				);
			},
			error: handleInsertFieldError
		});
		return false;
	}

	function maybeHideQuantityProductFieldOption() {
		var hide = true,
			opts = document.querySelectorAll( '.frmjs_prod_field_opt_cont' );

		if ( $newFields.find( 'li.edit_field_type_product' ).length > 1 ) {
			hide = false;
		}

		for ( var i = 0; i < opts.length; i++ ) {
			if ( hide ) {
				opts[ i ].classList.add( 'frm_hidden' );
			} else {
				opts[ i ].classList.remove( 'frm_hidden' );
			}
		}
	}

	function duplicateField() {
		var $field, fieldId, children, newRowId, fieldOrder;

		$field = jQuery( this ).closest( 'li.form-field' );

		if ( $field.hasClass( 'frm-page-collapsed' ) ) {
			return false;
		}

		closeOpenFieldDropdowns();
		fieldId = $field.data( 'fid' );
		children = fieldsInSection( fieldId );
		newRowId = this.getAttribute( 'frm-target-row-id' );

		if ( null !== newRowId ) {
			fieldOrder = this.getAttribute( 'frm-field-order' );
		}

		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'frm_duplicate_field',
				field_id: fieldId,
				form_id: thisFormId,
				children: children,
				nonce: frmGlobal.nonce
			},
			success: function( msg ) {
				var newRow;

				let replaceWith;

				if ( null !== newRowId ) {
					newRow = document.getElementById( newRowId );
					if ( null !== newRow ) {
						replaceWith = msgAsjQueryObject( msg );
						jQuery( newRow ).append( replaceWith );
						makeDraggable( replaceWith.get( 0 ), '.frm-move' );
						if ( null !== fieldOrder ) {
							newRow.lastElementChild.setAttribute( 'frm-field-order', fieldOrder );
						}
						jQuery( newRow ).trigger(
							'frm_added_duplicated_field_to_row',
							{
								duplicatedFieldHtml: msg,
								originalFieldId: fieldId
							}
						);
						afterAddField( msg, false );
						return;
					}
				}

				if ( $field.siblings( 'li.form-field' ).length ) {
					replaceWith = msgAsjQueryObject( msg );
					$field.after( replaceWith );
					syncLayoutClasses( $field );
					makeDraggable( replaceWith.get( 0 ), '.frm-move' );
				} else {
					replaceWith = wrapFieldLi( msg );
					$field.parent().parent().after( replaceWith );
					makeDroppable( replaceWith.get( 0 ).querySelector( 'ul.frm_sorting' ) );
					makeDraggable( replaceWith.get( 0 ).querySelector( 'li.form-field' ), '.frm-move' );
				}

				updateFieldOrder();
				afterAddField( msg, false );
				maybeDuplicateUnsavedSettings( fieldId, msg );
				toggleOneSectionHolder( replaceWith.find( '.start_divider' ) );
				$field[0].querySelector( '.frm-dropdown-menu.dropdown-menu-right' )?.classList.remove( 'show' );
			}
		});
		return false;
	}

	function maybeDuplicateUnsavedSettings( originalFieldId, newFieldHtml ) {
		var originalSettings, newFieldId, copySettings, fieldOptionKeys, originalDefault, copyDefault;

		originalSettings = document.getElementById( 'frm-single-settings-' + originalFieldId );
		if ( null === originalSettings ) {
			return;
		}

		newFieldId = jQuery( newFieldHtml ).attr( 'data-fid' );
		if ( 'undefined' === typeof newFieldId ) {
			return;
		}

		copySettings = document.getElementById( 'frm-single-settings-' + newFieldId );
		if ( null === copySettings ) {
			return;
		}

		fieldOptionKeys = [
			'name', 'required', 'unique', 'read_only', 'placeholder', 'description', 'size', 'max', 'format', 'prepend', 'append', 'separate_value'
		];

		originalSettings.querySelectorAll( 'input[name^="field_options["], textarea[name^="field_options["]' ).forEach(
			function( originalSetting ) {
				var key, tagType, copySetting;

				key = getKeyFromSettingInput( originalSetting );

				if ( 'options' === key ) {
					copyOption( originalSetting, copySettings, originalFieldId, newFieldId );
					return;
				}

				if ( -1 === fieldOptionKeys.indexOf( key ) ) {
					return;
				}

				tagType = originalSetting.matches( 'input' ) ? 'input' : 'textarea';
				copySetting = copySettings.querySelector( tagType + '[name="field_options[' + key + '_' + newFieldId + ']"]' );
				if ( null === copySetting ) {
					return;
				}

				if ( 'checkbox' === originalSetting.type ) {
					if ( originalSetting.checked !== copySetting.checked ) {
						jQuery( copySetting ).trigger( 'click' );
					}
				} else if ( 'text' === originalSetting.type || 'textarea' === tagType ) {
					if ( originalSetting.value !== copySetting.value ) {
						copySetting.value = originalSetting.value;
						jQuery( copySetting ).trigger( 'change' );
					}
				}
			}
		);

		originalDefault = originalSettings.querySelector( 'input[name="default_value_' + originalFieldId + '"]' );
		if ( null !== originalDefault ) {
			copyDefault = copySettings.querySelector( 'input[name="default_value_' + newFieldId + '"]' );
			if ( null !== copyDefault && originalDefault.value !== copyDefault.value ) {
				copyDefault.value = originalDefault.value;
				jQuery( copyDefault ).trigger( 'change' );
			}
		}
	}

	function copyOption( originalSetting, copySettings, originalFieldId, newFieldId ) {
		var remainingKeyDetails, copyKey, copySetting;
		remainingKeyDetails = originalSetting.name.substr( 23 + ( '' + originalFieldId ).length );
		copyKey = 'field_options[options_' + newFieldId + ']' + remainingKeyDetails;
		copySetting = copySettings.querySelector( 'input[name="' + copyKey + '"]' );
		if ( null !== copySetting && copySetting.value !== originalSetting.value ) {
			copySetting.value = originalSetting.value;
			jQuery( copySetting ).trigger( 'change' );
		}
	}

	function getKeyFromSettingInput( input ) {
		var nameWithoutPrefix, nameSplit;
		nameWithoutPrefix = input.name.substr( 14 );
		nameSplit = nameWithoutPrefix.split( '_' );
		nameSplit.pop();
		return nameSplit.join( '_' );
	}

	function closeOpenFieldDropdowns() {
		var openSettings = document.querySelector( '.frm-field-settings-open' );
		if ( null !== openSettings ) {
			openSettings.classList.remove( 'frm-field-settings-open' );
			jQuery( document ).off( 'click', '#frm_builder_page', handleClickOutsideOfFieldSettings );
			jQuery( '.frm-field-action-icons .dropdown.open' ).removeClass( 'open' );
		}
	}

	function handleClickOutsideOfFieldSettings( event ) {
		if ( ! jQuery( event.originalEvent.target ).closest( '.frm-field-action-icons' ).length ) {
			closeOpenFieldDropdowns();
		}
	}

	function checkForMultiselectKeysOnMouseMove( event ) {
		var keyIsDown = ! ! ( event.ctrlKey || event.metaKey || event.shiftKey );
		jQuery( document.getElementById( 'frm_builder_page' ) ).toggleClass( 'frm-multiselect-key-is-down', keyIsDown );
		checkForActiveHoverTarget( event );
	}

	function checkForActiveHoverTarget( event ) {
		var container, elementFromPoint, list, previousHoverTarget;

		container = postBodyContent;
		if ( container.classList.contains( 'frm-dragging-field' ) ) {
			return;
		}

		if ( null !== document.querySelector( '.frm-field-group-hover-target .frm-field-settings-open' ) ) {
			// do not set a hover target if a dropdown is open for the current hover target.
			return;
		}

		elementFromPoint = document.elementFromPoint( event.clientX, event.clientY );
		if ( null !== elementFromPoint && ! elementFromPoint.classList.contains( 'edit_field_type_divider' ) ) {

			list = elementFromPoint.closest( 'ul.frm_sorting' );

			if ( null !== list && ! list.classList.contains( 'start_divider' ) && 'frm-show-fields' !== list.id ) {
				previousHoverTarget = maybeRemoveGroupHoverTarget();
				if ( false !== previousHoverTarget && ! jQuery( previousHoverTarget ).is( list ) ) {
					destroyFieldGroupPopup();
				}
				updateFieldGroupControls( jQuery( list ), getFieldsInRow( jQuery( list ) ).length );
				list.classList.add( 'frm-field-group-hover-target' );
				jQuery( '#wpbody-content' ).on( 'mousemove', maybeRemoveHoverTargetOnMouseMove );
			}
		}
	}

	function maybeRemoveGroupHoverTarget() {
		var controls, previousHoverTarget;

		controls = document.getElementById( 'frm_field_group_controls' );
		if ( null !== controls ) {
			controls.style.display = 'none';
		}

		previousHoverTarget = document.querySelector( '.frm-field-group-hover-target' );
		if ( null === previousHoverTarget ) {
			return false;
		}

		jQuery( '#wpbody-content' ).off( 'mousemove', maybeRemoveHoverTargetOnMouseMove );
		previousHoverTarget.classList.remove( 'frm-field-group-hover-target' );
		return previousHoverTarget;
	}

	function maybeRemoveHoverTargetOnMouseMove( event ) {
		var elementFromPoint = document.elementFromPoint( event.clientX, event.clientY );
		if ( null !== elementFromPoint && null !== elementFromPoint.closest( '#frm-show-fields' ) ) {
			return;
		}
		maybeRemoveGroupHoverTarget();
	}

	function onFieldActionDropdownShow( isFieldGroup ) {
		unselectFieldGroups();
		// maybe offset the dropdown if it goes off of the right of the screen.
		setTimeout(
			function() {
				var ul, $ul;
				ul = document.querySelector( '.dropdown.show .frm-dropdown-menu' );
				if ( null === ul ) {
					return;
				}
				if ( null === ul.getAttribute( 'aria-label' ) ) {
					ul.setAttribute( 'aria-label', __( 'More Options', 'formidable' ) );
				}
				if ( 0 === ul.children.length ) {
					fillFieldActionDropdown( ul, true === isFieldGroup );
				}
				$ul = jQuery( ul );
				if ( $ul.offset().left > jQuery( window ).width() - $ul.outerWidth() ) {
					ul.style.left = ( -$ul.outerWidth() ) + 'px';
				}
				const firstAnchor = ul.firstElementChild.querySelector( 'a' );
				if ( firstAnchor ) {
					firstAnchor.focus();
				}
			},
			0
		);
	}

	function onFieldGroupActionDropdownShow() {
		onFieldActionDropdownShow( true );
	}

	function changeSectionStyle( e ) {
		const collapsedSection = e.target.closest( '.frm-section-collapsed' );
		if ( ! collapsedSection ) {
			return;
		}

		if ( e.type === 'show' ) {
			collapsedSection.style.zIndex = 3;
		} else {
			collapsedSection.style.zIndex = 1;
		}
	}

	function fillFieldActionDropdown( ul, isFieldGroup ) {
		var classSuffix, options;
		classSuffix = isFieldGroup ? '_field_group' : '_field';
		options = [ getDeleteActionOption( isFieldGroup ), getDuplicateActionOption( isFieldGroup ) ];
		if ( ! isFieldGroup ) {
			options.push(
				{ class: 'frm_select', icon: 'frm_settings_icon', label: __( 'Field Settings', 'formidable' ) }
			);
		}
		options.forEach(
			function( option ) {
				var li, anchor, span;
				li = document.createElement( 'div' );
				li.classList.add( 'frm_more_options_li', 'dropdown-item' );

				anchor = document.createElement( 'a' );
				anchor.classList.add( option.class + classSuffix );
				anchor.setAttribute( 'href', '#' );
				makeTabbable( anchor );

				span = document.createElement( 'span' );
				span.textContent = option.label;
				anchor.innerHTML = '<svg class="frmsvg"><use xlink:href="#' + option.icon + '"></use></svg>';
				anchor.appendChild( document.createTextNode( ' ' ) );
				anchor.appendChild( span );

				li.appendChild( anchor );
				ul.appendChild( li );
			}
		);
	}

	function getDeleteActionOption( isFieldGroup ) {
		var option = { class: 'frm_delete', icon: 'frm_delete_icon' };
		option.label = isFieldGroup ? __( 'Delete Group', 'formidable' ) : __( 'Delete', 'formidable' );
		return option;
	}

	function getDuplicateActionOption( isFieldGroup ) {
		var option = { class: 'frm_clone', icon: 'frm_clone_icon' };
		option.label = isFieldGroup ? __( 'Duplicate Group', 'formidable' ) : __( 'Duplicate', 'formidable' );
		return option;
	}

	function wrapFieldLi( field ) {
		const wrapper = div();

		if ( 'string' === typeof field ) {
			wrapper.innerHTML = field;
		} else {
			wrapper.appendChild( field );
		}

		let result = jQuery();
		Array.from( wrapper.children ).forEach(
			li => {
				result = result.add(
					jQuery( '<li>' )
						.addClass( 'frm_field_box' )
						.html(
							jQuery( '<ul>' ).addClass( 'frm_grid_container frm_sorting' ).append( li )
						)
				);
			}
		);

		return result;
	}

	function wrapFieldLiInPlace( li ) {
		const ul      = tag(
			'ul',
			{
				className: 'frm_grid_container frm_sorting'
			}
		);
		const wrapper = tag(
			'li',
			{
				className: 'frm_field_box',
				child: ul
			}
		);

		li.replaceWith( wrapper );
		ul.appendChild( li );

		makeDroppable( ul );
		makeDraggable( wrapper, '.frm-move' );
	}

	function afterAddField( msg, addFocus ) {
		var regex = /id="(\S+)"/,
			match = regex.exec( msg ),
			field = document.getElementById( match[1]),
			section = '#' + match[1] + '.edit_field_type_divider ul.frm_sorting.start_divider',
			$thisSection = jQuery( section ),
			type = field.getAttribute( 'data-type' ),
			toggled = false,
			$parentSection;

		setupSortable( section );

		if ( 'quantity' === type ) {
			// try to automatically attach a product field
			maybeSetProductField( field );
		}

		if ( 'product' === type || 'quantity' === type ) {
			// quantity too needs to be a part of the if stmt especially cos of the very
			// 1st quantity field (or even if it's just one quantity field in the form).
			maybeHideQuantityProductFieldOption();
		}

		if ( $thisSection.length ) {
			$thisSection.parent( '.frm_field_box' ).children( '.frm_no_section_fields' ).addClass( 'frm_block' );
		} else {
			$parentSection = jQuery( field ).closest( 'ul.frm_sorting.start_divider' );
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
				container.scroll({
					top: container.scrollHeight,
					left: 0,
					behavior: 'smooth'
				});
			}

			if ( toggled === false ) {
				toggleOneSectionHolder( $thisSection );
			}
		}

		deselectFields();
		initiateMultiselect();

		const addedEvent      = new Event( 'frm_added_field', { bubbles: false });
		addedEvent.frmField   = field;
		addedEvent.frmSection = section;
		addedEvent.frmType    = type;
		addedEvent.frmToggles = toggled;
		document.dispatchEvent( addedEvent );
	}

	function clearSettingsBox( preventFieldGroups ) {
		jQuery( '#new_fields .frm-single-settings' ).addClass( 'frm_hidden' );
		jQuery( '#frm-options-panel > .frm-single-settings' ).removeClass( 'frm_hidden' );
		deselectFields( preventFieldGroups );
	}

	function deselectFields( preventFieldGroups ) {
		jQuery( 'li.ui-state-default.selected' ).removeClass( 'selected' );
		if ( ! preventFieldGroups ) {
			unselectFieldGroups();
		}
	}

	function scrollToField( field ) {
		var newPos = field.getBoundingClientRect().top,
			container = document.getElementById( 'post-body-content' );

		if ( typeof animate === 'undefined' ) {
			jQuery( container ).scrollTop( newPos );
		} else {
			// TODO: smooth scroll
			jQuery( container ).animate({ scrollTop: newPos }, 500 );
		}
	}

	function checkCalculationCreatedByUser() {
		var calculation = this.value;
		var warningMessage = checkMatchingParens( calculation );
		warningMessage += checkShortcodes( calculation, this );

		if ( warningMessage !== '' ) {
			infoModal( calculation + '\n\n' + warningMessage );
		}
	}

	/**
	 * Checks the Detail Page slug to see if it's a reserved word and displays a message if it is.
	 */
	function checkDetailPageSlug() {
		var slug = jQuery( '#param' ).val(),
			msg;
		slug = slug.trim().toLowerCase();
		if ( Array.isArray( frm_admin_js.unsafe_params ) && frm_admin_js.unsafe_params.includes( slug ) ) {
			msg = frm_admin_js.slug_is_reserved;
			msg =  msg.replace( '****', addHtmlTags( slug, 'strong' ) );
			msg += '<br /><br />';
			msg += addHtmlTags( '<a href="https://codex.wordpress.org/WordPress_Query_Vars" target="_blank" class="frm-standard-link">' + frm_admin_js.reserved_words + '</a>', 'div' );
			infoModal( msg );
		}
	}

	/**
	 * Checks View filter value for params named with reserved words and displays a message if any are found.
	 */
	function checkFilterParamNames() {
		var regEx = /\[\s*get\s*param\s*=\s*['"]?([a-zA-Z-_]+)['"]?/ig,
			filterValue = jQuery( this ).val(),
			match = regEx.exec( filterValue ),
			unsafeParams = '';

		while ( match !== null ) {
			if ( Array.isArray( frm_admin_js.unsafe_params ) && frm_admin_js.unsafe_params.includes( match[1]) ) {
				if ( unsafeParams !== '' ) {
					unsafeParams += '", "' + match[ 1 ];
				} else {
					unsafeParams = match[ 1 ];
				}
			}
			match = regEx.exec( filterValue );
		}

		if ( unsafeParams !== '' ) {
			msg =  frm_admin_js.param_is_reserved;
			msg =  msg.replace( '****', addHtmlTags( unsafeParams, 'strong' ) );
			msg += '<br /><br />';
			msg += ' <a href="https://codex.wordpress.org/WordPress_Query_Vars" target="_blank" class="frm-standard-link">' + frm_admin_js.reserved_words + '</a>';

			infoModal( msg );
		}
	}

	function addHtmlTags( text, tag ) {
		tag = tag ? tag : 'p';
		return '<' + tag + '>' + text + '</' + tag + '>';
	}

	/**
	 * Checks a string for parens, brackets, and curly braces and returns a message if any unmatched are found.
	 * @param formula
	 * @returns {string}
	 */
	function checkMatchingParens( formula ) {

		var stack = [],
			formulaArray = formula.split( '' ),
			length = formulaArray.length,
			opening = [ '{', '[', '(' ],
			closing = {
				'}': '{',
				')': '(',
				']': '['
			},
			unmatchedClosing = [],
			msg = '',
			i, top;

		for ( i = 0; i < length; i++ ) {
			if ( opening.includes( formulaArray[i]) ) {
				stack.push( formulaArray[i]);
				continue;
			}
			if ( closing.hasOwnProperty( formulaArray[i]) ) {
				top = stack.pop();
				if ( top !== closing[formulaArray[i]]) {
					unmatchedClosing.push( formulaArray[i]);
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
			msg = frm_admin_js.text_shortcodes + '\n\n';
		}

		return msg;
	}

	/**
	 * Determines if the calculation input is from a text calculation.
	 *
	 * @param inputElement
	 */
	function isTextCalculation( inputElement ) {
		return jQuery( inputElement ).siblings( 'label[for^="calc_type"]' ).children( 'input' ).prop( 'checked' );
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
			msg += frm_admin_js.view_shortcodes + '\n\n';
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

	function isCalcBoxType( box, listClass ) {
		var list = jQuery( box ).find( '.frm_code_list' );
		return 1 === list.length && list.hasClass( listClass );
	}

	function extractExcludedOptions( exclude ) {
		var opts = [];
		if ( ! Array.isArray( exclude ) ) {
			return opts;
		}

		for ( var i = 0; i < exclude.length; i++ ) {
			if ( exclude[ i ].startsWith( '[' ) ) {
				opts.push( exclude[ i ]);
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
			if ( inputs.length && jQuery( inputs[0]).val() ) {
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

		var isSummary = isCalcBoxType( v, 'frm_js_summary_list' );

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
			if ( ( exclude && exclude.includes( fields[ i ].fieldType ) ) ||
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
					if ( includedExtras.includes( exclude[ i ]) ) {
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

	function getFieldList( fieldType ) {
		var i,
			fields = [],
			allFields = document.querySelectorAll( 'li.frm_field_box' ),
			checkType = 'undefined' !== typeof fieldType;

		for ( i = 0; i < allFields.length; i++ ) {
			// data-ftype is better (than data-type) cos of fields loaded by AJAX - which might not be ready yet
			if ( checkType && allFields[ i ].getAttribute( 'data-ftype' ) !== fieldType ) {
				continue;
			}

			var fieldId = allFields[ i ].getAttribute( 'data-fid' );
			if ( typeof fieldId !== 'undefined' && fieldId ) {
				fields.push({
					'fieldId': fieldId,
					'fieldName': getPossibleValue( 'frm_name_' + fieldId ),
					'fieldType': getPossibleValue( 'field_options_type_' + fieldId ),
					'fieldKey': getPossibleValue( 'field_options_field_key_' + fieldId )
				});
			}
		}

		return fields;
	}

	function popProductFields( field ) {
		var i, checked, id,
			options = [],
			current = getCurrentProductFields( field ),
			fName = field.getAttribute( 'data-frmfname' ),
			products = getFieldList( 'product' ),
			quantities = getFieldList( 'quantity' ),
			isSelect = field.tagName === 'SELECT', // for reverse compatibility.
			// whether we have just 1 product and 1 quantity field & should therefore attach the latter to the former
			auto = 1 === quantities.length && 1 === products.length;

		if ( isSelect ) {
			// This fallback can be removed after 4.05.
			current = field.getAttribute( 'data-frmcurrent' );
		}

		for ( i = 0 ; i < products.length ; i++ ) {
			// let's be double sure it's string, else indexOf will fail
			id = products[ i ].fieldId.toString();
			checked = auto || -1 !== current.indexOf( id );
			if ( isSelect ) {
				// This fallback can be removed after 4.05.
				checked = checked ? ' selected' : '';
				options.push( '<option value="' + id + '"' + checked + '>' + products[ i ].fieldName + '</option>' );
			} else {
				checked = checked ? ' checked' : '';
				options.push( '<label class="frm6">' );
				options.push( '<input type="checkbox" name="' + fName + '" value="' + id + '"' + checked + '> ' + products[ i ].fieldName );
				options.push( '</label>' );
			}
		}

		field.innerHTML = options.join( '' );
	}

	function getCurrentProductFields( prodFieldOpt ) {
		var products = prodFieldOpt.querySelectorAll( '[type="checkbox"]:checked' ),
			idsArray = [];

		for ( var i = 0; i < products.length; i++ ) {
			idsArray.push( products[ i ].value );
		}

		return idsArray;
	}

	function popAllProductFields() {
		var opts = document.querySelectorAll( '.frmjs_prod_field_opt' );
		for ( var i = 0; i < opts.length; i++ ) {
			popProductFields( opts[ i ]);
		}
	}

	function maybeSetProductField( field ) {
		var fieldId = field.getAttribute( 'data-fid' ),
			productFieldOpt = document.getElementById( 'field_options[product_field_' + fieldId + ']' );

		if ( null === productFieldOpt ) {
			return;
		}

		popProductFields( productFieldOpt );
		// in order to move its settings to that LHS panel where
		// the update form resides, else it'll lose this setting
		moveFieldSettings( document.getElementById( 'frm-single-settings-' + fieldId ) );
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
			} else if ( isSliderField( changes ) ) {
				updateSliderFieldPreview( changes, att, newValue );
			} else {
				changes.setAttribute( att, newValue );
			}
		} else if ( changes.id.indexOf( 'setup-message' ) === 0 ) {
			if ( newValue !== '' ) {
				changes.innerHTML = '<input type="text" value="" disabled />';
			}
		} else {
			changes.innerHTML = newValue;

			if ( 'TEXTAREA' === changes.nodeName && changes.classList.contains( 'wp-editor-area' ) ) {
				// Trigger change events on wysiwyg textareas so we can also sync default values in the visual tab.
				jQuery( changes ).trigger( 'change' );
			}

			if ( changes.classList.contains( 'frm_primary_label' ) && 'break' === changes.nextElementSibling.getAttribute( 'data-ftype' ) ) {
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

		if ( -1 === [ 'value', 'min', 'max' ].indexOf( att ) ) {
			return;
		}

		if ( ( 'max' === att || 'min' === att ) && '' === getSliderDefaultValueInput( field.id ) ) {
			field.value = getSliderMidpoint( field );
		}

		field.parentNode.querySelector( '.frm_range_value' ).textContent = field.value;
	}

	function getSliderDefaultValueInput( previewInputId ) {
		return document.querySelector( 'input[data-changeme="' + previewInputId + '"][data-changeatt="value"]' ).value;
	}

	function getSliderMidpoint( sliderInput ) {
		const max = parseFloat( sliderInput.getAttribute( 'max' ) );
		const min = parseFloat( sliderInput.getAttribute( 'min' ) );
		return ( max - min ) / 2 + min;
	}

	function isSliderField( previewInput ) {
		return 'range' === previewInput.type && previewInput.parentNode.classList.contains( 'frm_range_container' );
	}

	function toggleInvalidMsg() {
		/*jshint validthis:true */
		var typeDropdown, fieldType,
			fieldId = this.getAttribute( 'data-fid' ),
			value = '';

		[ 'field_options_max_', 'frm_format_' ].forEach( function( id ) {
			var input = document.getElementById( id + fieldId );
			if ( ! input ) {
				return;
			}

			value += input.value;
		});

		typeDropdown = document.getElementsByName( 'field_options[type_' + fieldId + ']' )[0];
		fieldType = typeDropdown.options[typeDropdown.selectedIndex].value;

		if ( fieldType === 'text' ) {
			toggleValidationBox( '' !== value, '.frm_invalid_msg' + fieldId );
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
		var fieldId = jQuery( this ).closest( '.frm-single-settings' ).data( 'fid' );
		var $thisField = jQuery( '.frm_unique_details' + fieldId );
		if ( this.checked ) {
			$thisField.fadeIn( 'fast' ).closest( '.frm_validation_msg' ).fadeIn( 'fast' );
			$unqDetail = jQuery( '.frm_unique_details' + fieldId + ' input' );
			if ( $unqDetail.val() === '' ) {
				$unqDetail.val( frm_admin_js.default_unique );
			}
		} else {
			var v = $thisField.fadeOut( 'fast' ).closest( '.frm_validation_box' ).children( ':not(.frm_unique_details' + fieldId + '):visible' ).length;
			if ( v === 0 ) {
				$thisField.closest( '.frm_validation_msg' ).fadeOut( 'fast' );
			}
		}
	}

	//Fade confirmation field and validation option in or out
	function addConf() {
		/*jshint validthis:true */
		var fieldId = jQuery( this ).closest( '.frm-single-settings' ).data( 'fid' );
		var val = jQuery( this ).val();
		var $thisField = jQuery( document.getElementById( 'frm_field_id_' + fieldId ) );

		toggleValidationBox( val !== '', '.frm_conf_details' + fieldId );

		if ( val !== '' ) {
			//Add default validation message if empty
			var valMsg = jQuery( '.frm_validation_box .frm_conf_details' + fieldId + ' input' );
			if ( valMsg.val() === '' ) {
				valMsg.val( frm_admin_js.default_conf );
			}

			setConfirmationFieldDescriptions( fieldId );

			//Add or remove class for confirmation field styling
			if ( val === 'inline' ) {
				$thisField.removeClass( 'frm_conf_below' ).addClass( 'frm_conf_inline' );
			} else if ( val === 'below' ) {
				$thisField.removeClass( 'frm_conf_inline' ).addClass( 'frm_conf_below' );
			}
			jQuery( '.frm-conf-box-' + fieldId ).removeClass( 'frm_hidden' );
		} else {
			jQuery( '.frm-conf-box-' + fieldId ).addClass( 'frm_hidden' );
			setTimeout( function() {
				$thisField.removeClass( 'frm_conf_inline frm_conf_below' );
			}, 200 );
		}
	}

	function setConfirmationFieldDescriptions( fieldId ) {
		var fieldType = document.getElementsByName( 'field_options[type_' + fieldId + ']' )[0].value;

		var fieldDescription = document.getElementById( 'field_description_' + fieldId );
		var hiddenDescName = 'field_options[description_' + fieldId + ']';
		var newValue = frm_admin_js['enter_' + fieldType];
		maybeSetNewDescription( fieldDescription, hiddenDescName, newValue );

		var confFieldDescription = document.getElementById( 'conf_field_description_' + fieldId );
		var hiddenConfName = 'field_options[conf_desc_' + fieldId + ']';
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

		jQuery( '.frm-insert-preset' ).on( 'click', insertBulkPreset );

		jQuery( builderForm ).on( 'click', 'a.frm-bulk-edit-link', function( event ) {
			event.preventDefault();
			var i, key, label,
				content = '',
				optList,
				opts,
				fieldId = jQuery( this ).closest( '[data-fid]' ).data( 'fid' ),
				separate = usingSeparateValues( fieldId ),
				product = isProductField( fieldId );

			optList = document.getElementById( 'frm_field_' + fieldId + '_opts' );
			if ( ! optList ) {
				return;
			}

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
						if ( product ) {
							content += '|' + document.getElementsByName( 'field_options[options_' + fieldId + '][' + key + '][price]' )[0].value;
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
		});

		jQuery( '#frm-update-bulk-opts' ).on( 'click', function() {
			var fieldId    = document.getElementById( 'bulk-field-id' ).value;
			var optionType = document.getElementById( 'bulk-option-type' ).value;

			if ( optionType ) {
				// Use custom handler for custom option type.
				return;
			}

			this.classList.add( 'frm_loading_button' );
			frmAdminBuild.updateOpts( fieldId, document.getElementById( 'frm_bulk_options' ).value, $info );
		});
	}

	function insertBulkPreset( event ) {
		/*jshint validthis:true */
		var opts = JSON.parse( this.getAttribute( 'data-opts' ) );
		event.preventDefault();
		document.getElementById( 'frm_bulk_options' ).value = opts.join( '\n' );
		return false;
	}

	//Add new option or "Other" option to radio/checkbox/dropdown
	function addFieldOption() {
		/*jshint validthis:true */
		var fieldId = jQuery( this ).closest( '.frm-single-settings' ).data( 'fid' ),
			newOption = jQuery( '#frm_field_' + fieldId + '_opts .frm_option_template' ).prop( 'outerHTML' ),
			optType = jQuery( this ).data( 'opttype' ),
			optKey = 0,
			oldKey = '000',
			lastKey = getHighestOptKey( fieldId );

		if ( lastKey !== oldKey ) {
			optKey = lastKey + 1;
		}

		//Update hidden field
		if ( optType === 'other' ) {
			document.getElementById( 'other_input_' + fieldId ).value = 1;

			//Hide "Add Other" option now if this is radio field
			var ftype = jQuery( this ).data( 'ftype' );
			if ( ftype === 'radio' || ftype === 'select' ) {
				jQuery( this ).fadeOut( 'slow' );
			}

			var data = {
				action: 'frm_add_field_option',
				field_id: fieldId,
				opt_key: optKey,
				opt_type: optType,
				nonce: frmGlobal.nonce
			};
			jQuery.post( ajaxurl, data, function( msg ) {
				jQuery( document.getElementById( 'frm_field_' + fieldId + '_opts' ) ).append( msg );
				resetDisplayedOpts( fieldId );
			});
		} else {
			newOption = newOption.replace( new RegExp( 'optkey="' + oldKey + '"', 'g' ), 'optkey="' + optKey + '"' );
			newOption = newOption.replace( new RegExp( '-' + oldKey + '_', 'g' ), '-' + optKey + '_' );
			newOption = newOption.replace( new RegExp( '-' + oldKey + '"', 'g' ), '-' + optKey + '"' );
			newOption = newOption.replace( new RegExp( '\\[' + oldKey + '\\]', 'g' ), '[' + optKey + ']' );
			newOption = newOption.replace( 'frm_hidden frm_option_template', '' );
			newOption = { newOption };
			addSaveAndDragIconsToOption( fieldId, newOption );
			jQuery( document.getElementById( 'frm_field_' + fieldId + '_opts' ) ).append( newOption.newOption );
			resetDisplayedOpts( fieldId );
		}
	}

	function getHighestOptKey( fieldId ) {
		var i = 0,
			optKey = 0,
			opts = jQuery( '#frm_field_' + fieldId + '_opts li' ),
			lastKey = 0;

		for ( i; i < opts.length; i++ ) {
			optKey = opts[i].getAttribute( 'data-optkey' );
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
		var fieldId = jQuery( this ).closest( '.frm-single-settings' ).data( 'fid' );
		toggleMultiSelect( fieldId, this.value );
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
		var fieldId = jQuery( this ).closest( '.frm-single-settings' ).data( 'fid' );
		toggle( jQuery( '.field_' + fieldId + '_option_key' ) );
		jQuery( '.field_' + fieldId + '_option' ).toggleClass( 'frm_with_key' );
	}

	function toggleImageOptions() {
		/*jshint validthis:true */
		var hasImageOptions, imageSize,
			$field = jQuery( this ).closest( '.frm-single-settings' ),
			fieldId = $field.data( 'fid' ),
			displayField = document.getElementById( 'frm_field_id_' + fieldId );

		refreshOptionDisplayNow( jQuery( this ) );

		toggle( jQuery( '.field_' + fieldId + '_image_id' ) );
		toggle( jQuery( '.frm_toggle_image_options_' + fieldId ) );
		toggle( jQuery( '.frm_image_size_' + fieldId ) );
		toggle( jQuery( '.frm_alignment_' + fieldId ) );
		toggle( jQuery( '.frm-add-other#frm_add_field_' + fieldId ) );

		hasImageOptions = imagesAsOptions( fieldId );

		if ( hasImageOptions ) {
			setAlignment( fieldId, 'inline' );
			removeImageSizeClasses( displayField );
			imageSize = getImageOptionSize( fieldId );
			displayField.classList.add( 'frm_image_options' );
			displayField.classList.add( 'frm_image_size_' + imageSize );
			$field.find( '.frm-bulk-edit-link' ).hide();
		} else {
			displayField.classList.remove( 'frm_image_options' );
			removeImageSizeClasses( displayField );
			setAlignment( fieldId, 'block' );
			$field.find( '.frm-bulk-edit-link' ).show();
		}
	}

	function removeImageSizeClasses( field ) {
		field.classList.remove( 'frm_image_size_', 'frm_image_size_small', 'frm_image_size_medium', 'frm_image_size_large', 'frm_image_size_xlarge' );
	}

	function setAlignment( fieldId, alignment ) {
		jQuery( '#field_options_align_' + fieldId ).val( alignment ).trigger( 'change' );
	}

	function setImageSize() {
		var $field = jQuery( this ).closest( '.frm-single-settings' ),
			fieldId = $field.data( 'fid' ),
			displayField = document.getElementById( 'frm_field_id_' + fieldId );

		refreshOptionDisplay();

		if ( imagesAsOptions( fieldId ) ) {
			removeImageSizeClasses( displayField );
			displayField.classList.add( 'frm_image_options' );
			displayField.classList.add( 'frm_image_size_' + getImageOptionSize( fieldId ) );
		}
	}

	function refreshOptionDisplayNow( object ) {
		var $field = object.closest( '.frm-single-settings' ),
			fieldID = $field.data( 'fid' );
		jQuery( '.field_' + fieldID + '_option' ).trigger( 'change' );
	}

	function refreshOptionDisplay() {
		/*jshint validthis:true */
		refreshOptionDisplayNow( jQuery( this ) );
	}

	function addImageToOption( event ) {
		const imagePreview = event.target.closest( '.frm_image_preview_wrapper' );

		event.preventDefault();

		wp.media.model.settings.post.id = 0;

		const fileFrame = wp.media.frames.file_frame = wp.media({
			multiple: false,
			library: {
				type: [ 'image' ]
			}
		});

		fileFrame.on( 'select', function() {
			const attachment = fileFrame.state().get( 'selection' ).first().toJSON();
			const img = imagePreview.querySelector( 'img' );

			img.setAttribute( 'src', attachment.url );
			img.classList.remove( 'frm_hidden' );
			img.removeAttribute( 'srcset' ); // Prevent the old image from sticking around.

			imagePreview.querySelector( '.frm_image_preview_frame' ).style.display = 'block';
			imagePreview.querySelector( '.frm_image_preview_title' ).textContent = attachment.filename;
			imagePreview.querySelector( '.frm_choose_image_box' ).style.display = 'none';

			const $imagePreview = jQuery( imagePreview );
			$imagePreview.siblings( 'input[name*="[label]"]' ).data( 'frmimgurl', attachment.url );
			$imagePreview.find( 'input.frm_image_id' ).val( attachment.id ).trigger( 'change' );
			wp.media.model.settings.post.id = 0;
		});

		fileFrame.open();
	}

	function removeImageFromOption( event ) {
		var $this = jQuery( this ),
			previewWrapper = $this.closest( '.frm_image_preview_wrapper' );

		event.preventDefault();
		event.stopPropagation();

		previewWrapper.find( 'img' ).attr( 'src', '' );
		previewWrapper.find( '.frm_image_preview_frame' ).hide();
		previewWrapper.find( '.frm_choose_image_box' ).show();
		previewWrapper.find( 'input.frm_image_id' ).val( 0 ).trigger( 'change' );
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
		var otherInput,
			parentLi = this.parentNode,
			parentUl = parentLi.parentNode,
			fieldId = this.getAttribute( 'data-fid' );

		jQuery( parentLi ).fadeOut( 'slow', function() {
			jQuery( parentLi ).remove();

			var hasOther = jQuery( parentUl ).find( '.frm_other_option' );
			if ( hasOther.length < 1 ) {
				otherInput = document.getElementById( 'other_input_' + fieldId );
				if ( otherInput !== null ) {
					otherInput.value = 0;
				}
				jQuery( '#other_button_' + fieldId ).fadeIn( 'slow' );
			}
		});
	}

	/**
	 * If a radio button is set as default, allow a click to
	 * deselect it.
	 */
	function maybeUncheckRadio() {
		var $self, uncheck, unbind, up;

		/*jshint validthis:true */
		$self = jQuery( this );
		if ( $self.is( ':checked' ) ) {
			uncheck = function() {
				setTimeout( function() {
					$self.prop( 'checked', false );
				}, 0 );
			};
			unbind = function() {
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
		if ( this.value === frm_admin_js.new_option ) {
			this.setAttribute( 'data-value-on-focus', this.value );
			this.value = '';
		}
	}

	function confirmFieldsDeleteMessage( numberOfFields ) {
		/* translators: %1$s: Number of fields that are selected to be deleted. */
		return __( 'Are you sure you want to delete these %1$s selected field(s)?', 'formidable' ).replace( '%1$s', numberOfFields );
	}

	function clickDeleteField() {
		/*jshint validthis:true */
		var confirmMsg = frm_admin_js.conf_delete,
			maybeDivider = this.parentNode.parentNode.parentNode.parentNode.parentNode,
			li = maybeDivider.parentNode,
			field = jQuery( this ).closest( 'li.form-field' ),
			fieldId = field.data( 'fid' );

		if ( field.data( 'ftype' ) === 'divider' ) {
			const fieldBoxes = document.querySelectorAll( '.frm-field-group-hover-target .start_divider .frm_field_box' );
			let fieldIdsToDelete = 0;
			fieldBoxes.forEach( fieldBox => {
				const fieldsInsideFieldBox = fieldBox.querySelectorAll( 'li.form-field' );
				if ( fieldsInsideFieldBox ) {
					fieldIdsToDelete += fieldsInsideFieldBox.length;
				}
			});
			if ( fieldIdsToDelete ) {
				confirmMsg = confirmFieldsDeleteMessage( ++fieldIdsToDelete );
			}
		}

		if ( li.classList.contains( 'frm-section-collapsed' ) || li.classList.contains( 'frm-page-collapsed' ) ) {
			return false;
		}

		// If deleting a section, use a special message.
		if ( maybeDivider.className === 'divider_section_only' ) {
			confirmMsg = frm_admin_js.conf_delete_sec;
		}

		this.setAttribute( 'data-frmverify', confirmMsg );
		this.setAttribute( 'data-frmverify-btn', 'frm-button-red' );
		this.setAttribute( 'data-deletefield', fieldId );

		closeOpenFieldDropdowns();

		confirmLinkClick( this );
		return false;
	}

	function clickSelectField() {
		this.closest( 'li.form-field' ).click();
	}

	function clickDeleteFieldGroup() {
		var hoverTarget, decoy;

		hoverTarget = document.querySelector( '.frm-field-group-hover-target' );
		if ( null === hoverTarget ) {
			return;
		}

		hoverTarget.classList.add( 'frm-selected-field-group' );

		decoy = document.createElement( 'div' );
		decoy.classList.add( 'frm-delete-field-groups', 'frm_hidden' );
		document.body.appendChild( decoy );
		decoy.click();
	}

	function duplicateFieldGroup() {
		const hoverTarget = document.querySelector( '.frm-field-group-hover-target' );
		if ( null === hoverTarget ) {
			return;
		}

		const newRowId           = 'frm_field_group_' + getAutoId();
		const placeholderUlChild = document.createTextNode( '' );
		wrapFieldLiInPlace( placeholderUlChild );

		const newRow = jQuery( placeholderUlChild ).closest( 'li' ).get( 0 );
		newRow.classList.add( 'frm_hidden' );

		const newRowUl = newRow.querySelector( 'ul' );
		newRowUl.id    = newRowId;

		jQuery( hoverTarget.closest( 'li.frm_field_box' ) ).after( newRow );

		const $fields              = getFieldsInRow( jQuery( hoverTarget ) );
		const syncDetails          = [];
		const injectedCloneOptions = [];

		const expectedLength                     = $fields.length;
		const originalFieldIdByDuplicatedFieldId = {};

		let duplicatedCount = 0;

		jQuery( newRow ).on(
			'frm_added_duplicated_field_to_row',
			function( _, args ) {
				originalFieldIdByDuplicatedFieldId[ jQuery( args.duplicatedFieldHtml ).attr( 'data-fid' ) ] = args.originalFieldId;

				if ( expectedLength > ++duplicatedCount ) {
					return;
				}

				const $newRowUl         = jQuery( newRowUl );
				const $duplicatedFields = getFieldsInRow( $newRowUl );

				injectedCloneOptions.forEach(
					function( cloneOption ) {
						cloneOption.remove();
					}
				);

				for ( let index = 0; index < expectedLength; ++index ) {
					$newRowUl.append( $newRowUl.children( 'li.form-field[frm-field-order="' + index + '"]' ) );
				}

				syncLayoutClasses( $duplicatedFields.first(), syncDetails );
				newRow.classList.remove( 'frm_hidden' );
				updateFieldOrder();

				getFieldsInRow( $newRowUl ).each(
					function() {
						maybeDuplicateUnsavedSettings( originalFieldIdByDuplicatedFieldId[ this.getAttribute( 'data-fid' ) ], jQuery( this ).prop( 'outerHTML' ) );
					}
				);
			}
		);

		$fields.each(
			function( index ) {
				var cloneOption;
				cloneOption = document.createElement( 'li' );
				cloneOption.classList.add( 'frm_clone_field' );
				cloneOption.setAttribute( 'frm-target-row-id', newRowId );
				cloneOption.setAttribute( 'frm-field-order', index );
				this.appendChild( cloneOption );
				cloneOption.click();
				injectedCloneOptions.push( cloneOption );
				syncDetails.push( getSizeOfLayoutClass( getLayoutClassName( this.classList ) ) );
			}
		);
	}

	function clickFieldGroupLayout() {
		var hoverTarget, sizeOfFieldGroup, popupWrapper;

		hoverTarget = document.querySelector( '.frm-field-group-hover-target' );

		if ( null === hoverTarget ) {
			return;
		}

		deselectFields();

		sizeOfFieldGroup = getSizeOfFieldGroupFromChildElement( hoverTarget.querySelector( 'li.form-field' ) );

		hoverTarget.classList.add( 'frm-has-open-field-group-popup' );
		jQuery( document ).on( 'click', '#frm_builder_page', destroyFieldGroupPopupOnOutsideClick );

		popupWrapper = div();
		popupWrapper.style.position = 'relative';
		popupWrapper.appendChild( getFieldGroupPopup( sizeOfFieldGroup, this ) );
		this.parentNode.appendChild( popupWrapper );

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
		var $ul = jQuery( element ).closest( 'ul' );
		if ( $ul.length ) {
			return getFieldsInRow( $ul ).length;
		}
		return getSelectedFieldCount();
	}

	function getFieldGroupPopup( sizeOfFieldGroup, childElement ) {
		var popup, wrapper, rowLayoutOptions, ul;

		popup = document.getElementById( 'frm_field_group_popup' );
		if ( null === popup ) {
			popup = div();
		} else {
			popup.innerHTML = '';
		}

		popup.id = 'frm_field_group_popup';

		wrapper = div();
		wrapper.style.padding = '0 24px 12px';
		wrapper.appendChild( getRowLayoutTitle() );

		rowLayoutOptions = getRowLayoutOptions( sizeOfFieldGroup );

		ul = childElement.closest( 'ul.frm_sorting' );
		if ( null !== ul ) {
			maybeMarkRowLayoutAsActive( ul, rowLayoutOptions );
		}

		wrapper.appendChild( rowLayoutOptions );

		popup.appendChild( wrapper );
		popup.appendChild( separator() );

		popup.appendChild( getCustomLayoutOption() );
		popup.appendChild( getBreakIntoDifferentRowsOption() );

		return popup;
	}

	function maybeMarkRowLayoutAsActive( activeRow, options ) {
		var length, index, currentRow;

		length = options.children.length;
		for ( index = 0; index < length; ++index ) {
			currentRow = options.children[ index ];
			if ( rowLayoutsMatch( currentRow, activeRow ) ) {
				currentRow.classList.add( 'frm-active-row-layout' );
				return;
			}
		}
	}

	function separator() {
		var hr = document.createElement( 'hr' );
		return hr;
	}

	function getCustomLayoutOption() {
		var option = div();
		option.textContent = __( 'Custom layout', 'formidable' );
		jQuery( option ).prepend( getIconClone( 'frm_gear_svg' ) );
		option.classList.add( 'frm-custom-field-group-layout' );
		makeTabbable( option );
		return option;
	}

	function makeTabbable( element, ariaLabel ) {
		element.setAttribute( 'tabindex', 0 );
		element.setAttribute( 'role', 'button' );
		if ( 'undefined' !== typeof ariaLabel ) {
			element.setAttribute( 'aria-label', ariaLabel );
		}
	}

	function getIconClone( iconId ) {
		var clone = document.getElementById( iconId ).cloneNode( true );
		clone.id = '';
		return clone;
	}

	function getBreakIntoDifferentRowsOption() {
		var option = div();
		option.textContent = __( 'Break into rows', 'formidable' );
		jQuery( option ).prepend( getIconClone( 'frm_break_field_group_svg' ) );
		option.classList.add( 'frm-break-field-group' );
		makeTabbable( option );
		return option;
	}

	function getRowLayoutTitle() {
		var rowLayoutTitle = div();
		rowLayoutTitle.classList.add( 'frm-row-layout-title' );
		rowLayoutTitle.textContent = __( 'Row Layout', 'formidable' );
		return rowLayoutTitle;
	}

	function getRowLayoutOptions( size ) {
		var wrapper, padding;

		wrapper = getEmptyGridContainer();
		if ( 5 !== size ) {
			wrapper.appendChild( getRowLayoutOption( size, 'even' ) );
		}
		if ( size % 2 === 1 ) {
			// only include the middle option for odd numbers because even doesn't make a lot of sense.
			wrapper.appendChild( getRowLayoutOption( size, 'middle' ) );
		}
		if ( size < 6 ) {
			wrapper.appendChild( getRowLayoutOption( size, 'left' ) );
			wrapper.appendChild( getRowLayoutOption( size, 'right' ) );
		} else {
			padding = div();
			padding.classList.add( 'frm_fourth' );
			wrapper.prepend( padding );
		}

		return wrapper;
	}

	function getRowLayoutOption( size, type ) {
		var option, useClass;

		option = div();
		option.classList.add( 'frm-row-layout-option' );
		makeTabbable( option, type );

		switch ( size ) {
			case 6:
				useClass = 'frm_half';
				break;
			case 5:
				useClass = 'frm_third';
				break;
			default:
				useClass = size % 2 === 1 ? 'frm_fourth' : 'frm_third';
				break;
		}

		option.classList.add( useClass );
		option.setAttribute( 'layout-type', type );

		option.appendChild( getRowForSizeAndType( size, type ) );
		return option;
	}

	function rowLayoutsMatch( row1, row2 ) {
		return getRowLayoutAsKey( row1 ) === getRowLayoutAsKey( row2 );
	}

	function getRowLayoutAsKey( row ) {
		var $fields, sizes;
		if ( row.classList.contains( 'frm-row-layout-option' ) ) {
			$fields = jQuery( row ).find( '.frm_grid_container' ).children();
		} else {
			$fields = getFieldsInRow( jQuery( row ) );
		}
		sizes = [];
		$fields.each(
			function() {
				sizes.push( getSizeOfLayoutClass( getLayoutClassName( this.classList ) ) );
			}
		);
		return sizes.join( '-' );
	}

	function getRowForSizeAndType( size, type ) {
		var row, index, block;

		row = getEmptyGridContainer();
		for ( index = 0; index < size; ++index ) {
			block = div();
			block.classList.add( getClassForBlock( size, type, index ) );
			block.style.height = '16px';
			block.style.background = '#9EA9B8';
			block.style.borderRadius = '1px';
			row.appendChild( block );
		}

		return row;
	}

	/**
	 * @param {int} size 2-6.
	 * @param {string} type even, middle, left, or right.
	 * @param {int} index 0-5.
	 * @returns string
	 */
	function getClassForBlock( size, type, index ) {
		if ( 'even' === type ) {
			return getEvenClassForSize( size, index );
		} else if ( 'middle' === type ) {
			if ( 3 === size ) {
				return 1 === index ? 'frm6' : 'frm3';
			}
			if ( 5 === size ) {
				return 2 === index ? 'frm4' : 'frm2';
			}
		} else if ( 'left' === type ) {
			return 0 === index ? getLargeClassForSize( size ) : getSmallClassForSize( size );
		} else if ( 'right' === type ) {
			return index === size - 1 ? getLargeClassForSize( size ) : getSmallClassForSize( size );
		}
		return 'frm12';
	}

	function getEvenClassForSize( size, index ) {
		if ( -1 !== [ 2, 3, 4, 6 ].indexOf( size ) ) {
			return getLayoutClassForSize( 12 / size );
		}
		if ( 5 === size && 'undefined' !== typeof index ) {
			return 0 === index ? 'frm4' : 'frm2';
		}
		return 'frm12';
	}

	function getSmallClassForSize( size ) {
		switch ( size ) {
			case 2: case 3:
				return 'frm3';
			case 4:
				return 'frm2';
			case 5:
				return 'frm2';
			case 6:
				return 'frm1';
		}
		return 'frm12';
	}

	function getLargeClassForSize( size ) {
		switch ( size ) {
			case 2:
				return 'frm9';
			case 3: case 4:
				return 'frm6';
			case 5:
				return 'frm4';
			case 6:
				return 'frm7';
		}
		return 'frm12';
	}

	function getEmptyGridContainer() {
		var wrapper = div();
		wrapper.classList.add( 'frm_grid_container' );
		return wrapper;
	}

	/**
	 * Handle when a field group layout option (that sets grid classes/column sizing) is selected in the "Row Layout" popup.
	 *
	 * @returns {void}
	 */
	function handleFieldGroupLayoutOptionClick() {
		const row  = document.querySelector( '.frm-field-group-hover-target' );
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
		var $ul, type;
		$ul = mergeSelectedFieldGroups();
		type = this.getAttribute( 'layout-type' );
		syncLayoutClasses( getFieldsInRow( $ul ).first(), type );
		unselectFieldGroups();
	}

	function mergeSelectedFieldGroups() {
		var $selectedFieldGroups = jQuery( '.frm-selected-field-group' ),
			$firstGroupUl = $selectedFieldGroups.first();
		$selectedFieldGroups.not( $firstGroupUl ).each(
			function() {
				getFieldsInRow( jQuery( this ) ).each(
					function() {
						var previousParent = this.parentNode;
						getFieldsInRow( $firstGroupUl ).last().after( this );
						if ( ! jQuery( previousParent ).children( 'li.form-field' ).length ) {
							// clean up the previous field group if we've removed all of its fields.
							previousParent.closest( 'li.frm_field_box' ).remove();
						}
					}
				);
			}
		);
		updateFieldOrder();
		syncLayoutClasses( getFieldsInRow( $firstGroupUl ).first() );
		return $firstGroupUl;
	}

	function customFieldGroupLayoutClick() {
		var $fields;
		if ( null !== this.closest( '.frm-merge-fields-into-row' ) ) {
			return;
		}
		$fields = getFieldsInRow( jQuery( '.frm-field-group-hover-target' ) );
		setupCustomLayoutOptions( $fields );
	}

	function setupCustomLayoutOptions( $fields ) {
		var size, popup, wrapper, layoutClass, inputRow, paddingElement, inputValueOverride, index, inputField, heading, label, buttonsWrapper, cancelButton, saveButton;

		size = $fields.length;

		popup = document.getElementById( 'frm_field_group_popup' );
		popup.innerHTML = '';

		wrapper = div();
		wrapper.style.padding = '24px';

		layoutClass = getEvenClassForSize( 5 === size ? 6 : size );

		inputRow = div();
		inputRow.style.padding = '20px 0';
		inputRow.classList.add( 'frm_grid_container' );

		if ( 5 === size ) {
			// add a span to pad the inputs by 1 column, to account for the missing 2 columns.
			paddingElement = document.createElement( 'span' );
			paddingElement.classList.add( 'frm1' );
			inputRow.appendChild( paddingElement );
		}

		inputValueOverride = getSelectedFieldCount() > 0 ? getSizeOfLayoutClass( getEvenClassForSize( size ) ) : false;
		if ( false !== inputValueOverride && inputValueOverride >= 12 ) {
			inputValueOverride = Math.floor( 12 / size );
		}

		for ( index = 0; index < size; ++index ) {
			inputField = document.createElement( 'input' );
			inputField.type = 'text';
			inputField.classList.add( layoutClass );
			inputField.classList.add( 'frm-custom-grid-size-input' );
			inputField.value = false !== inputValueOverride ? inputValueOverride : getSizeOfLayoutClass( getLayoutClassName( $fields.get( index ).classList ) );
			inputRow.appendChild( inputField );
		}

		heading = div();
		heading.classList.add( 'frm-builder-popup-heading' );
		heading.textContent = __( 'Enter number of columns for each field', 'formidable' );

		label = div();
		label.classList.add( 'frm-builder-popup-subheading' );
		label.textContent = __( 'Layouts are based on a 12-column grid system', 'formidable' );

		wrapper.appendChild( heading );
		wrapper.appendChild( label );

		wrapper.appendChild( inputRow );

		buttonsWrapper = div();
		buttonsWrapper.style.textAlign = 'right';

		cancelButton = getSecondaryButton();
		cancelButton.textContent = __( 'Cancel', 'formidable' );
		cancelButton.classList.add( 'frm-cancel-custom-field-group-layout' );
		cancelButton.style.marginRight = '10px';

		saveButton = getPrimaryButton();
		saveButton.textContent = __( 'Save', 'formidable' );
		saveButton.classList.add( 'frm-save-custom-field-group-layout' );

		buttonsWrapper.appendChild( cancelButton );
		buttonsWrapper.appendChild( saveButton );

		wrapper.appendChild( buttonsWrapper );

		popup.appendChild( wrapper );

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
		$fields = jQuery( '.frm-selected-field-group li.form-field' );
		setupCustomLayoutOptions( $fields );
	}

	function getPrimaryButton() {
		var button = getButton();
		button.classList.add( 'button-primary', 'frm-button-primary' );
		return button;
	}

	function getSecondaryButton() {
		var button = getButton();
		button.classList.add( 'button-secondary', 'frm-button-secondary' );
		return button;
	}

	function getButton() {
		var button = document.createElement( 'a' );
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
		}

		if ( 0 === className.indexOf( 'frm' ) ) {
			return parseInt( className.substr( 3 ) );
		}

		// Anything missing a layout class should be a full width row.
		return 12;
	}

	function getLayoutClassName( classList ) {
		var classes, index, currentClass;
		classes = getLayoutClasses();
		for ( index = 0; index < classes.length; ++index ) {
			currentClass = classes[ index ];
			if ( classList.contains( currentClass ) ) {
				return currentClass;
			}
		}
		return '';
	}

	function getLayoutClassForSize( size ) {
		return 'frm' + size;
	}

	function breakFieldGroupClick() {
		var row = document.querySelector( '.frm-field-group-hover-target' );
		breakRow( row );
		destroyFieldGroupPopup();
	}

	function breakRow( row ) {
		const $row = jQuery( row );
		getFieldsInRow( $row ).each(
			function( index ) {
				const field = this;
				if ( 0 !== index ) {
					$row.parent().after( wrapFieldLi( field ) );
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
		var popup, wrapper;
		popup = document.getElementById( 'frm_field_group_popup' );
		if ( popup === null ) {
			return;
		}
		wrapper = document.querySelector( '.frm-has-open-field-group-popup' );
		if ( null !== wrapper ) {
			wrapper.classList.remove( 'frm-has-open-field-group-popup' );
			popup.parentNode.remove();
		}
		jQuery( document ).off( 'click', '#frm_builder_page', destroyFieldGroupPopupOnOutsideClick );
	}

	function saveCustomFieldGroupClick() {
		var syncDetails, $controls, $ul;

		syncDetails = [];

		jQuery( document.getElementById( 'frm_field_group_popup' ).querySelectorAll( '.frm_grid_container input' ) )
			.each(
				function() {
					syncDetails.push( parseInt( this.value ) );
				}
			);

		$controls = jQuery( document.getElementById( 'frm_field_group_controls' ) );

		if ( $controls.length && 'none' !== $controls.get( 0 ).style.display ) {
			syncLayoutClasses( getFieldsInRow( jQuery( document.querySelector( '.frm-field-group-hover-target' ) ) ).first(), syncDetails );
		} else {
			$ul = mergeSelectedFieldGroups();
			syncLayoutClasses( getFieldsInRow( $ul ).first(), syncDetails );
			unselectFieldGroups();
		}

		destroyFieldGroupPopup();
	}

	function fieldGroupClick( e ) {
		if ( 'ul' !== e.originalEvent.target.nodeName.toLowerCase() ) {
			// only continue if the group itself was clicked / ignore when a field is clicked.
			return;
		}

		const hoverTarget = document.querySelector( '.frm-field-group-hover-target' );
		if ( ! hoverTarget ) {
			return;
		}

		const ctrlOrCmdKeyIsDown   = e.ctrlKey || e.metaKey;
		const shiftKeyIsDown       = e.shiftKey;
		const groupIsActive        = hoverTarget.classList.contains( 'frm-selected-field-group' );
		const $selectedFieldGroups = getSelectedFieldGroups();

		let numberOfSelectedGroups = $selectedFieldGroups.length;

		if ( ctrlOrCmdKeyIsDown || shiftKeyIsDown ) {
			// multi-selecting

			const selectedField = getSelectedField();
			if ( null !== selectedField && ! jQuery( selectedField ).siblings( 'li.form-field' ).length ) {
				// count a selected field on its own as a selected field group when multiselecting.
				selectedField.parentNode.classList.add( 'frm-selected-field-group' );
				++numberOfSelectedGroups;
			}

			if ( ctrlOrCmdKeyIsDown ) {
				if ( groupIsActive ) {
					// unselect if holding ctrl or cmd and the group was already active.
					--numberOfSelectedGroups;
					hoverTarget.classList.remove( 'frm-selected-field-group' );
					syncAfterMultiSelect( numberOfSelectedGroups );
					return; // exit early to avoid adding back frm-selected-field-group
				} else {
					++numberOfSelectedGroups;
				}
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
						var $fieldGroup = jQuery( this ).closest( 'li' ).find( 'ul.frm_sorting' );
						if ( ! $fieldGroup.hasClass( 'frm-selected-field-group' ) ) {
							++numberOfSelectedGroups;
							$fieldGroup.addClass( 'frm-selected-field-group' );
						}
					}
				);

				// when holding shift and clicking, text gets selected. unselect it.
				document.getSelection().removeAllRanges();
			}
		} else {
			// not multi-selecting
			unselectFieldGroups();
			numberOfSelectedGroups = 1;
		}

		hoverTarget.classList.add( 'frm-selected-field-group' );
		syncAfterMultiSelect( numberOfSelectedGroups );

		jQuery( document ).off( 'click', unselectFieldGroups );
		jQuery( document ).on( 'click', unselectFieldGroups );
	}

	function getSelectedField() {
		return document.getElementById( 'frm-show-fields' ).querySelector( 'li.form-field.selected' );
	}

	function getSelectedFieldGroups() {
		const $fieldGroups = jQuery( '.frm-selected-field-group' );
		if ( $fieldGroups.length ) {
			return $fieldGroups;
		}

		const selectedField = getSelectedField();
		if ( selectedField ) {
			// If there is only one field in a group and the field is selected, consider the field's group as selected for multi-select.
			const selectedFieldGroup = selectedField.closest( 'ul' );
			if ( selectedFieldGroup && 1 === getFieldsInRow( jQuery( selectedFieldGroup ) ).length ) {
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
		maybeRemoveGroupHoverTarget();
	}

	function selectedGroupHasMultipleFields() {
		return getFieldsInRow( jQuery( document.querySelector( '.frm-selected-field-group' ) ) ).length > 1;
	}

	function unselectFieldGroups( event ) {
		if ( 'undefined' !== typeof event ) {
			if ( null !== event.originalEvent.target.closest( '#frm-show-fields' ) ) {
				return;
			}
			if ( event.originalEvent.target.classList.contains( 'frm-merge-fields-into-row' ) ) {
				return;
			}
			if ( null !== event.originalEvent.target.closest( '.frm-merge-fields-into-row' ) ) {
				return;
			}
			if ( event.originalEvent.target.classList.contains( 'frm-custom-field-group-layout' ) ) {
				return;
			}
			if ( event.originalEvent.target.classList.contains( 'frm-cancel-custom-field-group-layout' ) ) {
				return;
			}
		}
		jQuery( '.frm-selected-field-group' ).removeClass( 'frm-selected-field-group' );
		jQuery( document ).off( 'click', unselectFieldGroups );
		maybeRemoveMultiselectPopup();
	}

	function maybeRemoveMultiselectPopup() {
		var popup = document.getElementById( 'frm_field_multiselect_popup' );
		if ( null !== popup ) {
			popup.remove();
		}
	}

	function addFieldMultiselectPopup() {
		getFieldMultiselectPopup();
	}

	function getFieldMultiselectPopup() {
		var popup, mergeOption, caret, verticalSeparator, deleteOption;

		popup = document.getElementById( 'frm_field_multiselect_popup' );

		if ( null !== popup ) {
			popup.classList.toggle( 'frm-unmergable', ! selectedFieldsAreMergable() );
			return popup;
		}

		popup = div();
		popup.id = 'frm_field_multiselect_popup';
		if ( ! selectedFieldsAreMergable() ) {
			popup.classList.add( 'frm-unmergable' );
		}

		mergeOption = div();
		mergeOption.classList.add( 'frm-merge-fields-into-row' );
		mergeOption.textContent = __( 'Merge into row', 'formidable' );

		caret = document.createElement( 'a' );
		caret.style.marginLeft = '5px';
		caret.classList.add( 'frm_icon_font', 'frm_arrowdown6_icon' );
		caret.setAttribute( 'href', '#' );
		mergeOption.appendChild( caret );

		popup.appendChild( mergeOption );

		verticalSeparator = div();
		verticalSeparator.classList.add( 'frm-multiselect-popup-separator' );
		popup.appendChild( verticalSeparator );

		deleteOption = div();
		deleteOption.classList.add( 'frm-delete-field-groups' );
		deleteOption.appendChild( getIconClone( 'frm_trash_svg' ) );
		popup.appendChild( deleteOption );

		document.getElementById( 'post-body-content' ).appendChild( popup );

		jQuery( popup ).hide().fadeIn();

		return popup;
	}

	function selectedFieldsAreMergable() {
		var selectedFieldGroups, totalFieldCount, length, index, fieldGroup;
		selectedFieldGroups = document.querySelectorAll( '.frm-selected-field-group' );
		length = selectedFieldGroups.length;
		if ( 1 === length ) {
			return false;
		}
		totalFieldCount = 0;
		for ( index = 0; index < length; ++index ) {
			fieldGroup = selectedFieldGroups[ index ];
			if ( null !== fieldGroup.querySelector( '.edit_field_type_break, .edit_field_type_hidden' ) ) {
				return false;
			}
			totalFieldCount += getFieldsInRow( jQuery( fieldGroup ) ).length;
			if ( totalFieldCount > 6 ) {
				return false;
			}
		}
		return true;
	}

	function mergeFieldsIntoRowClick( event ) {
		var size, popup;

		if ( null !== event.originalEvent.target.closest( '#frm_field_group_popup' ) ) {
			// prevent clicks within the popup from triggering the button again.
			return;
		}

		if ( event.originalEvent.target.classList.contains( 'frm-custom-field-group-layout' ) ) {
			// avoid switching back to the first page when clicking the custom option nested inside of the merge option.
			return;
		}

		size = getSelectedFieldCount();
		popup = getFieldGroupPopup( size, document.querySelector( '.frm-selected-field-group' ).firstChild );
		this.appendChild( popup );
	}

	function getSelectedFieldCount() {
		var count = 0;
		jQuery( document.querySelectorAll( '.frm-selected-field-group' ) ).each(
			function() {
				count += getFieldsInRow( jQuery( this ) ).length;
			}
		);
		return count;
	}

	function deleteFieldGroupsClick() {
		var fieldIdsToDelete, deleteOnConfirm, multiselectPopup;

		fieldIdsToDelete = getSelectedFieldIds();
		deleteOnConfirm = getDeleteSelectedFieldGroupsOnConfirmFunction( fieldIdsToDelete );

		multiselectPopup = document.getElementById( 'frm_field_multiselect_popup' );
		if ( null !== multiselectPopup ) {
			multiselectPopup.remove();
		}

		this.setAttribute( 'data-frmverify', confirmFieldsDeleteMessage( fieldIdsToDelete.length ) );
		confirmLinkClick( this );

		jQuery( '#frm-confirmed-click' ).on( 'click', deleteOnConfirm );
		jQuery( '#frm_confirm_modal' ).one( 'dialogclose', function() {
			jQuery( '#frm-confirmed-click' ).off( 'click', deleteOnConfirm );
		});
	}

	function getSelectedFieldIds() {
		var deleteFieldIds = [];
		jQuery( '.frm-selected-field-group > li.form-field' )
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

	function deleteAllSelectedFieldGroups( deleteFieldIds ) {
		deleteFieldIds.forEach(
			function( fieldId ) {
				deleteFields( fieldId );
			}
		);
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
			});
		}
		toggleSectionHolder();
	}

	function deleteField( fieldId ) {
		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'frm_delete_field',
				field_id: fieldId,
				nonce: frmGlobal.nonce
			},
			success: function() {
				var $thisField = jQuery( document.getElementById( 'frm_field_id_' + fieldId ) ),
					settings = jQuery( '#frm-single-settings-' + fieldId );

				// Remove settings from sidebar.
				if ( settings.is( ':visible' ) ) {
					document.getElementById( 'frm_insert_fields_tab' ).click();
				}
				settings.remove();

				$thisField.fadeOut( 'slow', function() {
					var $section = $thisField.closest( '.start_divider' ),
						type = $thisField.data( 'type' ),
						$adjacentFields = $thisField.siblings( 'li.form-field' ),
						$liWrapper;

					if ( ! $adjacentFields.length ) {
						if ( $thisField.is( '.edit_field_type_end_divider' ) ) {
							$adjacentFields.length = $thisField.closest( 'li.form-field' ).siblings();
						} else {
							$liWrapper = $thisField.closest( 'ul.frm_sorting' ).parent();
						}
					}

					$thisField.remove();
					if ( type === 'break' ) {
						renumberPageBreaks();
					}
					if ( type === 'product' ) {
						maybeHideQuantityProductFieldOption();
						// a product field attached to a quantity field earlier might be the one deleted, so re-populate
						popAllProductFields();
					}
					if ( jQuery( '#frm-show-fields li' ).length === 0 ) {
						document.getElementById( 'frm_form_editor_container' ).classList.remove( 'frm-has-fields' );
					} else if ( $section.length ) {
						toggleOneSectionHolder( $section );
					}
					if ( $adjacentFields.length ) {
						syncLayoutClasses( $adjacentFields.first() );
					} else {
						$liWrapper.remove();
					}

					// prevent "More Options" tooltips from staying around after their target field is deleted.
					deleteTooltips();
				});
			}
		});
	}

	function addFieldLogicRow() {
		/*jshint validthis:true */
		var id = jQuery( this ).closest( '.frm-single-settings' ).data( 'fid' ),
			formId = thisFormId,
			logicRows = document.getElementById( 'frm_logic_row_' + id ).querySelectorAll( '.frm_logic_row' );
		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'frm_add_logic_row',
				form_id: formId,
				field_id: id,
				nonce: frmGlobal.nonce,
				meta_name: getNewRowId( logicRows, 'frm_logic_' + id + '_' ),
				fields: getFieldList()
			},
			success: function( html ) {
				jQuery( document.getElementById( 'logic_' + id ) ).fadeOut( 'slow', function() {
					var logicRow = jQuery( document.getElementById( 'frm_logic_row_' + id ) );
					logicRow.append( html );
					logicRow.closest( '.frm_logic_rows' ).fadeIn( 'slow' );
				});
			}
		});
		return false;
	}

	function getNewRowId( rows, replace, defaultValue ) {
		if ( ! rows.length ) {
			return 'undefined' !== typeof defaultValue ? defaultValue : 0;
		}
		return parseInt( rows[ rows.length - 1 ].id.replace( replace, '' ), 10 ) + 1;
	}

	function addWatchLookupRow() {
		/*jshint validthis:true */
		var lastRowId,
			id = jQuery( this ).closest( '.frm-single-settings' ).data( 'fid' ),
			formId = thisFormId,
			lookupBlockRows = document.getElementById( 'frm_watch_lookup_block_' + id ).children;
		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'frm_add_watch_lookup_row',
				form_id: formId,
				field_id: id,
				row_key: getNewRowId( lookupBlockRows, 'frm_watch_lookup_' + id + '_' ),
				nonce: frmGlobal.nonce
			},
			success: function( newRow ) {
				var watchRowBlock = jQuery( document.getElementById( 'frm_watch_lookup_block_' + id ) );
				watchRowBlock.append( newRow );
				watchRowBlock.fadeIn( 'slow' );
			}
		});
		return false;
	}

	function resetOptionTextDetails() {
		jQuery( '.frm-single-settings ul input[type="text"][name^="field_options[options_"]' ).filter( '[data-value-on-load]' ).removeAttr( 'data-value-on-load' );
		jQuery( 'input[type="hidden"][name^=optionmap]' ).remove();
	}

	function optionTextAlreadyExists( input ) {
		var fieldId = jQuery( input ).closest( '.frm-single-settings' ).attr( 'data-fid' ),
			optionInputs = jQuery( input ).closest( 'ul' ).get( 0 ).querySelectorAll( '.field_' + fieldId + '_option' ),
			index,
			optionInput;

		for ( index in optionInputs ) {
			optionInput = optionInputs[ index ];
			if ( optionInput.id !== input.id && optionInput.value === input.value && optionInput.getAttribute( 'data-duplicate' ) !== 'true' ) {
				return true;
			}
		}

		return false;
	}

	function onOptionTextFocus() {
		var input,
			fieldId;

		if ( this.getAttribute( 'data-value-on-load' ) === null ) {
			this.setAttribute( 'data-value-on-load', this.value );

			fieldId = jQuery( this ).closest( '.frm-single-settings' ).attr( 'data-fid' );
			input = document.createElement( 'input' );
			input.value = this.value;
			input.setAttribute( 'type', 'hidden' );
			input.setAttribute( 'name', 'optionmap[' + fieldId + '][' + this.value + ']' );
			this.parentNode.appendChild( input );

			if ( typeof optionMap[ fieldId ] === 'undefined' ) {
				optionMap[ fieldId ] = {};
			}

			optionMap[ fieldId ][ this.value ] = input;
		}

		if ( this.getAttribute( 'data-duplicate' ) === 'true' ) {
			this.removeAttribute( 'data-duplicate' );

			// we want to use original value if actually still a duplicate
			if ( optionTextAlreadyExists( this ) ) {
				this.setAttribute( 'data-value-on-focus', this.getAttribute( 'data-value-on-load' ) );
				return;
			}
		}

		if ( '' !== this.value || frm_admin_js.new_option !== this.getAttribute( 'data-value-on-focus' ) ) {
			this.setAttribute( 'data-value-on-focus', this.value );
		}
	}

	function onOptionTextBlur() {
		var originalValue,
			oldValue = this.getAttribute( 'data-value-on-focus' ),
			newValue = this.value,
			fieldId,
			fieldIndex,
			logicId,
			row,
			rowLength,
			rowIndex,
			valueSelect,
			opts,
			fieldIds,
			settingId,
			setting,
			optionMatches,
			option;

		if ( oldValue === newValue ) {
			return;
		}

		fieldId = jQuery( this ).closest( '.frm-single-settings' ).attr( 'data-fid' );
		originalValue = this.getAttribute( 'data-value-on-load' );

		// check if the newValue is already mapped to another option
		// if it is, mark as duplicate and return
		if ( optionTextAlreadyExists( this ) ) {
			this.setAttribute( 'data-duplicate', 'true' );

			if ( typeof optionMap[ fieldId ] !== 'undefined' && typeof optionMap[ fieldId ][ originalValue ] !== 'undefined' ) {
				// unmap any other change that may have happened before instead of changing it to something unused
				optionMap[ fieldId ][ originalValue ].value = originalValue;
			}

			return;
		}

		if ( typeof optionMap[ fieldId ] !== 'undefined' && typeof optionMap[ fieldId ][ originalValue ] !== 'undefined' ) {
			optionMap[ fieldId ][ originalValue ].value = newValue;
		}

		fieldIds = [];
		rows = document.getElementById( 'frm_builder_page' ).querySelectorAll( '.frm_logic_row' );
		rowLength = rows.length;
		for ( rowIndex = 0; rowIndex < rowLength; rowIndex++ ) {
			row = rows[ rowIndex ];
			opts = row.querySelector( '.frm_logic_field_opts' );

			if ( opts.value !== fieldId ) {
				continue;
			}

			logicId = row.id.split( '_' )[ 2 ];
			valueSelect = row.querySelector( 'select[name="field_options[hide_opt_' + logicId + '][]"]' );

			if ( '' === oldValue ) {
				optionMatches = [];
			} else {
				optionMatches = valueSelect.querySelectorAll( 'option[value="' + oldValue + '"]' );
			}

			if ( ! optionMatches.length ) {
				optionMatches = valueSelect.querySelectorAll( 'option[value="' + newValue + '"]' );

				if ( ! optionMatches.length ) {
					option = document.createElement( 'option' );
					valueSelect.appendChild( option );
				}
			}

			if ( optionMatches.length ) {
				option = optionMatches[ optionMatches.length - 1 ];
			}

			option.setAttribute( 'value', newValue );
			option.textContent = newValue;

			if ( fieldIds.indexOf( logicId ) === -1 ) {
				fieldIds.push( logicId );
			}
		}

		for ( fieldIndex in fieldIds ) {
			settingId = fieldIds[ fieldIndex ];
			setting = document.getElementById( 'frm-single-settings-' + settingId );
			moveFieldSettings( setting );
		}
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
			jQuery.ajax({
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
			});
		}
	}

	// Clear the Watch Fields option when Lookup field switches to "Text" option
	function maybeClearWatchFields() {
		/*jshint validthis:true */
		var link, lookupBlock,
			fieldID = this.name.replace( 'field_options[data_type_', '' ).replace( ']', '' );

		link = document.getElementById( 'frm_add_watch_lookup_link_' + fieldID ).parentNode;

		if ( this.value === 'text' ) {
			lookupBlock = document.getElementById( 'frm_watch_lookup_block_' + fieldID );
			if ( lookupBlock !== null ) {
				// Clear and hide the Watch Fields option
				lookupBlock.innerHTML = '';
				link.classList.add( 'frm_hidden' );

				// Hide the Watch Fields row
				link.previousElementSibling.style.display = 'none';
				link.previousElementSibling.previousElementSibling.style.display = 'none';
				link.previousElementSibling.previousElementSibling.previousElementSibling.style.display = 'none';
			}
		} else {
			// Show the Watch Fields option
			link.classList.remove( 'frm_hidden' );
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
		var toCollapse = getAllFieldsForPage( field.get( 0 ).parentNode.closest( 'li.frm_field_box' ).nextElementSibling );
		togglePage( field, toCollapse );
	}

	function toggleCollapseFakePage() {
		var topLevel = document.getElementById( 'frm-fake-page' ),
			firstField = document.getElementById( 'frm-show-fields' ).firstElementChild,
			toCollapse = getAllFieldsForPage( firstField );

		if ( firstField.getAttribute( 'data-ftype' ) === 'break' ) {
			// Don't collapse if the first field is a page break.
			return;
		}

		togglePage( jQuery( topLevel ), toCollapse );
	}

	function getAllFieldsForPage( firstWrapper ) {
		var $fieldsForPage, currentWrapper;

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
		var i,
			fieldCount = toCollapse.length,
			slide = Math.min( fieldCount, 3 );

		if ( field.hasClass( 'frm-page-collapsed' ) ) {
			field.removeClass( 'frm-page-collapsed' );
			toCollapse.removeClass( 'frm-is-collapsed' );
			for ( i = 0; i < slide; i++ ) {
				if ( i === slide - 1 ) {
					jQuery( toCollapse[ i ]).slideDown( 150, function() {
						toCollapse.show();
					});
				} else {
					jQuery( toCollapse[ i ]).slideDown( 150 );
				}
			}
		} else {
			field.addClass( 'frm-page-collapsed' );
			toCollapse.addClass( 'frm-is-collapsed' );
			for ( i = 0; i < slide; i++ ) {
				if ( i === slide - 1 ) {
					jQuery( toCollapse[ i ]).slideUp( 150, function() {
						toCollapse.css( 'cssText', 'display:none !important;' );
					});
				} else {
					jQuery( toCollapse[ i ]).slideUp( 150 );
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
		if ( ! this.id ) {
			return;
		}

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
		var currentClass, originalList;

		currentClass = e.target.classList;

		if ( currentClass.contains( 'frm-collapse-page' ) || currentClass.contains( 'frm-sub-label' ) || e.target.closest( '.dropdown' ) !== null ) {
			return;
		}

		if ( this.closest( '.start_divider' ) !== null ) {
			e.stopPropagation();
		}

		if ( this.classList.contains( 'edit_field_type_divider' ) ) {
			originalList = e.originalEvent.target.closest( 'ul.frm_sorting' );
			if ( null !== originalList ) {
				// prevent section click if clicking a field group within a section.
				if ( originalList.classList.contains( 'edit_field_type_divider' ) || originalList.parentNode.parentNode.classList.contains( 'start_divider' ) ) {
					return;
				}
			}
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
			infoModal( frm_admin_js.repeat_limit_min );
			this.value = '';
		}
	}

	function checkCheckboxSelectionsLimit() {
		/*jshint validthis:true */
		var val = this.value;
		if ( val !== '' && ( val < 1 || val > 200 ) ) {
			infoModal( frm_admin_js.checkbox_limit );
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
		});
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
		var field, thisOpt;

		field = getFieldKeyFromOpt( this );
		if ( ! field ) {
			return;
		}

		thisOpt = jQuery( this ).closest( '.frm_single_option' );

		resetSingleOpt( field.fieldId, field.fieldKey, thisOpt );
	}

	function getFieldKeyFromOpt( object ) {
		var allOpts, fieldId, fieldKey;

		allOpts = jQuery( object ).closest( '.frm_sortable_field_opts' );
		if ( ! allOpts.length ) {
			return false;
		}

		fieldId  = allOpts.attr( 'id' ).replace( 'frm_field_', '' ).replace( '_opts', '' );
		fieldKey = allOpts.data( 'key' );

		return {
			fieldId: fieldId,
			fieldKey: fieldKey
		};
	}

	function resetSingleOpt( fieldId, fieldKey, thisOpt ) {
		var saved, text, defaultVal, previewInput, labelForDisplay, optContainer,
			optKey = thisOpt.data( 'optkey' ),
			separateValues = usingSeparateValues( fieldId ),
			single = jQuery( 'label[for="field_' + fieldKey + '-' + optKey + '"]' ),
			baseName = 'field_options[options_' + fieldId + '][' + optKey + ']',
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

		if ( label.length < 1 ) {
			return;
		}

		// Set the displayed value.
		text = single[0].childNodes;

		if ( imagesAsOptions( fieldId ) ) {
			labelForDisplay = getImageDisplayValue( thisOpt, fieldId, label );
			optContainer = single.find( '.frm_image_option_container' );

			if ( optContainer.length > 0 ) {
				optContainer.replaceWith( labelForDisplay );
			} else {
				text[ text.length - 1 ].nodeValue = '';
				single.append( labelForDisplay );
			}
		} else {
			text[ text.length - 1 ].nodeValue = ' ' + label.val();
		}

		// Set saved value.
		previewInput.val( saved );

		// Set the default value.
		defaultVal = thisOpt.find( 'input[name^="default_value_"]' );
		previewInput.prop( 'checked', defaultVal.is( ':checked' ) ? true : false );
	}

	/**
	 * Set the displayed value for an image option.
	 */
	function getImageDisplayValue( thisOpt, fieldId, label ) {
		var image, imageUrl, showLabelWithImage, fieldType;

		image = thisOpt.find( 'img' );
		if ( image ) {
			imageUrl = image.attr( 'src' );
		}

		showLabelWithImage = showingLabelWithImage( fieldId );
		fieldType = radioOrCheckbox( fieldId );
		return getImageLabel( label.val(), showLabelWithImage, imageUrl, fieldType );
	}

	function getImageOptionSize( fieldId ) {
		var val,
			field = document.getElementById( 'field_options_image_size_' + fieldId ),
			size = '';

		if ( field !== null ) {
			val = field.value;
			if ( val !== '' ) {
				size = val;
			}
		}

		return size;
	}

	function resetDisplayedOpts( fieldId ) {
		var i, opts, type, placeholder, fieldInfo,
			input = jQuery( '[name^="item_meta[' + fieldId + ']"]' );

		if ( input.length < 1 ) {
			return;
		}

		if ( input.is( 'select' ) ) {
			placeholder = document.getElementById( 'frm_placeholder_' + fieldId );
			if ( placeholder !== null && placeholder.value === '' ) {
				fillDropdownOpts( input[0], { sourceID: fieldId });
			} else {
				fillDropdownOpts( input[0], {
					sourceID: fieldId,
					placeholder: placeholder.value
				});
			}
		} else {
			opts = getMultipleOpts( fieldId );
			type = input.attr( 'type' );
			jQuery( '#field_' + fieldId + '_inner_container > .frm_form_fields' ).html( '' );
			fieldInfo = getFieldKeyFromOpt( jQuery( '#frm_delete_field_' + fieldId + '-000_container' ) );

			var container = jQuery( '#field_' + fieldId + '_inner_container > .frm_form_fields' ),
				hasImageOptions = imagesAsOptions( fieldId ),
				imageSize = hasImageOptions ? getImageOptionSize( fieldId ) : '',
				imageOptionClass = hasImageOptions ? ( 'frm_image_option frm_image_' + imageSize + ' ' ) : '',
				isProduct = isProductField( fieldId );

			for ( i = 0; i < opts.length; i++ ) {
				container.append( addRadioCheckboxOpt( type, opts[ i ], fieldId, fieldInfo.fieldKey, isProduct, imageOptionClass ) );
			}
		}

		adjustConditionalLogicOptionOrders( fieldId );
	}

	function adjustConditionalLogicOptionOrders( fieldId ) {
		var row, opts, logicId, valueSelect, rowOptions, expectedOrder, optionLength, optionIndex, expectedOption, optionMatch,
			rows = document.getElementById( 'frm_builder_page' ).querySelectorAll( '.frm_logic_row' ),
			rowLength = rows.length,
			fieldOptions = getFieldOptions( fieldId ),
			optionLength = fieldOptions.length;

		for ( rowIndex = 0; rowIndex < rowLength; rowIndex++ ) {
			row = rows[ rowIndex ];
			opts = row.querySelector( '.frm_logic_field_opts' );

			if ( opts.value != fieldId ) {
				continue;
			}

			logicId = row.id.split( '_' )[ 2 ];
			valueSelect = row.querySelector( 'select[name="field_options[hide_opt_' + logicId + '][]"]' );

			for ( optionIndex = optionLength - 1; optionIndex >= 0; optionIndex-- ) {
				expectedOption = fieldOptions[ optionIndex ];
				optionMatch = valueSelect.querySelector( 'option[value="' + expectedOption + '"]' );

				if ( optionMatch === null ) {
					optionMatch = document.createElement( 'option' );
					optionMatch.setAttribute( 'value', expectedOption );
					optionMatch.textContent = expectedOption;
				}

				valueSelect.prepend( optionMatch );
			}

			optionMatch = valueSelect.querySelector( 'option[value=""]' );
			if ( optionMatch !== null ) {
				valueSelect.prepend( optionMatch );
			}
		}
	}

	function getFieldOptions( fieldId ) {
		var index, input, li,
			listItems = document.getElementById( 'frm_field_' + fieldId + '_opts' ).querySelectorAll( '.frm_single_option' ),
			options = [],
			length = listItems.length;
		for ( index = 0; index < length; index++ ) {
			li = listItems[ index ];

			if ( li.classList.contains( 'frm_hidden' ) ) {
				continue;
			}

			input = li.querySelector( '.field_' + fieldId + '_option' );
			options.push( input.value );
		}
		return options;
	}

	function addRadioCheckboxOpt( type, opt, fieldId, fieldKey, isProduct, classes ) {
		var other, single,
			isOther = opt.key.indexOf( 'other' ) !== -1,

		id = 'field_' + fieldKey + '-' + opt.key;

		other = '<input type="text" id="field_' + fieldKey + '-' + opt.key + '-otext" class="frm_other_input frm_pos_none" name="item_meta[other][' + fieldId + '][' + opt.key + ']" value="" />';

		single = '<div class="frm_' + type + ' ' + type + ' ' + classes + '" id="frm_' + type + '_' + fieldId + '-' + opt.key + '"><label for="' + id +
			'"><input type="' + type +
			'" name="item_meta[' + fieldId + ']' + ( type === 'checkbox' ? '[]' : '' ) +
			'" value="' + opt.saved + '" id="' + id + '"' + ( isProduct ? ' data-price="' + opt.price + '"' : '' ) + ( opt.checked ? ' checked="checked"' : '' ) + '> ' + opt.label + '</label>' +
			( isOther ? other : '' ) +
			'</div>';

		return single;
	}

	function fillDropdownOpts( field, atts ) {
		if ( field === null ) {
			return;
		}
		var sourceID = atts.sourceID,
			placeholder = atts.placeholder,
			isProduct = isProductField( sourceID ),
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

				if ( isProduct ) {
					opt.setAttribute( 'data-price', opts[ i ].price );
				}

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
		var i, saved, labelName, label, key, optObj,
			image, savedLabel, input, field, checkbox, fieldType,
			checked = false,
			opts = [],
			imageUrl = '',

			optVals = jQuery( 'input[name^="field_options[options_' + fieldId + ']"]' ),
			separateValues = usingSeparateValues( fieldId ),
			hasImageOptions = imagesAsOptions( fieldId ),
			showLabelWithImage = showingLabelWithImage( fieldId ),
			isProduct = isProductField( fieldId );

		for ( i = 0; i < optVals.length; i++ ) {
			if ( optVals[ i ].name.indexOf( '[000]' ) > 0 || optVals[ i ].name.indexOf( '[value]' ) > 0 || optVals[ i ].name.indexOf( '[image]' ) > 0 || optVals[ i ].name.indexOf( '[price]' ) > 0 ) {
				continue;
			}
			saved = optVals[ i ].value;
			label = saved;
			key = optVals[ i ].name.replace( 'field_options[options_' + fieldId + '][', '' ).replace( '[label]', '' ).replace( ']', '' );

			if ( separateValues ) {
				labelName = optVals[ i ].name.replace( '[label]', '[value]' );
				saved = jQuery( 'input[name="' + labelName + '"]' ).val();
			}

			if ( hasImageOptions ) {
				imageUrl = getImageUrlFromInput( optVals[i]);
				fieldType = radioOrCheckbox( fieldId );
				label = getImageLabel(  label, showLabelWithImage, imageUrl, fieldType );
			}

			/**
			 * @since 5.0.04
			 */
			label = frmAdminBuild.hooks.applyFilters( 'frm_choice_field_label', label, fieldId, optVals[ i ], hasImageOptions );

			checked = getChecked( optVals[ i ].id  );

			optObj = {
				saved: saved,
				label: label,
				checked: checked,
				key: key
			};

			if ( isProduct ) {
				labelName = optVals[ i ].name.replace( '[label]', '[price]' );
				optObj.price = jQuery( 'input[name="' + labelName + '"]' ).val();
			}

			opts.push( optObj );
		}

		return opts;
	}

	function radioOrCheckbox( fieldId ) {
		var settings = document.getElementById( 'frm-single-settings-' + fieldId );
		if ( settings === null ) {
			return 'radio';
		}

		return settings.classList.contains( 'frm-type-checkbox' ) ? 'checkbox' : 'radio';
	}

	function getImageUrlFromInput( optVal ) {
		var img,
			wrapper = jQuery( optVal ).siblings( '.frm_image_preview_wrapper' );

		if ( ! wrapper.length ) {
			return '';
		}

		img = wrapper.find( 'img' );
		if ( ! img.length ) {
			return '';
		}

		return img.attr( 'src' );
	}

	function getImageLabel( label, showLabelWithImage, imageUrl, fieldType ) {
		var imageLabelClass, fullLabel,
			originalLabel = label,
			shape = fieldType === 'checkbox' ? 'square' : 'circle';

		fullLabel = '<div class="frm_selected_checkmark"><svg class="frmsvg"><use xlink:href="#frm_checkmark_' + shape + '_icon"></svg></div>';
		if ( imageUrl ) {
			fullLabel += '<img src="' + imageUrl + '" alt="' + originalLabel + '" />';
		} else {
			fullLabel += '<div class="frm_empty_url">' + frm_admin_js.image_placeholder_icon + '</div>';
		}
		if ( showLabelWithImage ) {
			fullLabel += '<span class="frm_text_label_for_image"><span class="frm_text_label_for_image_inner">' + originalLabel + '</span></span>';
		}

		imageLabelClass = showLabelWithImage ? ' frm_label_with_image' : '';

		return ( '<span class="frm_image_option_container' + imageLabelClass + '">' + fullLabel + '</span>' );
	}

	function getChecked( id ) {
		field = jQuery( '#' + id );

		if ( field.length === 0 ) {
			return false;
		}

		checkbox = field.siblings( 'input[type=checkbox]' );

		return checkbox.length && checkbox.prop( 'checked' );
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
		return isChecked( 'separate_value_' + fieldId );
	}

	/**
	 * Is the box checked to use images as options?
	 */
	function imagesAsOptions( fieldId ) {
		var checked = false,
			field = document.getElementsByName( 'field_options[image_options_' + fieldId + ']' );

		for ( var i = 0; i < field.length; i++ ) {
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
		const isShowing = ! isChecked( 'hide_image_text_' + fieldId );

		/**
		 * @since 5.0.04
		 */
		return frmAdminBuild.hooks.applyFilters( 'frm_choice_field_showing_label_with_image', isShowing, fieldId );
	}

	function isChecked( id ) {
		var field = document.getElementById( id );
		if ( field === null ) {
			return false;
		} else {
			return field.checked;
		}
	}

	function checkUniqueOpt( targetInput ) {
		const settingsContainer = targetInput.closest( '.frm-single-settings' );
		const fieldId = settingsContainer.getAttribute( 'data-fid' );
		const areValuesSeparate = settingsContainer.querySelector( '[name="field_options[separate_value_' + fieldId + ']"]' ).checked;

		if ( areValuesSeparate && ! targetInput.name.endsWith( '[value]' ) ) {
			return;
		}

		const container = document.getElementById( 'frm_field_' + fieldId + '_opts' );
		const conflicts = Array.from( container.querySelectorAll( 'input[type="text"]' ) ).filter(
			input => input.id !== targetInput.id &&
				areValuesSeparate === input.name.endsWith( '[value]' ) &&
				input.value === targetInput.value
		);
		if ( conflicts.length ) {
			infoModal( __( 'Duplicate option value "%s" detected', 'formidable' ).replace( '%s', targetInput.value ) );
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

	function getFieldValues() {
		/*jshint validthis:true */
		var isTaxonomy,
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
			var showSelect = ( valueFieldType === 'select' || valueFieldType === 'checkbox' || valueFieldType === 'radio' );
			var showText = ( valueFieldType === 'text' || valueFieldType === 'email' || valueFieldType === 'phone' || valueFieldType === 'url' || valueFieldType === 'number' );

			if ( showSelect ) {
				isTaxonomy = document.getElementById( 'frm_has_hidden_options_' + val );
				if ( isTaxonomy !== null ) {
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
					});
				}
			} else {
				var thisType = this.getAttribute( 'data-type' );
				frmGetFieldValues( val, fieldID, metaKey, thisType );
			}
		}
	}

	function getFieldSelection() {
		/*jshint validthis:true */
		var formId = this.value;
		if ( formId ) {
			var fieldId = jQuery( this ).closest( '.frm-single-settings' ).data( 'fid' );
			getTaxOrFieldSelection( formId, fieldId );
		}
	}

	function getTaxOrFieldSelection( formId, fieldId ) {
		if ( formId ) {
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'frm_get_field_selection',
					field_id: fieldId,
					form_id: formId,
					nonce: frmGlobal.nonce
				},
				success: function( msg ) {
					jQuery( '#frm_show_selected_fields_' + fieldId ).html( msg ).show();
				}
			});
		}
	}

	function updateFieldOrder() {
		var fields, fieldId, field, currentOrder, newOrder;
		renumberPageBreaks();
		jQuery( '#frm-show-fields' ).each( function( i ) {
			fields = jQuery( 'li.frm_field_box', this );
			for ( i = 0; i < fields.length; i++ ) {
				fieldId = fields[ i ].getAttribute( 'data-fid' );
				field = jQuery( 'input[name="field_options[field_order_' + fieldId + ']"]' );
				currentOrder = field.val();
				newOrder = i + 1;

				if ( currentOrder != newOrder ) {
					field.val( newOrder );
					singleField = document.getElementById( 'frm-single-settings-' + fieldId );

					moveFieldSettings( singleField );
					fieldUpdated();
				}
			}
		});
	}

	function toggleSectionHolder() {
		document.querySelectorAll( '.start_divider' ).forEach(
			function( divider ) {
				toggleOneSectionHolder( jQuery( divider ) );
			}
		);
	}

	function toggleOneSectionHolder( $section ) {
		var noSectionFields, $rows, length, index, sectionHasFields;

		if ( ! $section.length ) {
			return;
		}

		$rows = $section.find( 'ul.frm_sorting' );
		sectionHasFields = false;
		length = $rows.length;
		for ( index = 0; index < length; ++index ) {
			if ( 0 !== getFieldsInRow( jQuery( $rows.get( index ) ) ).length ) {
				sectionHasFields = true;
				break;
			}
		}

		noSectionFields = $section.parent().children( '.frm_no_section_fields' ).get( 0 );
		noSectionFields.classList.toggle( 'frm_block', ! sectionHasFields );
	}

	function slideDown() {
		/*jshint validthis:true */
		var id = jQuery( this ).data( 'slidedown' );
		var $thisId = jQuery( document.getElementById( id ) );
		if ( $thisId.is( ':hidden' ) ) {
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

	function adjustVisibilityValuesForEveryoneValues( element, option ) {
		if ( '' === option.getAttribute( 'value' ) ) {
			onEveryoneOptionSelected( jQuery( this ) );
		} else {
			unselectEveryoneOptionIfSelected( jQuery( this ) );
		}
	}

	function onEveryoneOptionSelected( $select ) {
		$select.val( '' );
		$select.next( '.btn-group' ).find( '.multiselect-container input[value!=""]' ).prop( 'checked', false );
	}

	function unselectEveryoneOptionIfSelected( $select ) {
		var selectedValues = $select.val(),
			index;

		if ( selectedValues === null ) {
			$select.next( '.btn-group' ).find( '.multiselect-container input[value=""]' ).prop( 'checked', true );
			onEveryoneOptionSelected( $select );
			return;
		}

		index = selectedValues.indexOf( '' );
		if ( index >= 0 ) {
			selectedValues.splice( index, 1 );
			$select.val( selectedValues );
			$select.next( '.btn-group' ).find( '.multiselect-container input[value=""]' ).prop( 'checked', false );
		}
	}

	/**
	 * Get rid of empty container that inserts extra space.
	 */
	function hideEmptyEle() {
		jQuery( '.frm-hide-empty' ).each( function() {
			if ( jQuery( this ).text().trim().length === 0 ) {
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
		replaceWith = replaceWith.replace( ' block ', ' ' ).replace( ' inline ', ' horizontal_radio ' );

		classes = field.className.split( ' frmstart ' )[1];
		classes = 0 === classes.indexOf( 'frmend ' ) ? '' : classes.split( ' frmend ' )[0];

		if ( classes.trim() === '' ) {
			replace = ' frmstart  frmend ';
			if ( -1 === field.className.indexOf( replace ) ) {
				replace = ' frmstart frmend ';
			}
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
		jQuery( '.frm-open [data-open="' + this.parentNode.id + '"]' ).closest( '.frm-open' ).removeClass( 'frm-open' );
	}

	function changeInputtedValue() {
		/*jshint validthis:true */
		var i,
			action = this.getAttribute( 'data-frmchange' ).split( ',' );

		for ( i = 0; i < action.length; i++ ) {
			if ( action[i] === 'updateOption' ) {
				changeHiddenSeparateValue( this );
			} else if ( action[i] === 'updateDefault' ) {
				changeDefaultRadioValue( this );
			} else if ( action[i] === 'checkUniqueOpt' ) {
				checkUniqueOpt( this );
			} else {
				this.value = this.value[ action[i] ]();
			}
		}
	}

	/**
	 * When the saved value is changed, update the default value radio.
	 */
	function changeDefaultRadioValue( input ) {
		var parentLi = getOptionParent( input ),
			key = parentLi.getAttribute( 'data-optkey' ),
			fieldId = getOptionFieldId( parentLi, key ),
			defaultRadio = parentLi.querySelector( 'input[name="default_value_' + fieldId + '"]' );

		if ( defaultRadio !== null ) {
			defaultRadio.value = input.value;
		}
	}

	/**
	 * If separate values are not enabled, change the saved value when
	 * the displayed value is changed.
	 */
	function changeHiddenSeparateValue( input ) {
		var savedVal,
			parentLi = getOptionParent( input ),
			key = parentLi.getAttribute( 'data-optkey' ),
			fieldId = getOptionFieldId( parentLi, key ),
			sep = document.getElementById( 'separate_value_' + fieldId );

		if ( sep !== null && sep.checked === false ) {
			// If separate values are not turned on.
			savedVal = document.getElementById( 'field_key_' + fieldId + '-' + key );
			savedVal.value = input.value;
			changeDefaultRadioValue( savedVal );
		}
	}

	function getOptionParent( input ) {
		var parentLi = input.parentNode;
		if ( parentLi.tagName !== 'LI' ) {
			parentLi = parentLi.parentNode;
		}
		return parentLi;
	}

	function getOptionFieldId( li, key ) {
		var liId = li.id;

		return liId.replace( 'frm_delete_field_', '' ).replace( '-' + key + '_container', '' );
	}

	function submitBuild() {
		/*jshint validthis:true */
		var $thisEle = this;

		preFormSave( this );

		var $form = jQuery( builderForm );
		var v = JSON.stringify( $form.serializeArray() );

		jQuery( document.getElementById( 'frm_compact_fields' ) ).val( v );
		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {action: 'frm_save_form', 'frm_compact_fields': v, nonce: frmGlobal.nonce},
			success: function( msg ) {
				afterFormSave( $thisEle );

				var $postStuff = document.getElementById( 'post-body-content' );
				var $html = document.createElement( 'div' );
				$html.setAttribute( 'class', 'frm_updated_message' );
				$html.innerHTML = msg;
				$postStuff.insertBefore( $html, $postStuff.firstChild );
			},
			error: function() {
				triggerSubmit( document.getElementById( 'frm_js_build_form' ) );
			}
		});
	}

	function triggerSubmit( form ) {
		var button = form.ownerDocument.createElement( 'input' );
		button.style.display = 'none';
		button.type = 'submit';
		form.appendChild( button ).click();
		form.removeChild( button );
	}

	function triggerChange( element ) {
		jQuery( element ).trigger( 'change' );
	}

	function submitNoAjax() {
		/*jshint validthis:true */
		var form;

		preFormSave( this );
		form = jQuery( builderForm );
		jQuery( document.getElementById( 'frm_compact_fields' ) ).val( JSON.stringify( form.serializeArray() ) );
		triggerSubmit( document.getElementById( 'frm_js_build_form' ) );
	}

	function preFormSave( b ) {
		removeWPUnload();
		if ( jQuery( 'form.inplace_form' ).length ) {
			jQuery( '.inplace_save, .postbox' ).trigger( 'click' );
		}

		if ( b.classList.contains( 'frm_button_submit' ) ) {
			b.classList.add( 'frm_loading_form' );
		} else {
			b.classList.add( 'frm_loading_button' );
		}
		b.setAttribute( 'aria-busy', 'true' );
	}

	function afterFormSave( button ) {
		button.classList.remove( 'frm_loading_form' );
		button.classList.remove( 'frm_loading_button' );
		resetOptionTextDetails();
		fieldsUpdated = 0;
		button.setAttribute( 'aria-busy', 'false' );

		setTimeout( function() {
			jQuery( '.frm_updated_message' ).fadeOut( 'slow', function() {
				this.parentNode.removeChild( this );
			});
		}, 5000 );
	}

	function initUpgradeModal() {
		const $info = initModal( '#frm_upgrade_modal' );
		if ( $info === false ) {
			return;
		}

		document.addEventListener( 'click', handleUpgradeClick );

		function handleUpgradeClick( event ) {
			let element, link, content;

			element = event.target;

			if ( ! element.classList ) {
				return;
			}

			const showExpiredModal = element.classList.contains( 'frm_show_expired_modal' ) || null !== element.querySelector( '.frm_show_expired_modal' ) || element.closest( '.frm_show_expired_modal' );

			if ( ! element.dataset.upgrade ) {
				const parent = element.closest( '[data-upgrade]' );
				if ( ! parent ) {
					return;
				}
				element = parent;
			}

			if ( showExpiredModal ) {
				const hookName = 'frm_show_expired_modal';
				wp.hooks.doAction( hookName, element );
				return;
			}

			const upgradeLabel = element.dataset.upgrade;
			if ( ! upgradeLabel || element.classList.contains( 'frm_show_upgrade_tab' ) ) {
				return;
			}

			event.preventDefault();

			const modal = $info.get( 0 );
			const lockIcon = modal.querySelector( '.frm_lock_icon' );

			if ( lockIcon ) {
				lockIcon.style.display = 'block';
				lockIcon.classList.remove( 'frm_lock_open_icon' );
				lockIcon.querySelector( 'use' ).setAttribute( 'href', '#frm_lock_icon' );
			}

			const upgradeImageId = 'frm_upgrade_modal_image';
			const oldImage = document.getElementById( upgradeImageId );
			if ( oldImage ) {
				oldImage.remove();
			}

			if ( element.dataset.image ) {
				if ( lockIcon ) {
					lockIcon.style.display = 'none';
				}
				lockIcon.parentNode.insertBefore( img({ id: upgradeImageId, src: frmGlobal.url + '/images/' + element.dataset.image }), lockIcon );
			}

			const level = modal.querySelector( '.license-level' );
			if ( level ) {
				level.textContent = getRequiredLicenseFromTrigger( element );
			}

			// If one click upgrade, hide other content
			addOneClick( element, 'modal', upgradeLabel );

			modal.querySelector( '.frm_are_not_installed' ).style.display = element.dataset.image ? 'none' : 'inline-block';
			modal.querySelector( '.frm_feature_label' ).textContent = upgradeLabel;
			modal.querySelector( 'h2' ).style.display = 'block';

			$info.dialog( 'open' );

			// set the utm medium
			const button = modal.querySelector( '.button-primary:not(.frm-oneclick-button)' );
			link = button.getAttribute( 'href' ).replace( /(medium=)[a-z_-]+/ig, '$1' + element.getAttribute( 'data-medium' ) );
			content = element.getAttribute( 'data-content' );
			if ( content === null ) {
				content = '';
			}
			link = link.replace( /(content=)[a-z_-]+/ig, '$1' + content );
			button.setAttribute( 'href', link );
		}
	}

	function getRequiredLicenseFromTrigger( element ) {
		if ( element.dataset.requires ) {
			return element.dataset.requires;
		}
		return 'Pro';
	}

	function populateUpgradeTab( element ) {
		const title = element.dataset.upgrade;

		const tab = element.getAttribute( 'href' ).replace( '#', '' );
		const container = document.querySelector( '.frm_' + tab ) || document.querySelector( '.' + tab );

		if ( ! container ) {
			return;
		}

		if ( container.querySelector( '.frm-upgrade-message' ) ) {
			// Tab has already been populated.
			return;
		}

		const h2 = container.querySelector( 'h2' );
		h2.style.borderBottom = 'none';

		/* translators: %s: Form Setting section name (ie Form Permissions, Form Scheduling). */
		h2.textContent = __( '%s are not installed' ).replace( '%s', title );

		container.classList.add( 'frmcenter' );

		const upgradeModal = document.getElementById( 'frm_upgrade_modal' );
		appendClonedModalElementToContainer( 'frm-oneclick' );
		appendClonedModalElementToContainer( 'frm-addon-status' );

		// Borrow the call to action from the Upgrade upgradeModal which should exist on the settings page (it is still used for other upgrades including Actions).
		const upgradeModalLink = upgradeModal.querySelector( '.frm-upgrade-link' );
		if ( upgradeModalLink ) {
			const upgradeButton = upgradeModalLink.cloneNode( true );
			const level         = upgradeButton.querySelector( '.license-level' );

			if ( level ) {
				level.textContent = getRequiredLicenseFromTrigger( element );
			}

			container.appendChild( upgradeButton );

			// Maybe append the secondary "Already purchased?" link from the upgradeModal as well.
			if ( upgradeModalLink.nextElementSibling && upgradeModalLink.nextElementSibling.querySelector( '.frm-link-secondary' ) ) {
				container.appendChild( upgradeModalLink.nextElementSibling.cloneNode( true ) );
			}

			appendClonedModalElementToContainer( 'frm-oneclick-button' );
		}

		appendClonedModalElementToContainer( 'frm-upgrade-message' );
		addOneClick( element, 'tab', element.dataset.message );

		if ( element.dataset.screenshot ) {
			container.appendChild( getScreenshotWrapper( element.dataset.screenshot ) );
		}

		function appendClonedModalElementToContainer( className ) {
			container.appendChild( upgradeModal.querySelector( '.' + className ).cloneNode( true ) );
		}
	}

	function getScreenshotWrapper( screenshot ) {
		const folderUrl = frmGlobal.url + '/images/screenshots/';
		const wrapper = div({
			className: 'frm-settings-screenshot-wrapper',
			children: [
				getToolbar(),
				div({ child: img({ src: folderUrl + screenshot }) })
			]
		});

		function getToolbar() {
			const children = getColorIcons();
			children.push( img({ src: frmGlobal.url + '/images/tab.svg' }) );
			return div({
				className: 'frm-settings-screenshot-toolbar',
				children
			});
		}

		function getColorIcons() {
			return [ '#ED8181', '#EDE06A', '#80BE30' ].map(
				color => {
					const circle = div({ className: 'frm-minmax-icon' });
					circle.style.backgroundColor = color;
					return circle;
				}
			);
		}

		return wrapper;
	}

	/**
	 * Allow addons to be installed from the upgrade modal.
	 *
	 * @param {Element} link
	 * @param {String} context Either 'modal' or 'tab'.
	 * @param {String|undefined} upgradeLabel
	 */
	function addOneClick( link, context, upgradeLabel ) {
		let container;

		if ( 'modal' === context ) {
			container = document.getElementById( 'frm_upgrade_modal' );
		} else if ( 'tab' === context ) {
			container = document.getElementById( link.getAttribute( 'href' ).substr( 1 ) );
		} else {
			return;
		}

		const oneclickMessage = container.querySelector( '.frm-oneclick' );
		const upgradeMessage  = container.querySelector( '.frm-upgrade-message' );
		const showLink        = container.querySelector( '.frm-upgrade-link' );
		const button          = container.querySelector( '.frm-oneclick-button' );
		const addonStatus     = container.querySelector( '.frm-addon-status' );

		let oneclick   = link.getAttribute( 'data-oneclick' );
		let newMessage = link.getAttribute( 'data-message' );
		let showIt  = 'block';
		let showMsg = 'block';
		let hideIt  = 'none';

		// If one click upgrade, hide other content.
		if ( oneclickMessage !== null && typeof oneclick !== 'undefined' && oneclick ) {
			if ( newMessage === null ) {
				showMsg = 'none';
			}
			showIt = 'none';
			hideIt = 'block';
			oneclick = JSON.parse( oneclick );

			button.className   = button.className.replace( ' frm-install-addon', '' ).replace( ' frm-activate-addon', '' );
			button.className   = button.className + ' ' + oneclick.class;
			button.textContent = __( 'Activate', 'formidable' );
			button.rel = oneclick.url;
		}

		if ( ! newMessage ) {
			newMessage = upgradeMessage.getAttribute( 'data-default' );
		}
		if ( undefined !== upgradeLabel ) {
			newMessage = newMessage.replace( '<span class="frm_feature_label"></span>', upgradeLabel );
		}

		upgradeMessage.innerHTML = newMessage;

		// Either set the link or use the default.
		showLink.href = getShowLinkHrefValue( link, showLink );

		addonStatus.style.display = 'none';

		oneclickMessage.style.display = hideIt;
		button.style.display = hideIt === 'block' ? 'inline-block' : hideIt;
		upgradeMessage.style.display = showMsg;
		showLink.style.display = showIt === 'block' ? 'inline-block' : showIt;
	}

	function getShowLinkHrefValue( link, showLink ) {
		let customLink = link.getAttribute( 'data-link' );
		if ( customLink === null || typeof customLink === 'undefined' || customLink === '' ) {
			customLink = showLink.getAttribute( 'data-default' );
		}
		return customLink;
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
		var i,
			missingClass = jQuery( parentClass + ' :not(.frm_has_shortcodes) .frm_not_email_message, ' + parentClass + ' :not(.frm_has_shortcodes) .frm_not_email_to, ' + parentClass + ' :not(.frm_has_shortcodes) .frm_not_email_subject' );
		for ( i = 0; i < missingClass.length; i++ ) {
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

	function copyFormAction( event ) {
		if ( waitForActionToLoadBeforeCopy( event.target ) ) {
			return;
		}

		const targetSettings = event.target.closest( '.frm_form_action_settings' );
		const wysiwyg        = targetSettings.querySelector( '.wp-editor-area' );
		if ( wysiwyg ) {
			// Temporary remove TinyMCE before cloning to avoid TinyMCE conflicts.
			tinymce.EditorManager.execCommand( 'mceRemoveEditor', true, wysiwyg.id );
		}

		const $action   = jQuery( targetSettings ).clone();
		const currentID = $action.attr( 'id' ).replace( 'frm_form_action_', '' );
		const newID     = newActionId( currentID );

		$action.find( '.frm_action_id, .frm-btn-group' ).remove();
		$action.find( 'input[name$="[' + currentID + '][ID]"]' ).val( '' );
		$action.find( '.widget-inside' ).hide();

		// the .html() gets original values, so they need to be set
		$action.find( 'input[type=text], textarea, input[type=number]' ).prop( 'defaultValue', function() {
			return this.value;
		});

		$action.find( 'input[type=checkbox], input[type=radio]' ).prop( 'defaultChecked', function() {
			return this.checked;
		});

		const rename  = new RegExp( '\\[' + currentID + '\\]', 'g' );
		const reid    = new RegExp( '_' + currentID + '"', 'g' );
		const reclass = new RegExp( '-' + currentID + '"', 'g' );
		const revalue = new RegExp( '"' + currentID + '"', 'g' ); // if a field id matches, this could cause trouble

		let html = $action.html().replace( rename, '[' + newID + ']' ).replace( reid, '_' + newID + '"' );
		html = html.replace( reclass, '-' + newID + '"' ).replace( revalue, '"' + newID + '"' );

		const newAction = div({
			id: 'frm_form_action_' + newID,
			className: $action.get( 0 ).className
		});
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
		document.getElementById( 'frm_notification_settings' ).appendChild( newAction );

		if ( wysiwyg ) {
			// Re-initialize the original wysiwyg which was removed before cloning.
			frmDom.wysiwyg.init( wysiwyg );
			frmDom.wysiwyg.init( newAction.querySelector( '.wp-editor-area' ) );
		}

		if ( newAction.classList.contains( 'frm_single_on_submit_settings' ) ) {
			const autocompleteInput = newAction.querySelector( 'input.frm-page-search' );
			if ( autocompleteInput ) {
				frmDom.autocomplete.initAutocomplete( 'page', newAction );
			}
		}

		initiateMultiselect();

		const hookName = 'frm_after_duplicate_action';
		wp.hooks.doAction( hookName, newAction );
	}

	function waitForActionToLoadBeforeCopy( element ) {
		var $trigger = jQuery( element ),
			$original = $trigger.closest( '.frm_form_action_settings' ),
			$inside = $original.find( '.widget-inside' ),
			$top;

		if ( $inside.find( 'p, div, table' ).length ) {
			return false;
		}

		$top = $original.find( '.widget-top' );
		$top.on( 'frm-action-loaded', function() {
			$trigger.trigger( 'click' );
			$original.removeClass( 'open' );
			$inside.hide();
		});
		$top.trigger( 'click' );
		return true;
	}

	function newActionId( currentID ) {
		var newID = parseInt( currentID, 10 ) + 11;
		var exists = document.getElementById( 'frm_form_action_' + newID );
		if ( exists !== null ) {
			newID++;
			newID = newActionId( newID );
		}
		return newID;
	}

	function addFormAction() {
		/*jshint validthis:true */
		const type = jQuery( this ).data( 'actiontype' );

		if ( isAtLimitForActionType( type ) ) {
			return;
		}

		const actionId = getNewActionId();
		const formId = thisFormId;

		const placeholderSetting = document.createElement( 'div' );
		placeholderSetting.classList.add( 'frm_single_' + type + '_settings' );

		const actionsList = document.getElementById( 'frm_notification_settings' );
		actionsList.appendChild( placeholderSetting );

		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'frm_add_form_action',
				type: type,
				list_id: actionId,
				form_id: formId,
				nonce: frmGlobal.nonce
			},
			success: handleAddFormActionSuccess
		});

		function handleAddFormActionSuccess( html ) {
			fieldUpdated();
			placeholderSetting.remove();

			closeOpenActions();

			const newActionContainer = div();
			newActionContainer.innerHTML = html;

			const widgetTop = newActionContainer.querySelector( '.widget-top' );
			Array.from( newActionContainer.children ).forEach( child => actionsList.appendChild( child ) );

			jQuery( '.frm_form_action_settings' ).fadeIn( 'slow' );

			const newAction = document.getElementById( 'frm_form_action_' + actionId );

			newAction.classList.add( 'open' );
			document.getElementById( 'post-body-content' ).scroll({
				top: newAction.offsetTop + 10,
				left: 0,
				behavior: 'smooth'
			});

			// Check if icon should be active
			checkActiveAction( type );
			showInputIcon( '#frm_form_action_' + actionId );

			initiateMultiselect();
			frmDom.autocomplete.initAutocomplete( 'page', newAction );

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

	function closeOpenActions() {
		document.querySelectorAll( '.frm_form_action_settings.open' ).forEach(
			setting => setting.classList.remove( 'open' )
		);
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
		var actionSettings = document.querySelectorAll( '.frm_form_action_settings' ),
			len = getNewRowId( actionSettings, 'frm_form_action_' );
		if ( typeof document.getElementById( 'frm_form_action_' + len ) !== 'undefined' ) {
			len = len + 100;
		}
		if ( lastNewActionIdReturned >= len ) {
			len = lastNewActionIdReturned + 1;
		}
		lastNewActionIdReturned = len;
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
			fieldType = obj.getAttribute( 'data-type' ),
			allFieldSettings = document.querySelectorAll( '.frm-single-settings:not(.frm_hidden)' );

		for ( i = 0; i < allFieldSettings.length; i++ ) {
			allFieldSettings[i].classList.add( 'frm_hidden' );
		}

		singleField = document.getElementById( 'frm-single-settings-' + fieldId );
		moveFieldSettings( singleField );

		if ( fieldType && 'quantity' === fieldType ) {
			popProductFields( jQuery( singleField ).find( '.frmjs_prod_field_opt' )[0]);
		}

		singleField.classList.remove( 'frm_hidden' );
		document.getElementById( 'frm-options-panel-tab' ).click();

		const editor = singleField.querySelector( '.wp-editor-area' );
		if ( editor ) {
			frmDom.wysiwyg.init(
				editor,
				{ setupCallback: setupTinyMceEventHandlers }
			);
		}
	}

	function setupTinyMceEventHandlers( editor ) {
		editor.on( 'Change', function() {
			handleTinyMceChange( editor );
		});
	}

	function handleTinyMceChange( editor ) {
		if ( ! isTinyMceActive() || tinyMCE.activeEditor.isHidden() ) {
			return;
		}

		editor.targetElm.value = editor.getContent();
		jQuery( editor.targetElm ).trigger( 'change' );
	}

	function isTinyMceActive() {
		var activeSettings, wrapper;

		activeSettings = document.querySelector( '.frm-single-settings:not(.frm_hidden)' );
		if ( ! activeSettings ) {
			return false;
		}

		wrapper = activeSettings.querySelector( '.wp-editor-wrap' );
		return null !== wrapper && wrapper.classList.contains( 'tmce-active' );
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
		var actionBox = jQuery( this ).closest( '.frm_form_action_settings' ),
			rowType = this.getAttribute( 'data-emailrow' ),
			emailRowSelector = '.frm_' + rowType + '_row',
			emailButtonSelector = '.frm_' + rowType + '_button';

		jQuery( actionBox ).find( emailButtonSelector ).fadeIn( 'slow' );
		jQuery( actionBox ).find( emailRowSelector ).fadeOut( 'slow', function() {
			jQuery( actionBox ).find( emailRowSelector + ' input' ).val( '' );
		});
	}

	function showEmailWarning() {
		/*jshint validthis:true */
		var actionBox = jQuery( this ).closest( '.frm_form_action_settings' ),
			emailRowSelector = '.frm_from_to_match_row',
			fromVal = actionBox.find( 'input[name$="[post_content][from]"]' ).val(),
			toVal = actionBox.find( 'input[name$="[post_content][email_to]"]' ).val();

		if ( fromVal === toVal ) {
			jQuery( actionBox ).find( emailRowSelector ).fadeIn( 'slow' );
		} else {
			jQuery( actionBox ).find( emailRowSelector ).fadeOut( 'slow' );
		}
	}

	function checkActiveAction( type ) {
		const actionTriggers = document.querySelectorAll( '.frm_' + type + '_action' );

		if ( isAtLimitForActionType( type ) ) {
			const addAlreadyUsedClass = getLimitForActionType( type ) > 0;
			markActionTriggersInactive( actionTriggers, addAlreadyUsedClass  );
			return;
		}

		markActionTriggersActive( actionTriggers );
	}

	function markActionTriggersActive( triggers ) {
		triggers.forEach(
			trigger => {
				if ( trigger.querySelector( '.frm_show_upgrade' ) ) {
					// Prevent disabled action becoming active.
					return;
				}

				trigger.classList.remove( 'frm_inactive_action', 'frm_already_used' );
				trigger.classList.add( 'frm_active_action' );
			}
		);
	}

	function markActionTriggersInactive( triggers, addAlreadyUsedClass ) {
		triggers.forEach(
			trigger => {
				trigger.classList.remove( 'frm_active_action' );
				trigger.classList.add( 'frm_inactive_action' );
				if ( addAlreadyUsedClass ) {
					trigger.classList.add( 'frm_already_used' );
				}
			}
		);
	}

	function isAtLimitForActionType( type ) {
		let atLimit = getNumberOfActionsForType( type ) >= getLimitForActionType( type );

		const hookName = 'frm_action_at_limit';
		const hookArgs = { type };
		atLimit = wp.hooks.applyFilters( hookName, atLimit, hookArgs );

		return atLimit;
	}

	function getLimitForActionType( type ) {
		return parseInt( jQuery( '.frm_' + type + '_action' ).data( 'limit' ), 10 );
	}

	function getNumberOfActionsForType( type ) {
		return jQuery( '.frm_single_' + type + '_settings' ).length;
	}

	function onlyOneActionMessage() {
		infoModal( frm_admin_js.only_one_action );
	}

	function addFormLogicRow() {
		/*jshint validthis:true */
		var id = jQuery( this ).data( 'emailkey' ),
			type = jQuery( this ).closest( '.frm_form_action_settings' ).find( '.frm_action_name' ).val(),
			formId = document.getElementById( 'form_id' ).value,
			logicRows = document.getElementById( 'frm_form_action_' + id ).querySelectorAll( '.frm_logic_row' );
		jQuery.ajax({
			type: 'POST', url: ajaxurl,
			data: {
				action: 'frm_add_form_logic_row',
				email_id: id,
				form_id: formId,
				meta_name: getNewRowId( logicRows, 'frm_logic_' + id + '_' ),
				type: type,
				nonce: frmGlobal.nonce
			},
			success: function( html ) {
				jQuery( document.getElementById( 'logic_link_' + id ) ).fadeOut( 'slow', function() {
					var $logicRow = jQuery( document.getElementById( 'frm_logic_row_' + id ) );
					$logicRow.append( html );
					$logicRow.parent( '.frm_logic_rows' ).fadeIn( 'slow' );
				});
			}
		});
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
		var formId = thisFormId,
			logicRows = document.getElementById( 'frm_submit_logic_row' ).querySelectorAll( '.frm_logic_row' );
		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'frm_add_submit_logic_row',
				form_id: formId,
				meta_name: getNewRowId( logicRows, 'frm_logic_submit_' ),
				nonce: frmGlobal.nonce
			},
			success: function( html ) {
				var $logicRow = jQuery( document.getElementById( 'frm_submit_logic_row' ) );
				$logicRow.append( html );
				$logicRow.parent( '.frm_submit_logic_rows' ).fadeIn( 'slow' );
			}
		});
		return false;
	}

	/**
	 *  When the user selects a field for a submit condition, update corresponding options field accordingly.
	 */
	function addSubmitLogicOpts() {
		var fieldOpt = jQuery( this );
		var fieldId = fieldOpt.find( ':selected' ).val();

		if ( fieldId ) {
			var row = fieldOpt.data( 'row' );
			frmGetFieldValues( fieldId, 'submit', row, '', 'options[submit_conditions][hide_opt][]' );
		}
	}

	function formatEmailSetting() {
		/*jshint validthis:true */
		/*var val = jQuery( this ).val();
		var email = val.match( /(\s[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z0-9._-]+)/gi );
		if(email !== null && email.length) {
			//has email
			//TODO: add < > if they aren't there
		}*/
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
				infoModal( frm_admin_js.field_already_used );
				return false;
			}
		});
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
			jQuery.ajax({
				type: 'POST', url: ajaxurl,
				data: {action: 'frm_display_get_content', id: v, nonce: frmGlobal.nonce},
				success: function( val ) {
					$dyn.val( val );
					jQuery( '.frm_dyncontent_opt' ).show();
				}
			});
		}
	}

	function switchPostType() {
		/*jshint validthis:true */
		// update all rows of categories/taxonomies
		var curSelect, newSelect,
			catRows = document.getElementById( 'frm_posttax_rows' ).childNodes,
			postParentField = document.querySelector( '.frm_post_parent_field' ),
			postMenuOrderField = document.querySelector( '.frm_post_menu_order_field' ),
			postType = this.value;

		// Get new category/taxonomy options
		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'frm_replace_posttax_options',
				post_type: postType,
				nonce: frmGlobal.nonce
			},
			success: function( html ) {

				// Loop through each category row, and replace the first dropdown
				for ( i = 0; i < catRows.length; i++ ) {
					// Check if current element is a div
					if ( catRows[i].tagName !== 'DIV' ) {
						continue;
					}

					// Get current category select
					curSelect = catRows[i].getElementsByTagName( 'select' )[0];

					// Set up new select
					newSelect = document.createElement( 'select' );
					newSelect.innerHTML = html;
					newSelect.className = curSelect.className;
					newSelect.name = curSelect.name;

					// Replace the old select with the new select
					catRows[i].replaceChild( newSelect, curSelect );
				}
			}
		});

		// Get new post parent option.
		if ( postParentField ) {
			getActionOption(
				postParentField,
				postType,
				'frm_get_post_parent_option',
				function( response, optName ) {
					// The replaced string is declared in FrmProFormActionController::ajax_get_post_menu_order_option() in the pro version.
					postParentField.querySelector( '.frm_post_parent_opt_wrapper' ).innerHTML = response.replaceAll( 'REPLACETHISNAME', optName );
					frmDom.autocomplete.initAutocomplete( 'page', postParentField );
				}
			);
		}

		if ( postMenuOrderField ) {
			getActionOption( postMenuOrderField, postType, 'frm_should_use_post_menu_order_option' );
		}
	}

	function getActionOption( field, postType, action, successHandler ) {
		const opt = field.querySelector( '.frm_autocomplete_value_input' ) || field.querySelector( 'select' ),
			optName = opt.getAttribute( 'name' );

		jQuery.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: action,
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
		});
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
		var name,
			id = jQuery( 'input[name="id"]' ).val(),
			settings = jQuery( button ).closest( '.frm_form_action_settings' ),
			key = settings.data( 'actionkey' ),
			postType = settings.find( '.frm_post_type' ).val(),
			metaName = 0,
			postTypeRows = document.querySelectorAll( '.frm_post' + type + '_row' );

		if ( postTypeRows.length ) {
			name = postTypeRows[ postTypeRows.length - 1 ].id.replace( 'frm_post' + type + '_', '' );
			if ( isNumeric( name ) ) {
				metaName = 1 + parseInt( name, 10 );
			} else {
				metaName = 1;
			}
		}

		jQuery.ajax({
			type: 'POST', url: ajaxurl,
			data: {
				action: 'frm_add_post' + type + '_row',
				form_id: id,
				meta_name: metaName,
				tax_key: metaName,
				post_type: postType,
				action_key: key,
				nonce: frmGlobal.nonce
			},
			success: function( html ) {
				var cfOpts, optIndex;
				jQuery( document.getElementById( 'frm_post' + type + '_rows' ) ).append( html );
				jQuery( '.frm_add_post' + type + '_row.button' ).hide();

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
		});
	}

	function isNumeric( value ) {
		return ! isNaN( parseFloat( value ) ) && isFinite( value );
	}

	function getMetaValue( id, metaName ) {
		var newMeta = metaName;
		if ( jQuery( document.getElementById( id + metaName ) ).length > 0 ) {
			newMeta = getMetaValue( id, metaName + 1 );
		}
		return newMeta;
	}

	function changePosttaxRow() {
		/*jshint validthis:true */
		if ( ! jQuery( this ).closest( '.frm_posttax_row' ).find( '.frm_posttax_opt_list' ).length ) {
			return;
		}

		jQuery( this ).closest( '.frm_posttax_row' ).find( '.frm_posttax_opt_list' ).html( '<div class="spinner frm_spinner" style="display:block"></div>' );

		var postType = jQuery( this ).closest( '.frm_form_action_settings' ).find( 'select[name$="[post_content][post_type]"]' ).val(),
			actionKey = jQuery( this ).closest( '.frm_form_action_settings' ).data( 'actionkey' ),
			taxKey = jQuery( this ).closest( '.frm_posttax_row' ).attr( 'id' ).replace( 'frm_posttax_', '' ),
			metaName = jQuery( this ).val(),
			showExclude = jQuery( document.getElementById( taxKey + '_show_exclude' ) ).is( ':checked' ) ? 1 : 0,
			fieldId = jQuery( 'select[name$="[post_category][' + taxKey + '][field_id]"]' ).val(),
			id = jQuery( 'input[name="id"]' ).val();

		jQuery.ajax({
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
			success: function( html ) {
				var $tax = jQuery( document.getElementById( 'frm_posttax_' + taxKey ) );
				$tax.replaceWith( html );
			}
		});
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
		triggerSubmit( document.querySelector( '.frm_form_settings' ) );
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
		var formId = jQuery( this ).val();
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
		var logicRows = document.getElementById( 'frm_order_options' ).querySelectorAll( '.frm_logic_rows div' );
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
		var rowDivs = document.getElementById( 'frm_where_options' ).querySelectorAll( '.frm_logic_rows div' );
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
		var value = this.value,
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
		var value = this.value,
			whereKey = jQuery( this ).closest( '.frm_where_row' ).attr( 'id' ).replace( 'frm_where_field_', '' );

		if ( value === 'group_by' || value === 'group_by_newest' ) {
			document.getElementById( 'where_field_options_' + whereKey ).style.display = 'none';
		} else {
			document.getElementById( 'where_field_options_' + whereKey ).style.display = 'inline-block';
		}
	}

	function setDefaultPostStatus() {
		var urlQuery = window.location.search.substring( 1 );
		if ( urlQuery.indexOf( 'action=edit' ) === -1 ) {
			document.getElementById( 'post-visibility-display' ).innerHTML = frm_admin_js.private_label;
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
			elementId = element;
		if ( typeof element === 'object' ) {
			if ( element.hasClass( 'frm_noallow' ) ) {
				return;
			}

			elementId = jQuery( element ).closest( '[data-fills]' ).attr( 'data-fills' );
			if ( typeof elementId === 'undefined' ) {
				elementId = element.closest( 'div' ).attr( 'class' );
				if ( typeof elementId !== 'undefined' ) {
					elementId = elementId.split( ' ' )[1];
				}
			}
		}

		if ( typeof elementId === 'undefined' ) {
			var active = document.activeElement;
			if ( active.type === 'search' ) {
				// If the search field has focus, find the correct field.
				elementId = active.id.replace( '-search-input', '' );
				if ( elementId.match( /\d/gi ) === null ) {
					active = jQuery( '.frm-single-settings:visible .' + elementId );
					elementId = active.attr( 'id' );
				}
			} else {
				elementId = active.id;
			}
		}

		if ( elementId ) {
			rich = jQuery( '#wp-' + elementId + '-wrap.wp-editor-wrap' ).length > 0;
		}

		var contentBox = jQuery( document.getElementById( elementId ) );
		if ( typeof element.attr( 'data-shortcode' ) === 'undefined' && ( ! contentBox.length || typeof contentBox.attr( 'data-shortcode' ) === 'undefined' ) ) {
			// this helps to exclude those that don't want shortcode-like inserted content e.g. frm-pro's summary field
			var doShortcode = element.parents( 'ul.frm_code_list' ).attr( 'data-shortcode' );
			if ( doShortcode === 'undefined' || doShortcode !== 'no' ) {
				variable = '[' + variable + ']';
			}
		}

		if ( rich ) {
			wpActiveEditor = elementId;
		}

		if ( ! contentBox.length ) {
			return false;
		}

		if ( variable === '[default-html]' || variable === '[default-plain]' ) {
			var p = 0;
			if ( variable === '[default-plain]' ) {
				p = 1;
			}
			jQuery.ajax({
				type: 'POST', url: ajaxurl,
				data: {
					action: 'frm_get_default_html',
					form_id: jQuery( 'input[name="id"]' ).val(),
					plain_text: p,
					nonce: frmGlobal.nonce
				},
				elementId: elementId,
				success: function( msg ) {
					if ( rich ) {
						let p = document.createElement( 'p' );
						p.innerText = msg;
						send_to_editor( p.innerHTML );
					} else {
						insertContent( contentBox, msg );
					}
				}
			});
		} else {
			variable = maybeAddSanitizeUrlToShortcodeVariable( variable, element, contentBox );
			if ( rich ) {
				send_to_editor( variable );
			} else {
				insertContent( contentBox, variable );
			}
		}
		return false;
	}

	function maybeAddSanitizeUrlToShortcodeVariable( variable, element, contentBox ) {
		if ( 'object' !== typeof element || ! ( element instanceof jQuery ) || 'success_url' !== contentBox[0].id ) {
			return variable;
		}

		element = element[0];
		if ( ! element.closest( '#frm-insert-fields-box' ) ) {
			// Only add sanitize_url=1 to field shortcodes.
			return variable;
		}

		if ( ! element.parentNode.classList.contains( 'frm_insert_url' ) ) {
			variable = variable.replace( ']', ' sanitize_url=1]' );
		}

		return variable;
	}

	function insertContent( contentBox, variable ) {
		if ( document.selection ) {
			contentBox[0].focus();
			document.selection.createRange().text = variable;
		} else {
			obj = contentBox[0];
			var e = obj.selectionEnd;

			variable = maybeFormatInsertedContent( contentBox, variable, obj.selectionStart, e );

			obj.value = obj.value.substr( 0, obj.selectionStart ) + variable + obj.value.substr( obj.selectionEnd, obj.value.length );
			var s = e + variable.length;
			obj.focus();
			obj.setSelectionRange( s, s );
		}
		triggerChange( contentBox );
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

		var startPattern = new RegExp( separator + '\\s*$' );
		var endPattern = new RegExp( '^\\s*' + separator );

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

	function showBuilderModal() {
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
			box.style.left = ( pos.left - parentPos.left - 280 ) + 'px';

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
				if ( 'none' !== input.style.display ) {
					input.focus();
				} else {
					jQuery( tinymce.get( input.id ) ).trigger( 'focus' );
				}
			}
		}
	}

	function fieldUpdated() {
		if ( ! fieldsUpdated ) {
			fieldsUpdated = 1;
			window.addEventListener( 'beforeunload', confirmExit );
		}
	}

	function buildSubmittedNoAjax() {
		// set fieldsUpdated to 0 to avoid the unsaved changes pop up
		fieldsUpdated = 0;
	}

	function settingsSubmitted() {
		// set fieldsUpdated to 0 to avoid the unsaved changes pop up
		fieldsUpdated = 0;
	}

	function saveAndReloadSettings() {
		var page, form;
		page = document.getElementById( 'form_settings_page' );
		if ( null !== page ) {
			form = page.querySelector( 'form.frm_form_settings' );
			if ( null !== form ) {
				fieldsUpdated = 0;
				form.submit();
			}
		}
	}

	function saveAndReloadFormBuilder() {
		document.getElementById( 'frm_submit_side_top' ).click();
	}

	function confirmExit( event ) {
		if ( fieldsUpdated ) {
			event.preventDefault();
			event.returnValue = '';
		}
	}

	function bindClickForDialogClose( $modal ) {
		const closeModal = function() {
			$modal.dialog( 'close' );
		};
		jQuery( '.ui-widget-overlay' ).on( 'click', closeModal );
		$modal.on( 'click', 'a.dismiss', closeModal );
	}

	function triggerNewFormModal( event ) {
		var $modal,
			dismiss = document.getElementById( 'frm_new_form_modal' ).querySelector( 'a.dismiss' );

		if ( typeof event !== 'undefined' ) {
			event.preventDefault();
		}

		dismiss.setAttribute( 'tabindex', -1 );

		$modal = initModal( '#frm_new_form_modal', '600px' );
		offsetModalY( $modal, '50px' );
		$modal.attr( 'frm-page', 'create' );
		$modal.find( '#template-search-input' ).val( '' ).trigger( 'change' );
		$modal.dialog( 'open' );

		dismiss.removeAttribute( 'tabindex' );
		bindClickForDialogClose( $modal );

		addApplicationsToNewFormModal( $modal.get( 0 ) );
	}

	function addApplicationsToNewFormModal( modal ) {
		if ( modal.querySelector( '.frm-ready-made-solution' ) ) {
			// Avoid adding duplicates if the modal is opened multiple times.
			return;
		}

		if ( ! frmGlobal.canAccessApplicationDashboard ) {
			// User does not have privileges to see Ready Made Solutions.
			return;
		}

		doJsonFetch( 'get_applications_data&view=templates' ).then( addTemplatesOnFetchSuccess );

		const categoryList = modal.querySelector( 'ul.frm-categories-list' );

		function addTemplatesOnFetchSuccess( data ) {
			data.templates.forEach( addTemplateToCategoryList );
			initSearch( 'template-search-input', 'frm-searchable-template frm-ready-made-solution' );
		}

		function addTemplateToCategoryList( template ) {
			categoryList.appendChild( getReadyMadeSolution( template ) );
		}

		function getReadyMadeSolution( template ) {
			const image = tag( 'img' );
			const thumbnailFolderUrl = frmGlobal.url + '/images/applications/thumbnails/';
			const filenameToUse = template.hasLiteThumbnail ? template.key + '.png' : 'placeholder.svg';
			image.setAttribute( 'src', thumbnailFolderUrl + filenameToUse );

			const imageWrapper = div({ child: image });
			const href = frmGlobal.applicationsUrl + '&triggerViewApplicationModal=1&template=' + template.key;

			return tag(
				'li',
				{
					className: 'frm-searchable-template frm-ready-made-solution frm-selectable',
					data: {
						href: href
					},
					children: [
						imageWrapper,
						div({
							className: 'frm-solution-details',
							children: [
								span({
									className: 'frm-meta-tag',
									text: __( 'Ready Made Solution', 'formidable' )
								}),
								tag( 'h3', template.name ),
								a({
									text: __( 'See all applications', 'formidable' ),
									href: frmGlobal.applicationsUrl
								})
							]
						}),
						div({
							className: 'frm-hover-icons',
							child: a({
								child: svg({ href: '#frm_plus_icon' }),
								href: href
							})
						})
					]
				}
			);
		}
	}

	function offsetModalY( $modal, amount ) {
		const position = {
			my: 'top',
			at: 'top+' + amount,
			of: window
		};
		$modal.dialog( 'option', 'position', position );
	}

	/**
	 * Get the input box for the selected ... icon.
	 */
	function getInputForIcon( moreIcon ) {
		var input = moreIcon.nextElementSibling;

		while ( input !== null && input.tagName !== 'INPUT' && input.tagName !== 'TEXTAREA' ) {
			input = getInputForIcon( input );
		}

		return input;
	}

	/**
	 * Get the ... icon for the selected input box.
	 */
	function getIconForInput( input ) {
		var moreIcon = input.previousElementSibling;

		while ( moreIcon !== null && moreIcon.tagName !== 'I' && moreIcon.tagName !== 'svg' ) {
			moreIcon = getIconForInput( moreIcon );
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
		if ( typeof DOM.events !== 'undefined' && typeof DOM.events.add !== 'undefined' ) {
			DOM.events.add( DOM.select( '.wp-editor-wrap' ), 'mouseover', function() {
				if ( jQuery( '*:focus' ).length > 0 ) {
					return;
				}
				if ( this.id ) {
					toggleAllowedShortcodes( this.id.slice( 3, -5 ), 'focusin' );
				}
			});
			DOM.events.add( DOM.select( '.wp-editor-wrap' ), 'mouseout', function() {
				if ( jQuery( '*:focus' ).length > 0 ) {
					return;
				}
				if ( this.id ) {
					toggleAllowedShortcodes( this.id.slice( 3, -5 ), 'focusin' );
				}
			});
		} else {
			jQuery( '#frm_dyncontent' ).on( 'mouseover mouseout', '.wp-editor-wrap', function() {
				if ( jQuery( '*:focus' ).length > 0 ) {
					return;
				}
				if ( this.id ) {
					toggleAllowedShortcodes( this.id.slice( 3, -5 ), 'focusin' );
				}
			});
		}
	}

	function toggleAllowedShortcodes( id ) {
		var c, clickedID;
		if ( typeof id === 'undefined' ) {
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
					id = d.trim();
				}
				c = c + ' ' + d;
				c = c.replace( 'widefat', '' ).replace( 'frm_with_left_label', '' );
			}
		}

		jQuery( '#frm-insert-fields-box,#frm-conditionals,#frm-adv-info-tab,#frm-dynamic-values' ).attr( 'data-fills', c.trim() );
		var a = [
			'content', 'wpbody-content', 'dyncontent', 'success_url',
			'success_msg', 'edit_msg', 'frm_dyncontent', 'frm_not_email_message',
			'frm_not_email_subject'
		];
		var b = [
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

	function toggleAllowedHTML( input ) {
		var b,
			id = input.id;
		if ( typeof id === 'undefined' || id.indexOf( '-search-input' ) !== -1 ) {
			return;
		}

		jQuery( '#frm-adv-info-tab' ).attr( 'data-fills', id.trim() );
		if ( input.classList.contains( 'field_custom_html' ) ) {
			id = 'field_custom_html';
		}

		b = [ 'after_html', 'before_html', 'submit_html', 'field_custom_html' ];
		if ( jQuery.inArray( id, b ) >= 0 ) {
			jQuery( '.frm_code_list li:not(.show_' + id + ')' ).addClass( 'frm_hidden' );
			jQuery( '.frm_code_list li.show_' + id ).removeClass( 'frm_hidden' );
		}
	}

	function toggleKeyID( switchTo, e ) {
		e.stopPropagation();
		jQuery( '.frm_code_list .frmids, .frm_code_list .frmkeys' ).addClass( 'frm_hidden' );
		jQuery( '.frm_code_list .' + switchTo ).removeClass( 'frm_hidden' );
		jQuery( '.frmids, .frmkeys' ).removeClass( 'current' );
		jQuery( '.' + switchTo ).addClass( 'current' );
	}

	function onActionLoaded( event ) {
		const settings = event.target.closest( '.frm_form_action_settings' );
		if ( settings && ( settings.classList.contains( 'frm_single_email_settings' ) || settings.classList.contains( 'frm_single_on_submit_settings' ) ) ) {
			initWysiwygOnActionLoaded( settings );
		}
	}

	function initWysiwygOnActionLoaded( settings ) {
		const wysiwyg = settings.querySelector( '.wp-editor-area' );
		if ( wysiwyg ) {
			frmDom.wysiwyg.init(
				wysiwyg,
				{ height: 160, addFocusEvents: true }
			);
		}
	}

	/* Global settings page */
	function loadSettingsTab( anchor ) {
		var holder = anchor.replace( '#', '' );
		var holderContainer = jQuery( '.frm_' + holder + '_ajax' );
		if ( holderContainer.length ) {
			jQuery.ajax({
				type: 'POST', url: ajaxurl,
				data: {
					'action': 'frm_settings_tab',
					'tab': holder.replace( '_settings', '' ),
					'nonce': frmGlobal.nonce
				},
				success: function( html ) {
					holderContainer.replaceWith( html );
				}
			});
		}
	}

	function uninstallNow() {
		/*jshint validthis:true */
		if ( confirmLinkClick( this ) === true ) {
			jQuery( '.frm_uninstall .frm-wait' ).css( 'visibility', 'visible' );
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: 'action=frm_uninstall&nonce=' + frmGlobal.nonce,
				success: function( msg ) {
					jQuery( '.frm_uninstall' ).fadeOut( 'slow' );
					window.location = msg;
				}
			});
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
		jQuery.ajax({
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
		});
	}

	/* Import/Export page */

	function startFormMigration( event ) {
		event.preventDefault();

		var checkedBoxes = jQuery( event.target ).find( 'input:checked' );
		if ( ! checkedBoxes.length ) {
			return;
		}

		var ids = [];
		checkedBoxes.each( function( i ) {
			ids[i] = this.value;
		});

		// Begin the import process.
		importForms( ids, event.target );
	}

	/**
	 * Begins the process of importing the forms.
	 */
	function importForms( forms, targetForm ) {

		// Hide the form select section.
		var $form = jQuery( targetForm ),
			$processSettings = $form.next( '.frm-importer-process' );

		// Display total number of forms we have to import.
		$processSettings.find( '.form-total' ).text( forms.length );
		$processSettings.find( '.form-current' ).text( '1' );

		$form.hide();

		// Show processing status.
		// '.process-completed' might have been shown earlier during a previous import, so hide now.
		$processSettings.find( '.process-completed' ).hide();
		$processSettings.show();

		// Create global import queue.
		s.importQueue = forms;
		s.imported = 0;

		// Import the first form in the queue.
		importForm( $processSettings );
	}

	/**
	 * Imports a single form from the import queue.
	 */
	function importForm( $processSettings ) {
		var formID = s.importQueue[0],
			provider = jQuery( '#welcome-panel' ).find( 'input[name="slug"]' ).val(),
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
				});
				s.imported++;

				if ( s.importQueue.length === 0 ) {
					$processSettings.find( '.process-count' ).hide();
					$processSettings.find( '.forms-completed' ).text( s.imported );
					$processSettings.find( '.process-completed' ).show();
				} else {
					// Import next form in the queue.
					$processSettings.find( '.form-current' ).text( s.imported + 1 );
					importForm( $processSettings );
				}
			}
		});
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
		if ( typeof t === 'undefined' ) {
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

	function getExportOption() {
		const exportFormatSelect = document.querySelector( 'select[name="format"]' );
		if ( exportFormatSelect ) {
			return exportFormatSelect.value;
		} else {
			return '';
		}
	}

	function exportTypeChanged( event ) {
		showOrHideRepeaters( event.target.value );
		checkExportTypes.call( event.target );
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
				if ( multiple === -1 ) {
					this.checked = true;
				}
			} else {
				this.disabled = true;
			}
		});

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
			exportField.prop( 'checked', false );
		} else {
			exportField.prop( 'multiple', true );
			exportField.prop( 'disabled', false );
		}
		$dropdown.trigger( 'change' );
	}

	function showOrHideRepeaters( exportOption ) {
		if ( exportOption === '' ) {
			return;
		}

		const repeaters = document.querySelectorAll( '.frm-is-repeater' );
		if ( ! repeaters.length ) {
			return;
		}

		if ( exportOption === 'csv' ) {
			repeaters.forEach( form => {
				form.classList.remove( 'frm_hidden' );
			});
		} else {
			repeaters.forEach( form => {
				form.classList.add( 'frm_hidden' );
			});
		}

		searchContent.call( document.querySelector( '.frm-auto-search' ) );
	}

	function preventMultipleExport() {
		var type = jQuery( 'select[name=format]' ),
			selected = type.find( ':selected' ),
			count = selected.data( 'count' ),
			exportField = jQuery( 'input[name="frm_export_forms[]"]' );

		if ( count === 'single' ) {
			// Disable all other fields to prevent multiple selections.
			if ( this.checked ) {
				exportField.prop( 'disabled', true );
				this.removeAttribute( 'disabled' );
			} else {
				exportField.prop( 'disabled', false );
			}
		} else {
			exportField.prop( 'disabled', false );
		}
	}

	function initiateMultiselect() {
		jQuery( '.frm_multiselect' ).hide().each( frmDom.bootstrap.multiselect.init );
	}

	/* Addons page */
	function installMultipleAddons( e ) {
		e.preventDefault();
		installOrActivate( this, 'frm_multiple_addons' );
	}

	function activateAddon( e ) {
		e.preventDefault();
		installOrActivate( this, 'frm_activate_addon' );
	}

	function installAddon( e ) {
		e.preventDefault();
		installOrActivate( this, 'frm_install_addon' );
	}

	function installOrActivate( clicked, action ) {
		let button, plugin, el, message;

		// Remove any leftover error messages, output an icon and get the plugin basename that needs to be activated.
		jQuery( '.frm-addon-error' ).remove();
		button  = jQuery( clicked );
		plugin  = button.attr( 'rel' );
		el      = button.parent();
		message = el.parent().find( '.addon-status-label' );

		button.addClass( 'frm_loading_button' );

		// Process the Ajax to perform the activation.
		jQuery.ajax({
			url: ajaxurl,
			type: 'POST',
			async: true,
			cache: false,
			dataType: 'json',
			data: {
				action: action,
				nonce: frmGlobal.nonce,
				plugin: plugin
			},
			success: function( response ) {
				let saveAndReload;

				if ( 'string' !== typeof response && 'string' === typeof response.message ) {
					if ( 'undefined' !== typeof response.saveAndReload ) {
						saveAndReload = response.saveAndReload;
					}
					response = response.message;
				}

				const error = extractErrorFromAddOnResponse( response );
				if ( error ) {
					addonError( error, el, button );
					return;
				}

				afterAddonInstall( response, button, message, el, saveAndReload );
			},
			error: function() {
				button.removeClass( 'frm_loading_button' );
			}
		});
	}

	function installAddonWithCreds( e ) {
		// Prevent the default action, let the user know we are attempting to install again and go with it.
		e.preventDefault();

		// Now let's make another Ajax request once the user has submitted their credentials.
		const proceed = jQuery( this );
		const el      = proceed.parent().parent();
		const plugin  = proceed.attr( 'rel' );

		proceed.addClass( 'frm_loading_button' );

		jQuery.ajax({
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
				const error = extractErrorFromAddOnResponse( response );
				if ( error ) {
					addonError( error, el, proceed );
					return;
				}

				afterAddonInstall( response, proceed, message, el );
			},
			error: function() {
				proceed.removeClass( 'frm_loading_button' );
			}
		});
	}

	function afterAddonInstall( response, button, message, el, saveAndReload ) {
		const addonStatuses = document.querySelectorAll( '.frm-addon-status' );
		addonStatuses.forEach(
			addonStatus => {
				addonStatus.textContent   = response;
				addonStatus.style.display = 'block';
			}
		);

		// The Ajax request was successful, so let's update the output.
		button.css({ opacity: '0' });
		message.text( frm_admin_js.active );

		document.querySelectorAll( '.frm-oneclick' ).forEach(
			oneClick => {
				oneClick.style.display = 'none';
			}
		);

		jQuery( '#frm_upgrade_modal h2' ).hide();
		jQuery( '#frm_upgrade_modal .frm_lock_icon' ).addClass( 'frm_lock_open_icon' );
		jQuery( '#frm_upgrade_modal .frm_lock_icon use' ).attr( 'xlink:href', '#frm_lock_open_icon' );

		// Proceed with CSS changes
		el.parent().removeClass( 'frm-addon-not-installed frm-addon-installed' ).addClass( 'frm-addon-active' );
		button.removeClass( 'frm_loading_button' );

		// Maybe refresh import and SMTP pages
		const refreshPage = document.querySelectorAll( '.frm-admin-page-import, #frm-admin-smtp, #frm-welcome' );
		if ( refreshPage.length > 0 ) {
			window.location.reload();
			return;
		}

		if ([ 'settings', 'form_builder' ].includes( saveAndReload ) ) {
			addonStatuses.forEach(
				addonStatus => {
					const inModal = null !== addonStatus.closest( '#frm_upgrade_modal' );
					addonStatus.appendChild( getSaveAndReloadSettingsOptions( saveAndReload, inModal ) );
				}
			);
		}
	}

	function getSaveAndReloadSettingsOptions( saveAndReload, inModal ) {
		const className = 'frm-save-and-reload-options';
		const children  = [ saveAndReloadSettingsButton( saveAndReload ) ];
		if ( inModal ) {
			children.push( closePopupButton() );
		}
		return div({ className, children });
	}

	function saveAndReloadSettingsButton( saveAndReload ) {
		var button = document.createElement( 'button' );
		button.classList.add( 'frm-save-and-reload', 'button', 'button-primary', 'frm-button-primary' );
		button.textContent = __( 'Save and Reload', 'formidable' );
		button.addEventListener( 'click', () => {
			if ( saveAndReload === 'form_builder' ) {
				saveAndReloadFormBuilder();
			} else if ( saveAndReload === 'settings' ) {
				saveAndReloadSettings();
			}
		});
		return button;
	}

	function closePopupButton() {
		var a = document.createElement( 'a' );
		a.setAttribute( 'href', '#' );
		a.classList.add( 'button', 'button-secondary', 'frm-button-secondary', 'dismiss' );
		a.textContent = __( 'Close', 'formidable' );
		return a;
	}

	function extractErrorFromAddOnResponse( response ) {
		if ( typeof response !== 'string' ) {
			if ( typeof response.success !== 'undefined' && response.success ) {
				return false;
			}

			if ( response.form ) {
				if ( jQuery( response.form ).is( '#message' ) ) {
					return {
						message: jQuery( response.form ).find( 'p' ).html()
					};
				}
			}

			return response;
		}

		return false;
	}

	function addonError( response, el, button ) {
		if ( response.form ) {
			jQuery( '.frm-inline-error' ).remove();
			button.closest( '.frm-card' )
				.html( response.form )
				.css({ padding: 5 })
				.find( '#upgrade' )
					.attr( 'rel', button.attr( 'rel' ) )
					.on( 'click', installAddonWithCreds );
		} else {
			el.append( '<div class="frm-addon-error frm_error_style"><p><strong>' + response.message + '</strong></p></div>' );
			button.removeClass( 'frm_loading_button' );
			jQuery( '.frm-addon-error' ).delay( 4000 ).fadeOut();
		}
	}

	/* Templates */

	function initNewFormModal() {
		var installFormTrigger,
			activeHoverIcons,
			$modal,
			handleError,
			handleEmailAddressError,
			handleConfirmEmailAddressError,
			showFreeTemplatesForm,
			firstLockedTemplate,
			isShowFreeTemplatesFormFirst,
			url,
			urlParams;

		url       = new URL( window.location.href );
		urlParams = url.searchParams;

		isShowFreeTemplatesFormFirst = urlParams.get( 'free-templates' );

		jQuery( document ).on( 'click', '.frm-trigger-new-form-modal', triggerNewFormModal );
		$modal = initModal( '#frm_new_form_modal', '600px' );

		if ( false === $modal ) {
			return;
		}

		setTimeout(
			function() {
				$modal.get( 0 ).querySelector( '.postbox' ).style.display = 'block'; // Fixes pro issue #3508, prevent a conflict that hides the postbox in modal.
			},
			0
		);

		installFormTrigger = document.createElement( 'a' );
		installFormTrigger.classList.add( 'frm-install-template', 'frm_hidden' );
		document.body.appendChild( installFormTrigger );

		jQuery( '.frm-install-template' ).on( 'click', function( event ) {
			var $h3Clone = jQuery( this ).closest( 'li, td' ).find( 'h3' ).clone(),
				nameLabel = document.getElementById( 'frm_new_name' ),
				descLabel = document.getElementById( 'frm_new_desc' ),
				oldName;

			$h3Clone.find( 'svg, .frm-plan-required-tag' ).remove();
			oldName = $h3Clone.html().trim();

			event.preventDefault();

			document.getElementById( 'frm_template_name' ).value = oldName;
			document.getElementById( 'frm_link' ).value = this.attributes.rel.value;
			document.getElementById( 'frm_action_type' ).value = 'frm_install_template';
			nameLabel.innerHTML = nameLabel.getAttribute( 'data-form' );
			descLabel.innerHTML = descLabel.getAttribute( 'data-form' );
			$modal.dialog( 'open' );
		});

		jQuery( document ).on( 'submit', '#frm-new-template', installTemplate );

		jQuery( document ).on( 'click', '.frm-hover-icons .frm-preview-form', function( event ) {
			var $li, link, iframe,
				container = document.getElementById( 'frm-preview-block' );

			event.preventDefault();

			$li = jQuery( this ).closest( 'li' );
			link = $li.attr( 'data-preview' );

			if ( link.indexOf( ajaxurl ) > -1 ) {
				iframe = document.createElement( 'iframe' );
				iframe.src = link;
				iframe.height = '400';
				iframe.width = '100%';
				container.innerHTML = '';
				container.appendChild( iframe );
			} else {
				frmApiPreview( container, link );
			}

			jQuery( '#frm-preview-title' ).text( getStrippedTemplateName( $li ) );
			$modal.attr( 'frm-page', 'preview' );
			activeHoverIcons = jQuery( this ).closest( '.frm-hover-icons' );
		});

		jQuery( document ).on( 'click', 'li.frm-ready-made-solution[data-href]', function() {
			window.location = this.getAttribute( 'data-href' );
		});

		jQuery( document ).on( 'click', 'li .frm-hover-icons .frm-create-form', function( event ) {
			var $li, name, link, action;

			event.preventDefault();

			$li = jQuery( this ).closest( 'li' );

			if ( $li.is( '[data-href]' ) ) {
				window.location = $li.attr( 'data-href' );
				return;
			}

			if ( $li.hasClass( 'frm-add-blank-form' ) ) {
				name = link = '';
				action = 'frm_install_form';
			} else if ( $li.is( '[data-rel]' ) ) {
				name = getStrippedTemplateName( $li );
				link = $li.attr( 'data-rel' );
				action = 'frm_install_template';
			} else {
				return;
			}

			transitionToAddDetails( $modal, name, link, action );
		});

		// Welcome page modals.
		jQuery( document ).on( 'click', '.frm-create-blank-form', function( event ) {
			event.preventDefault();
			jQuery( '.frm-trigger-new-form-modal' ).trigger( 'click' );
			transitionToAddDetails( $modal, '', '', 'frm_install_form' );

			// Close the modal with the cancel button.
			jQuery( '.frm-modal-cancel.frm-back-to-all-templates' ).on( 'click', function() {
				jQuery( '.ui-widget-overlay' ).trigger( 'click' );
			});
		});

		jQuery( document ).on( 'click', '.frm-featured-forms.frm-templates-list li [role="button"]:not(a), .frm-templates-list .accordion-section.open li [role="button"]:not(a)', function( event ) {
			var $hoverIcons, $trigger,
				$li = jQuery( this ).closest( 'li' ),
				triggerClass = $li.hasClass( 'frm-locked-template' ) ? 'frm-unlock-form' : 'frm-create-form';

			$hoverIcons = $li.find( '.frm-hover-icons' );
			if ( ! $hoverIcons.length ) {
				$li.trigger( 'mouseover' );
				$hoverIcons = $li.find( '.frm-hover-icons' );
				$hoverIcons.hide();
			}

			$trigger = $hoverIcons.find( '.' + triggerClass );
			$trigger.trigger( 'click' );
		});

		jQuery( document ).on( 'click', 'li .frm-hover-icons .frm-delete-form', function( event ) {
			var $li,
				trigger;

			event.preventDefault();

			$li = jQuery( this ).closest( 'li' );
			$li.addClass( 'frm-deleting' );
			trigger = document.createElement( 'a' );
			trigger.setAttribute( 'href', '#' );
			trigger.setAttribute( 'data-id', $li.attr( 'data-formid' ) );
			$li.attr( 'id', 'frm-template-custom-' + $li.attr( 'data-formid' ) );
			jQuery( trigger ).on( 'click', trashTemplate );
			trigger.click();
			setTemplateCount( $li.closest( '.accordion-section' ).get( 0 ) );
		});

		showFreeTemplatesForm = function( $el ) {
			var formContainer = document.getElementById( 'frmapi-email-form' );
			jQuery.ajax({
				dataType: 'json',
				url: formContainer.getAttribute( 'data-url' ),
				success: function( json ) {
					var form = json.renderedHtml;
					form = form.replace( /<link\b[^>]*(formidableforms.css|action=frmpro_css)[^>]*>/gi, '' );
					formContainer.innerHTML = form;
				}
			});

			$modal.attr( 'frm-page', 'email' );
			$modal.attr( 'frm-this-form', $el.attr( 'data-key' ) );
			$el.append( installFormTrigger );
		};

		jQuery( document ).on( 'click', 'li.frm-locked-template .frm-hover-icons .frm-unlock-form', function( event ) {
			var $li,
				activePage;

			event.preventDefault();

			$li = jQuery( this ).closest( '.frm-locked-template' );

			if ( $li.hasClass( 'frm-free-template' ) ) {
				showFreeTemplatesForm( $li );
				return;
			}

			if ( $modal.hasClass( 'frm-expired' ) ) {
				activePage = 'renew';
			} else {
				activePage = 'upgrade';
			}

			$modal.attr( 'frm-page', activePage );
		});

		jQuery( document ).on( 'click', '#frm_new_form_modal #frm-template-drop', function() {
			jQuery( this )
				.closest( '.accordion-section-content' ).css( 'overflow', 'visible' )
				.closest( '.accordion-section' ).css( 'z-index', 1 );
		});

		jQuery( document ).on( 'click', '#frm_new_form_modal #frm-template-drop + .frm-dropdown-menu .frm-build-template', function() {
			var name = this.getAttribute( 'data-fullname' ),
				link = this.getAttribute( 'data-formid' ),
				action = 'frm_build_template';
			transitionToAddDetails( $modal, name, link, action );
		});

		handleError = function( inputId, errorId, type, message ) {
			var $error = jQuery( errorId );
			$error.removeClass( 'frm_hidden' ).attr( 'frm-error', type );

			if ( typeof message !== 'undefined' ) {
				$error.find( 'span[frm-error="' + type + '"]' ).text( message );
			}

			jQuery( inputId ).one( 'keyup', function() {
				$error.addClass( 'frm_hidden' );
			});
		};

		handleEmailAddressError = function( type ) {
			handleError( '#frm_leave_email', '#frm_leave_email_error', type );
		};

		jQuery( document ).on( 'click', '#frm-add-my-email-address', function( event ) {
			var email = document.getElementById( 'frm_leave_email' ).value.trim(),
				regex,
				$hiddenForm,
				$hiddenEmailField;

			event.preventDefault();

			if ( '' === email ) {
				handleEmailAddressError( 'empty' );
				return;
			}

			regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/i;

			if ( regex.test( email ) === false ) {
				handleEmailAddressError( 'invalid' );
				return;
			}

			$hiddenForm = jQuery( '#frmapi-email-form' ).find( 'form' );
			$hiddenEmailField = $hiddenForm.find( '[type="email"]' ).not( '.frm_verify' );
			if ( ! $hiddenEmailField.length ) {
				return;
			}

			$hiddenEmailField.val( email );
			jQuery.ajax({
				type: 'POST',
				url: $hiddenForm.attr( 'action' ),
				data: $hiddenForm.serialize() + '&action=frm_forms_preview'
			}).done( function( data ) {
				var message = jQuery( data ).find( '.frm_message' ).text().trim();
				if ( message.indexOf( 'Thanks!' ) >= 0 ) {
					$modal.attr( 'frm-page', 'code' );
				} else {
					handleEmailAddressError( 'invalid' );
				}
			});
		});

		handleConfirmEmailAddressError = function( type, message ) {
			handleError( '#frm_code_from_email', '#frm_code_from_email_error', type, message );
		};

		jQuery( document ).on( 'click', '.frm-confirm-email-address', function( event ) {
			var code = document.getElementById( 'frm_code_from_email' ).value.trim();

			event.preventDefault();

			if ( '' === code ) {
				handleConfirmEmailAddressError( 'empty' );
				return;
			}

			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				dataType: 'json',
				data: {
					action: 'template_api_signup',
					nonce: frmGlobal.nonce,
					code: code,
					key: $modal.attr( 'frm-this-form' )
				},
				success: function( response ) {
					if ( response.success ) {
						if ( isShowFreeTemplatesFormFirst ) {
							// Remove free-templates param from URL then reload page.
							urlParams.delete( 'free-templates' );
							url.search = urlParams.toString();
							window.location.href = url.toString();

							return;
						}

						if ( typeof response.data !== 'undefined' && typeof response.data.url !== 'undefined' ) {
							installFormTrigger.setAttribute( 'rel', response.data.url );
							installFormTrigger.click();
							$modal.attr( 'frm-page', 'details' );

							const hookName = 'frm_new_form_modal_form';
							wp.hooks.doAction( hookName, $modal );

							document.getElementById( 'frm_action_type' ).value = 'frm_install_template';

							if ( typeof response.data.urlByKey !== 'undefined' ) {
								updateTemplateModalFreeUrls( response.data.urlByKey );
							}
						}
					} else {
						if ( Array.isArray( response.data ) && response.data.length ) {
							handleConfirmEmailAddressError( 'custom', response.data[0].message );
						} else {
							handleConfirmEmailAddressError( 'wrong-code' );
						}

						jQuery( '#frm_code_from_email_options' ).removeClass( 'frm_hidden' );
					}
				}
			});
		});

		jQuery( document ).on( 'click', '#frm-change-email-address', function() {
			$modal.attr( 'frm-page', 'email' );
		});

		jQuery( document ).on( 'click', '#frm-resend-code', function() {
			document.getElementById( 'frm_code_from_email' ).value = '';
			jQuery( '#frm_code_from_email_options, #frm_code_from_email_error' ).addClass( 'frm_hidden' );
			document.getElementById( 'frm-add-my-email-address' ).click();
		});

		jQuery( document ).on( 'click', '#frm_new_form_modal .frm-modal-back, #frm_new_form_modal .frm_modal_footer .frm-modal-cancel, #frm_new_form_modal .frm-back-to-all-templates', function( event ) {
			document.getElementById( 'frm-create-title' ).removeAttribute( 'frm-type' );
			$modal.attr( 'frm-page', 'create' );
		});

		jQuery( document ).on( 'click', '.frm-use-this-template', function( event ) {
			var $trigger;

			event.preventDefault();

			$trigger = activeHoverIcons.find( '.frm-create-form' );
			if ( $trigger.closest( '.frm-selectable' ).hasClass( 'frm-locked-template' ) ) {
				$trigger = activeHoverIcons.find( '.frm-unlock-form' );
			}

			$trigger.trigger( 'click' );
		});

		jQuery( document ).on( 'click', '.frm-submit-new-template', function( event ) {
			var button;
			event.preventDefault();
			button = document.getElementById( 'frm-new-template' ).querySelector( 'button' );
			if ( null !== button ) {
				button.click();
			}
		});

		if ( urlParams.get( 'triggerNewFormModal' ) ) {
			triggerNewFormModal();

			if ( isShowFreeTemplatesFormFirst ) {
				firstLockedTemplate = jQuery( 'li.frm-locked-template.frm-free-template' ).eq( 0 );

				if ( firstLockedTemplate.length ) {
					showFreeTemplatesForm( firstLockedTemplate );
				}
			}
		}

		initSearch( 'template-search-input', 'control-section accordion-section' );
	}

	function initSearch( inputID, itemClass ) {
		const searchInput = document.getElementById( inputID );

		if ( itemClass === 'control-section accordion-section' ) {
			itemClass = 'frm-selectable frm-searchable-template';

			const handleTemplateSearch = () => {
				document.querySelectorAll( '.control-section.accordion-section' ).forEach( category => {
					const found = category.querySelector( '.frm-selectable.frm-searchable-template:not(.frm_hidden)' ) || ( searchInput.value === '' && category.querySelector( '#frm-template-drop' ) );
					if ( found ) {
						setTemplateCount( category );
					}
					category.classList.toggle( 'frm_hidden', ! found );
				});
			};

			frmDom.search.init( searchInput, itemClass, { handleSearchResult: handleTemplateSearch });

		} else {
			if ( itemClass === 'frm-searchable-template frm-ready-made-solution' ) {
				Array.from( document.getElementsByClassName( itemClass ) ).forEach( item => {
					let innerText = '';
					innerText = item.querySelector( 'h3' ).innerText;
					item.setAttribute( 'frm-search-text', innerText.toLowerCase() );
				});
			}
			frmDom.search.init( searchInput, itemClass );
		}

	}

	function updateTemplateModalFreeUrls( urlByKey ) {
		jQuery( '#frm_new_form_modal' ).find( '.frm-selectable[data-key]' ).each( function() {
			var $template = jQuery( this ),
				key = $template.attr( 'data-key' );
			if ( 'undefined' !== typeof urlByKey[ key ]) {
				$template.removeClass( 'frm-locked-template' );
				$template.find( 'h3 svg' ).remove(); // remove the lock from the title
				$template.attr( 'data-rel', urlByKey[ key ]);
			}
		});
	}

	function transitionToAddDetails( $modal, name, link, action ) {
		var nameLabel = document.getElementById( 'frm_new_name' ),
			descLabel = document.getElementById( 'frm_new_desc' ),
			type = [ 'frm_install_template', 'frm_install_form' ].indexOf( action ) >= 0 ? 'form' : 'template',
			templateNameInput = document.getElementById( 'frm_template_name' );

		templateNameInput.value = name;
		document.getElementById( 'frm_link' ).value = link;
		document.getElementById( 'frm_action_type' ).value = action;
		nameLabel.textContent = nameLabel.getAttribute( 'data-' + type );
		if ( descLabel !== null ) {
			descLabel.textContent = descLabel.getAttribute( 'data-' + type );
		}

		document.getElementById( 'frm-create-title' ).setAttribute( 'frm-type', type );

		$modal.attr( 'frm-page', 'details' );

		const hookName = 'frm_new_form_modal_form';
		wp.hooks.doAction( hookName, $modal );

		if ( '' === name ) {
			templateNameInput.focus();
		}
	}

	function getStrippedTemplateName( $li ) {
		var $clone = $li.find( 'h3' ).clone();
		$clone.find( 'svg, .frm-plan-required-tag, .frm-new-pill' ).remove();
		return $clone.html().trim();
	}

	function setTemplateCount( category, searchableTemplates ) {
		var count,
			templateIndex,
			availableCounter,
			availableCount;

		if ( typeof searchableTemplates === 'undefined' ) {
			searchableTemplates = category.querySelectorAll( '.frm-searchable-template:not(.frm_hidden):not(.frm-deleting)' );
		}

		count = searchableTemplates.length;
		category.querySelector( '.frm-template-count' ).textContent = count;

		jQuery( category ).find( '.frm-templates-plural' ).toggleClass( 'frm_hidden', count === 1 );
		jQuery( category ).find( '.frm-templates-singular' ).toggleClass( 'frm_hidden', count !== 1 );

		availableCounter = category.querySelector( '.frm-available-templates-count' );
		if ( availableCounter !== null ) {
			availableCount = 0;
			for ( templateIndex in searchableTemplates ) {
				if ( ! isNaN( templateIndex ) && ! searchableTemplates[ templateIndex ].classList.contains( 'frm-locked-template' ) ) {
					availableCount++;
				}
			}

			availableCounter.textContent = availableCount;
		}
	}

	function initSelectionAutocomplete() {
		frmDom.autocomplete.initSelectionAutocomplete();
	}

	function nextInstallStep( thisStep ) {
		thisStep.classList.add( 'frm_grey' );
		thisStep.nextElementSibling.classList.remove( 'frm_grey' );
	}

	function frmApiPreview( cont, link ) {
		cont.innerHTML = '<div class="frm-wait"></div>';
		jQuery.ajax({
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
				setTimeout( function() {
					document.getElementById( 'frm-remove-me' ).style.display = 'none';
					document.getElementById( 'frm-show-me' ).style.opacity = '1';
				}, 300 );
			}
		});
	}

	function installTemplateFieldset( e ) {
		/*jshint validthis:true */
		var fieldset = this.parentNode.parentNode,
			action = fieldset.elements.type.value,
			button = this;
		e.preventDefault();
		button.classList.add( 'frm_loading_button' );
		installNewForm( fieldset, action, button );
	}

	function installTemplate( e ) {
		/*jshint validthis:true */
		var action = this.elements.type.value,
			button = this.querySelector( 'button' );
		e.preventDefault();
		button.classList.add( 'frm_loading_button' );
		installNewForm( this, action, button );
	}

	function installNewForm( form, action, button ) {
		const formData = formToData( form );
		const formName = formData.template_name;
		const formDesc = formData.template_desc;
		const link = form.elements.link.value;

		let data = {
			action: action,
			xml: link,
			name: formName,
			desc: formDesc,
			form: JSON.stringify( formData ),
			nonce: frmGlobal.nonce
		};

		const hookName = 'frm_before_install_new_form';
		const filterArgs = { formData };
		data = wp.hooks.applyFilters( hookName, data, filterArgs );

		postAjax( data, function( response ) {
			if ( typeof response.redirect !== 'undefined' ) {
				const redirect = response.redirect;
				if ( typeof form.elements.redirect === 'undefined' ) {
					window.location = redirect;
				} else {
					const href = document.getElementById( 'frm-redirect-link' );
					if ( typeof link !== 'undefined' && href !== null ) {
						// Show the next installation step.
						href.setAttribute( 'href', redirect );
						href.classList.remove( 'frm_grey', 'disabled' );
						nextInstallStep( form.parentNode.parentNode );
						button.classList.add( 'frm_grey', 'disabled' );
					}
				}
			} else {
				jQuery( '.spinner' ).css( 'visibility', 'hidden' );

				// Show response.message
				if ( 'string' === typeof response.message ) {
					showInstallFormErrorModal( response.message );
				}
			}
			button.classList.remove( 'frm_loading_button' );
		});
	}

	function showInstallFormErrorModal( message ) {
		const modalContent = div( message );
		modalContent.style.padding = '20px 40px';
		const modal = frmDom.modal.maybeCreateModal(
			'frmInstallFormErrorModal',
			{
				title: __( 'Unable to install template', 'formidable' ),
				content: modalContent
			}
		);
		modal.classList.add( 'frm_common_modal' );
	}

	function handleCaptchaTypeChange( e ) {
		const thresholdContainer = document.getElementById( 'frm_captcha_threshold_container' );
		if ( thresholdContainer ) {
			thresholdContainer.classList.toggle( 'frm_hidden', 'v3' !== e.target.value );
		}
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
			});
		});
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
			searchText = searchText.join( '|' ).toLowerCase();
			regEx = true;
		}

		if ( toSearch === 'frm-action' && searchText !== '' ) {
			var addons = document.getElementById( 'frm_email_addon_menu' ).classList;
			addons.remove( 'frm-all-actions' );
			addons.add( 'frm-limited-actions' );
		}

		for ( i = 0; i < items.length; i++ ) {
			var innerText = items[i].innerText.toLowerCase();

			const itemCanBeShown = ! ( getExportOption() === 'xml' && items[i].classList.contains( 'frm-is-repeater' ) );
			if ( searchText === '' ) {
				if ( itemCanBeShown ) {
					items[i].classList.remove( 'frm_hidden' );
				}
				items[i].classList.remove( 'frm-search-result' );
			} else if ( ( regEx && new RegExp( searchText ).test( innerText ) ) || innerText.indexOf( searchText ) >= 0 ) {
				if ( itemCanBeShown ) {
					items[i].classList.remove( 'frm_hidden' );
				}
				items[i].classList.add( 'frm-search-result' );
			} else {
				items[i].classList.add( 'frm_hidden' );
				items[i].classList.remove( 'frm-search-result' );
			}
		}

		jQuery( this ).trigger( 'frmAfterSearch' );
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
		let response;

		const xmlHttp = new XMLHttpRequest();
		const params = typeof data === 'string' ? data : Object.keys( data ).map(
			function( k ) {
				return encodeURIComponent( k ) + '=' + encodeURIComponent( data[k]);
			}
		).join( '&' );

		xmlHttp.open( 'post', ajaxurl, true );
		xmlHttp.onreadystatechange = function() {
			if ( xmlHttp.readyState > 3 && xmlHttp.status == 200 ) {
				response = xmlHttp.responseText;
				try {
					response = JSON.parse( response );
				} catch ( e ) {
					// The response may not be JSON, so just return it.
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

	function invisible( classes ) {
		jQuery( classes ).css( 'visibility', 'hidden' );
	}

	function visible( classes ) {
		jQuery( classes ).css( 'visibility', 'visible' );
	}

	function initModal( id, width ) {
		const $info = jQuery( id );
		if ( ! $info.length ) {
			return false;
		}

		if ( typeof width === 'undefined' ) {
			width = '550px';
		}

		const dialogArgs = {
			dialogClass: 'frm-dialog',
			modal: true,
			autoOpen: false,
			closeOnEscape: true,
			width: width,
			resizable: false,
			draggable: false,
			open: function() {
				jQuery( '.ui-dialog-titlebar' ).addClass( 'frm_hidden' ).removeClass( 'ui-helper-clearfix' );
				jQuery( '#wpwrap' ).addClass( 'frm_overlay' );
				jQuery( '.frm-dialog' ).removeClass( 'ui-widget ui-widget-content ui-corner-all' );
				$info.removeClass( 'ui-dialog-content ui-widget-content' );
				bindClickForDialogClose( $info );
			},
			close: function() {
				jQuery( '#wpwrap' ).removeClass( 'frm_overlay' );
				jQuery( '.spinner' ).css( 'visibility', 'hidden' );

				this.removeAttribute( 'data-option-type' );
				const optionType = document.getElementById( 'bulk-option-type' );
				if ( optionType ) {
					optionType.value = '';
				}
			}
		};

		$info.dialog( dialogArgs );

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

	function addMultiselectLabelListener() {
		const clickListener = ( e ) => {
			if ( 'LABEL' !== e.target.nodeName ) {
				return;
			}

			const labelFor = e.target.getAttribute( 'for' );
			if ( ! labelFor ) {
				return;
			}

			const input = document.getElementById( labelFor );
			if ( ! input || ! input.nextElementSibling ) {
				return;
			}

			const buttonToggle = input.nextElementSibling.querySelector( 'button.dropdown-toggle.multiselect' );
			if ( ! buttonToggle ) {
				return;
			}

			const triggerMultiselectClick = () => buttonToggle.click();
			setTimeout( triggerMultiselectClick, 0 );
		};
		document.addEventListener( 'click', clickListener );
	}

	function maybeChangeEmbedFormMsg() {
		var fieldId = jQuery( this ).closest( '.frm-single-settings' ).data( 'fid' );
		var fieldItem = document.getElementById( 'frm_field_id_' + fieldId );
		if ( null === fieldItem || 'form' !== fieldItem.dataset.type ) {
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

	function toggleProductType() {
		var settings = jQuery( this ).closest( '.frm-single-settings' ),
			container = settings.find( '.frmjs_product_choices' ),
			heading = settings.find( '.frm_prod_options_heading' ),
			currentVal = this.options[ this.selectedIndex ].value;

		container.removeClass( 'frm_prod_type_single frm_prod_type_user_def' );
		heading.removeClass( 'frm_prod_user_def' );

		if ( 'single' === currentVal ) {
			container.addClass( 'frm_prod_type_single' );
		} else if ( 'user_def' === currentVal ) {
			container.addClass( 'frm_prod_type_user_def' );
			heading.addClass( 'frm_prod_user_def' );
		}
	}

	function isProductField( fieldId ) {
		var field = document.getElementById( 'frm_field_id_' + fieldId );
		if ( field === null ) {
			return false;
		} else {
			return 'product' === field.getAttribute( 'data-type' );
		}
	}

	/**
	 * Serialize form data with vanilla JS.
	 */
	function formToData( form ) {
		var subKey, i,
			object = {},
			formData = form.elements;

		for ( i = 0; i < formData.length; i++ ) {
			var input = formData[i],
				key = input.name,
				value = input.value,
				names = key.match( /(.*)\[(.*)\]/ );

			if ( ( input.type === 'radio' || input.type === 'checkbox' ) && ! input.checked ) {
				continue;
			}

			if ( names !== null ) {
				key = names[1];
				subKey = names[2];
				if ( ! Reflect.has( object, key ) ) {
					object[key] = {};
				}
				object[key][subKey] = value;
				continue;
			}

			// Reflect.has in favor of: object.hasOwnProperty(key)
			if ( ! Reflect.has( object, key ) ) {
				object[key] = value;
				continue;
			}
			if ( ! Array.isArray( object[key]) ) {
				object[key] = [ object[key] ];
			}
			object[key].push( value );
		}

		return object;
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
		 * @param {String} fieldId Field ID.
		 * @param {String} key     Cache key.
		 * @returns {HTMLElement|undefined} Return the element from cache or undefined if not found.
		 */
		const getSubFieldElFromCache = ( fieldId, key ) => {
			window.frmCachedSubFields = window.frmCachedSubFields || {};
			window.frmCachedSubFields[fieldId] = window.frmCachedSubFields[fieldId] || {};
			return window.frmCachedSubFields[fieldId][key];
		};

		/**
		 * Sets subfield element to cache.
		 *
		 * @param {String}      fieldId Field ID.
		 * @param {String}      key     Cache key.
		 * @param {HTMLElement} el      Element.
		 */
		const setSubFieldElToCache = ( fieldId, key, el ) => {
			window.frmCachedSubFields = window.frmCachedSubFields || {};
			window.frmCachedSubFields[fieldId] = window.frmCachedSubFields[fieldId] || {};
			window.frmCachedSubFields[fieldId][key] = el;
		};

		/**
		 * Gets column class from the number of columns.
		 *
		 * @param {Number} colCount Number of columns.
		 * @returns {string}
		 */
		const getColClass = colCount => 'frm' + parseInt( 12 / colCount );

		const colClasses = [ 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 ].map( num => 'frm' + num );

		const allSubFieldNames = [ 'first', 'middle', 'last' ];

		/**
		 * Handles name layout change.
		 *
		 * @param {Event} event Event object.
		 */
		const onChangeLayout = event => {
			const value = event.target.value;
			const subFieldNames = value.split( '_' );
			const fieldId = event.target.dataset.fieldId;

			/*
			 * Live update form on the form builder.
			 */
			const container = document.querySelector( '#field_' + fieldId + '_inner_container .frm_combo_inputs_container' );
			const newColClass = getColClass( subFieldNames.length );

			// Set all sub field elements to cache and hide all of them first.
			allSubFieldNames.forEach( name => {
				const subFieldEl = container.querySelector( '[data-sub-field-name="' + name + '"]' );
				if ( subFieldEl ) {
					subFieldEl.classList.add( 'frm_hidden' );
					subFieldEl.classList.remove( ...colClasses );
					setSubFieldElToCache( fieldId, name, subFieldEl );
				}
			});

			subFieldNames.forEach( subFieldName => {
				const subFieldEl = getSubFieldElFromCache( fieldId, subFieldName );
				if ( ! subFieldEl ) {
					return;
				}

				subFieldEl.classList.remove( 'frm_hidden' );
				subFieldEl.classList.add( newColClass );

				container.append( subFieldEl );
			});

			/*
			 * Live update subfield options.
			 */
			// Hide all subfield options.
			allSubFieldNames.forEach( name => {
				const optionsEl = document.querySelector( '.frm_sub_field_options-' + name + '[data-field-id="' + fieldId + '"]' );
				if ( optionsEl ) {
					optionsEl.classList.add( 'frm_hidden' );
					setSubFieldElToCache( fieldId, name + '_options', optionsEl );
				}
			});

			subFieldNames.forEach( subFieldName => {
				const optionsEl = getSubFieldElFromCache( fieldId, subFieldName + '_options' );
				if ( ! optionsEl ) {
					return;
				}
				optionsEl.classList.remove( 'frm_hidden' );
			});
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
		let li, useTag, useTagHref;
		let hasDragIcon = false;
		let hasSaveIcon = false;

		if ( liObject.newOption ) {
			const parser = new DOMParser();
			li = parser.parseFromString( liObject.newOption, 'text/html' ).body.childNodes[0];
		} else {
			li = liObject;
		}

		const liIcons = li.querySelectorAll( 'svg' );

		liIcons.forEach( ( svg, key ) => {
			useTag = svg.getElementsByTagNameNS( 'http://www.w3.org/2000/svg', 'use' )[0];
			if ( ! useTag ) {
				return;
			}
			useTagHref = useTag.getAttributeNS( 'http://www.w3.org/1999/xlink', 'href' ) || useTag.getAttribute( 'href' );

			if ( useTagHref === '#frm_drag_icon' ) {
				hasDragIcon = true;
			}

			if ( useTagHref === '#frm_save_icon' ) {
				hasSaveIcon = true;
			}
		});

		if ( ! hasDragIcon ) {
			li.prepend( icons.drag.cloneNode( true ) );
		}

		if ( li.querySelector( `[id^=field_key_${fieldId}-]` ) && ! hasSaveIcon ) {
			li.querySelector( `[id^=field_key_${fieldId}-]` ).after( icons.save.cloneNode( true ) );
		}

		if ( liObject.newOption ) {
			liObject.newOption = li;
		}
	}

	function maybeAddSaveAndDragIcons( fieldId ) {
		fieldOptions = document.querySelectorAll( `[id^=frm_delete_field_${fieldId}-]` );
		// return if there are no options.
		if ( fieldOptions.length < 2 ) {
			return;
		}

		let options = [ ...fieldOptions ].slice( 1 );
		options.forEach( ( li, _key ) => {
			if ( li.classList.contains( 'frm_other_option' ) ) {
				return;
			}
			addSaveAndDragIconsToOption( fieldId, li );
		});
	}

	function initOnSubmitAction() {
		const onChangeType = event => {
			if ( ! event.target.checked ) {
				return;
			}

			const actionEl = event.target.closest( '.frm_form_action_settings' );
			actionEl.querySelectorAll( '.frm_on_submit_dependent_setting:not(.frm_hidden)' ).forEach( el => {
				el.classList.add( 'frm_hidden' );
			});

			const activeEls = actionEl.querySelectorAll( '.frm_on_submit_dependent_setting[data-show-if-' + event.target.value + ']' );
			activeEls.forEach( activeEl => {
				activeEl.classList.remove( 'frm_hidden' );
			});

			actionEl.setAttribute( 'data-on-submit-type', event.target.value );
		};

		frmDom.util.documentOn( 'change', '.frm_on_submit_type input[type="radio"]', onChangeType );
	}

	return {
		init: function() {
			s = {};

			// Bootstrap dropdown button
			jQuery( '.wp-admin' ).on( 'click', function( e ) {
				var t = jQuery( e.target );
				var $openDrop = jQuery( '.dropdown.open' );
				if ( $openDrop.length && ! t.hasClass( 'dropdown' ) && ! t.closest( '.dropdown' ).length ) {
					$openDrop.removeClass( 'open' );
				}
			});
			jQuery( '#frm_bs_dropdown:not(.open) a' ).on( 'click', focusSearchBox );

			if ( typeof thisFormId === 'undefined' ) {
				thisFormId = jQuery( document.getElementById( 'form_id' ) ).val();
			}

			frmAdminBuild.inboxBannerInit();

			if ( $newFields.length > 0 ) {
				// only load this on the form builder page
				frmAdminBuild.buildInit();
			} else if ( document.getElementById( 'frm_notification_settings' ) !== null ) {
				// only load on form settings page
				frmAdminBuild.settingsInit();
			} else if ( document.getElementById( 'frm_styling_form' ) !== null ) {
				// load styling settings js
				frmAdminBuild.styleInit();
			} else if ( document.getElementById( 'form_global_settings' ) !== null ) {
				// global settings page
				frmAdminBuild.globalSettingsInit();
			} else if ( document.getElementById( 'frm_export_xml' ) !== null ) {
				// import/export page
				frmAdminBuild.exportInit();
			} else if ( document.getElementById( 'frm_dyncontent' ) !== null ) {
				// only load on views settings page
				frmAdminBuild.viewInit();
			} else if ( document.getElementById( 'frm_inbox_page' ) !== null ) {
				// Inbox page
				frmAdminBuild.inboxInit();
			} else if ( document.getElementById( 'frm-welcome' ) !== null ) {
				// Solution install page
				frmAdminBuild.solutionInit();
			} else {
				// New form selection page
				initNewFormModal();
				initSelectionAutocomplete();

				jQuery( '[data-frmprint]' ).on( 'click', function() {
					window.print();
					return false;
				});
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
				jQuery( 'a.edit-frm_shortcode' ).on( 'click', function() {
					if ( $shortCodeDiv.is( ':hidden' ) ) {
						$shortCodeDiv.slideDown( 'fast' );
						this.style.display = 'none';
					}
					return false;
				});

				jQuery( '.cancel-frm_shortcode', '#frm_shortcodediv' ).on( 'click', function() {
					$shortCodeDiv.slideUp( 'fast' );
					$shortCodeDiv.siblings( 'a.edit-frm_shortcode' ).show();
					return false;
				});
			}

			// tabs
			jQuery( document ).on( 'click', '#frm-nav-tabs a', clickNewTab );
			jQuery( '.post-type-frm_display .frm-nav-tabs a, .frm-category-tabs a' ).on( 'click', function() {
				const showUpgradeTab = this.classList.contains( 'frm_show_upgrade_tab' );
				if ( this.classList.contains( 'frm_noallow' ) && ! showUpgradeTab ) {
					return;
				}

				if ( showUpgradeTab ) {
					populateUpgradeTab( this );
				}

				clickTab( this );
				return false;
			});
			clickTab( jQuery( '.starttab a' ), 'auto' );

			// submit the search form with dropdown
			jQuery( document ).on( 'click', '#frm-fid-search-menu a', function() {
				var val = this.id.replace( 'fid-', '' );
				jQuery( 'select[name="fid"]' ).val( val );
				triggerSubmit( document.getElementById( 'posts-filter' ) );
				return false;
			});

			jQuery( '.frm_select_box' ).on( 'click focus', function() {
				this.select();
			});

			jQuery( document ).on( 'input search change', '.frm-auto-search:not(#template-search-input)', searchContent );
			jQuery( document ).on( 'focusin click', '.frm-auto-search', stopPropagation );
			var autoSearch = jQuery( '.frm-auto-search' );
			if ( autoSearch.val() !== '' ) {
				autoSearch.trigger( 'keyup' );
			}

			// Initialize Formidable Connection.
			FrmFormsConnect.init();

			jQuery( document ).on( 'click', '.frm-install-addon', installAddon );
			jQuery( document ).on( 'click', '.frm-activate-addon', activateAddon );
			jQuery( document ).on( 'click', '.frm-solution-multiple', installMultipleAddons );

			// prevent annoying confirmation message from WordPress
			jQuery( 'button, input[type=submit]' ).on( 'click', removeWPUnload );

			addMultiselectLabelListener();

			frmAdminBuild.hooks.addFilter(
				'frm_before_embed_modal',
				( ids, { element, type }) => {
					if ( 'form' !== type ) {
						return ids;
					}

					let formId, formKey;
					const row = element.closest( 'tr' );

					if ( row ) {
						// Embed icon on form index.
						formId = parseInt( row.querySelector( '.column-id' ).textContent );
						formKey = row.querySelector( '.column-form_key' ).textContent;
					} else {
						// Embed button in form builder / form settings.
						formId = document.getElementById( 'form_id' ).value;

						const formKeyInput = document.getElementById( 'frm_form_key' );
						if ( formKeyInput ) {
							formKey = formKeyInput.value;
						} else {
							const previewDrop = document.getElementById( 'frm-previewDrop' );
							if ( previewDrop ) {
								formKey = previewDrop.nextElementSibling.querySelector( '.dropdown-item a' ).getAttribute( 'href' ).split( 'form=' )[1];
							}
						}
					}

					return [ formId, formKey ];
				}
			);

			document.querySelectorAll( '#frm-show-fields > li, .frm_grid_container li' ).forEach( ( el, _key ) => {
				el.addEventListener( 'click', function() {
					let fieldId     = this.querySelector( 'li' )?.dataset.fid || this.dataset.fid;
					maybeAddSaveAndDragIcons( fieldId );
				});
			});
		},

		buildInit: function() {
			let loadFieldId, $builderForm, builderArea;

			debouncedSyncAfterDragAndDrop = debounce( syncAfterDragAndDrop, 10 );
			postBodyContent = document.getElementById( 'post-body-content' );
			$postBodyContent = jQuery( postBodyContent );

			if ( jQuery( '.frm_field_loading' ).length ) {
				loadFieldId = jQuery( '.frm_field_loading' ).first().attr( 'id' );
				loadFields( loadFieldId );
			}

			setupSortable( 'ul.frm_sorting' );

			document.querySelectorAll( '.field_type_list > li' ).forEach( makeDraggable );

			jQuery( 'ul.field_type_list, .field_type_list li, ul.frm_code_list, .frm_code_list li, .frm_code_list li a, #frm_adv_info #category-tabs li, #frm_adv_info #category-tabs li a' ).disableSelection();

			jQuery( '.frm_submit_ajax' ).on( 'click', submitBuild );
			jQuery( '.frm_submit_no_ajax' ).on( 'click', submitNoAjax );

			jQuery( 'a.edit-form-status' ).on( 'click', slideDown );
			jQuery( '.cancel-form-status' ).on( 'click', slideUp );
			jQuery( '.save-form-status' ).on( 'click', function() {
				const newStatus = jQuery( document.getElementById( 'form_change_status' ) ).val();
				jQuery( 'input[name="new_status"]' ).val( newStatus );
				jQuery( document.getElementById( 'form-status-display' ) ).html( newStatus );
				jQuery( '.cancel-form-status' ).trigger( 'click' );
				return false;
			});

			jQuery( '.frm_form_builder form' ).first().on( 'submit', function() {
				jQuery( '.inplace_field' ).trigger( 'blur' );
			});

			initiateMultiselect();
			renumberPageBreaks();

			$builderForm = jQuery( builderForm );
			builderArea = document.getElementById( 'frm_form_editor_container' );
			$builderForm.on( 'click', '.frm_add_logic_row', addFieldLogicRow );
			$builderForm.on( 'click', '.frm_add_watch_lookup_row', addWatchLookupRow );
			$builderForm.on( 'change', '.frm_get_values_form', updateGetValueFieldSelection );
			$builderForm.on( 'change', '.frm_logic_field_opts', getFieldValues );
			$builderForm.on( 'change', '.radio_maxnum', setStarValues );
			$builderForm.on( 'frm-multiselect-changed', 'select[name^="field_options[admin_only_"]', adjustVisibilityValuesForEveryoneValues );

			jQuery( document.getElementById( 'frm-insert-fields' ) ).on( 'click', '.frm_add_field', addFieldClick );
			$newFields.on( 'click', '.frm_clone_field', duplicateField );
			$builderForm.on( 'blur', 'input[id^="frm_calc"]', checkCalculationCreatedByUser );
			$builderForm.on( 'change', 'input.frm_format_opt, input.frm_max_length_opt', toggleInvalidMsg );
			$builderForm.on( 'change click', '[data-changeme]', liveChanges );
			$builderForm.on( 'click', 'input.frm_req_field', markRequired );
			$builderForm.on( 'click', '.frm_mark_unique', markUnique );

			$builderForm.on( 'change', '.frm_repeat_format', toggleRepeatButtons );
			$builderForm.on( 'change', '.frm_repeat_limit', checkRepeatLimit );
			$builderForm.on( 'change', '.frm_js_checkbox_limit', checkCheckboxSelectionsLimit );
			$builderForm.on( 'input', 'input[name^="field_options[add_label_"]', function() {
				updateRepeatText( this, 'add' );
			});
			$builderForm.on( 'input', 'input[name^="field_options[remove_label_"]', function() {
				updateRepeatText( this, 'remove' );
			});
			$builderForm.on( 'change', 'select[name^="field_options[data_type_"]', maybeClearWatchFields );
			jQuery( builderArea ).on( 'click', '.frm-collapse-page', maybeCollapsePage );
			jQuery( builderArea ).on( 'click', '.frm-collapse-section', maybeCollapseSection );
			$builderForm.on( 'click', '.frm-single-settings h3', maybeCollapseSettings );

			jQuery( builderArea ).on( 'show.bs.dropdown hide.bs.dropdown', changeSectionStyle );

			$builderForm.on( 'click', '.frm_toggle_sep_values', toggleSepValues );
			$builderForm.on( 'click', '.frm_toggle_image_options', toggleImageOptions );
			$builderForm.on( 'click', '.frm_remove_image_option', removeImageFromOption );
			$builderForm.on( 'click', '.frm_choose_image_box', addImageToOption );
			$builderForm.on( 'change', '.frm_hide_image_text', refreshOptionDisplay );
			$builderForm.on( 'change', '.frm_field_options_image_size', setImageSize );
			$builderForm.on( 'click', '.frm_multiselect_opt', toggleMultiselect );
			$newFields.on( 'mousedown', 'input, textarea, select', stopFieldFocus );
			$newFields.on( 'click', 'input[type=radio], input[type=checkbox]', stopFieldFocus );
			$newFields.on( 'click', '.frm_delete_field', clickDeleteField );
			$newFields.on( 'click', '.frm_select_field', clickSelectField );
			jQuery( document ).on( 'click', '.frm_delete_field_group', clickDeleteFieldGroup );
			jQuery( document ).on( 'click', '.frm_clone_field_group', duplicateFieldGroup );
			jQuery( document ).on( 'click', '#frm_field_group_controls > span:first-child', clickFieldGroupLayout );
			jQuery( document ).on( 'click', '.frm-row-layout-option', handleFieldGroupLayoutOptionClick );
			jQuery( document ).on( 'click', '.frm-merge-fields-into-row .frm-row-layout-option', handleFieldGroupLayoutOptionInsideMergeClick );
			jQuery( document ).on( 'click', '.frm-custom-field-group-layout', customFieldGroupLayoutClick );
			jQuery( document ).on( 'click', '.frm-merge-fields-into-row .frm-custom-field-group-layout', customFieldGroupLayoutInsideMergeClick );
			jQuery( document ).on( 'click', '.frm-break-field-group', breakFieldGroupClick );
			$newFields.on( 'click', '#frm_field_group_popup .frm_grid_container input', focusFieldGroupInputOnClick );
			jQuery( document ).on( 'click', '.frm-cancel-custom-field-group-layout', cancelCustomFieldGroupClick );
			jQuery( document ).on( 'click', '.frm-save-custom-field-group-layout', saveCustomFieldGroupClick );
			$newFields.on( 'click', 'ul.frm_sorting', fieldGroupClick );
			jQuery( document ).on( 'click', '.frm-merge-fields-into-row', mergeFieldsIntoRowClick );
			jQuery( document ).on( 'click', '.frm-delete-field-groups', deleteFieldGroupsClick );
			$newFields.on( 'click', '.frm-field-action-icons [data-toggle="dropdown"]', function() {
				this.closest( 'li.form-field' ).classList.add( 'frm-field-settings-open' );
				jQuery( document ).on( 'click', '#frm_builder_page', handleClickOutsideOfFieldSettings );
			});
			$newFields.on( 'mousemove', 'ul.frm_sorting', checkForMultiselectKeysOnMouseMove );
			$newFields.on( 'show.bs.dropdown', '.frm-field-action-icons', onFieldActionDropdownShow );
			jQuery( document ).on( 'show.bs.dropdown', '#frm_field_group_controls', onFieldGroupActionDropdownShow );
			$builderForm.on( 'click', '.frm_single_option a[data-removeid]', deleteFieldOption );
			$builderForm.on( 'mousedown', '.frm_single_option input[type=radio]', maybeUncheckRadio );
			$builderForm.on( 'focusin', '.frm_single_option input[type=text]', maybeClearOptText );
			$builderForm.on( 'click', '.frm_add_opt', addFieldOption );
			$builderForm.on( 'change', '.frm_single_option input', resetOptOnChange );
			$builderForm.on( 'change', '.frm_image_id', resetOptOnChange );
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

			jQuery( document ).on( 'submit', '#frm_js_build_form', buildSubmittedNoAjax );
			jQuery( document ).on( 'change', '#frm_builder_page input:not(.frm-search-input):not(.frm-custom-grid-size-input), #frm_builder_page select, #frm_builder_page textarea', fieldUpdated );

			popAllProductFields();

			jQuery( document ).on( 'change', '.frmjs_prod_data_type_opt', toggleProductType );

			jQuery( document ).on( 'focus', '.frm-single-settings ul input[type="text"][name^="field_options[options_"]', onOptionTextFocus );
			jQuery( document ).on( 'blur', '.frm-single-settings ul input[type="text"][name^="field_options[options_"]', onOptionTextBlur );

			initBulkOptionsOverlay();
			hideEmptyEle();
			maybeHideQuantityProductFieldOption();
			handleNameFieldOnFormBuilder();
			toggleSectionHolder();
		},

		settingsInit: function() {
			const $formActions = jQuery( document.getElementById( 'frm_notification_settings' ) );

			let formSettings, $loggedIn, $cookieExp, $editable;

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
			jQuery( document ).on( 'change', 'select[data-toggleclass], input[data-toggleclass]', toggleFormOpts );
			jQuery( '.frm_actions_list' ).on( 'click', '.frm_active_action', addFormAction );
			jQuery( '#frm-show-groups, #frm-hide-groups' ).on( 'click', toggleActionGroups );
			initiateMultiselect();

			//set actions icons to inactive
			jQuery( 'ul.frm_actions_list li' ).each( function() {
				checkActiveAction( jQuery( this ).children( 'a' ).data( 'actiontype' ) );

				// If the icon is a background image, don't add BG color.
				const icon = jQuery( this ).find( 'i' );
				if ( icon.css( 'background-image' ) !== 'none' ) {
					icon.addClass( 'frm-inverse' );
				}
			});

			jQuery( '.frm_submit_settings_btn' ).on( 'click', submitSettings );

			formSettings = jQuery( '.frm_form_settings' );
			formSettings.on( 'click', '.frm_add_form_logic', addFormLogicRow );
			formSettings.on( 'blur', '.frm_email_blur', formatEmailSetting );
			formSettings.on( 'click', '.frm_already_used', onlyOneActionMessage );

			formSettings.on( 'change', '#logic_link_submit', toggleSubmitLogic );
			formSettings.on( 'click', '.frm_add_submit_logic', addSubmitLogic );
			formSettings.on( 'change', '.frm_submit_logic_field_opts', addSubmitLogicOpts );

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
			formSettings.on( 'mouseup', '*:not(.frm-show-box)', function( e ) {
				e.stopPropagation();

				if ( e.target.classList.contains( 'frm-show-box' )  || e.target.parentElement.classList.contains( 'frm-show-box' ) ) {
					return;
				}

				const sidebar = document.getElementById( 'frm_adv_info' );
				if ( ! sidebar ) {
					return;
				}

				if ( sidebar.getAttribute( 'data-fills' ) === e.target.id && typeof e.target.id !== 'undefined' ) {
					return;
				}

				const isChild = jQuery( e.target ).closest( '#frm_adv_info' ).length > 0;

				if ( ! isChild && sidebar.display !== 'none' ) {
					hideShortcodes( sidebar );
				}
			});

			//Warning when user selects "Do not store entries ..."
			jQuery( document.getElementById( 'no_save' ) ).on( 'change', function() {
				if ( this.checked ) {
					if ( confirm( frm_admin_js.no_save_warning ) !== true ) {
						// Uncheck box if user hits "Cancel"
						jQuery( this ).attr( 'checked', false );
					}
				}
			});

			jQuery( 'select[name="options[edit_action]"]' ).on( 'change', showSuccessOpt );

			$loggedIn = document.getElementById( 'logged_in' );
			jQuery( $loggedIn ).on( 'change', function() {
				if ( this.checked ) {
					visible( '.hide_logged_in' );
				} else {
					invisible( '.hide_logged_in' );
				}
			});

			$cookieExp = jQuery( document.getElementById( 'frm_cookie_expiration' ) );
			jQuery( document.getElementById( 'frm_single_entry_type' ) ).on( 'change', function() {
				if ( this.value === 'cookie' ) {
					$cookieExp.fadeIn( 'slow' );
				} else {
					$cookieExp.fadeOut( 'slow' );
				}
			});

			var $singleEntry = document.getElementById( 'single_entry' );
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
			});

			jQuery( '.hide_save_draft' ).hide();

			var $saveDraft = jQuery( document.getElementById( 'save_draft' ) );
			$saveDraft.on( 'change', function() {
				if ( this.checked ) {
					jQuery( '.hide_save_draft' ).fadeIn( 'slow' );
				} else {
					jQuery( '.hide_save_draft' ).fadeOut( 'slow' );
				}
			});
			triggerChange( $saveDraft );

			//If Allow editing is checked/unchecked
			$editable = document.getElementById( 'editable' );
			jQuery( $editable ).on( 'change', function() {
				if ( this.checked ) {
					jQuery( '.hide_editable' ).fadeIn( 'slow' );
					triggerChange( document.getElementById( 'edit_action' ) );
				} else {
					jQuery( '.hide_editable' ).fadeOut( 'slow' );
					jQuery( '.edit_action_message_box' ).fadeOut( 'slow' );//Hide On Update message box
				}
			});

			//If File Protection is checked/unchecked
			jQuery( document ).on( 'change', '#protect_files', function() {
				if ( this.checked ) {
					jQuery( '.hide_protect_files' ).fadeIn( 'slow' );
				} else {
					jQuery( '.hide_protect_files' ).fadeOut( 'slow' );
				}
			});

			jQuery( document ).on( 'frm-multiselect-changed', '#protect_files_role', adjustVisibilityValuesForEveryoneValues );

			jQuery( document ).on( 'submit', '.frm_form_settings', settingsSubmitted );
			jQuery( document ).on( 'change', '#form_settings_page input:not(.frm-search-input), #form_settings_page select, #form_settings_page textarea', fieldUpdated );

            // Page Selection Autocomplete
			initSelectionAutocomplete();

			jQuery( document ).on( 'frm-action-loaded', onActionLoaded );

			initOnSubmitAction();
		},

		panelInit: function() {
			var customPanel, settingsPage, viewPage, insertFieldsTab;

			jQuery( '.frm_wrap, #postbox-container-1' ).on( 'click', '.frm_insert_code', insertCode );
			jQuery( document ).on( 'change', '.frm_insert_val', function() {
				insertFieldCode( jQuery( this ).data( 'target' ), jQuery( this ).val() );
				jQuery( this ).val( '' );
			});

			jQuery( document ).on( 'click change', '#frm-id-key-condition', resetLogicBuilder );
			jQuery( document ).on( 'keyup change', '.frm-build-logic', setLogicExample );

			showInputIcon();
			jQuery( document ).on( 'frmElementAdded', function( event, parentEle ) {
				/* This is here for add-ons to trigger */
				showInputIcon( parentEle );
			});
			jQuery( document ).on( 'mousedown', '.frm-show-box', showShortcodes );

			settingsPage = document.getElementById( 'form_settings_page' );
			viewPage = document.body.classList.contains( 'post-type-frm_display' );
			insertFieldsTab = document.getElementById( 'frm_insert_fields_tab' );

			if ( settingsPage !== null || viewPage ) {
			jQuery( document ).on( 'focusin', 'form input, form textarea', function( e ) {
				var htmlTab;
				e.stopPropagation();
				maybeShowModal( this );

				if ( jQuery( this ).is( ':not(:submit, input[type=button], .frm-search-input, input[type=checkbox])' ) ) {
					if ( jQuery( e.target ).closest( '#frm_adv_info' ).length ) {
						// Don't trigger for fields inside of the modal.
						return;
					}

					if ( settingsPage !== null ) {
						/* form settings page */
						htmlTab = jQuery( '#frm_html_tab' );
						if ( jQuery( this ).closest( '#html_settings' ).length > 0 ) {
							htmlTab.show();
							htmlTab.siblings().hide();
							jQuery( '#frm_html_tab a' ).trigger( 'click' );
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
			});
			}

			jQuery( '.frm_wrap, #postbox-container-1' ).on( 'mousedown', '#frm_adv_info a, .frm_field_list a', function( e ) {
				e.preventDefault();
			});

			customPanel = jQuery( '#frm_adv_info' );
			customPanel.on( 'click', '.subsubsub a.frmids', function( e ) {
				toggleKeyID( 'frmids', e );
			});
			customPanel.on( 'click', '.subsubsub a.frmkeys', function( e ) {
				toggleKeyID( 'frmkeys', e );
			});
		},

		viewInit: function() {
			var $addRemove,
				$advInfo = jQuery( document.getElementById( 'frm_adv_info' ) );
			$advInfo.before( '<div id="frm_position_ele"></div>' );
			setupMenuOffset();

			jQuery( document ).on( 'blur', '#param', checkDetailPageSlug );
			jQuery( document ).on( 'blur', 'input[name^="options[where_val]"]', checkFilterParamNames );

			// Show loading indicator.
			jQuery( '#publish' ).on( 'mousedown', function() {
				fieldsUpdated = 0;
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
		},

		inboxInit: function() {
			jQuery( '.frm_inbox_dismiss, footer .frm-button-secondary, footer .frm-button-primary' ).on( 'click', function( e ) {
				var message = this.parentNode.parentNode,
					key = message.getAttribute( 'data-message' ),
					href = this.getAttribute( 'href' );

				if ( 'free_templates' === key && ! this.classList.contains( 'frm_inbox_dismiss' ) ) {
					return;
				}

				e.preventDefault();

				data = {
					action: 'frm_inbox_dismiss',
					key: key,
					nonce: frmGlobal.nonce
				};
				postAjax( data, function() {
					if ( href !== '#' ) {
						window.location = href;
						return true;
					}
					fadeOut( message, function() {
						message.parentNode.removeChild( message );
					});
				});
			});
			jQuery( '#frm-dismiss-inbox' ).on( 'click', function( e ) {
				data = {
					action: 'frm_inbox_dismiss',
					key: 'all',
					nonce: frmGlobal.nonce
				};
				postAjax( data, function() {
					fadeOut( document.getElementById( 'frm_message_list' ), function() {
						document.getElementById( 'frm_empty_inbox' ).classList.remove( 'frm_hidden' );
					});
				});
			});
		},

		solutionInit: function() {
			jQuery( document ).on( 'submit', '#frm-new-template', installTemplate );
		},

		styleInit: function() {
			const $previewWrapper = jQuery( '.frm_image_preview_wrapper' );
			$previewWrapper.on( 'click', '.frm_choose_image_box', addImageToOption );
			$previewWrapper.on( 'click', '.frm_remove_image_option', removeImageFromOption );

			wp.hooks.doAction( 'frm_style_editor_init' );
		},

		customCSSInit: function() {
			console.warn( 'Calling frmAdminBuild.customCSSInit is deprecated.' );
		},

		globalSettingsInit: function() {
			var licenseTab;

			jQuery( document ).on( 'click', '[data-frmuninstall]', uninstallNow );

			initiateMultiselect();

			// activate addon licenses
			licenseTab = document.getElementById( 'licenses_settings' );
			if ( licenseTab !== null ) {
				jQuery( licenseTab ).on( 'click', '.edd_frm_save_license', saveAddonLicense );
			}

			// Solution install page
			jQuery( document ).on( 'click', '#frm-new-template button', installTemplateFieldset );

			jQuery( '#frm-dismissable-cta .dismiss' ).on( 'click', function( event ) {
				event.preventDefault();
				jQuery.post( ajaxurl, {
					action: 'frm_lite_settings_upgrade'
				});
				jQuery( '.settings-lite-cta' ).remove();
			});

			const captchaType = document.getElementById( 'frm_re_type' );
			if ( captchaType ) {
				captchaType.addEventListener( 'change', handleCaptchaTypeChange );
			}

			document.querySelector( '.frm_captchas' ).addEventListener( 'change', function() {
				document.querySelector( '.captcha_settings .frm_note_style' ).classList.toggle( 'frm_hidden' );
			});
		},

		exportInit: function() {
			jQuery( '.frm_form_importer' ).on( 'submit', startFormMigration );
			jQuery( document.getElementById( 'frm_export_xml' ) ).on( 'submit', validateExport );
			jQuery( '#frm_export_xml input, #frm_export_xml select' ).on( 'change', removeExportError );
			jQuery( 'input[name="frm_import_file"]' ).on( 'change', checkCSVExtension );
			document.querySelector( 'select[name="format"]' ).addEventListener( 'change', exportTypeChanged );

			jQuery( 'input[name="frm_export_forms[]"]' ).on( 'click', preventMultipleExport );
			initiateMultiselect();

			jQuery( '.frm-feature-banner .dismiss' ).on( 'click', function( event ) {
				event.preventDefault();
				jQuery.post( ajaxurl, {
					action: 'frm_dismiss_migrator',
					plugin: this.id,
					nonce: frmGlobal.nonce
				});
				this.parentElement.remove();
			});

			showOrHideRepeaters( getExportOption() );
		},

		inboxBannerInit: function() {
			const banner = document.getElementById( 'frm_banner' );
			if ( ! banner ) {
				return;
			}

			const dismissButton = banner.querySelector( '.frm-banner-dismiss' );
			document.addEventListener(
				'click',
				function( event ) {
					if ( event.target !== dismissButton ) {
						return;
					}

					const data = {
						action: 'frm_inbox_dismiss',
						key: banner.dataset.key,
						nonce: frmGlobal.nonce
					};
					postAjax(
						data,
						function() {
							jQuery( banner ).fadeOut(
								400,
								function() {
									banner.remove();
								}
							);
						}
					);
				}
			);
		},

		updateOpts: function( fieldId, opts, modal ) {
			var separate = usingSeparateValues( fieldId ),
				action = isProductField( fieldId ) ? 'frm_bulk_products' : 'frm_import_options';
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					action: action,
					field_id: fieldId,
					opts: opts,
					separate: separate,
					nonce: frmGlobal.nonce
				},
				success: function( html ) {
					document.getElementById( 'frm_field_' + fieldId + '_opts' ).innerHTML = html;
					resetDisplayedOpts( fieldId );

					if ( typeof modal !== 'undefined' ) {
						modal.dialog( 'close' );
						document.getElementById( 'frm-update-bulk-opts' ).classList.remove( 'frm_loading_button' );
					}
				}
			});
		},

		/* remove conditional logic if the field doesn't exist */
		triggerRemoveLogic: function( fieldID, metaName ) {
			jQuery( '#frm_logic_' + fieldID + '_' + metaName + ' .frm_remove_tag' ).trigger( 'click' );
		},

		downloadXML: function( controller, ids, isTemplate ) {
			var url = ajaxurl + '?action=frm_' + controller + '_xml&ids=' + ids;
			if ( isTemplate !== null ) {
				url = url + '&is_template=' + isTemplate;
			}
			location.href = url;
		},

		/**
		 * @since 5.0.04
		 */
		hooks: {
			applyFilters: function( hookName, ...args ) {
				return wp.hooks.applyFilters( hookName, ...args );
			},
			addFilter: function( hookName, callback, priority ) {
				return wp.hooks.addFilter( hookName, 'formidable', callback, priority );
			},
			doAction: function( hookName, ...args ) {
				return wp.hooks.doAction( hookName, ...args );
			},
			addAction: function( hookName, callback, priority ) {
				return wp.hooks.addAction( hookName, 'formidable', callback, priority );
			}
		},

		infoModal: infoModal
	};
}

frmAdminBuild = frmAdminBuildJS();

jQuery( document ).ready(
	() => {
		frmAdminBuild.init();

		frmDom.bootstrap.setupBootstrapDropdowns( convertOldBootstrapDropdownsToBootstrap4 );
		function convertOldBootstrapDropdownsToBootstrap4( frmDropdownMenu ) {
			const toggle = frmDropdownMenu.querySelector( '.frm-dropdown-toggle' );
			if ( toggle ) {
				if ( ! toggle.hasAttribute( 'role' ) ) {
					toggle.setAttribute( 'role', 'button' );
				}
				if ( ! toggle.hasAttribute( 'tabindex' ) ) {
					toggle.setAttribute( 'tabindex', 0 );
				}
			}

			// Convert <li> and <ul> tags.
			if ( 'UL' === frmDropdownMenu.tagName ) {
				convertBootstrapUl( frmDropdownMenu );
			}
		}

		function convertBootstrapUl( ul ) {
			let html = ul.outerHTML;
			html = html.replace( '<ul ', '<div ' );
			html = html.replace( '</ul>', '</div>' );
			html = html.replaceAll( '<li>', '<div class="dropdown-item">' );
			html = html.replaceAll( '<li class="', '<div class="dropdown-item ' );
			html = html.replaceAll( '</li>', '</div>' );
			ul.outerHTML = html;
		}
	}
);

function frm_remove_tag( htmlTag ) { // eslint-disable-line camelcase
	console.warn( 'DEPRECATED: function frm_remove_tag in v2.0' );
	jQuery( htmlTag ).remove();
}

function frm_show_div( div, value, showIf, classId ) { // eslint-disable-line camelcase
	if ( value == showIf ) {
		jQuery( classId + div ).fadeIn( 'slow' ).css( 'visibility', 'visible' );
	} else {
		jQuery( classId + div ).fadeOut( 'slow' );
	}
}

function frmCheckAll( checked, n ) {
	jQuery( 'input[name^="' + n + '"]' ).prop( 'checked', ! ! checked );
}

function frmCheckAllLevel( checked, n, level ) {
	var $kids = jQuery( '.frm_catlevel_' + level ).children( '.frm_checkbox' ).children( 'label' );
	$kids.children( 'input[name^="' + n + '"]' ).prop( 'checked', ! ! checked );
}

function frm_add_logic_row( id, formId ) { // eslint-disable-line camelcase
	console.warn( 'DEPRECATED: function frm_add_logic_row in v2.0' );
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: {
			action: 'frm_add_logic_row',
			form_id: formId,
			field_id: id,
			meta_name: jQuery( '#frm_logic_row_' + id + ' > div' ).length,
			nonce: frmGlobal.nonce
		},
		success: function( html ) {
			jQuery( '#frm_logic_row_' + id ).append( html );
		}
	});
	return false;
}

function frmGetFieldValues( fieldId, cur, rowNumber, fieldType, htmlName ) {

	if ( fieldId ) {
		jQuery.ajax({
			type: 'POST', url: ajaxurl,
			data: 'action=frm_get_field_values&current_field=' + cur + '&field_id=' + fieldId + '&name=' + htmlName + '&t=' + fieldType + '&form_action=' + jQuery( 'input[name="frm_action"]' ).val() + '&nonce=' + frmGlobal.nonce,
			success: function( msg ) {
				document.getElementById( 'frm_show_selected_values_' + cur + '_' + rowNumber ).innerHTML = msg;
			}
		});
	}
}

function frmImportCsv( formID ) {
	var urlVars = '';
	if ( typeof __FRMURLVARS !== 'undefined' ) {
		urlVars = __FRMURLVARS;
	}

	jQuery.ajax({
		type: 'POST', url: ajaxurl,
		data: 'action=frm_import_csv&nonce=' + frmGlobal.nonce + '&frm_skip_cookie=1' + urlVars,
		success: function( count ) {
			var max = jQuery( '.frm_admin_progress_bar' ).attr( 'aria-valuemax' );
			var imported = max - count;
			var percent = ( imported / max ) * 100;
			jQuery( '.frm_admin_progress_bar' ).css( 'width', percent + '%' ).attr( 'aria-valuenow', imported );

			if ( parseInt( count, 10 ) > 0 ) {
				jQuery( '.frm_csv_remaining' ).html( count );
				frmImportCsv( formID );
			} else {
				jQuery( document.getElementById( 'frm_import_message' ) ).html( frm_admin_js.import_complete );
				setTimeout( function() {
					location.href = '?page=formidable-entries&frm_action=list&form=' + formID + '&import-message=1';
				}, 2000 );
			}
		}
	});
}
