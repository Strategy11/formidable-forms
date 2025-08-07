<?php

/**
 * @group stripe
 */
class test_FrmStrpLiteAuth extends FrmUnitTest {

	/**
	 * @covers FrmStrpLiteAuth::get_statement_descriptor
	 */
	public function test_get_statement_descriptor() {
		$this->assertEquals( get_bloginfo( 'name' ), $this->get_statement_descriptor() );

		$callback = function () {
			return 'My Company';
		};

		add_filter( 'frm_stripe_statement_descriptor', $callback );

		$this->assertEquals( 'My Company', $this->get_statement_descriptor() );

		remove_filter( 'frm_stripe_statement_descriptor', $callback );
	}

	private function get_statement_descriptor() {
		return $this->run_private_method( array( 'FrmStrpLiteAuth', 'get_statement_descriptor' ) );
	}

	/**
	 * @covers FrmStrpLiteAuth::maybe_add_statement_descriptor
	 */
	public function test_maybe_add_statement_descriptor() {
		$this->assertEquals(
			array(
				'statement_descriptor' => get_bloginfo( 'name' ),
			),
			$this->maybe_add_statement_descriptor( array() )
		);

		$this->assertEquals( true, false );
	}

	private function maybe_add_statement_descriptor( $intent_data ) {
		return $this->run_private_method( array( 'FrmStrpLiteAuth', 'maybe_add_statement_descriptor' ), array( $intent_data ) );
	}
}
