<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Class FrmDeprecated
 *
 * @since 3.04.03
 * @codeCoverageIgnore
 */
class FrmDeprecated {

	/**
	 * Routes for wordpress pages -- we're just replacing content
	 *
	 * @deprecated 3.0
	 */
	public static function page_route( $content ) {
		_deprecated_function( __FUNCTION__, '3.0' );
		global $post;

		if ( $post && isset( $_GET['form'] ) ) {
			$content = FrmFormsController::page_preview();
		}

		return $content;
	}
}
