<?php
if ( isset( $args['field']['post_field'] ) && $args['field']['post_field'] == 'post_category' ) {
	?>
	<div class="frm-inline-message" id="frm_has_hidden_options_<?php echo esc_attr( $args['field']['id'] ); ?>">
		<?php echo FrmFieldsHelper::get_term_link( $args['field']['taxonomy'] ); // WPCS: XSS ok. ?>
	</div>
	<?php
} elseif ( in_array( $args['field']['type'], array( 'select', 'radio', 'checkbox' ) ) ) {
	$has_options = ! empty( $args['field']['options'] );
	$short_name  = FrmAppHelper::truncate( strip_tags( str_replace( '"', '&quot;', $args['field']['name'] ) ), 20 );

	/* translators: %s: Field name */
	$option_title = sprintf( __( '%s Options', 'formidable' ), $short_name );

	?>
	<span class="frm-bulk-edit-link">
		<a href="#" title="<?php echo esc_attr( $option_title ); ?>" class="frm-bulk-edit-link">
			<?php esc_html_e( 'Bulk Edit Options', 'formidable' ); ?>
		</a>
	</span>
	<?php do_action( 'frm_add_multiple_opts_labels', $args['field'] ); ?>
	<ul id="frm_field_<?php echo esc_attr( $args['field']['id'] ); ?>_opts" class="frm_sortable_field_opts frm_clear<?php echo ( count( $args['field']['options'] ) > 10 ) ? ' frm_field_opts_list' : ''; ?> frm_add_remove" data-key="<?php echo esc_attr( $args['field']['field_key'] ); ?>">
		<?php FrmFieldsHelper::show_single_option( $args['field'] ); ?>
	</ul>
	<div class="frm6 frm_form_field">
		<a href="javascript:void(0);" data-opttype="single" class="frm_cb_button frm-small-add frm_add_opt frm6 frm_form_field" id="frm_add_opt_<?php echo esc_attr( $args['field']['id'] ); ?>">
			<span class="frm_icon_font frm_add_tag"></span>
			<?php esc_html_e( 'Add Option', 'formidable' ); ?>
		</a>
	</div>

	<?php
}
