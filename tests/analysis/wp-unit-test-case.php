<?php
/**
 * Connect the WordPress test base to PHPUnit for static analysis.
 * WordPress provides the complete implementation when the tests run.
 *
 * @package Formidable
 *
 * @since x.x
 */

if ( ! class_exists( 'WP_UnitTestCase' ) ) {
	/**
	 * Describe PHPUnit inheritance without requiring its runtime in the analyzer.
	 *
	 * @mixin \PHPUnit\Framework\TestCase
	 *
	 * @method void tearDown() Restore WordPress state after a test.
	 */
	abstract class WP_UnitTestCase {
	}
}
