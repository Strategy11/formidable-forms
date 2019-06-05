<?php

if ( isset( $field['post_field'] ) && $field['post_field'] == 'post_category' && FrmAppHelper::pro_is_installed() ) {
	echo FrmProPost::get_category_dropdown( // WPCS: XSS ok.
		$field,
		array(
			'location' => 'front',
			'name'     => $field_name,
			'id'       => $html_id,
		)
	);
} else {
	if ( $read_only ) {
		?>
		<select <?php do_action( 'frm_field_input_html', $field ); ?>>
	<?php } else { ?>
		<select name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( $html_id ); ?>" <?php do_action( 'frm_field_input_html', $field ); ?>>
		<?php
	}

	$placeholder = FrmField::get_option( $field, 'placeholder' );
	if ( empty( $placeholder ) ) {
		$placeholder = FrmFieldsController::get_default_value_from_name( $field );
	}

	$skipped = false;
	if ( $placeholder !== '' ) {
		?>
		<option value="">
			<?php echo esc_html( FrmField::get_option( $field, 'autocom' ) ? '' : $placeholder ); ?>
		</option>
		<?php
	}

	$other_opt = false;
	$other_checked = false;
	if ( empty( $field['options'] ) ) {
		$field['options'] = array();
	}
	foreach ( $field['options'] as $opt_key => $opt ) {
		$field_val = FrmFieldsHelper::get_value_from_array( $opt, $opt_key, $field );
		$opt = FrmFieldsHelper::get_label_from_array( $opt, $opt_key, $field );
		$selected = FrmAppHelper::check_selected( $field['value'], $field_val );
		if ( $other_opt === false ) {
			$other_args = FrmFieldsHelper::prepare_other_input( compact( 'field', 'field_name', 'opt_key' ), $other_opt, $selected );
			if ( FrmFieldsHelper::is_other_opt( $opt_key ) && $selected ) {
				$other_checked = true;
			}
		}

		if ( ! empty( $placeholder ) && $opt == '' && ! $skipped ) {
			$skipped = true;
			continue;
		}
		?>
		<option value="<?php echo esc_attr( $field_val ); ?>" <?php echo $selected ? ' selected="selected"' : ''; ?> class="<?php echo esc_attr( FrmFieldsHelper::is_other_opt( $opt_key ) ? 'frm_other_trigger' : '' ); ?>">
			<?php echo esc_html( $opt == '' ? ' ' : $opt ); ?>
		</option>
	<?php } ?>
	</select>
	<?php

	if ( isset( $other_args ) ) {
		FrmFieldsHelper::include_other_input(
			array(
				'other_opt' => $other_opt,
				'read_only' => $read_only,
				'checked' => $other_checked,
				'name'    => $other_args['name'],
				'value'   => $other_args['value'],
				'field'   => $field,
				'html_id' => $html_id,
				'opt_key' => false,
			)
		);
	}
}
