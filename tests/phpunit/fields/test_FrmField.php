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
					'type'    => $field_type,
					'form_id' => $form_id,
				)
			);
			$this->assertIsNumeric( $field_id );
			$this->assertGreaterThan( 0, $field_id );
		}
	}

	/**
	 * A caller may leave options or field_options out, or pass something that is not an
	 * array. Neither should warn, and the stored value should always read back as an array
	 * so consumers of $field->field_options can index it safely.
	 *
	 * @covers FrmField::create
	 */
	public function test_create_without_option_arrays() {
		$form_id = $this->factory->form->get_id_by_key( 'contact-db12' );

		$cases = array(
			'field_options missing'    => array(
				'name'    => 'No field options',
				'type'    => 'text',
				'form_id' => $form_id,
				'options' => array(),
			),
			'both option keys missing' => array(
				'name'    => 'Neither option key',
				'type'    => 'text',
				'form_id' => $form_id,
			),
			'field_options not array'  => array(
				'name'          => 'Field options is a string',
				'type'          => 'text',
				'form_id'       => $form_id,
				'options'       => array(),
				'field_options' => '',
			),
		);

		foreach ( $cases as $label => $values ) {
			$field_id = FrmField::create( $values );
			$this->assertIsNumeric( $field_id, 'A field should be created when ' . $label . '.' );

			$field = FrmField::getOne( $field_id );
			$this->assertIsArray( $field->field_options, 'field_options should read back as an array when ' . $label . '.' );
			$this->assertIsArray( $field->options, 'options should read back as an array when ' . $label . '.' );

			FrmField::destroy( $field_id );
			unset( $label, $values );
		}
	}

	/**
	 * @covers FrmField::getAll
	 */
	public function test_getAll() {
		$forms = array(
			$this->contact_form_key    => $this->contact_form_field_count,
			$this->all_fields_form_key => $this->all_field_types_count - $this->contact_form_field_count - 3,
		);

		foreach ( $forms as $form_key => $expected_count ) {
			$form_id = $this->factory->form->get_id_by_key( $form_key );
			$fields  = FrmField::getAll( array( 'fi.form_id' => (int) $form_id ) );
			$this->assertNotEmpty( $fields );
			$this->assertCount( $expected_count, $fields, 'An incorrect number of fields are retrieved with FrmField::getAll.' );
		}
	}

	/**
	 * @covers FrmField::get_all_for_form
	 */
	public function test_get_all_for_form() {
		$forms = array(
			'basic_test'         => array(
				'form_key' => $this->contact_form_key,
				'count'    => $this->contact_form_field_count,
			),
			'no_repeat_or_embed' => array(
				'form_key' => $this->all_fields_form_key,
				'count'    => $this->all_field_types_count - $this->contact_form_field_count - 3,
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
			$this->assertCount( $args['count'], $fields, 'An incorrect number of fields are retrieved with FrmField::get_all_for_form for ' . $test . '.' );
		}
	}
}
