<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$field_option_count = is_array( $args['field']['options'] ) ? count( $args['field']['options'] ) : 0;
?>
<span class="frm-bulk-edit-link <?php echo empty( FrmField::get_option( $args['field'], 'image_options' ) ) ? '' : 'frm_hidden'; ?> ">
	<a href="#" title="<?php echo esc_attr( $option_title ); ?>" class="frm-bulk-edit-link">
		<?php echo esc_html( $this->get_bulk_edit_string() ); ?>
	</a>
</span>

<?php do_action( 'frm_add_multiple_opts_labels', $args['field'] ); ?>

<ul id="frm_field_<?php echo esc_attr( $args['field']['id'] ); ?>_opts" class="frm_sortable_field_opts frm_clear<?php echo ( $field_option_count > 10 ) ? ' frm_field_opts_list' : ''; ?> frm_add_remove" data-key="<?php echo esc_attr( $args['field']['field_key'] ); ?>">
	<?php $this->show_single_option( $args ); ?>
</ul>

<div class="frm6 frm_form_field frm_add_opt_container">
	<a href="javascript:void(0);" data-opttype="single" class="frm_cb_button frm-small-add frm_add_opt frm6 frm_form_field" id="frm_add_opt_<?php echo esc_attr( $args['field']['id'] ); ?>">
		<span class="frm_icon_font frm_add_tag"></span>
		<?php echo esc_html( $this->get_add_option_string() ); ?>
	</a>
</div>
