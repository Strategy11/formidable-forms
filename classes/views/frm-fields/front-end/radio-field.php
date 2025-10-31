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
	$field_choices_limit_reached_statuses = FrmFieldsController::get_choices_limit_reached_statuses( $field );

	foreach ( $field_choices_limit_reached_statuses as $choices_limit_reached_status ) {
		if ( ! $choices_limit_reached_status ) {
			break;
		}
	}
	if ( current( $field_choices_limit_reached_statuses ) ) {
		echo esc_html( FrmFieldsHelper::get_error_msg( $field, 'choice_limit_msg' ) );
		return;
	}
	foreach ( $field['options'] as $opt_key => $opt ) {
		$choice_limit_reached = $field_choices_limit_reached_statuses[ $opt_key ] ?? false;

		$atts = isset( $shortcode_atts ) && is_array( $shortcode_atts ) ? $shortcode_atts : array();
		if ( FrmFieldsController::should_hide_field_choice( $choice_limit_reached, $atts, $opt_key, $field['form_id'] ) ) {
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
		$label = apply_filters( 'frm_choice_field_option_label', $opt, compact( 'field' ) );
		?>
		<div class="<?php echo esc_attr( apply_filters( 'frm_radio_class', 'frm_radio', $field, $field_val ) ); ?>" id="<?php echo esc_attr( FrmFieldsHelper::get_checkbox_id( $field, $opt_key, 'radio' ) ); ?>"><?php

		if ( ! isset( $shortcode_atts ) || ! isset( $shortcode_atts['label'] ) || $shortcode_atts['label'] ) {
			$label_attributes = array(
				'for' => $html_id . '-' . $opt_key,
			);
			if ( $read_only ) {
				$label_attributes['class'] = 'frm-label-disabled';
			}
			?>
			<label <?php FrmAppHelper::array_to_html_params( $label_attributes, true ); ?>>
			<?php
		}
		$checked = FrmAppHelper::check_selected( $field['value'], $field_val ) ? 'checked="checked" ' : ' ';

		$other_opt  = false;
		$other_args = FrmFieldsHelper::prepare_other_input( compact( 'field_name', 'opt_key', 'field' ), $other_opt, $checked );
		?>
		<input type="radio" name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( $html_id . '-' . $opt_key ); ?>" value="<?php echo esc_attr( $field_val ); ?>"
		<?php
		do_action( 'frm_field_input_html', $field );
		echo $choice_limit_reached ? ' disabled="disabled" ' : $checked . ' '; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>/><?php

		if ( ! isset( $shortcode_atts ) || ! isset( $shortcode_atts['label'] ) || $shortcode_atts['label'] ) {
	echo ' ' . FrmAppHelper::kses( $label, 'all' ) . '</label>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

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
