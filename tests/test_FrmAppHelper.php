<?php

class WP_Test_FrmAppHelper extends FrmUnitTest {
	/**
	 * @covers FrmAppHelper::pro_is_installed
	 */
	function test_pro_is_installed() {
		$active = FrmAppHelper::pro_is_installed();
		if ( is_callable( 'FrmUpdatesController::pro_is_authorized' ) ) {
			$this->assertTrue( $active );
		} else {
			$this->assertFalse( $active );
		}
	}
}