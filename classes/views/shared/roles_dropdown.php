<?php
/**
 * WP Roles dropdown view
 *
 * @since x.x
 *
 * @var string $field_name Field name for the dropdown
 * @var array  $capability Capability array for filtering roles
 * @var string $multiple   Whether to allow multiple selections ('single' or 'multiple')
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<select name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( $field_name ); ?>"
	<?php echo 'multiple' === $multiple ? 'multiple="multiple"' : ''; ?>
	class="frm_multiselect">
	<?php FrmAppHelper::roles_options( $capability ); ?>
</select>
