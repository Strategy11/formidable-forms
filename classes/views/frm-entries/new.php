<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

FrmAntiSpam::maybe_init( $form->id );
?>
<div class="frm_forms <?php echo esc_attr( FrmFormsHelper::get_form_style_class( $values ) ); ?>" id="frm_form_<?php echo esc_attr( $form->id ); ?>_container" <?php echo wp_strip_all_tags( apply_filters( 'frm_form_div_attributes', '', $form ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
<?php if ( ! isset( $include_form_tag ) || $include_form_tag ) { ?>
<form enctype="<?php echo esc_attr( apply_filters( 'frm_form_enctype', 'multipart/form-data', $form ) ); ?>" method="post" class="frm-show-form <?php do_action( 'frm_form_classes', $form ); ?>" id="form_<?php echo esc_attr( $form->form_key ); ?>" <?php echo $frm_settings->use_html ? '' : 'action=""'; ?> <?php echo wp_strip_all_tags( apply_filters( 'frm_form_attributes', '', $form ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
<?php } else { ?>
<div id="form_<?php echo esc_attr( $form->form_key ); ?>" class="frm-show-form <?php do_action( 'frm_form_classes', $form ); ?>" >
	<?php
}

$message_placement = isset( $message_placement ) ? $message_placement : 'before';

if ( ! in_array( $message_placement, array( 'after', 'submit' ), true ) ) {
	include FrmAppHelper::plugin_path() . '/classes/views/frm-entries/errors.php';
}

$form_action = 'create';
require FrmAppHelper::plugin_path() . '/classes/views/frm-entries/form.php';

if ( $message_placement === 'after' ) {
	include FrmAppHelper::plugin_path() . '/classes/views/frm-entries/errors.php';
}

if ( ! isset( $include_form_tag ) || $include_form_tag ) {
	?>
</form>
<?php } else { ?>
</div>
<?php } ?>
</div>
