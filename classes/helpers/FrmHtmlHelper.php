<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 6.0
 */
class FrmHtmlHelper {

	/**
	 * Create a toggle and either echo or return the string.
	 * This is intended for use on admin pages only. The CSS is included in frm_admin.css.
	 *
	 * @since 6.0
	 *
	 * @param string $id
	 * @param string $name
	 * @param array  $args {
	 *     @param bool|null $echo True if you want the toggle to echo. False if you want it to return an HTML string.
	 * }
	 *
	 * @return string|void
	 */
	public static function toggle( $id, $name, $args ) {
		wp_enqueue_script( 'formidable_settings' );
		return FrmAppHelper::clip(
			// @phpstan-ignore-next-line
			function() use ( $id, $name, $args ) {
				require FrmAppHelper::plugin_path() . '/classes/views/shared/toggle.php';
			},
			isset( $args['echo'] ) ? $args['echo'] : false
		);
	}
}
