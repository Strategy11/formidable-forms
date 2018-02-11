<?php

/**
 * @group fields
 */
class test_FrmFieldValidate extends FrmUnitTest {

	protected $form;

	public function setUp() {
		parent::setUp();

		$this->create_form();
	}

	protected function create_form() {
		$this->form = $this->factory->form->create_and_get();
		$field_types = $this->get_all_fields();
		foreach ( $field_types as $field_type ) {
			$this->factory->field->create( array(
				'type'      => $field_type,
				'form_id'   => $this->form->id,
				'field_key' => $this->get_field_key( $field_type ),
			) );
		}
	}

	protected function get_all_fields() {
		$fields = array_keys( FrmField::field_selection() );
		$exclude = array( 'html' );
		return array_diff( $fields, $exclude );
	}

	/**
	 * @covers FrmFieldValidate::validate
	 */
	public function test_not_required_fields() {
		$_POST = array(
			'form_id'   => $this->form->id,
			'item_meta' => array(),
			'action'    => 'create',
		);

		$errors = FrmEntryValidate::validate( $_POST );
		$error_fields = array();

		if ( ! empty( $errors ) ) {
			$error_field_ids = array_keys( $errors );
			foreach ( $error_field_ids as $error_field ) {
				$field = FrmField::getOne( str_replace( 'field', '', $error_field ) );
				$error_fields[] = ( $field ) ? $field->type : $error_field;
			}
		}

		$this->assertEmpty( $errors, 'A field was required when it should not have been. ' . implode( ', ', $error_fields ) );
	}

	/**
	 * @covers FrmFieldType::validate
	 */
	public function test_format_validation() {
		$test_formats = $this->expected_format_errors();
		foreach ( $test_formats as $test_format ) {
			$field_key = $this->get_field_key( $test_format['type'] );
			$field_id = FrmField::get_id_by_key( $field_key );

			$errors = $this->check_single_value( array( $field_id => $test_format['value'] ) );

			if ( $test_format['invalid'] ) {
				$this->assertNotEmpty( $errors, $test_format['type'] .' value ' . $test_format['value'] .' passed validation'  );
			} else {
				$this->assertEmpty( $errors, $test_format['type'] .' value ' . $test_format['value'] .' did not pass validation'  );
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
	 * @covers FrmFieldValidate::validate
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

		if ( ! empty( $errors ) ) {
			$error_field_ids = array_keys( $errors );
			foreach ( $fields as $field ) {
				if ( ! isset( $errors[ 'field'. $field->id ] ) ) {
					$error_fields[] = $field->type;
				}
			}
		}

		$this->assertEmpty( $error_fields, 'A field was not required when it should have been. ' . implode( ', ', $error_fields ) );
	}

	public function test_filled_required_fields() {
		$_POST = $this->factory->field->generate_entry_array( $this->form );

		$errors = FrmEntryValidate::validate( $_POST );

		$error_fields = array();

		if ( ! empty( $errors ) ) {
			$error_field_ids = array_keys( $errors );
			foreach ( $error_field_ids as $error_field ) {
				$field = FrmField::getOne( str_replace( 'field', '', $error_field ) );
				$error_fields[] = $field ? $field->type : $error_field;
			}
		}

		$this->assertEmpty( $error_fields, 'A field was required when it was not empty. ' . implode( ', ', $error_fields ) );
	}

	/**
	 * When a url field is required, http:// should not pass
	 * @covers FrmFieldUrl:validate
	 */
	public function test_url_value() {
		$field = FrmField::getOne( $this->get_field_key( 'url' ) );
		$this->assertNotEmpty( $field );

		$this->set_required_field( $field );

		$errors = $this->check_single_value( array( $field->id => 'http://' ) );
		$this->assertTrue( isset( $errors[ 'field'. $field->id ] ), 'http:// passed required validation '. print_r($errors,1) );
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
}
