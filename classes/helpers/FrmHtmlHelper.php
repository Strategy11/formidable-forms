<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since x.x
 */
class FrmHtmlHelper {

	/**
	 * Create a toggle and either echo or return the string.
	 * This isn't completely compatible with Pro. The input_html option has been omitted as it isn't secure.
	 *
	 * @since x.x
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
		return FrmAppHelper::clip(
			function() use ( $id, $name, $args ) {
				require FrmAppHelper::plugin_path() . '/classes/views/shared/toggle.php';
			},
			isset( $args['echo'] ) ? $args['echo'] : false
		);
	}
}
