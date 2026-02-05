<?php
/**
 * WP Roles dropdown view
 *
 * @since 3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @var string $field_name Field name for the dropdown
 * @var array  $capability Capability array for filtering roles
 * @var string $multiple   Whether to allow multiple selections ('single' or 'multiple')
 */
?>
<select name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( $field_name ); ?>"
	<?php echo 'multiple' === $multiple ? 'multiple="multiple"' : ''; ?>
	class="frm_multiselect">
	<?php FrmAppHelper::roles_options( $capability ); ?>
</select>
