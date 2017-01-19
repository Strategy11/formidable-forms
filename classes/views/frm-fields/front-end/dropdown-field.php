<?php

$read_only = false;
if ( isset($field['post_field']) && $field['post_field'] == 'post_category' && FrmAppHelper::pro_is_installed() ) {
	echo FrmProPost::get_category_dropdown( $field, array( 'location' => 'front', 'name' => $field_name, 'id' => $html_id ) );
} else {
	if ( FrmAppHelper::pro_is_installed() && FrmField::is_read_only( $field ) && ! FrmAppHelper::is_admin() ) {
		$read_only = true;

		echo FrmProDropdownFieldsController::get_hidden_fields_with_readonly_values( $field, $field_name, $html_id ); ?>
		<select <?php do_action('frm_field_input_html', $field) ?>> <?php

	} else { ?>
		<select name="<?php echo esc_attr( $field_name ) ?>" id="<?php echo esc_attr( $html_id ) ?>" <?php do_action('frm_field_input_html', $field) ?>>
	<?php   }

	$other_opt = false;
	$other_checked = false;
	foreach ( $field['options'] as $opt_key => $opt ) {
		$field_val = apply_filters( 'frm_field_value_saved', $opt, $opt_key, $field );
		$opt = apply_filters( 'frm_field_label_seen', $opt, $opt_key, $field );
		$selected = FrmAppHelper::check_selected( $field['value'], $field_val );
		if ( $other_opt === false ) {
			$other_args = FrmFieldsHelper::prepare_other_input( compact( 'field', 'field_name', 'opt_key' ), $other_opt, $selected );
			if ( FrmFieldsHelper::is_other_opt( $opt_key ) && $selected ) {
				$other_checked = true;
			}
		}
		?>
		<option value="<?php echo esc_attr($field_val) ?>" <?php echo $selected ? ' selected="selected"' : ''; ?><?php echo ( FrmFieldsHelper::is_other_opt( $opt_key ) ) ? ' class="frm_other_trigger"' : '';?>><?php echo esc_html( $opt == '' ? ' ' : $opt ); ?></option>
		<?php
	} ?>
	</select>
	<?php

	FrmFieldsHelper::include_other_input( array(
		'other_opt' => $other_opt, 'read_only' => $read_only,
		'checked' => $other_checked, 'name' => $other_args['name'],
		'value' => $other_args['value'], 'field' => $field,
		'html_id' => $html_id, 'opt_key' => false,
	) );
}
