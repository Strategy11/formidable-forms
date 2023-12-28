<?php
/**
 * Frontend template for combo field
 *
 * @package Formidable
 * @since 4.10.02
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
			$sub_field_class   = "frm_form_field form-field frm_form_subfield-{$name} {$sub_field['wrapper_classes']}";
			$sub_field_desc    = FrmField::get_option( $field, $name . '_desc' );

			if ( isset( $errors[ 'field' . $field_id . '-' . $name ] ) ) {
				$sub_field_class .= ' frm_blank_field';
			}
			?>
			<div
				id="frm_field_<?php echo esc_attr( $field_id . '-' . $name ); ?>_container"
				class="<?php echo esc_attr( $sub_field_class ); ?>"
				data-sub-field-name="<?php echo esc_attr( $name ); ?>"
			>
				<label for="<?php echo esc_attr( $html_id . '_' . $name ); ?>" class="frm_screen_reader frm_hidden">
					<?php echo esc_html( $sub_field_desc ? $sub_field_desc : $field_label ); ?>
				</label>

				<?php
				switch ( $sub_field['type'] ) {
					default:
						?>
						<input
							type="<?php echo esc_attr( $sub_field['type'] ); ?>"
							id="<?php echo esc_attr( $html_id . '_' . $name ); ?>"
							value="<?php echo esc_attr( isset( $field_value[ $name ] ) ? $field_value[ $name ] : '' ); ?>"
							<?php
							if ( ! empty( $field_value[ $name ] ) ) {
								echo 'data-frmval="' . esc_attr( $field_value[ $name ] ) . '" ';
							}
							if ( empty( $args['remove_names'] ) ) {
								echo 'name="' . esc_attr( $field_name ) . '[' . esc_attr( $name ) . ']" ';
							}

							$this->print_input_atts( compact( 'field', 'sub_field' ) );
							?>
						/>
						<?php
				}

				if ( $sub_field['label'] && ( $sub_field_desc || $this->should_print_hidden_sub_fields() ) ) {
					echo '<div class="frm_description" id="frm_field_' . esc_attr( $field_id . '_' . $sub_field['name'] ) . '_desc">' . FrmAppHelper::kses( $sub_field_desc ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}

				// Don't show individual field errors when there is a combo field error.
				if ( ! empty( $errors ) && isset( $errors[ 'field' . $field_id . '-' . $name ] ) && ! isset( $errors[ 'field' . $field_id ] ) ) {
					?>
					<div class="frm_error" role="alert"><?php echo esc_html( $errors[ 'field' . $field_id . '-' . $name ] ); ?></div>
				<?php } ?>
			</div>
			<?php
		}//end foreach
		?>
	</div>
</fieldset>
