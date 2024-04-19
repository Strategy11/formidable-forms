<?php

/**
 * @group fields
 */
class test_FrmField extends FrmUnitTest {

	public static function wpSetUpBeforeClass() {
		$_POST = array();
		self::empty_tables();
		self::frm_install();
	}

	public function test_create() {
		$form_id     = $this->factory->form->get_id_by_key( 'contact-db12' );
		$field_types = array_merge( FrmField::field_selection(), FrmField::pro_field_selection() );
		foreach ( $field_types as $field_type => $field_info ) {
			$field_id = $this->factory->field->create(
				array(
					'form_id' => $form_id,
					'type'    => $field_type,
				)
			);
			$this->assertTrue( is_numeric( $field_id ) );
			$this->assertTrue( $field_id > 0 );
		}
	}

	/**
	 * @covers FrmField::getAll
	 */
	public function test_getAll() {
		$forms = array(
			$this->all_fields_form_key => $this->all_field_types_count - $this->contact_form_field_count - 3,
			$this->contact_form_key    => $this->contact_form_field_count,
		);

		foreach ( $forms as $form_key => $expected_count ) {
			$form_id = $this->factory->form->get_id_by_key( $form_key );
			$fields  = FrmField::getAll( array( 'fi.form_id' => (int) $form_id ) );
			$this->assertNotEmpty( $fields );
			$this->assertEquals( $expected_count, count( $fields ), 'An incorrect number of fields are retrieved with FrmField::getAll.' );
		}
	}

	/**
	 * @covers FrmField::get_all_for_form
	 */
	public function test_get_all_for_form() {
		$forms = array(
			'basic_test'         => array(
				'count'    => $this->contact_form_field_count,
				'form_key' => $this->contact_form_key,
			),
			'no_repeat_or_embed' => array(
				'count'    => $this->all_field_types_count - $this->contact_form_field_count - 3,
				'form_key' => $this->all_fields_form_key,
			),
		);

		foreach ( $forms as $test => $args ) {
			$form_id = FrmForm::get_id_by_key( $args['form_key'] );

			if ( $test === 'no_repeat_or_embed' ) {
				$fields = FrmField::get_all_for_form( $form_id, '', 'exclude', 'exclude' );
			} else {
				$fields = FrmField::get_all_for_form( $form_id );
			}

			$this->assertNotEmpty( $fields );
			$this->assertEquals( $args['count'], count( $fields ), 'An incorrect number of fields are retrieved with FrmField::get_all_for_form for ' . $test . '.' );
		}
	}
}
