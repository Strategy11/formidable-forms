<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Show the radio field on the front-end.
 * Extra line breaks show as space on the front-end when
 * the form is double filtered and not minimized.
 *
 * @phpcs:disable Generic.WhiteSpace.ScopeIndent
 */

if ( isset( $field['post_field'] ) && $field['post_field'] === 'post_category' ) {
	$type = $field['type'];
	do_action( 'frm_after_checkbox', compact( 'field', 'field_name', 'type' ) );
} elseif ( is_array( $field['options'] ) ) {
	$field_choices_limit_reached_statuses = FrmFieldsHelper::get_choices_limit_reached_statuses( $field );

	if ( FrmFieldsHelper::should_skip_rendering_options_for_field( $field_choices_limit_reached_statuses, $field ) ) {
		return;
	}

	foreach ( $field['options'] as $opt_key => $opt ) {
		if ( isset( $shortcode_atts ) && isset( $shortcode_atts['opt'] ) && $shortcode_atts['opt'] !== $opt_key ) {
			continue;
		}

		$choice_limit_reached = $field_choices_limit_reached_statuses[ $opt_key ] ?? false;

		if ( FrmFieldsHelper::should_hide_field_choice( $choice_limit_reached, $field['form_id'] ) ) {
			continue;
		}

		$field_val = FrmFieldsHelper::get_value_from_array( $opt, $opt_key, $field );
		$opt       = FrmFieldsHelper::get_label_from_array( $opt, $opt_key, $field );

		/**
		 * Allows changing the HTML of option label in choice field (radio, checkbox,...).
		 *
		 * @since 5.0.04
		 *
		 * @param string $label Label HTML.
		 * @param array  $args  The arguments. Contains `field`.
		 */
		$label = apply_filters( 'frm_choice_field_option_label', $opt, compact( 'field', 'field_val' ) );
		?>
		<div class="<?php echo esc_attr( apply_filters( 'frm_radio_class', 'frm_radio', $field, $field_val ) ); ?>" id="<?php echo esc_attr( FrmFieldsHelper::get_checkbox_id( $field, $opt_key, 'radio' ) ); ?>"><?php

		$checked = FrmAppHelper::check_selected( $field['value'], $field_val ) ? 'checked="checked" ' : ' ';

		$should_echo_disabled_att = FrmFieldsHelper::should_echo_disabled_attribute( $choice_limit_reached, $checked );

		if ( ! isset( $shortcode_atts ) || ! isset( $shortcode_atts['label'] ) || $shortcode_atts['label'] ) {
			$label_attributes = array(
				'for' => $html_id . '-' . $opt_key,
			);

			if ( $read_only || $should_echo_disabled_att ) {
				$label_attributes['class'] = 'frm-label-disabled';
			}
			?>
			<label <?php FrmAppHelper::array_to_html_params( $label_attributes, true ); ?>>
			<?php
		}

		$other_opt  = false;
		$other_args = FrmFieldsHelper::prepare_other_input( compact( 'field_name', 'opt_key', 'field' ), $other_opt, $checked );
		?>
		<input type="radio" name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( $html_id . '-' . $opt_key ); ?>" value="<?php echo esc_attr( $field_val ); ?>"
		<?php
		do_action( 'frm_field_input_html', $field );
		echo $checked; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		if ( $should_echo_disabled_att ) {
			echo 'disabled="disabled" ';
		}
		?>/><?php

		if ( ! isset( $shortcode_atts ) || ! isset( $shortcode_atts['label'] ) || $shortcode_atts['label'] ) {
	echo ' ' . FrmAppHelper::kses( $label, 'all' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		do_action( 'frm_after_option_input', $field, $opt_key );

		echo '</label>';

		$other_args = array(
			'other_opt' => $other_opt,
			'read_only' => $read_only,
			'checked'   => $checked,
			'name'      => $other_args['name'],
			'value'     => $other_args['value'],
			'field'     => $field,
			'html_id'   => $html_id,
			'opt_key'   => $opt_key,
			'opt_label' => $opt,
		);
		FrmFieldsHelper::include_other_input( $other_args );

		unset( $other_opt, $other_args );
		?></div>
<?php
	}//end foreach
}//end if
