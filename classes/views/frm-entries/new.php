<div class="frm_forms <?php echo FrmFormsHelper::get_form_style_class($values); ?>" id="frm_form_<?php echo esc_attr( $form->id ) ?>_container">
<?php
if ( ! isset( $include_form_tag ) || $include_form_tag ) {
?>
<form enctype="<?php echo esc_attr( apply_filters( 'frm_form_enctype', 'multipart/form-data', $form ) ) ?>" method="post" class="frm-show-form <?php do_action('frm_form_classes', $form) ?>" id="form_<?php echo esc_attr( $form->form_key ) ?>" <?php echo $frm_settings->use_html ? '' : 'action=""'; ?>>
<?php
} else { ?>
<div id="form_<?php echo esc_attr( $form->form_key ) ?>" class="frm-show-form <?php do_action('frm_form_classes', $form) ?>" >
<?php
}

include(FrmAppHelper::plugin_path() .'/classes/views/frm-entries/errors.php');
$form_action = 'create';
require(FrmAppHelper::plugin_path() .'/classes/views/frm-entries/form.php');

if ( ! isset( $include_form_tag ) || $include_form_tag ) {
?>
</form>
<?php
} else { ?>
</div>
<?php
}
?>
</div>
