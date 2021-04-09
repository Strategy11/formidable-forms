<?php
/**
 * Content of field name show on the form builder
 *
 * @package Formidable
 * @since 4.10.02
 *
 * @var FrmFieldCombo $this  Field type object.
 * @var array         $field Field array.
 * @var string        $name  Field name.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$sub_fields = $this->get_processed_sub_fields();
$field_name = $this->html_name( $name );
$html_id    = $this->html_id();
?>
<div class="frm_multi_fields_container frm_grid_container">
	<?php foreach ( $sub_fields as $key => $sub_field ) { ?>
		<div id="frm_field_<?php echo esc_attr( $field['id'] . '-' . $key ); ?>_container" class="frm_form_field form-field <?php echo esc_attr( $sub_field['classes'] ); ?>">
			<?php
			switch ( $sub_field['type'] ) {
				case 'select':
					?>
					<select name="<?php echo esc_attr( $field_name ); ?>[<?php echo esc_attr( $key ); ?>]" id="<?php echo esc_attr( $html_id . '_' . $key ); ?>" >
						<option value="">
							<?php echo 'asdfsdf'; // esc_html( FrmProComboFieldsController::get_dropdown_label( compact( 'field', 'key', 'sub_field' ) ) ); ?>
						</option>
						<?php foreach ( $sub_field['options'] as $option ) { ?>
							<option value="<?php echo esc_attr( $option ) ?>" <?php selected( $field['value'][ $key ], $option ) ?>>
								<?php echo esc_html( $option ) ?>
							</option>
						<?php } ?>
					</select>
					<?php
					break;

				default:
					?>
					<input
						type="<?php echo esc_attr( $sub_field['type'] ) ?>"
						id="<?php echo esc_attr( $html_id . '_' . $key ) ?>"
						value="<?php echo esc_attr( $field['value'][ $key ] ) ?>"
						class="dyn_default_value"
						<?php
						if ( ! isset( $remove_names ) || ! $remove_names ) {
							echo ' name="' . esc_attr( $field_name ) . '[' . esc_attr( $key ) . ']" ';
						}
//						FrmProComboFieldsController::add_atts_to_input( compact( 'field', 'sub_field', 'key' ) );
						?>
					/>
				<?php
			}
			?>
			<div class="frm_description" id="field_<?php echo esc_attr( $key . '_desc_' . $field['id'] ); ?>">
				<?php echo esc_html( $sub_field['label'] ); ?>
			</div>
		</div>
	<?php } ?>
</div>
