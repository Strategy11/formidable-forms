<?php
if(empty($values) or !isset($values['fields']) or empty($values['fields'])){ ?>
<div class="frm_forms with_frm_style" id="frm_form_<?php echo $form->id ?>_container">
    <div class="frm_error_style"><strong><?php _e('Oops!', 'formidable') ?></strong> <?php printf(__('You did not add any fields to your form. %1$sGo back%2$s and add some.', 'formidable'), '<a href="'. admin_url('?page=formidable&frm_action=edit&id='. $form->id) .'">', '</a>') ?>
    </div>
</div>    
<?php
    return;
} ?>
<div class="frm_forms<?php echo ($values['custom_style']) ? ' with_frm_style' : ''; ?>" id="frm_form_<?php echo $form->id ?>_container">
<form enctype="<?php echo apply_filters('frm_form_enctype', 'multipart/form-data', $form) ?>" method="post" class="frm-show-form <?php do_action('frm_form_classes', $form) ?>" id="form_<?php echo $form->form_key ?>" <?php echo ($frm_settings->use_html) ? '' : 'action=""'; ?>>
<?php 
include(FrmAppHelper::plugin_path() .'/classes/views/frm-entries/errors.php');
$form_action = 'create';
require(FrmAppHelper::plugin_path() .'/classes/views/frm-entries/form.php'); 
?>
</form>
</div>