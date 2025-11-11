<?php
/**
 * @package Formidable
 *
 * @var array $field
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( isset( $field['post_field'] ) && $field['post_field'] === 'post_category' && FrmAppHelper::pro_is_installed() ) {
	echo FrmProPost::get_category_dropdown( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$field,
		array(
			'location' => 'front',
			'name'     => $field_name, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'id'       => $html_id, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		)
	);
} else {
	$field_choices_limit_reached_statuses = FrmFieldsController::get_choices_limit_reached_statuses( $field );

	foreach ( $field_choices_limit_reached_statuses as $choices_limit_reached_status ) {
		if ( ! $choices_limit_reached_status ) {
			break;
		}
	}
	if ( ! empty( $choices_limit_reached_status ) ) {
		echo esc_html( FrmFieldsHelper::get_error_msg( $field, 'choice_limit_msg' ) );
		return;
	}
	if ( $read_only ) {
		?>
		<select <?php do_action( 'frm_field_input_html', $field ); ?>>
	<?php } else { ?>
		<select name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( $html_id ); ?>" <?php do_action( 'frm_field_input_html', $field ); ?>>
		<?php
	}

	$placeholder = FrmFieldsController::add_placeholder_to_select( $field );

	$skipped       = false;
	$other_opt     = false;
	$other_checked = false;

	if ( empty( $field['options'] ) ) {
		$field['options'] = array();
	}

	if ( ! $placeholder ) {
		/**
		 * @since 6.16.2
		 *
		 * @param array $field
		 */
		do_action( 'frm_dropdown_field_after_no_placeholder_option', $field );
	}

	foreach ( $field['options'] as $opt_key => $opt ) {
		$field_val = FrmFieldsHelper::get_value_from_array( $opt, $opt_key, $field );
		$opt       = FrmFieldsHelper::get_label_from_array( $opt, $opt_key, $field );
		$selected  = FrmAppHelper::check_selected( $field['value'], $field_val );

		$choice_limit_reached = $field_choices_limit_reached_statuses[ $opt_key ];
		$atts                 = isset( $shortcode_atts ) && is_array( $shortcode_atts ) ? $shortcode_atts : array();
		if ( FrmFieldsController::should_hide_field_choice( $choice_limit_reached, $atts, $opt_key, $field['form_id'] ) ) {
			continue;
		}
		if ( $other_opt === false ) {
			$other_args = FrmFieldsHelper::prepare_other_input( compact( 'field', 'field_name', 'opt_key' ), $other_opt, $selected );
			if ( FrmFieldsHelper::is_other_opt( $opt_key ) && $selected ) {
				$other_checked = true;
			}
		}

		if ( $placeholder && $opt == '' && ! $skipped ) {
			$skipped = true;
			continue;
		}

		$option_params = array(
			'value' => $field_val,
		);

		if ( FrmFieldsHelper::is_other_opt( $opt_key ) ) {
			$option_params['class'] = 'frm_other_trigger';
		}
		if ( $choice_limit_reached ) {
			$option_params['disabled'] = 'disabled';
			$selected                  = false;
		}
		FrmHtmlHelper::echo_dropdown_option( $opt, (bool) $selected, $option_params );
		unset( $option_params );
	}//end foreach
	?>
	</select>
	<?php

	if ( isset( $other_args ) ) {
		FrmFieldsHelper::include_other_input(
			array(
				'other_opt' => $other_opt,
				'read_only' => $read_only,
				'checked'   => $other_checked,
				'name'      => $other_args['name'],
				'value'     => $other_args['value'],
				'field'     => $field,
				'html_id'   => $html_id,
				'opt_key'   => false,
			)
		);
	}
}//end if
