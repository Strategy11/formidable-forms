<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="howto">
	<?php esc_html_e( 'Select users that are allowed access to Formidable. Without access to View Forms, users will be unable to see the Formidable menu.', 'formidable' ); ?>
</p>

<table class="form-table">
	<?php
	foreach ( $frm_roles as $frm_role => $frm_role_description ) {
		$role_field_name = $frm_role . '[]';
		?>
		<tr>
			<td class="frm_left_label">
				<label id="for_<?php echo esc_attr( str_replace( '[]', '', $role_field_name ) ); ?>"><?php echo esc_html( $frm_role_description ); ?></label>
			</td>
			<td><?php FrmAppHelper::wp_roles_dropdown( $role_field_name, $frm_settings->$frm_role, 'multiple' ); ?></td>
		</tr>
	<?php } ?>
</table>

<?php
FrmAppHelper::multiselect_accessibility();
