<script type="text/javascript">
function frmAdminPopupJS(){
    function switchSc(){
        jQuery('.frm_switch_sc').removeClass( 'active' );
        jQuery(this).addClass( 'active' );
        toggleMenu();
        jQuery('#frm_popup_content .media-frame-title h1').html(jQuery(this).children('.howto').text() +' <span class="spinner" style="float:left;"></span><span class="dashicons dashicons-arrow-down"></span>');
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
    		    data:{action:'frm_get_shortcode_opts', shortcode:val},
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

                if(($thisInput.attr('type') == 'checkbox' && !this.checked) || (($thisInput.attr('type') == 'text' || $thisInput.is('select')) && '' == attrVal)){
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
	        	data:{action:'frm_get_field_selection',field_id:0,form_id:form_id},
	        	success:function(msg){
	        	    msg = msg.replace('name="field_options[form_select_0]"', 'id="'+ thisId.replace('frm_form_', '') +'"');
	        	    jQuery(document.getElementById(thisId+'_fields')).html(msg);
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
            jQuery('.button.frm_insert_form').click(function(){populateOpts('formidable')});
            jQuery(document.getElementById('frm_insert_shortcode')).click(insertShortcode);

            var $scOptsDiv = jQuery(document.getElementById('frm_shortcode_options'));
            $scOptsDiv.on('change', 'select, input', addToShortcode);
            $scOptsDiv.on('change', '.frm_get_field_selection', getFieldSelection);

            jQuery('#frm_popup_content .media-modal-close').click(tb_remove);
            jQuery('#frm_popup_content .media-frame-title h1').click(toggleMenu);
        }
    }
}

var frmAdminPopup = frmAdminPopupJS();
jQuery(document).ready(function($){
    if(typeof tb_remove == 'function') {
	    frmAdminPopup.init();
    }
});
</script>
<style type="text/css">
#TB_ajaxContent{height:auto !important;width:auto !important;}
</style>
<div id="frm_insert_form" style="display:none;">
    <div id="frm_popup_content">
    <div class="media-modal wp-core-ui">
    	<a href="#" class="media-modal-close"><span class="media-modal-icon"><span class="screen-reader-text"><?php _e('Close panel', 'formidable') ?></span></span></a>

    	<div class="media-modal-content">
    	<div class="media-frame mode-select wp-core-ui hide-router">

        <div id="frm_insert_form_content">

        <div class="media-frame-menu">
        <div class="media-menu">
            <?php foreach ( $shortcodes as $shortcode => $labels ) { ?>
            <a href="#" class="media-menu-item frm_switch_sc" id="sc-link-<?php echo esc_attr( $shortcode ) ?>">
                <?php echo esc_html( $labels['name'] ) ?>
                <span class="howto"><?php echo esc_html( $labels['label'] ) ?></span>
            </a>
            <?php } ?>
        <div class="clear"></div>
        </div>
        </div>

        <div class="media-frame-title"><h1><?php _e('Insert a Form', 'formidable') ?> <span class="spinner" style="float:left;"></span><span class="dashicons dashicons-arrow-down"></span></h1></div>

        <div class="media-frame-content">
        <div class="attachments-browser">
        <div id="frm_shortcode_options" class="media-embed">

        </div>
        </div>
        </div>

        <div class="media-frame-toolbar">
            <div class="media-toolbar">
            <div class="media-toolbar-secondary">
                <input type="text" value="" id="frm_complete_shortcode" />
            </div>
            <div class="media-toolbar-primary search-form">
                <a href="javascript:void(0);" class="button-primary button button-large media-button-group" id="frm_insert_shortcode"><?php _e('Insert into Post', 'formidable') ?></a>
            </div>
            </div>
        </div>
        </div>
        </div>

        </div>
    </div>
    </div>
</div>