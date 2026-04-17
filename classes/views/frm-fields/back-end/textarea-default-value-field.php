<?php
/**
 * Textarea input for the default value setting.
 *
 * @package Formidable
 *
 * @var array  $field         Field data including 'id' and 'field_key'.
 * @var string $default_name  HTML name attribute for the textarea.
 * @var mixed  $default_value Current default value.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>

<textarea name="<?php echo esc_attr( $default_name ); ?>" class="default-value-field" id="frm_default_value_<?php echo esc_attr( $field['id'] ); ?>" rows="2" data-changeme="field_<?php echo esc_attr( $field['field_key'] ); ?>">
<?php
	echo FrmAppHelper::esc_textarea( $default_value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>
</textarea>
