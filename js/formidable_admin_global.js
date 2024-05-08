/* global jQuery:false, frmGlobal, tb_remove, ajaxurl, adminpage */
/* exported frm_install_now, frmSelectSubnav, frmCreatePostEntry */
/* eslint-disable prefer-const */

jQuery( document ).ready( function() {
    let deauthLink, submenuItem, li,
		installLink = document.getElementById( 'frm_install_link' );
    if ( installLink !== null ) {
        jQuery( installLink ).on( 'click', frmInstallPro );
    }

	deauthLink = jQuery( '.frm_deauthorize_link' );
	if ( deauthLink.length ) {
		deauthLink.on( 'click', frmDeauthorizeNow );
	}

    if ( typeof tb_remove === 'function' ) { // eslint-disable-line camelcase
        frmAdminPopup.init();
    }

	submenuItem = document.querySelector( '.frm-upgrade-submenu' );
	if ( null !== submenuItem ) {
		li = submenuItem.parentNode.parentNode;
		if ( li ) {
			li.classList.add( 'frm-submenu-highlight' );
		}
	}
});

function frm_install_now() { // eslint-disable-line camelcase
	const $msg = jQuery( document.getElementById( 'frm_install_message' ) );
	$msg.html( '<div class="frm_plugin_updating">' + frmGlobal.updating_msg + '<div class="spinner frm_spinner"></div></div>' );
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: {
			action: 'frm_install',
			nonce: frmGlobal.nonce
		},
		success: function() {
			$msg.fadeOut( 'slow' );
		}
	});
	return false;
}

function frmInstallPro( e ) {
	let $msg,
		plugin = this.getAttribute( 'data-prourl' );
	if ( plugin === '' ) {
		return true;
	}

	e.preventDefault();

	$msg = jQuery( document.getElementById( 'frm_install_message' ) );
	$msg.html( '<div class="frm_plugin_updating">' + frmGlobal.updating_msg + '<div class="spinner frm_spinner"></div></div>' );
	$msg.fadeIn( 'slow' );

	jQuery.ajax({
		url: ajaxurl,
		type: 'POST',
		async: true,
		cache: false,
		dataType: 'json',
		data: {
			action: 'frm_install_addon',
			nonce: frmGlobal.nonce,
			plugin: plugin
		},
		success: function() {
			$msg.fadeOut( 'slow' );
			$msg.parent().fadeOut( 'slow' );
		},
		error: function() {
			$msg.fadeOut( 'slow' );
		}
	});
	return false;
}

function frmDeauthorizeNow() {
    if ( ! confirm( frmGlobal.deauthorize ) ) {
		return false;
    }
    jQuery( this ).html( '<span class="spinner"></span>' );
    jQuery.ajax({
        type: 'POST',
		url: ajaxurl,
        data: {
			action: 'frm_deauthorize',
			nonce: frmGlobal.nonce
		},
        success: function() {
			jQuery( '.error' ).fadeOut( 'slow' );
		}
    });
    return false;
}

function frmSelectSubnav() {
    const frmMenu = document.getElementById( 'toplevel_page_formidable' );
    jQuery( frmMenu ).removeClass( 'wp-not-current-submenu' ).addClass( 'wp-has-current-submenu wp-menu-open' );
    jQuery( '#toplevel_page_formidable a.wp-has-submenu' ).removeClass( 'wp-not-current-submenu' ).addClass( 'wp-has-current-submenu wp-menu-open' );
}

function frmCreatePostEntry( id, postId ) {
    jQuery( '#frm_create_entry p' ).replaceWith( '<img src="' + frmGlobal.url + '/images/wpspin_light.gif" alt="' + frmGlobal.loading + '" />' );
    jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: {
			action: 'frm_create_post_entry',
			id: id,
			post_id: postId,
			nonce: frmGlobal.nonce
		},
		success: function() {
			jQuery( document.getElementById( 'frm_create_entry' ) ).fadeOut( 'slow' );
		}
    });
}

function frmAdminPopupJS() {
    function switchSc() {
		let val;
        jQuery( '.frm_switch_sc' ).removeClass( 'active' );
        jQuery( this ).addClass( 'active' );
        toggleMenu();
        jQuery( '#frm_popup_content .media-frame-title h1' ).html( jQuery( this ).children( '.howto' ).text() + ' <span class="spinner" style="float:left;"></span>' );
        val = this.id.replace( 'sc-link-', '' );
        populateOpts( val );
        return false;
    }

    function populateOpts( val ) {
		let $settings, $scOpts, $spinner,
			sc = document.getElementById( 'frm_complete_shortcode' );
		if ( sc !== null ) {
			sc.value = '[' + val + ']';
		}
        jQuery( '.frm_shortcode_option' ).hide();

        $settings = document.getElementById( 'sc-opts-' + val );
        if ( $settings !== null ) {
            $settings.style.display = '';
            jQuery( document.getElementById( 'sc-' + val ) ).trigger( 'click' );
        } else {
            $scOpts = jQuery( document.getElementById( 'frm_shortcode_options' ) );
            $spinner = jQuery( '.media-frame-title .spinner' );
            $spinner.show();
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'frm_get_shortcode_opts',
					shortcode: val,
					nonce: frmGlobal.nonce
				},
				success: function( html ) {
					$spinner.hide();
					$scOpts.append( html );
					jQuery( document.getElementById( 'sc-' + val ) ).trigger( 'click' );
				}
			});
		}
	}

    function addToShortcode() {
        const sc = jQuery( 'input[name=frmsc]:checked' ).val();
        const inputs = jQuery( document.getElementById( 'sc-opts-' + sc ) ).find( 'input, select' );
        let output = '[' + sc;
        inputs.each( function() {
            let attrName, attrVal,
				$thisInput = jQuery( this ),
				attrId = this.id;
            if ( attrId.indexOf( 'frmsc_' ) === 0 ) {
				attrName = attrId.replace( 'frmsc_' + sc + '_', '' );
				attrVal = $thisInput.val();

                if ( ( $thisInput.attr( 'type' ) === 'checkbox' && ! this.checked ) || ( ( $thisInput.attr( 'type' ) === 'text' || $thisInput.is( 'select' ) ) && attrVal === '' ) ) {
                } else {
                    output += ' ' + attrName + '="' + attrVal + '"';
                }
            }
        });
        output += ']';
        document.getElementById( 'frm_complete_shortcode' ).value = output;
    }

    function insertShortcode() {
        const win = window.dialogArguments || opener || parent || top;
        win.send_to_editor( document.getElementById( 'frm_complete_shortcode' ).value );
    }

    function getFieldSelection() {
		let thisId,
			formId = this.value;
        if ( formId ) {
            thisId = this.id;
            jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
                data: {
					action: 'frm_get_field_selection',
					field_id: 0,
					form_id: formId,
					nonce: frmGlobal.nonce
				},
                success: function( msg ) {
                    const baseId = thisId.replace( '_form', '' );
                    msg = msg.replace( 'name="field_options[form_select_0]"', 'id="frmsc_' + baseId + '_fields"' );
                    jQuery( document.getElementById( baseId + '_fields_container' ) ).html( msg );
                }
            });
        }
    }

	function toggleMenu() {
		jQuery( document.getElementById( 'frm_popup_content' ) ).find( '.media-menu' ).toggleClass( 'visible' );
	}

    return {
        init: function() {
			let $scOptsDiv;

            jQuery( '.frm_switch_sc' ).on( 'click', switchSc );
            jQuery( '.button.frm_insert_form' ).on( 'click', function() {
                populateOpts( 'formidable' );
            });
            jQuery( document.getElementById( 'frm_insert_shortcode' ) ).on( 'click', insertShortcode );

			$scOptsDiv = jQuery( document.getElementById( 'frm_shortcode_options' ) );
            $scOptsDiv.on( 'change', 'select, input', addToShortcode );
            $scOptsDiv.on( 'change', '.frm_get_field_selection', getFieldSelection );

            jQuery( '#frm_popup_content .media-modal-close' ).on( 'click', tb_remove );
            jQuery( '#frm_popup_content .media-frame-title h1' ).on( 'click', toggleMenu );
        }
    };
}

window.frmAdminPopup = frmAdminPopupJS();

function frmWidgetsJS() {
    function toggleCatOpt() {
        const catOpts = jQuery( this ).closest( '.widget-content' ).children( '.frm_list_items_hide_cat_opts' );
        if ( this.checked ) {
            catOpts.fadeIn();
        } else {
            catOpts.fadeOut();
        }
    }

    function getFields() {
		let widget,
			displayId = this.value;
        if ( displayId !== '' ) {
			widget = jQuery( this ).closest( '.widget-content' );

            jQuery.ajax({
                type: 'POST',
				url: ajaxurl,
                dataType: 'json',
                data: {
					action: 'frm_get_dynamic_widget_opts',
					display_id: displayId,
					nonce: frmGlobal.nonce
				},
                success: function( opts ) {
					let titleField,
						catField = widget.find( '.frm_list_items_cat_id' );
                    catField.find( 'option' ).remove().end();
                    catField.append( jQuery( '<option></option>' ) );
                    jQuery.each( opts.catValues, function( key, value ) {
                        catField.append( jQuery( '<option></option>' ).attr( 'value', key ).text( value ) );
                    });

					titleField = widget.find( '.frm_list_items_title_id' );
                    titleField.find( 'option' ).remove().end();
                    titleField.append( jQuery( '<option></option>' ) );
                    jQuery.each( opts.titleValues, function( key, value ) {
                        titleField.append( jQuery( '<option></option>' ).attr( 'value', key ).text( value ) );
                    });
                }
            });
        }
    }

    return {
        init: function() {
            jQuery( document ).on( 'click', '.frm_list_items_cat_list', toggleCatOpt );
            jQuery( document ).on( 'change', '.frm_list_items_display_id', getFields );
        }
    };
}
if ( typeof adminpage !== 'undefined' && adminpage === 'widgets-php' ) {
    window.frmWidgets = frmWidgetsJS();
    window.frmWidgets.init();
}
