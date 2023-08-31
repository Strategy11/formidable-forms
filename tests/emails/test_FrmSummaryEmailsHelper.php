<?php

class test_FrmSummaryEmailsHelper extends FrmUnitTest {

	public function test_get_options() {
		$options = $this->run_private_method(
			array( 'FrmSummaryEmailsHelper', 'get_options' )
		);

		$this->assertTrue( isset( $options['last_monthly'] ) );
		$this->assertTrue( isset( $options['last_yearly'] ) );
		$this->assertTrue( isset( $options['last_license'] ) );
		$this->assertTrue( isset( $options['renewal_date'] ) );
	}

	public function test_get_date_obj() {
		$date = $this->run_private_method(
			array( 'FrmSummaryEmailsHelper', 'get_date_obj' ),
			array( '2023-08-13' )
		);

		$this->assertTrue( $date instanceof DateTime );
		$this->assertEquals( $date->format( 'Y-m-d' ), '2023-08-13' );

		$this->assertFalse(
			$this->run_private_method(
				array( 'FrmSummaryEmailsHelper', 'get_date_obj' ),
				array( '2023-08-' )
			)
		);
	}

	public function test_get_date_diff() {
		$this->assertEquals(
			$this->run_private_method(
				array( 'FrmSummaryEmailsHelper', 'get_date_diff' ),
				array( '2023-08-12', '2023-08-16' )
			),
			4
		);

		$this->assertFalse(
			$this->run_private_method(
				array( 'FrmSummaryEmailsHelper', 'get_date_diff' ),
				array( '2023-08-', '2023-08-16' )
			)
		);
	}

	public function test_get_earliest_form_created_date() {
		$form = FrmForm::getAll( array(), 'id ASC', 1 );
		$this->assertNotEmpty( $form );
		$this->assertEquals(
			$form->created_at,
			$this->run_private_method(
				array( 'FrmSummaryEmailsHelper', 'get_earliest_form_created_date' )
			)
		);
	}

	public function test_should_send_email() {

	}
}
