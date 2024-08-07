<?php

class test_FrmSubmitHelper extends FrmUnitTest {

	public function test_copy_submit_field_settings_to_form() {
		$form = $this->factory->form->create_and_get();
		$this->assertEquals( $form, FrmSubmitHelper::copy_submit_field_settings_to_form( $form ) );

		$this->factory->field->create(
			array(
				'form_id' => $form->id,
				'type'    => 'submit',
				'name'    => 'Submit form',
			)
		);

		$new_form = FrmSubmitHelper::copy_submit_field_settings_to_form( $form );
		$this->assertEquals( $new_form->options['submit_value'], 'Submit form' );
	}

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
		$fields[]    = $last_submit;
		$this->assertEquals( $last_submit, FrmSubmitHelper::only_contains_submit_field( $fields ) );
	}
}
