<div id="frm_adv_info" class="postbox">
    <div class="handlediv" title="<?php _e('Click to toggle', 'formidable') ?>"><br/></div><h3 class="hndle"><span><?php _e('Customization', 'formidable') ?></span></h3>
    <div class="inside">
    <?php if($frm_vars['pro_is_installed']){
        FrmProDisplaysController::mb_tags_box($id);
    }else{ ?>
        <div id="taxonomy-linkcategory" class="categorydiv">
            <ul id="category-tabs" class="category-tabs frm-category-tabs">
        		<li class="tabs" ><a href="#frm-insert-fields-box" id="frm_insert_fields_tab" ><?php _e( 'Insert Fields', 'formidable' ); ?></a></li>
        		<li class="hide-if-no-js"><a href="#frm-html-tags" id="frm_html_tags_tab" ><?php _e( 'HTML Tags', 'formidable' ); ?></a></li>
        	</ul>
        	<div id="frm-insert-fields-box" class="tabs-panel" style="max-height:none;padding-right:0;">
        	    <br/><br/>
                <?php FrmAppController::update_message('insert field values into your messages'); ?>
        	</div>
        	<?php include(FrmAppHelper::plugin_path() .'/classes/views/frm-forms/mb_html_tab.php'); ?>
        </div>
<?php 
    } ?>


</div>
</div>