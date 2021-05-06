<?php

/**
 * @group fields
 */
class test_FrmFieldsController extends FrmUnitTest {

	/**
	 * @covers FrmFieldsController::prepare_placeholder
	 */
	public function test_prepare_placeholder() {
		$name        = 'Number';
		$field       = array(
			'type'        => 'number',
			'placeholder' => '',
			'label'       => 'inside',
			'name'        => $name,
			'required'    => 0,
		);
		$placeholder = $this->prepare_placeholder( $field );
		$this->assertEquals( $name, $placeholder, 'an empty string should be replaced by the label inside of an input.' );

		$field['placeholder'] = '0';
		$placeholder          = $this->prepare_placeholder( $field );
		$this->assertEquals( '0', $placeholder, '0 is a valid placeholder value.' );

		$field['placeholder'] = '';
		$field['type']        = 'hidden';
		$placeholder          = $this->prepare_placeholder( $field );
		$this->assertEquals( '', $placeholder, 'some types of fields are not "is_placeholder_field_type" and should be left empty.' );
	}

	private function prepare_placeholder( $field ) {
		return $this->run_private_method( array( 'FrmFieldsController', 'prepare_placeholder' ), array( $field ) );
	}
}
