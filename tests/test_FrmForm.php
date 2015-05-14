<?php

class WP_Test_FrmForm extends FrmUnitTest {
	/**
	 * @covers FrmForm::create
	 */
	function test_create() {
		$values = FrmFormsHelper::setup_new_vars( false );
		$form_id = FrmForm::create( $values );
		$this->assertTrue( is_numeric( $form_id ) );
		$this->assertNotEmpty( $form_id );
	}

	/**
	 * @covers FrmForm::duplicate
	 */
	function test_duplicate(){
		$form = $this->factory->form->get_object_by_id( 'contact' );

		$id = FrmForm::duplicate( $form->id );
		$this->assertTrue( is_numeric( $id ) );
		$this->assertNotEmpty( $id );
	}

	/**
	 * @covers FrmForm::destroy
	 */
	function test_destroy(){
		$forms = FrmForm::getAll();
		$this->assertNotEmpty( count( $forms ) );

		foreach ( $forms as $form ) {
			if ( $form->is_template ) {
				continue;
			}

			$id = FrmForm::destroy( $form->id );
			$form_exists = FrmForm::getOne( $form->id );
			$this->assertEmpty( $form_exists, 'Failed to delete form ' . $form->form_key );

			$subforms_exist = FrmForm::getAll( array( 'parent_form_id' => $form->id ) );
			$this->assertEmpty( $subforms_exist, 'Failed to delete child forms for parent form ' . $form->form_key );
		}
	}
	
}