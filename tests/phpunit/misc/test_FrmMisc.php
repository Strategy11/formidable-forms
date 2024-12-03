<?php

/**
 * @group base
 */
class test_FrmMisc extends FrmUnitTest {

	/**
	 * @covers ::load_formidable_forms
	 */
	public function test_load_formidable_forms() {
		global $frm_vars;
		$this->assertNotEmpty( $frm_vars );
		$this->assertTrue( isset( $frm_vars['load_css'] ) );
		$this->assertTrue( isset( $frm_vars['pro_is_authorized'] ) );

		$this->assertSame( 0, has_action( 'init', 'FrmAppController::load_lang' ) );
	}

	/**
	 * @covers ::frm_class_autoloader
	 * @covers ::frm_forms_autoloader
	 */
	public function test_frm_class_autoloader() {
		$test_classes = array( 'FrmTipsHelper', 'FrmFormActionsController', 'FrmEntryFactory', 'FrmFieldDefault' );
		foreach ( $test_classes as $class_name ) {
			if ( ! class_exists( $class_name ) ) {
				frm_forms_autoloader( $class_name );
			}
			$this->assertTrue( class_exists( $class_name ) );
		}
	}

	public function test_no_references_to_map_files() {
		$popper_js = file_get_contents( FrmAppHelper::plugin_path() . '/js/popper.min.js' );
		$this->assertStringNotContainsString( 'sourceMappingURL=popper.min.js.map', $popper_js, 'We do not want the popper JS file to include a source map reference. Since the sourcem ap is not included, this shows 404 errors in Safari.' );

		$bootstrap_js = file_get_contents( FrmAppHelper::plugin_path() . '/js/bootstrap.min.js' );
		$this->assertStringNotContainsString( 'sourceMappingURL=bootstrap.min.js.map', $bootstrap_js, 'We do not want the popper JS file to include a source map reference. Since the sourcem ap is not included, this shows 404 errors in Safari.' );
	}
}
