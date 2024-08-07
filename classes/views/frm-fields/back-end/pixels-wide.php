<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="<?php echo esc_attr( $display_max ? 'frm6 frm_form_field' : '' ); ?>">
	<label for="field_options_size_<?php echo esc_attr( $field['id'] ); ?>">
		<?php esc_html_e( 'Field Size', 'formidable' ); ?>
		<span class="frm-sub-label">
			<?php esc_html_e( '(%, px, em)', 'formidable' ); ?>
		</span>
	</label>

	<input type="text" name="field_options[size_<?php echo esc_attr( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['size'] ); ?>" size="5" id="field_options_size_<?php echo esc_attr( $field['id'] ); ?>" aria-describedby="howto_size_<?php echo esc_attr( $field['id'] ); ?>" />
</p>

<?php
if ( $display_max ) {
	include FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/max.php';
}
?>
