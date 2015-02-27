<div class="wrap">
    <div class="frmicon icon32"><br/></div>    
    <h2><?php _e('Build New Form', 'formidable') ?>
		<a href="?page=formidable-new" class="add-new-h2" style="visibility:hidden;"><?php _e('Add New', 'formidable'); ?></a>
	</h2>
    <?php 
    if ( !$form->is_template ) {
        FrmAppController::get_form_nav($id, true);
    }
	require(FrmAppHelper::plugin_path() .'/classes/views/shared/errors.php');
    
    if(version_compare( $GLOBALS['wp_version'], '3.3.3', '<')){ ?>
    <div id="poststuff" class="metabox-holder has-right-sidebar">
    <?php   
        require(FrmAppHelper::plugin_path() .'/classes/views/frm-forms/add_field_links.php'); 
    }else{ ?>
    <div id="poststuff">
<?php } ?>

    <div id="post-body" class="metabox-holder columns-2">
    <div id="post-body-content">
    <div class="frm_form_builder<?php echo (isset($form->options['custom_style']) && $form->options['custom_style']) ? ' with_frm_style' : ''; ?>">
        
            <p style="margin-top:0;">
                <input type="button" onclick="frmSubmit<?php echo (isset($form->options['ajax_load']) && $form->options['ajax_load']) ? 'Build' : 'NoAjax'; ?>(this)" value="<?php _e('Create', 'formidable') ?>" class="button-primary" />
                <?php _e('or', 'formidable') ?>
                <a class="button-secondary cancel" href="?page=formidable&amp;frm_action=destroy&amp;id=<?php echo $id; ?>"><?php _e('Cancel', 'formidable') ?></a>
                <span class="frm-loading-img"></span>
            </p>
            
        <form method="post" id="frm_build_form">
            <input type="hidden" name="frm_action" value="create" />
            <input type="hidden" name="action" value="create" />
            <input type="hidden" name="id" value="<?php echo $id; ?>" />

            <?php require(FrmAppHelper::plugin_path() .'/classes/views/frm-forms/form.php'); ?>

            <p>
                <input type="button" onclick="frmSubmit<?php echo (isset($form->options['ajax_load']) && $form->options['ajax_load']) ? 'Build' : 'NoAjax'; ?>(this)" value="<?php _e('Create', 'formidable') ?>" class="button-primary" />
                <?php _e('or', 'formidable') ?>
                <a class="button-secondary cancel" href="?page=formidable&amp;frm_action=destroy&amp;id=<?php echo $id; ?>"><?php _e('Cancel', 'formidable') ?></a>
                <span class="frm-loading-img"></span>
            </p>
        </form>
    </div>
    </div>
    <?php 
    if(version_compare( $GLOBALS['wp_version'], '3.3.2', '>'))
        require(FrmAppHelper::plugin_path() .'/classes/views/frm-forms/add_field_links.php'); 
    ?>
    </div>
    </div>
</div> 
