<?php

/**
 * @group fields
 */
class test_FrmFieldValidate extends FrmUnitTest {

	protected $form;

	public function setUp(): void {
		parent::setUp();

		$this->create_validation_form();
	}

	protected function create_validation_form() {
		$this->form  = $this->factory->form->create_and_get();
		$field_types = $this->get_all_fields();

		foreach ( $field_types as $field_type ) {
			$this->factory->field->create(
				array(
					'type'      => $field_type,
					'form_id'   => $this->form->id,
					'field_key' => $this->get_field_key( $field_type ),
				)
			);
		}
	}

	protected function get_all_fields() {
		$fields  = array_keys( FrmField::field_selection() );
		$exclude = array( 'html' );
		return array_diff( $fields, $exclude );
	}

	/**
	 * @covers FrmEntryValidate::validate
	 */
	public function test_not_required_fields() {
		$_POST = array(
			'form_id'   => $this->form->id,
			'item_meta' => array(),
			'action'    => 'create',
		);

		$errors       = FrmEntryValidate::validate( $_POST );
		$error_fields = array();

		if ( $errors ) {
			$error_field_ids = array_keys( $errors );

			foreach ( $error_field_ids as $error_field ) {
				$field          = FrmField::getOne( str_replace( 'field', '', $error_field ) );
				$error_fields[] = $field ? $field->type : $error_field;
			}
		}

		$this->assertEmpty( $errors, 'A field was required when it should not have been. ' . implode( ', ', $error_fields ) );
	}

	/**
	 * @covers FrmFieldType::validate
	 * @covers FrmFieldNumber::validate
	 * @covers FrmFieldPhone::validate
	 * @covers FrmFieldUrl::validate
	 */
	public function test_format_validation() {
		$test_formats = $this->expected_format_errors();

		foreach ( $test_formats as $test_format ) {
			$field_key = $this->get_field_key( $test_format['type'] );
			$field_id  = FrmField::get_id_by_key( $field_key );
			$errors    = $this->check_single_value( array( $field_id => $test_format['value'] ) );

			if ( $test_format['invalid'] ) {
				$this->assertNotEmpty( $errors, $test_format['type'] . ' value ' . $test_format['value'] . ' passed validation' );
			} else {
				$this->assertEmpty( $errors, $test_format['type'] . ' value ' . $test_format['value'] . ' did not pass validation' );
			}
		}
	}

	protected function expected_format_errors() {
		return array(
			array(
				'type'    => 'number',
				'value'   => 123,
				'invalid' => false,
			),
			array(
				'type'    => 'number',
				'value'   => 'hello',
				'invalid' => true,
			),
			array(
				'type'    => 'number',
				'value'   => '1.234',
				'invalid' => false,
			),
			array(
				'type'    => 'phone',
				'value'   => '232-343-2322',
				'invalid' => false,
			),
			array(
				'type'    => 'phone',
				'value'   => '2323',
				'invalid' => true,
			),
			array(
				'type'    => 'url',
				'value'   => '2323',
				'invalid' => true,
			),
			array(
				'type'    => 'url',
				'value'   => 'http://',
				'invalid' => false,
			),
		);
	}

	/**
	 * @covers FrmEntryValidate::validate
	 */
	public function test_empty_required_fields() {
		$fields = $this->factory->field->get_fields_from_form( $this->form->id );
		$this->set_required_fields( $fields );

		$_POST = array(
			'form_id'   => $this->form->id,
			'item_meta' => array(),
			'action'    => 'create',
		);

		$errors = FrmEntryValidate::validate( $_POST );
		$this->assertNotEmpty( $errors );
		$error_fields = array();

		if ( $errors ) {
			foreach ( $fields as $field ) {
				if ( ! isset( $errors[ 'field' . $field->id ] ) ) {
					$error_fields[] = $field->type;
				}
			}
		}

		$this->assertEmpty( $error_fields, 'A field was not required when it should have been. ' . implode( ', ', $error_fields ) );
	}

	public function test_filled_required_fields() {
		$_POST        = $this->factory->field->generate_entry_array( $this->form );
		$errors       = FrmEntryValidate::validate( $_POST );
		$error_fields = array();

		if ( $errors ) {
			$error_field_ids = array_keys( $errors );

			foreach ( $error_field_ids as $error_field ) {
				$field          = FrmField::getOne( str_replace( 'field', '', $error_field ) );
				$error_fields[] = $field ? $field->type : $error_field;
			}
		}

		$this->assertEmpty( $error_fields, 'A field was required when it was not empty. ' . implode( ', ', $error_fields ) );
	}

	/**
	 * When a url field is required, http:// should not pass
	 *
	 * @covers FrmFieldUrl::validate
	 */
	public function test_url_value() {
		$field = FrmField::getOne( $this->get_field_key( 'url' ) );
		$this->assertNotEmpty( $field );

		$this->set_required_field( $field );

		$errors = $this->check_single_value( array( $field->id => 'http://' ) );
		$this->assertTrue( isset( $errors[ 'field' . $field->id ] ), 'http:// passed required validation ' . print_r( $errors, 1 ) );
	}

	/**
	 * @covers FrmFieldEmail::validate
	 */
	public function test_email_value() {
		$field = $this->factory->field->get_object_by_id( $this->get_field_key( 'email' ) );
		$this->assertNotEmpty( $field );
		$this->set_required_field( $field );

		$errors = $this->check_single_value( array( $field->id => 'notemail@' ) );
		$this->assertTrue( isset( $errors[ 'field' . $field->id ] ), 'Poorly formatted email passed validation ' . print_r( $errors, 1 ) );

		$errors = $this->check_single_value( array( $field->id => '' ) );
		$this->assertTrue( isset( $errors[ 'field' . $field->id ] ), 'Email email passed required validation ' . print_r( $errors, 1 ) );

		$errors = $this->check_single_value( array( $field->id => 'email@example.com' ) );
		$this->assertFalse( isset( $errors[ 'field' . $field->id ] ), 'Properly formatted email did not pass validation ' . print_r( $errors, 1 ) );
	}

	/**
	 * @covers FrmFieldNumber::validate
	 */
	public function test_number_validation() {
		$field  = $this->factory->field->get_object_by_id( $this->get_field_key( 'number' ) );
		$errors = $this->check_single_value( array( $field->id => '10.5' ) );
		$this->assertFalse( isset( $errors[ 'field' . $field->id ] ), 'Number failed validation ' . print_r( $errors, 1 ) );

		$field = $this->factory->field->create_and_get(
			array(
				'type'          => 'number',
				'form_id'       => $this->form->id,
				'field_options' => array(
					'minnum' => 0,
					'maxnum' => 20,
				),
			)
		);
		$this->assertEquals( 20, $field->field_options['maxnum'] );

		$errors = $this->check_single_value( array( $field->id => '10.5' ) );
		$this->assertFalse( isset( $errors[ 'field' . $field->id ] ), 'Number failed range validation ' . print_r( $errors, 1 ) );

		$errors = $this->check_single_value( array( $field->id => 'not numeric' ) );
		$this->assertTrue( isset( $errors[ 'field' . $field->id ] ), 'Number failed numeric validation' );

		$errors = $this->check_single_value( array( $field->id => '25' ) );
		$this->assertTrue( isset( $errors[ 'field' . $field->id ] ), 'Number failed max range validation' );

		$errors = $this->check_single_value( array( $field->id => '-25' ) );
		$this->assertTrue( isset( $errors[ 'field' . $field->id ] ), 'Number failed min range validation' );
	}

	protected function set_required_fields( $fields ) {
		foreach ( $fields as $field ) {
			$this->set_required_field( $field );
		}
	}

	protected function set_required_field( $field ) {
		global $wpdb;
		$query_results = $wpdb->update( $wpdb->prefix . 'frm_fields', array( 'required' => 1 ), array( 'id' => $field->id ) );

		if ( $query_results ) {
			wp_cache_delete( $field->id, 'frm_field' );
			FrmField::delete_form_transient( $this->form->id );

			$field = FrmField::getOne( $field->id );
			$this->assertNotEmpty( $field->required );
		}
	}

	protected function get_field_key( $field_type ) {
		return $field_type . '-form' . $this->form->id;
	}

	protected function check_single_value( $item_meta ) {
		$_POST = array(
			'form_id'   => $this->form->id,
			'item_meta' => $item_meta,
			'action'    => 'create',
		);

		return FrmEntryValidate::validate( $_POST );
	}

	/**
	 * @covers FrmEntryValidate::phone_format
	 */
	public function test_phone_format() {
		$check_formats = array(
			array(
				'field_key' => 'phone_with_default_format',
				'format'    => '',
				'expected'  => $this->run_private_method( array( 'FrmEntryValidate', 'default_phone_format' ), array() ),
			),
			array(
				'field_key' => 'phone_with_format',
				'format'    => '999-999-9999',
				'expected'  => '^\d\d\d-\d\d\d-\d\d\d\d$',
			),
			array(
				'field_key' => 'phone_with_regex',
				'format'    => '^\d{3}-\d{4}$',
				'expected'  => '^\d{3}-\d{4}$', // leave it alone
			),
		);

		foreach ( $check_formats as $check_it ) {
			$field = $this->factory->field->create_and_get(
				array(
					'type'          => 'phone',
					'form_id'       => $this->form->id,
					'field_key'     => $check_it['field_key'],
					'field_options' => array(
						'format' => $check_it['format'],
					),
				)
			);
			$this->assertEquals( $check_it['format'], $field->field_options['format'] );

			$format = FrmEntryValidate::phone_format( $field );
			$this->assertEquals( '/' . $check_it['expected'] . '/', $format );
		}
	}

	/**
	 * @covers FrmEntryValidate::create_regular_expression_from_format
	 */
	public function test_create_regular_expression_from_format() {
		$formats = array(
			'(999)999-2323' => '^\(\d\d\d\)\d\d\d-\d\d\d\d$',
			'a9aa2328'      => '^[a-zA-Z]\d[a-zA-Z][a-zA-Z]\d\d\d\d$',
			'****'          => '^\w\w\w\w$',
			'99/23'         => '^\d\d\/\d\d$',
			'99?99'         => '^\d\d(\d\d)?$',
		);

		foreach ( $formats as $start => $expected ) {
			$new_format = $this->run_private_method( array( 'FrmEntryValidate', 'create_regular_expression_from_format' ), array( $start ) );
			$this->assertEquals( $expected, $new_format );
		}
	}

	/**
	 * @covers FrmEntryValidate::is_akismet_enabled_for_user
	 */
	public function test_is_akismet_enabled_for_user() {
		$this->assertEmpty( $this->form->options['akismet'] );
		$enabled = $this->run_private_method( array( 'FrmEntryValidate', 'is_akismet_enabled_for_user' ), array( $this->form->id ) );
		$this->assertFalse( $enabled );

		$akismet_for_everyone = $this->factory->form->create_and_get(
			array(
				'options' => array(
					'akismet' => '1',
				),
			)
		);
		$this->assertNotEmpty( $akismet_for_everyone->options['akismet'] );
		$enabled = $this->run_private_method( array( 'FrmEntryValidate', 'is_akismet_enabled_for_user' ), array( $akismet_for_everyone->id ) );
		$this->assertTrue( $enabled );

		$akismet_logged = $this->factory->form->create_and_get(
			array(
				'options' => array(
					'akismet' => 'logged',
				),
			)
		);
		$this->assertEquals( 'logged', $akismet_logged->options['akismet'] );

		wp_set_current_user( 0 );
		$this->assertFalse( is_user_logged_in() );
		$enabled = $this->run_private_method( array( 'FrmEntryValidate', 'is_akismet_enabled_for_user' ), array( $akismet_logged->id ) );
		$this->assertTrue( $enabled, 'Akismet not enabled for logged out users' );

		$this->set_current_user_to_1();
		$this->assertTrue( is_user_logged_in() );
		$enabled = $this->run_private_method( array( 'FrmEntryValidate', 'is_akismet_enabled_for_user' ), array( $akismet_logged->id ) );
		$this->assertFalse( $enabled, 'Akismet enabled for logged in users' );
	}
}
