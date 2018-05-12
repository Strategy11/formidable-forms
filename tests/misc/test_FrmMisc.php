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

		$this->assertNotEmpty( has_action( 'plugins_loaded', 'FrmAppController::load_lang' ) );
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
}
