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
?>
<p>
	<label for="name_layout_<?php echo esc_attr( $field['id'] ); ?>">
		<?php esc_html_e( 'Name layout', 'formidable' ); ?>
	</label>

	<select name="field_options[name_layout_<?php echo esc_attr( $field['id'] ); ?>]" id="name_layout_<?php echo esc_attr( $field['id'] ); ?>">
		<option value="first_last" <?php selected( $field['name_layout'], 'first_last' ); ?>>
			<?php esc_html_e( 'First Last', 'formidable' ); ?>
		</option>
		<option value="last_first" <?php selected( $field['name_layout'], 'last_first' ); ?>>
			<?php esc_html_e( 'Last First', 'formidable' ); ?>
		</option>
		<option value="first_middle_last" <?php selected( $field['name_layout'], 'first_middle_last' ); ?>>
			<?php esc_html_e( 'First Middle Last', 'formidable' ); ?>
		</option>
	</select>
</p>
