<?php
if ( empty($values) || ! isset($values['fields']) || empty($values['fields']) ) { ?>
<div class="frm_forms <?php echo FrmFormsHelper::get_form_style_class($form); ?>" id="frm_form_<?php echo esc_attr( $form->id ) ?>_container">
	<div class="frm_error_style"><strong><?php _e( 'Oops!', 'formidable' ) ?></strong> <?php printf( __( 'You did not add any fields to your form. %1$sGo back%2$s and add some.', 'formidable' ), '<a href="' . esc_url( admin_url( '?page=formidable&frm_action=edit&id=' . $form->id ) ) . '">', '</a>' ) ?>
    </div>
</div>
<?php
    return;
} ?>
<div class="frm_forms <?php echo FrmFormsHelper::get_form_style_class($values); ?>" id="frm_form_<?php echo esc_attr( $form->id ) ?>_container">
<form enctype="<?php echo apply_filters('frm_form_enctype', 'multipart/form-data', $form) ?>" method="post" class="frm-show-form <?php do_action('frm_form_classes', $form) ?>" id="form_<?php echo esc_attr( $form->form_key ) ?>" <?php echo $frm_settings->use_html ? '' : 'action=""'; ?>>
<?php
include(FrmAppHelper::plugin_path() .'/classes/views/frm-entries/errors.php');
$form_action = 'create';
require(FrmAppHelper::plugin_path() .'/classes/views/frm-entries/form.php');
?>
</form>
</div>