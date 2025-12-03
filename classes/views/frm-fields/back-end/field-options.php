<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$field_option_count = is_array( $args['field']['options'] ) ? count( $args['field']['options'] ) : 0;
?>
<span class="frm-bulk-edit-link <?php echo $should_hide_bulk_edit ? 'frm_hidden' : ''; ?>">
	<a href="#" title="<?php echo esc_attr( $option_title ); ?>" class="frm-h-stack frm-justify-end frm-bulk-edit-link">
		<span>
			<?php
			FrmAppHelper::icon_by_class(
				'frmfont frm_simple_pencil_icon frm_svg24',
				array(
					'echo'       => true,
					'aria-label' => __( 'Bulk Edit', 'formidable' ),
				)
			);
			?>
		</span>
		<span><?php echo esc_html( $this->get_bulk_edit_string() ); ?></span>
	</a>
</span>

<?php do_action( 'frm_add_multiple_opts_labels', $args['field'] ); ?>

<span class="frm-hr frm-mb-sm"></span>

<ul id="frm_field_<?php echo esc_attr( $args['field']['id'] ); ?>_opts" class="frm_sortable_field_opts frm_clear<?php echo $field_option_count > 10 ? ' frm_field_opts_list' : ''; ?> frm_add_remove" data-key="<?php echo esc_attr( $args['field']['field_key'] ); ?>">
	<?php $this->show_single_option( $args ); ?>
</ul>

<?php
// Backwards compatibility "@since 6.24".
if ( FrmAppHelper::pro_is_connected() && ! is_callable( array( 'FrmProHtmlHelper', 'echo_radio_group' ) ) && 'product' === $args['field']['type'] ) {
	?>
	<div class="frm6 frm_form_field frm_add_opt_container">
		<a href="javascript:void(0);" data-opttype="single" class="frm-h-stack frm_cb_button frm_add_opt frm6 frm_form_field frm-add-option-legacy" id="frm_add_opt_<?php echo esc_attr( $args['field']['id'] ); ?>">
			<span><?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_plus1_icon frm_add_tag frm_svg13' ); ?></span>
			<span><?php echo esc_html( $this->get_add_option_string() ); ?></span>
		</a>
	</div>
<?php } ?>
