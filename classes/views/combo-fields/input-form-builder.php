<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<fieldset aria-labelledby="<?php echo esc_attr( $html_id ); ?>_label">
<legend class="frm_screen_reader frm_hidden">
	<?php echo esc_html( $field['name'] ); ?>
</legend>
<div class="frm_combo_inputs_container">
<?php foreach ( $sub_fields as $key => $sub_field ) { ?>
<div id="frm_field_<?php echo esc_attr( $field['id'] . '-' . $key ); ?>_container" class="frm_form_field form-field <?php echo esc_attr( $sub_field['classes'] ); ?>">
	<label for="<?php echo esc_attr( $html_id . '_' . $key ); ?>" class="frm_screen_reader frm_hidden">
		<?php echo esc_html( ! empty( $field[ $key . '_desc' ] ) ? $field[ $key . '_desc' ] : $field['name'] ); ?>
	</label>
	<?php if ( $sub_field['type'] === 'select' ) { ?>
		<select name="<?php echo esc_attr( $field_name ); ?>[<?php echo esc_attr( $key ); ?>]" id="<?php echo esc_attr( $html_id . '_' . $key ); ?>" <?php FrmComboFieldsController::add_atts_to_input( compact( 'field', 'sub_field', 'key' ) ); ?>>
			<option value="" class="<?php echo esc_attr( ! empty( $field['placeholder'][ $key ] ) ? 'frm-select-placeholder' : '' ); ?>">
				<?php echo esc_html( FrmComboFieldsController::get_dropdown_label( compact( 'field', 'key', 'sub_field' ) ) ); ?>
			</option>
			<?php
			foreach ( $sub_field['options'] as $option ) {
				$selected = (string) $field['value'][ $key ] === (string) $option;
				$params   = array( 'value' => $option );

				if ( 'address' === $field['type'] && 'country' === $key ) {
					$code = FrmAddressesController::get_country_code( $option );

					if ( $code ) {
						$params['data-code'] = $code;
					}
				}

				FrmHtmlHelper::echo_dropdown_option( $option, $selected, $params );
			}
			?>
		</select>
	<?php } else { ?>
	<input type="<?php echo esc_attr( $sub_field['type'] ); ?>" id="<?php echo esc_attr( $html_id . '_' . $key ); ?>" value="<?php echo esc_attr( $field['value'][ $key ] ); ?>" <?php
	if ( empty( $remove_names ) ) {
		echo 'name="' . esc_attr( $field_name ) . '[' . esc_attr( $key ) . ']" ';
	}
	FrmComboFieldsController::add_atts_to_input( compact( 'field', 'sub_field', 'key' ) );
	?> />
	<?php
	}//end if

	if ( $sub_field['label'] ) {
		FrmComboFieldsController::include_sub_label(
			array(
				'field'       => $field,
				'option_name' => $key . '_desc',
			)
		);
	}
	?>
</div>
<?php
}//end foreach
 ?>
</div>
</fieldset>
