<?php
/**
 * Frontend template for address field
 *
 * @package Formidable
 *
 * @since x.x
 *
 * @var array         $args           Data passed to this view. See FrmFieldCombo::load_field_output().
 * @var array         $shortcode_atts Shortcode attributes.
 * @var array         $sub_fields     Sub fields array.
 * @var string        $html_id        HTML ID.
 * @var string        $field_name     Field Name.
 * @var array         $errors         Field errors.
 * @var bool          $remove_names   Remove field name or not.
 * @var FrmFieldCombo $this           Field type object.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$field        = $args['field'];
$field_id     = $field['id'];
$field_label  = $field['name'];
$field_value  = $field['value'];
$sub_fields   = $args['sub_fields'];
$html_id      = $args['html_id'];
$field_name   = $args['field_name'];
$errors       = $args['errors'];
$inputs_attrs = $this->get_inputs_container_attrs();
?>
<fieldset aria-labelledby="<?php echo esc_attr( $html_id ); ?>_label">
	<legend class="frm_screen_reader frm_hidden">
		<?php echo esc_html( $field_label ); ?>
	</legend>

	<div <?php FrmAppHelper::array_to_html_params( $inputs_attrs, true ); ?>>
		<?php
		foreach ( $sub_fields as $name => $sub_field ) {
			$sub_field['name'] = $name;
			?>
			<div
				id="frm_field_<?php echo esc_attr( $field_id . '-' . $name ); ?>_container"
				class="frm_form_field form-field frm_form_subfield-<?php echo esc_attr( $name ); ?> <?php echo esc_attr( $sub_field['wrapper_classes'] ); ?><?php
				if ( isset( $errors ) ) {
					FrmComboFieldsController::maybe_add_error_class( compact( 'field', 'name', 'errors', 'atts' ) );
				}
				?>"
				data-sub-field-name="<?php echo esc_attr( $name ); ?>"
			>
				<label for="<?php echo esc_attr( $html_id . '_' . $name ); ?>" class="frm_screen_reader frm_hidden">
					<?php echo esc_html( ! empty( $field[ $name . '_desc' ] ) ? $field[ $name . '_desc' ] : $field['name'] ); ?>
				</label>
				<?php if ( 'select' === $sub_field['type'] ) { ?>
					<select name="<?php echo esc_attr( $field_name ); ?>[<?php echo esc_attr( $name ); ?>]" id="<?php echo esc_attr( $html_id . '_' . $name ); ?>" <?php FrmComboFieldsController::add_atts_to_input( compact( 'field', 'sub_field', 'name' ) ); ?>>
						<option value="" class="<?php echo esc_attr( ! empty( $field['placeholder'][ $name ] ) ? 'frm-select-placeholder' : '' ); ?>">
							<?php echo esc_html( FrmComboFieldsController::get_dropdown_label( compact( 'field', 'name', 'sub_field' ) ) ); ?>
						</option>
						<?php
						foreach ( $sub_field['options'] as $value => $label ) {
							$selected = isset( $field['value'][ $name ] ) && (string) $field['value'][ $name ] === (string) $value;
							$params   = array( 'value' => $value );

							if ( 'address' === $field['type'] && 'country' === $name ) {
								$code = FrmAddressesController::get_country_code( $value );

								if ( $code ) {
									$params['data-code'] = $code;
								}
							}

							FrmHtmlHelper::echo_dropdown_option( $label, $selected, $params );
						}
						?>
					</select>
				<?php } else { ?>
					<input type="<?php echo esc_attr( $sub_field['type'] ); ?>" id="<?php echo esc_attr( $html_id . '_' . $name ); ?>" value="<?php echo esc_attr( $field['value'][ $name ] ?? '' ); ?>" <?php
					if ( empty( $remove_names ) ) {
						echo 'name="' . esc_attr( $field_name ) . '[' . esc_attr( $name ) . ']" ';
					}
					FrmComboFieldsController::add_atts_to_input( compact( 'field', 'sub_field', 'name' ) );
					?> />
				<?php } ?>

				<?php
				if ( $sub_field['label'] ) {
					FrmComboFieldsController::include_sub_label(
						array(
							'field'       => $field,
							'option_name' => $name . '_desc',
						)
					);
				}

				$temp_id = ! empty( $atts['field_id'] ) ? $atts['field_id'] : $field['id'];

				// Don't show individual field errors when there is a combo field error
				if ( ! empty( $errors ) && isset( $errors[ 'field' . $temp_id . '-' . $name ] ) && ! isset( $errors[ 'field' . $field['id'] ] ) ) {
					?>
					<div class="frm_error" role="alert"><?php echo esc_html( $errors[ 'field' . $temp_id . '-' . $name ] ); ?></div>
				<?php } ?>
			</div>
		<?php } ?>
	</div>
</fieldset>
