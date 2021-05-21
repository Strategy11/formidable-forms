<?php
/**
 * Primary options for name field
 *
 * @package Formidable
 * @since 4.10.02
 *
 * @var array        $field Field array.
 * @var array        $args  Includes 'field', 'display', and 'values'.
 * @var FrmFieldName $this  Field type object.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

// Options here need to be declared in FrmFieldName::extra_field_opts().
$field_id    = $field['id'];
$name_layout = FrmField::get_option( $field, 'name_layout' );
?>
<p>
	<label for="name_layout_<?php echo esc_attr( $field_id ); ?>">
		<?php esc_html_e( 'Name layout', 'formidable' ); ?>
	</label>

	<select
		name="field_options[name_layout_<?php echo esc_attr( $field_id ); ?>]"
		id="name_layout_<?php echo esc_attr( $field_id ); ?>"
		class="frm_name_layout_dropdown"
		data-field-id="<?php echo intval( $field_id ); ?>"
		data-changeme="frm_combo_inputs_container_<?php echo intval( $field_id ); ?>"
		data-changeatt="data-name-layout"
	>
		<option value="first_last" <?php selected( $name_layout, 'first_last' ); ?>>
			<?php esc_html_e( 'First Last', 'formidable' ); ?>
		</option>
		<option value="last_first" <?php selected( $name_layout, 'last_first' ); ?>>
			<?php esc_html_e( 'Last First', 'formidable' ); ?>
		</option>
		<option value="first_middle_last" <?php selected( $name_layout, 'first_middle_last' ); ?>>
			<?php esc_html_e( 'First Middle Last', 'formidable' ); ?>
		</option>
	</select>
</p>
