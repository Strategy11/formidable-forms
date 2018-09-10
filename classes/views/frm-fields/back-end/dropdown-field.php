<?php

if ( isset( $field['post_field'] ) && 'post_category' === $field['post_field'] && FrmAppHelper::pro_is_installed() ) {
	echo FrmProPost::get_category_dropdown( // WPCS: XSS ok.
		$field,
		array(
			'name'     => $field_name,
			'id'       => 'placeholder_id',
			'location' => 'form_builder',
		)
	);
} else {
?>
	<select id="frm_dropdown_<?php echo esc_attr( $field['id'] ) ?>"
		name="<?php echo esc_attr( $field_name ) . ( FrmField::is_option_true( $field, 'multiple' ) ? '[]' : '' ); ?>" <?php echo FrmField::is_option_true( $field, 'size' ) ? 'class="auto_width"' : ''; ?> <?php echo FrmField::is_option_true( $field, 'multiple' ) ? 'multiple="multiple"' : ''; ?>>
		<?php
		foreach ( $field['options'] as $opt_key => $opt ) {
			$field_val = apply_filters( 'frm_field_value_saved', $opt, $opt_key, $field );
			$opt = FrmFieldsHelper::get_label_from_array( $opt, $opt_key, $field );
			$selected = ( $field['default_value'] === $field_val || FrmFieldsHelper::get_other_val( array( 'opt_key', 'field' ) ) ) ? ' selected="selected"' : '';
			?>
			<option value="<?php echo esc_attr( $field_val ) ?>"<?php echo $selected; // WPCS: XSS ok. ?>><?php echo esc_html( $opt ) ?> </option>
		<?php } ?>
	</select>
<?php } ?>

<?php if ( $display['default_blank'] ) { ?>
	<span id="frm_clear_on_focus_<?php echo esc_attr( $field['id'] ) ?>" class="frm_clear_on_focus frm-show-click">
		<?php FrmFieldsHelper::show_default_blank_js( $field['default_blank'] ); ?>
	</span>
	<input type="hidden" name="field_options[default_blank_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['default_blank'] ) ?>" />
<?php } ?>
<div class="clear"></div>
<div class="frm-show-click frm_small_top_margin">
	<?php if ( ! isset( $field['post_field'] ) || 'post_category' !== $field['post_field'] ) { ?>
		<?php do_action( 'frm_add_multiple_opts_labels', $field ); ?>
		<ul id="frm_field_<?php echo esc_attr( $field['id'] ) ?>_opts" class="frm_sortable_field_opts<?php echo ( count( $field['options'] ) > 10 ) ? ' frm_field_opts_list' : ''; ?>">
			<?php FrmFieldsHelper::show_single_option( $field ); ?>
		</ul>
	<?php } ?>
</div>
