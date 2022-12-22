<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="template">
	<p class="howto">
		<?php esc_html_e( 'You can add custom css here or in your theme style.css. Any CSS added here will be used anywhere the Formidable CSS is loaded.', 'formidable' ); ?>
	</p>

	<?php
	require FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php';

	$textarea_params = array(
		'name' => 'frm_custom_css',
		'id'   => $id,
	);
	if ( ! empty( $settings ) ) {
		$textarea_params['class'] = 'hide-if-js';
	}
	?>
	<textarea <?php FrmAppHelper::array_to_html_params( $textarea_params, true ); ?>><?php echo FrmAppHelper::esc_textarea( $custom_css ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></textarea>
</div>
