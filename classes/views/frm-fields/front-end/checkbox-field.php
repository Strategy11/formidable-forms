<?php

if ( isset( $field['post_field'] ) && $field['post_field'] == 'post_category' ) {
	$type = $field['type'];
	do_action( 'frm_after_checkbox', compact( 'field', 'field_name', 'type' ) );
} elseif ( $field['options'] ) {
	foreach ( $field['options'] as $opt_key => $opt ) {
		if ( isset( $shortcode_atts ) && isset( $shortcode_atts['opt'] ) && ( $shortcode_atts['opt'] !== $opt_key ) ) {
			continue;
		}

		$field_val = FrmFieldsHelper::get_value_from_array( $opt, $opt_key, $field );
		$opt = FrmFieldsHelper::get_label_from_array( $opt, $opt_key, $field );

		$checked = FrmAppHelper::check_selected( $field['value'], $field_val ) ? ' checked="checked"' : '';

		// Check if other opt, and get values for other field if needed
		$other_opt = false;
		$other_args = FrmFieldsHelper::prepare_other_input( compact( 'field', 'field_name', 'opt_key' ), $other_opt, $checked );

		?>
		<div class="<?php echo esc_attr( apply_filters( 'frm_checkbox_class', 'frm_checkbox', $field, $field_val ) ) ?>" id="<?php echo esc_attr( FrmFieldsHelper::get_checkbox_id( $field, $opt_key ) ) ?>"><?php

			if ( ! isset( $shortcode_atts ) || ! isset( $shortcode_atts['label'] ) || $shortcode_atts['label'] ) {
				?><label for="<?php echo esc_attr( $html_id ) ?>-<?php echo esc_attr( $opt_key ) ?>"><?php
			}

			?><input type="checkbox" name="<?php echo esc_attr( $field_name ); ?>[<?php echo esc_attr( $other_opt ? $opt_key : '' ); ?>]" id="<?php echo esc_attr( $html_id ); ?>-<?php echo esc_attr( $opt_key ); ?>" value="<?php echo esc_attr( $field_val ); ?>"<?php echo $checked; // WPCS: XSS ok. ?> <?php do_action( 'frm_field_input_html', $field ); ?> /><?php

			if ( ! isset( $shortcode_atts ) || ! isset( $shortcode_atts['label'] ) || $shortcode_atts['label'] ) {
				echo ' ' . FrmAppHelper::kses( $opt, 'all' ) . '</label>'; // WPCS: XSS ok.
			}

			FrmFieldsHelper::include_other_input(
				array(
					'other_opt' => $other_opt,
					'read_only' => $read_only,
					'checked'   => $checked,
					'name'      => $other_args['name'],
					'value'     => $other_args['value'],
					'field'     => $field,
					'html_id'   => $html_id,
					'opt_key'   => $opt_key,
					'opt_label' => $opt,
				)
			);

			unset( $other_opt, $other_args, $checked );

		?></div>
<?php
	}
}
