<?php

/**
 * @group fields
 */
class test_FrmFieldGridHelper extends FrmUnitTest {

	/**
	 * @covers FrmFieldGridHelper::get_size_of_class
	 */
	public function test_get_size_of_class() {
		$this->assertEquals( 1, $this->get_size_of_class( 'frm1' ) );
		$this->assertEquals( 6, $this->get_size_of_class( 'frm6' ) );
		$this->assertEquals( 8, $this->get_size_of_class( 'frm8' ) );
		$this->assertEquals( 10, $this->get_size_of_class( 'frm10' ) );
		$this->assertEquals( 12, $this->get_size_of_class( 'frm12' ) );
		$this->assertEquals( 2, $this->get_size_of_class( 'frm_sixth' ) );
		$this->assertEquals( 3, $this->get_size_of_class( 'frm_fourth' ) );
		$this->assertEquals( 4, $this->get_size_of_class( 'frm_third' ) );
		$this->assertEquals( 6, $this->get_size_of_class( 'frm_half' ) );
		$this->assertEquals( 12, $this->get_size_of_class( 'frm_full' ) );
	}

	private function get_size_of_class( $class ) {
		return $this->run_private_method( array( 'FrmFieldGridHelper', 'get_size_of_class' ), array( $class ) );
	}
}
