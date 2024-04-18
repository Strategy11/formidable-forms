<?php

class WP_Test_WordPress_Plugin_Tests extends FrmUnitTest {

	public function test_plugin_activated() {
		$this->assertTrue( is_plugin_active( 'formidable/formidable.php' ) );
	}

	public function test_wpml_install() {
		if ( is_callable( 'FrmProCopy::install' ) ) {
			$copy = new FrmProCopy();
			$copy->install();
			self::do_tables_exist( true );
		}
	}
}

/**
 * Namespacing allows more flexibility
 *   - Mock objects without real data
 * Check out PHPUnit test doubles, PHPunit data providers, Test-driven development (TDD)
 * qunit/phantomjs for js unit testing
 * See: "Browser Eyeballing != Javascript testing" by Jordna Kaspar
 */
