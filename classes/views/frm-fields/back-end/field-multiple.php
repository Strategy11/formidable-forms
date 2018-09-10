<?php
$field['default_value'] = maybe_unserialize( $field['default_value'] );
if ( isset( $field['post_field'] ) && $field['post_field'] == 'post_category' ) {
	$type = $field['type'];
	do_action( 'frm_after_checkbox', compact( 'field', 'field_name', 'type' ) );
} else {
	do_action( 'frm_add_multiple_opts_labels', $field );
	?>
    <ul id="frm_field_<?php echo esc_attr( $field['id'] ) ?>_opts" class="frm_sortable_field_opts frm_clear<?php echo ( count( $field['options'] ) > 10 ) ? ' frm_field_opts_list' : ''; ?>">
		<?php FrmFieldsHelper::show_single_option( $field ); ?>
    </ul>
<?php
}
