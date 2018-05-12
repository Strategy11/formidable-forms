<?php

/**
 * This class handles the pointers used in the introduction tour.
 */
class FrmPointers {

	/**
	 * @var object Instance of this class
	 */
	public static $instance;

	/**
	 * Get the singleton instance of this class
	 *
	 * @return object
	 */
	public static function get_instance() {
		_deprecated_function( __FUNCTION__, '3.01.03' );

		if ( ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Load the introduction tour
	 */
	public function intro_tour() {
		_deprecated_function( __FUNCTION__, '3.01.03' );
	}

	/**
	 * Prints the pointer script
	 *
	 * @param string $selector The CSS selector the pointer is attached to.
	 * @param array  $options  The options for the pointer.
	 */
	public function print_scripts( $selector, $options ) {
		_deprecated_function( __FUNCTION__, '3.01.03' );
	}
}
