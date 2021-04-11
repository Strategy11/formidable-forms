<?php
/**
 * Frontend template for combo field
 *
 * @package Formidable
 * @since 4.10.02
 *
 * @var array         $args           Passed args.
 * @var array         $shortcode_atts Shortcode attributes.
 * @var array         $field          Field data.
 * @var array         $sub_fields     Sub fields array.
 * @var FrmFieldCombo $this           Field type object.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

?>
<fieldset aria-labelledby="<?php echo esc_attr( $args['html_id'] ); ?>_label">
	<legend class="frm_screen_reader frm_hidden">
		<?php echo esc_html( $field['name'] ); ?>
	</legend>

	<div class="frm_combo_inputs_container">
		<?php
		foreach ( $sub_fields as $name => $sub_field ) {
			$sub_field['name'] = $name;
			$sub_field_class   = 'frm_form_field form-field ' . $sub_field['classes'];

			if ( isset( $args['errors'][ 'field' . $field['id'] . '-' . $name ] ) ) {
				$sub_field_class .= ' frm_blank_field';
			}
			?>
			<div
				id="frm_field_<?php echo esc_attr( $field['id'] . '-' . $name ); ?>_container"
				class="<?php echo esc_attr( $sub_field_class ); ?>"
			>
				<label for="<?php echo esc_attr( $args['html_id'] . '_' . $name ); ?>" class="frm_screen_reader frm_hidden">
					<?php echo esc_html( isset( $field[ $name . '_desc' ] ) && ! empty( $field[ $name . '_desc' ] ) ? $field[ $name . '_desc' ] : $field['name'] ); ?>
				</label>

				<?php
				switch ( $sub_field['type'] ) {
					default:
						?>
						<input
							type="<?php echo esc_attr( $sub_field['type'] ); ?>"
							id="<?php echo esc_attr( $args['html_id'] . '_' . $name ); ?>"
							value="<?php echo esc_attr( $field['value'][ $name ] ); ?>"
							<?php
							if ( ! isset( $remove_names ) || ! $remove_names ) {
								echo 'name="' . esc_attr( $args['field_name'] ) . '[' . esc_attr( $name ) . ']" ';
							}

							$this->print_input_atts( compact( 'field', 'sub_field' ) );
							?>
						/>
						<?php
				}

				if ( $sub_field['label'] && ! empty( $field[ $name . '_desc' ] ) ) {
					echo '<div class="frm_description">' . wp_kses_post( $field[ $name . '_desc' ] ) . '</div>';
				}

				// Don't show individual field errors when there is a combo field error.
				if ( ! empty( $errors ) && isset( $errors[ 'field' . $field['id'] . '-' . $name ] ) && ! isset( $errors[ 'field' . $field['id'] ] ) ) {
					?>
					<div class="frm_error"><?php echo esc_html( $errors[ 'field' . $field['id'] . '-' . $name ] ); ?></div>
				<?php } ?>
			</div>
		<?php } ?>
	</div>
</fieldset>
