<?php

class WP_Test_FrmField extends FrmUnitTest {

	/**
	 * @covers FrmField::getAll
	 */
	function test_getAll() {
		$form_id = FrmForm::getIdByKey( $this->contact_form_key );
		$fields = FrmField::getAll( array( 'fi.form_id' => (int) $form_id ) );
		$this->assertNotEmpty( $fields );
		$this->assertEquals( count( $fields ), 8 );

		foreach ( $fields as $field ) {
			
		}
	}
}