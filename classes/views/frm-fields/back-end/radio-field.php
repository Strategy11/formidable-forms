<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( isset( $field['post_field'] ) && $field['post_field'] === 'post_category' ) {
	$type = $field['type'];
	do_action( 'frm_after_checkbox', compact( 'field', 'field_name', 'type' ) );
} elseif ( is_array( $field['options'] ) ) {

	foreach ( $field['options'] as $opt_key => $opt ) {

		$field_val = FrmFieldsHelper::get_value_from_array( $opt, $opt_key, $field );
		$opt       = FrmFieldsHelper::get_label_from_array( $opt, $opt_key, $field );
		?>
		<div class="frm_radio">
			<label for="<?php echo esc_attr( $html_id . '-' . $opt_key ); ?>">
				<?php

				$checked = FrmAppHelper::check_selected( $field['value'], $field_val ) ? 'checked="checked" ' : ' ';

				$other_opt  = false;
				$other_args = FrmFieldsHelper::prepare_other_input( compact( 'field_name', 'opt_key', 'field' ), $other_opt, $checked );
				?>
				<input type="radio" name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( $html_id . '-' . $opt_key ); ?>" value="<?php echo esc_attr( $field_val ); ?>" <?php
				echo $checked; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				do_action( 'frm_field_input_html', $field );
				?>/>
				<?php echo ' ' . FrmAppHelper::kses( $opt, 'all' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</label>
			<?php
			FrmFieldsHelper::include_other_input(
				array(
					'other_opt' => $other_opt,
					'read_only' => false,
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
			?>
		</div>
		<?php
	}//end foreach
}//end if
