<?php

class test_FrmSubmitHelper extends FrmUnitTest {

	public function test_only_contains_submit_field() {
		$fields = array(
			array( 'type' => 'text' ),
			array( 'type' => 'number' ),
		);

		$this->assertFalse( FrmSubmitHelper::only_contains_submit_field( $fields ) );

		$fields[] = array( 'type' => 'submit' );
		$this->assertFalse( FrmSubmitHelper::only_contains_submit_field( $fields ) );

		unset( $fields[0], $fields[1] );
		$this->assertEquals( array( 'type' => 'submit' ), FrmSubmitHelper::only_contains_submit_field( $fields ) );

		$last_submit = array(
			'type' => 'submit',
			'id'   => 2,
		);
		$fields[] = $last_submit;
		$this->assertEquals( $last_submit, FrmSubmitHelper::only_contains_submit_field( $fields ) );
	}
}
