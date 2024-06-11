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
	 *     Any extra arguments.
	 *
	 *     @type bool|null $echo True if you want the toggle to echo. False if you want it to return an HTML string.
	 * }
	 *
	 * @return string|void
	 */
	public static function toggle( $id, $name, $args ) {
		wp_enqueue_script( 'formidable_settings' );
		return FrmAppHelper::clip(
			// @phpstan-ignore-next-line
			function () use ( $id, $name, $args ) {
				require FrmAppHelper::plugin_path() . '/classes/views/shared/toggle.php';
			},
			isset( $args['echo'] ) ? $args['echo'] : false
		);
	}

	/**
	 * Echo a dropdown option.
	 * This is useful to avoid closing and opening PHP to echo <option> tags which leads to extra whitespace.
	 * Avoiding whitespace saves 5KB of HTML for an international address field with a country dropdown with 252 options.
	 *
	 * @since 6.3.1
	 *
	 * @param string $option   The string used as the option label.
	 * @param bool   $selected True if the option should be selected.
	 * @param array  $params   Other HTML params for the option.
	 * @return void
	 */
	public static function echo_dropdown_option( $option, $selected, $params = array() ) {
		echo '<option ';
		FrmAppHelper::array_to_html_params( $params, true );
		selected( $selected );
		echo '>';
		echo esc_html( $option === '' ? ' ' : $option );
		echo '</option>';
	}
}
