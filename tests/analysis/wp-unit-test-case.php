<?php
/**
 * Connect the WordPress test base to PHPUnit for static analysis.
 * WordPress provides the complete implementation when the tests run.
 *
 * @package Formidable
 *
 * @since x.x
 */

if ( ! class_exists( 'WP_UnitTestCase' ) && class_exists( 'PHPUnit\Framework\TestCase' ) ) {
	abstract class WP_UnitTestCase extends \PHPUnit\Framework\TestCase {
	}
}
