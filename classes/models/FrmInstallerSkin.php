<?php
/**
 * @since 3.04.02
 *
 * @package     Formidable
 * @subpackage  Upgrader Skin
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Upgrader_Skin' ) ) {
	// this is to prevent a unit test from failing
	return;
}

class FrmInstallerSkin extends WP_Upgrader_Skin {

	/**
	 * Set the upgrader object and store it as a property in the parent class.
	 *
	 * @since 3.04.02
	 *
	 * @param object $upgrader The upgrader object (passed by reference).
	 */
	public function set_upgrader( &$upgrader ) {
		if ( is_object( $upgrader ) ) {
			$this->upgrader =& $upgrader;
		}
	}

	/**
	 * Set the upgrader result and store it as a property in the parent class.
	 *
	 * @since 3.04.02
	 *
	 * @param object $result The result of the install process.
	 */
	public function set_result( $result ) {
		$this->result = $result;
	}

	/**
	 * Empty out the header of its HTML content and only check to see if it has
	 * been performed or not.
	 *
	 * @since 3.04.02
	 */
	public function header() {}

	/**
	 * Empty out the footer of its HTML contents.
	 *
	 * @since 3.04.02
	 */
	public function footer() {}

	/**
	 * Instead of outputting HTML for errors, json_encode the errors and send them
	 * back to the Ajax script for processing.
	 *
	 * @since 3.04.02
	 *
	 * @param object|string $errors The WP Error object of errors with the install process.
	 */
	public function error( $errors ) {
		if ( ! empty( $errors ) ) {
			if ( ! is_string( $errors ) ) {
				$error   = $errors->get_error_message();
				$message = $errors->get_error_data();
				$errors  = $error . ' ' . $message;
			}
			echo json_encode(
				array(
					'error'   => $errors,
					'message' => $errors,
					'success' => false,
				)
			);
			if ( wp_doing_ajax() ) {
				wp_die();
			} else {
				die();
			}
		}
	}

	/**
	 * Empty out the feedback method to prevent outputting HTML strings as the install
	 * is progressing.
	 *
	 * @since 3.04.02
	 *
	 * @param string $string The feedback string.
	 * @param mixed  ...$args Optional text replacements.
	 */
	public function feedback( $string, ...$args ) {}
}
