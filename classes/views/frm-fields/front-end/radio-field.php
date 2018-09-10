<?php

if ( isset( $field['post_field'] ) && $field['post_field'] == 'post_category' ) {
	$type = $field['type'];
	do_action( 'frm_after_checkbox', compact( 'field', 'field_name', 'type' ) );
} elseif ( is_array( $field['options'] ) ) {
	foreach ( $field['options'] as $opt_key => $opt ) {
		if ( isset( $shortcode_atts ) && isset( $shortcode_atts['opt'] ) && ( $shortcode_atts['opt'] !== $opt_key ) ) {
			continue;
		}

		$field_val = FrmFieldsHelper::get_value_from_array( $opt, $opt_key, $field );
		$opt = FrmFieldsHelper::get_label_from_array( $opt, $opt_key, $field );
		?>
		<div class="<?php echo esc_attr( apply_filters( 'frm_radio_class', 'frm_radio', $field, $field_val ) ) ?>"><?php

			if ( ! isset( $shortcode_atts ) || ! isset( $shortcode_atts['label'] ) || $shortcode_atts['label'] ) {
				?><label for="<?php echo esc_attr( $html_id ) ?>-<?php echo esc_attr( $opt_key ) ?>"><?php
			}
			$checked = FrmAppHelper::check_selected( $field['value'], $field_val ) ? 'checked="checked" ' : ' ';

			$other_opt = false;
			$other_args = FrmFieldsHelper::prepare_other_input( compact( 'field_name', 'opt_key', 'field' ), $other_opt, $checked );
			?>
			<input type="radio" name="<?php echo esc_attr( $field_name ) ?>" id="<?php echo esc_attr( $html_id . '-' . $opt_key ) ?>" value="<?php echo esc_attr( $field_val ) ?>" <?php
			echo $checked; // WPCS: XSS ok.
			do_action( 'frm_field_input_html', $field );
			?>/><?php

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

			unset( $other_opt, $other_args );
		?></div>
<?php
	}
}
