<?php

/**
 * Tests to test that that testing framework is testing tests. Meta, huh?
 */
class WP_Test_WordPress_Plugin_Tests extends WP_UnitTestCase {
    
	/**
	 * Ensure that the plugin has been installed and activated.
	 */
    function setUp() {
		parent::setUp();
		FrmAppController::install();
	}

	/**
	 * Run a simple test to ensure that the tests are running
	*/
	function test_tests() {
		$this->assertTrue( true );
	}
	
	function test_plugin_activated() {
		$this->assertTrue( is_plugin_active( 'formidable/formidable.php' ) );
	}
	 
	function test_create_form() {
	    FrmAppController::install();
        
        $frm_form = new FrmForm();
        $values = FrmFormsHelper::setup_new_vars(false);
        $id = $frm_form->create( $values );
        
	    $this->assertGreaterThan(0, $id);
	}
	
	function test_wpml_install(){
	    require(FrmAppHelper::plugin_path() .'/pro/classes/models/FrmProCopy.php');
	    $copy = new FrmProCopy();
	    $copy->install();
	}

	/**
	 * If these tests are being run on Travis CI, verify that the version of
	 * WordPress installed is the version that we requested.
	 *
	 * @requires PHP 5.3
	 */
	/*function test_wp_version() {

		if ( !getenv( 'TRAVIS' ) )
			$this->markTestSkipped( 'Test skipped since Travis CI was not detected.' );

		$requested_version = getenv( 'WP_VERSION' ) . '-src';

		// The "master" version requires special handling.
		if ( $requested_version == 'master-src' ) {
			$file = file_get_contents( 'https://raw.github.com/tierra/wordpress/master/src/wp-includes/version.php' );
			preg_match( '#\$wp_version = \'([^\']+)\';#', $file, $matches );
			$requested_version = $matches[1];
		}

		$this->assertEquals( get_bloginfo( 'version' ), $requested_version );

	}*/


}
