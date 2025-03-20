<?php
/**
 * @package Formidable
 * @since 6.18
 *
 * @var array        $field Field data.
 * @var array        $args  Includes 'field', 'display', and 'values' settings.
 * @var FrmFieldType $this  Field type object.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$field_id = $field['id'];
?>
<p class="frm6 frm_form_field frm-format-dropdown">
	<label for="format_dropdown_<?php echo esc_attr( $field_id ); ?>">
		<?php esc_html_e( 'Format', 'formidable' ); ?>
	</label>

	<select
		name="field_options[format_dropdown_<?php echo esc_attr( $field_id ); ?>]"
		id="frm_format_dropdown_<?php echo esc_attr( $field_id ); ?>"
		class="frm_format_dropdown frm_select_with_upgrade frm_select_with_dependency"
		data-field-id="<?php echo intval( $field_id ); ?>"
	>
		<?php
		$options_view_path = FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/format-dropdown-options.php';

		/**
		 * Includes the formatted options view file.
		 *
		 * @since 6.18
		 *
		 * @param string $options_view_path The path to the options view file.
		 */
		require apply_filters( 'frm_format_options_view_path', $options_view_path );
		?>
	</select>
</p>

<?php
/**
 * Fires after the format dropdown template is rendered.
 *
 * @since 6.18
 *
 * @param array $field The field array.
 */
do_action( 'frm_after_format_dropdown_template', $field );
