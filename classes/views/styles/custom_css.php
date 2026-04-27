<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="template">
	<p class="howto">
		<?php echo esc_html( $heading ); ?>
	</p>

	<?php
	if ( true === $show_errors ) {
		require FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php';
	}
	?>
	<textarea <?php FrmAppHelper::array_to_html_params( $textarea_params, true ); ?>><?php echo FrmAppHelper::esc_textarea( $custom_css ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></textarea>
</div>
