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
	<?php
	foreach ( $sub_fields as $name => $sub_field ) {
		$sub_field['name'] = $name;
		?>
		<div id="frm_field_<?php echo esc_attr( $field['id'] . '-' . $name ); ?>_container" class="frm_form_field form-field <?php echo esc_attr( $sub_field['classes'] ); ?>">
			<?php
			switch ( $sub_field['type'] ) {
				default:
					?>
					<input
						type="<?php echo esc_attr( $sub_field['type'] ); ?>"
						id="<?php echo esc_attr( $html_id . '_' . $name ); ?>"
						value="<?php echo esc_attr( $field['value'][ $name ] ); ?>"
						class="dyn_default_value"
						<?php
						if ( ! isset( $remove_names ) || ! $remove_names ) {
							echo ' name="' . esc_attr( $field_name ) . '[' . esc_attr( $name ) . ']" ';
						}
						$this->print_input_atts( compact( 'field', 'sub_field' ) );
						?>
					/>
					<?php
			}
			?>
			<div class="frm_description" id="field_<?php echo esc_attr( $name . '_desc_' . $field['id'] ); ?>">
				<?php echo esc_html( $sub_field['label'] ); ?>
			</div>
		</div>
	<?php } ?>
</div>
