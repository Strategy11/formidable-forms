jQuery(document).ready(function(){
    var installLink = document.getElementById('frm_install_link');
    if(installLink !== null){
        jQuery(installLink).click(frmInstallPro);
    }

	var deauthLink = jQuery('.frm_deauthorize_link');
	if(deauthLink.length){
		deauthLink.click(frmDeauthorizeNow);
	}

    if(typeof tb_remove == 'function') {
        frmAdminPopup.init();
    }
});

function frm_install_now(){
	var $msg = jQuery(document.getElementById('frm_install_message'));
	$msg.html('<div class="frm_plugin_updating">'+frmGlobal.updating_msg+'<div class="spinner frm_spinner"></div></div>');
	jQuery.ajax({
		type:'POST',url:ajaxurl,
		data:{action:'frm_install',nonce:frmGlobal.nonce},
		success:function(){$msg.fadeOut('slow');}
	});
	return false;
}

function frmInstallPro( e ){
	var plugin = this.getAttribute('data-prourl');
	if ( plugin === '' ) {
		return true;
	}

	e.preventDefault();

	var $msg = jQuery(document.getElementById('frm_install_message'));
	$msg.html('<div class="frm_plugin_updating">'+frmGlobal.updating_msg+'<div class="spinner frm_spinner"></div></div>');
	$msg.fadeIn('slow');

	jQuery.ajax({
		url: ajaxurl,
		type: 'POST',
		async: true,
		cache: false,
		dataType: 'json',
		data: {
			action: 'frm_install_addon',
			nonce:  frmGlobal.nonce,
			plugin: plugin
		},
		success: function() {
			$msg.fadeOut('slow');
			$msg.parent().fadeOut('slow');
		},
		error: function(xhr, textStatus, e) {
			$msg.fadeOut('slow');
		}
	});
	return false;
}

function frmDeauthorizeNow(){
    if(!confirm(frmGlobal.deauthorize)){
	    return false;
    }
    jQuery(this).html('<span class="spinner"></span>');
    jQuery.ajax({
        type:'POST',url:ajaxurl,
        data:{action:'frm_deauthorize',nonce:frmGlobal.nonce},
        success:function(msg){jQuery('.error').fadeOut('slow');}
    });
    return false;
}

function frmSelectSubnav(){
    var frmMenu = document.getElementById('toplevel_page_formidable');
    jQuery(frmMenu).removeClass('wp-not-current-submenu').addClass('wp-has-current-submenu wp-menu-open');
    jQuery('#toplevel_page_formidable a.wp-has-submenu').removeClass('wp-not-current-submenu').addClass('wp-has-current-submenu wp-menu-open');
}

function frmCreatePostEntry(id,post_id){
    jQuery('#frm_create_entry p').replaceWith('<img src="'+ frmGlobal.url +'/images/wpspin_light.gif" alt="'+ frmGlobal.loading +'" />');
    jQuery.ajax({
        type:'POST',url:ajaxurl,
        data:{action:'frm_create_post_entry',id:id,post_id:post_id,nonce:frmGlobal.nonce},
        success:function(msg){jQuery(document.getElementById('frm_create_entry')).fadeOut('slow');}
    });
}

function frmAdminPopupJS(){
    function switchSc(){
        jQuery('.frm_switch_sc').removeClass( 'active' );
        jQuery(this).addClass( 'active' );
        toggleMenu();
        jQuery('#frm_popup_content .media-frame-title h1').html(jQuery(this).children('.howto').text() +' <span class="spinner" style="float:left;"></span>');
        var val = this.id.replace('sc-link-', '');
        populateOpts(val);
        return false;
    }

    function populateOpts(val){
        document.getElementById('frm_complete_shortcode').value = '['+ val +']';
        jQuery('.frm_shortcode_option').hide();

        var $settings = document.getElementById('sc-opts-'+ val);
        if($settings !== null){
            $settings.style.display = '';
            jQuery(document.getElementById('sc-'+ val)).click();
        }else{
            var $scOpts = jQuery(document.getElementById('frm_shortcode_options'));
            var $spinner = jQuery('.media-frame-title .spinner');
            $spinner.show();
            jQuery.ajax({
    		    type:'POST',url:ajaxurl,
    		    data:{action:'frm_get_shortcode_opts', shortcode:val, nonce:frmGlobal.nonce},
    		    success:function(html){
    		        $spinner.hide();
    				$scOpts.append(html);
    				jQuery(document.getElementById('sc-'+ val)).click();
    			}
    		});
    	}
    }

    function addToShortcode(){
        var sc = jQuery('input[name=frmsc]:checked').val();
        var inputs = jQuery(document.getElementById('sc-opts-'+sc)).find('input, select');
        var output = '['+sc;
        inputs.each(function(){
            var $thisInput = jQuery(this);
            var attrId = this.id;
            if ( attrId.indexOf('frmsc_') === 0){
                var attrName = attrId.replace('frmsc_'+ sc +'_', '');
                var attrVal = $thisInput.val();

                if(($thisInput.attr('type') == 'checkbox' && !this.checked) || (($thisInput.attr('type') == 'text' || $thisInput.is('select')) && attrVal === '')){
                }else{
                    output += ' '+ attrName +'="'+ attrVal +'"';
                }
            }
        });
        output += ']';
        document.getElementById('frm_complete_shortcode').value = output;
    }

    function insertShortcode(){
        var win = window.dialogArguments || opener || parent || top;
        win.send_to_editor(document.getElementById('frm_complete_shortcode').value);
    }

    function getFieldSelection(){
        var form_id = this.value;
        if(form_id){
            var thisId = this.id;
            jQuery.ajax({
                type:'POST',url:ajaxurl,
                data:{action:'frm_get_field_selection',field_id:0,form_id:form_id,nonce:frmGlobal.nonce},
                success:function(msg){
                    var baseId = thisId.replace( '_form', '' );
                    msg = msg.replace('name="field_options[form_select_0]"', 'id="frmsc_' + baseId + '_fields"');
                    jQuery(document.getElementById(baseId+'_fields_container')).html(msg);
                }
            });
        }
    }

	function toggleMenu(){
		jQuery(document.getElementById('frm_popup_content')).find( '.media-menu' ).toggleClass( 'visible' );
	}

    return {
        init: function(){
            jQuery('.frm_switch_sc').click(switchSc);
            jQuery('.button.frm_insert_form').click(function(){
                populateOpts('formidable' );
            });
            jQuery(document.getElementById('frm_insert_shortcode')).click(insertShortcode);

            var $scOptsDiv = jQuery(document.getElementById('frm_shortcode_options'));
            $scOptsDiv.on('change', 'select, input', addToShortcode);
            $scOptsDiv.on('change', '.frm_get_field_selection', getFieldSelection);

            jQuery('#frm_popup_content .media-modal-close').click(tb_remove);
            jQuery('#frm_popup_content .media-frame-title h1').click(toggleMenu);
        }
    };
}
var frmAdminPopup = frmAdminPopupJS();

function frmWidgetsJS(){
    function toggleCatOpt(){
        var catOpts = jQuery(this).closest('.widget-content').children('.frm_list_items_hide_cat_opts');
        if(this.checked){
            catOpts.fadeIn();
        }else{
            catOpts.fadeOut();
        }
    }

    function getFields(){
        var display_id = this.value;
        if(display_id !== ''){
            var widget = jQuery(this).closest('.widget-content');

            jQuery.ajax({
                type:'POST', url:ajaxurl,
                dataType: 'json',
                data:{action:'frm_get_dynamic_widget_opts',display_id:display_id,nonce:frmGlobal.nonce},
                success:function(opts){
                    var catField = widget.find('.frm_list_items_cat_id');
                    catField.find('option').remove().end();
                    catField.append(jQuery('<option></option>'));
                    jQuery.each(opts.catValues, function(key, value) {   
                        catField.append(jQuery('<option></option>').attr('value', key).text(value)); 
                    });

                    var titleField = widget.find('.frm_list_items_title_id');
                    titleField.find('option').remove().end();
                    titleField.append(jQuery('<option></option>'));
                    jQuery.each(opts.titleValues, function(key, value) {   
                        titleField.append(jQuery('<option></option>').attr('value', key).text(value)); 
                    });
                }
            });
        }
    }
    
    return {
        init: function(){
            jQuery(document).on('click', '.frm_list_items_cat_list', toggleCatOpt);
            jQuery(document).on('change', '.frm_list_items_display_id', getFields);
        }
    };
}
if(typeof adminpage != 'undefined' && adminpage == 'widgets-php'){
    var frmWidgets = frmWidgetsJS();
    frmWidgets.init();
}

