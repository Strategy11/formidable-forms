<?php

/**
 * @group fields
 */
class test_FrmFieldAddress extends FrmUnitTest {

	/**
	 * Test empty_value_array returns correct structure.
	 */
	public function test_empty_value_array_returns_correct_structure() {
		$controller = new FrmAddressesController();
		$result     = $this->run_private_method(
			array( $controller, 'empty_value_array' ),
			array()
		);

		$this->assertIsArray( $result, 'Result should be an array.' );
		$this->assertArrayHasKey( 'line1', $result, 'Result should have line1 key.' );
		$this->assertArrayHasKey( 'line2', $result, 'Result should have line2 key.' );
		$this->assertArrayHasKey( 'city', $result, 'Result should have city key.' );
		$this->assertArrayHasKey( 'state', $result, 'Result should have state key.' );
		$this->assertArrayHasKey( 'zip', $result, 'Result should have zip key.' );
		$this->assertArrayHasKey( 'country', $result, 'Result should have country key.' );
		$this->assertSame( '', $result['line1'], 'line1 should be empty string.' );
		$this->assertSame( '', $result['line2'], 'line2 should be empty string.' );
		$this->assertSame( '', $result['city'], 'city should be empty string.' );
		$this->assertSame( '', $result['state'], 'state should be empty string.' );
		$this->assertSame( '', $result['zip'], 'zip should be empty string.' );
		$this->assertSame( '', $result['country'], 'country should be empty string.' );
	}

	/**
	 * Test get_sub_fields for international address type.
	 */
	public function test_get_sub_fields_international_returns_all_fields() {
		$field = array(
			'id'           => 10,
			'type'         => 'address',
			'address_type' => 'international',
		);

		$controller = new FrmAddressesController();
		$result     = $this->run_private_method(
			array( $controller, 'get_sub_fields' ),
			array( $field )
		);

		$this->assertIsArray( $result, 'Result should be an array.' );
		$this->assertArrayHasKey( 'line1', $result, 'Result should have line1.' );
		$this->assertArrayHasKey( 'line2', $result, 'Result should have line2.' );
		$this->assertArrayHasKey( 'city', $result, 'Result should have city.' );
		$this->assertArrayHasKey( 'state', $result, 'Result should have state.' );
		$this->assertArrayHasKey( 'zip', $result, 'Result should have zip.' );
		$this->assertArrayHasKey( 'country', $result, 'Result should have country.' );
	}

	/**
	 * Test get_sub_fields for US address type.
	 */
	public function test_get_sub_fields_us_returns_correct_fields() {
		$field = array(
			'id'           => 10,
			'type'         => 'address',
			'address_type' => 'us',
		);

		$controller = new FrmAddressesController();
		$result     = $this->run_private_method(
			array( $controller, 'get_sub_fields' ),
			array( $field )
		);

		$this->assertIsArray( $result, 'Result should be an array.' );
		$this->assertArrayHasKey( 'line1', $result, 'Result should have line1.' );
		$this->assertArrayHasKey( 'line2', $result, 'Result should have line2.' );
		$this->assertArrayHasKey( 'city', $result, 'Result should have city.' );
		$this->assertArrayHasKey( 'state', $result, 'Result should have state.' );
		$this->assertArrayHasKey( 'zip', $result, 'Result should have zip.' );
		$this->assertArrayNotHasKey( 'country', $result, 'Result should not have country for US address.' );
	}

	/**
	 * Test get_sub_fields for Europe address type (no state field).
	 */
	public function test_get_sub_fields_europe_excludes_state() {
		$field = array(
			'id'           => 10,
			'type'         => 'address',
			'address_type' => 'europe',
		);

		$controller = new FrmAddressesController();
		$result     = $this->run_private_method(
			array( $controller, 'get_sub_fields' ),
			array( $field )
		);

		$this->assertIsArray( $result, 'Result should be an array.' );
		$this->assertArrayHasKey( 'line1', $result, 'Result should have line1.' );
		$this->assertArrayHasKey( 'line2', $result, 'Result should have line2.' );
		$this->assertArrayHasKey( 'city', $result, 'Result should have city.' );
		$this->assertArrayNotHasKey( 'state', $result, 'Result should not have state for Europe address.' );
		$this->assertArrayHasKey( 'zip', $result, 'Result should have zip.' );
		$this->assertArrayHasKey( 'country', $result, 'Result should have country.' );
	}

	/**
	 * Test get_sub_fields for generic address type.
	 */
	public function test_get_sub_fields_generic_returns_all_fields() {
		$field = array(
			'id'           => 10,
			'type'         => 'address',
			'address_type' => 'generic',
		);

		$controller = new FrmAddressesController();
		$result     = $this->run_private_method(
			array( $controller, 'get_sub_fields' ),
			array( $field )
		);

		$this->assertIsArray( $result, 'Result should be an array.' );
		$this->assertArrayHasKey( 'line1', $result, 'Result should have line1.' );
		$this->assertArrayHasKey( 'line2', $result, 'Result should have line2.' );
		$this->assertArrayHasKey( 'city', $result, 'Result should have city.' );
		$this->assertArrayHasKey( 'state', $result, 'Result should have state.' );
		$this->assertArrayHasKey( 'zip', $result, 'Result should have zip.' );
		// Generic address type does not include country field
		$this->assertArrayNotHasKey( 'country', $result, 'Result should not have country for generic address type.' );
	}

	/**
	 * Test get_sub_fields with missing address_type defaults to international.
	 */
	public function test_get_sub_fields_missing_address_type_defaults_to_international() {
		$field = array(
			'id'   => 10,
			'type' => 'address',
		);

		$controller = new FrmAddressesController();
		$result     = $this->run_private_method(
			array( $controller, 'get_sub_fields' ),
			array( $field )
		);

		$this->assertArrayHasKey( 'country', $result, 'Result should have country when address_type is missing.' );
	}

	/**
	 * Test fill_values merges empty array with value.
	 */
	public function test_fill_values_merges_empty_array_with_value() {
		$defaults = array(
			'line1'   => '',
			'line2'   => '',
			'city'    => '',
			'state'   => '',
			'zip'     => '',
			'country' => '',
		);

		$value = array(
			'line1' => '123 Main St',
			'city'  => 'Springfield',
		);

		FrmComboFieldsController::fill_values( $value, $defaults );

		$this->assertSame( '123 Main St', $value['line1'], 'line1 should be preserved.' );
		$this->assertSame( '', $value['line2'], 'line2 should be empty.' );
		$this->assertSame( 'Springfield', $value['city'], 'city should be preserved.' );
		$this->assertSame( '', $value['state'], 'state should be empty.' );
		$this->assertSame( '', $value['zip'], 'zip should be empty.' );
		$this->assertSame( '', $value['country'], 'country should be empty.' );
	}

	/**
	 * Test fill_values with empty value uses defaults.
	 */
	public function test_fill_values_with_empty_value_uses_defaults() {
		$defaults = array(
			'line1'   => '',
			'line2'   => '',
			'city'    => '',
			'state'   => '',
			'zip'     => '',
			'country' => '',
		);

		$value = array();

		FrmComboFieldsController::fill_values( $value, $defaults );

		$this->assertSame( $defaults, $value, 'Value should match defaults.' );
	}

	/**
	 * Test get_country_code for valid country.
	 */
	public function test_get_country_code_returns_code_for_valid_country() {
		$result = FrmAddressesController::get_country_code( 'United States' );
		$this->assertSame( 'US', $result, 'Country code for United States should be US.' );
	}

	/**
	 * Test get_country_code for invalid country.
	 */
	public function test_get_country_code_returns_empty_string_for_invalid_country() {
		$result = FrmAddressesController::get_country_code( 'Invalid Country Name' );
		$this->assertSame( '', $result, 'Country code for invalid country should be empty string.' );
	}

	/**
	 * Test get_country_code with empty string.
	 */
	public function test_get_country_code_returns_empty_string_for_empty_string() {
		$result = FrmAddressesController::get_country_code( '' );
		$this->assertSame( '', $result, 'Country code for empty string should be empty string.' );
	}

	/**
	 * Test get_export_headings adds address columns.
	 */
	public function test_get_export_headings_adds_address_columns() {
		$field = array(
			'id'   => '10',
			'type' => 'address',
			'name' => 'Address',
		);

		$address_field = new FrmFieldAddress( $field );
		$result        = $address_field->get_export_headings();

		$this->assertArrayHasKey( '10', $result, 'Result should have main field column.' );
		$this->assertArrayHasKey( '10_line1', $result, 'Result should have line1 column.' );
		$this->assertArrayHasKey( '10_line2', $result, 'Result should have line2 column.' );
		$this->assertArrayHasKey( '10_city', $result, 'Result should have city column.' );
		$this->assertArrayHasKey( '10_state', $result, 'Result should have state column.' );
		$this->assertArrayHasKey( '10_zip', $result, 'Result should have zip column.' );
		$this->assertArrayHasKey( '10_country', $result, 'Result should have country column.' );
	}

	/**
	 * Test add_optional_class adds frm_optional class.
	 */
	public function test_add_optional_class_adds_frm_optional() {
		$class  = 'frm_form_field';
		$field  = array( 'type' => 'address' );
		$result = FrmAddressesController::add_optional_class( $class, $field );

		$this->assertStringContainsString( 'frm_optional', $result, 'Result should contain frm_optional class.' );
		$this->assertStringContainsString( 'frm_form_field', $result, 'Result should contain original class.' );
	}

	/**
	 * Test address field model can be instantiated.
	 */
	public function test_address_field_model_can_be_instantiated() {
		$field = new FrmFieldAddress();

		$this->assertInstanceOf( 'FrmFieldAddress', $field, 'Should be instance of FrmFieldAddress.' );
		$this->assertInstanceOf( 'FrmFieldCombo', $field, 'Should extend FrmFieldCombo.' );
	}

	/**
	 * @dataProvider address_type_provider
	 */
	public function test_get_sub_fields_for_different_address_types( $address_type, $expected_keys ) {
		$field = array(
			'id'           => 10,
			'type'         => 'address',
			'address_type' => $address_type,
		);

		$controller = new FrmAddressesController();
		$result     = $this->run_private_method(
			array( $controller, 'get_sub_fields' ),
			array( $field )
		);

		foreach ( $expected_keys as $key ) {
			$this->assertArrayHasKey( $key, $result, "Result should have {$key} for {$address_type} address type." );
		}
	}

	/**
	 * @return void array<(array<string> | string)>>
	 */
	public function address_type_provider() {
		yield 'international' => array( 'international', array( 'line1', 'line2', 'city', 'state', 'zip', 'country' ) );
		yield 'us' => array( 'us', array( 'line1', 'line2', 'city', 'state', 'zip' ) );
		yield 'europe' => array( 'europe', array( 'line1', 'line2', 'city', 'zip', 'country' ) );
		yield 'generic' => array( 'generic', array( 'line1', 'line2', 'city', 'state', 'zip' ) );
	}

	/**
	 * Create an address field in the database and return its field type object.
	 *
	 * @param string $address_type
	 * @param int    $required
	 *
	 * @return FrmFieldAddress
	 */
	private function create_address_field_type( $address_type = 'international', $required = 0 ) {
		$form  = $this->factory->form->create_and_get();
		$field = $this->factory->field->create_and_get(
			array(
				'form_id'       => $form->id,
				'type'          => 'address',
				'required'      => $required,
				'field_options' => array( 'address_type' => $address_type ),
			)
		);

		return new FrmFieldAddress( $field );
	}

	/**
	 * Test validate zip format for US addresses.
	 *
	 * @dataProvider us_zip_provider
	 */
	public function test_validate_checks_us_zip_format( $zip, $is_valid ) {
		$field_type = $this->create_address_field_type( 'us' );
		$field_id   = $field_type->get_field()->id;

		$errors = $field_type->validate(
			array(
				'errors' => array(),
				'id'     => $field_id,
				'value'  => array( 'zip' => $zip ),
			)
		);

		if ( $is_valid ) {
			$this->assertArrayNotHasKey( 'field' . $field_id . '-zip', $errors, 'Valid US zip should not add a zip error.' );
		} else {
			$this->assertArrayHasKey( 'field' . $field_id . '-zip', $errors, 'Invalid US zip should add a zip error.' );
		}
	}

	/**
	 * @return void array<(array<string> | string)>>
	 */
	public function us_zip_provider() {
		yield 'five digits' => array( '62704', true );
		yield 'zip plus four' => array( '62704-1234', true );
		yield 'too short' => array( '1234', false );
		yield 'letters' => array( 'ABCDE', false );
		yield 'zip plus four missing dash' => array( '627041234', false );
	}

	/**
	 * Test validate does not apply the US zip format to other address types.
	 */
	public function test_validate_skips_zip_format_for_international_type() {
		$field_type = $this->create_address_field_type( 'international' );
		$field_id   = $field_type->get_field()->id;

		$errors = $field_type->validate(
			array(
				'errors' => array(),
				'id'     => $field_id,
				'value'  => array( 'zip' => 'SW1A 2AA' ),
			)
		);

		$this->assertArrayNotHasKey( 'field' . $field_id . '-zip', $errors, 'International zip should not be checked against the US format.' );
	}

	/**
	 * Test validate flags empty required sub-fields but not the optional line2.
	 */
	public function test_validate_required_flags_empty_sub_fields() {
		$field_type = $this->create_address_field_type( 'international', 1 );
		$field_id   = $field_type->get_field()->id;

		$errors = $field_type->validate(
			array(
				'errors' => array(),
				'id'     => $field_id,
				'value'  => array( 'line1' => '123 Main St' ),
			)
		);

		$this->assertArrayNotHasKey( 'field' . $field_id . '-line1', $errors, 'A filled sub-field should not be flagged.' );
		$this->assertArrayNotHasKey( 'field' . $field_id . '-line2', $errors, 'The optional line2 sub-field should not be flagged.' );
		$this->assertArrayHasKey( 'field' . $field_id . '-city', $errors, 'An empty required city should be flagged.' );
		$this->assertArrayHasKey( 'field' . $field_id . '-state', $errors, 'An empty required state should be flagged.' );
		$this->assertArrayHasKey( 'field' . $field_id . '-zip', $errors, 'An empty required zip should be flagged.' );
		$this->assertArrayHasKey( 'field' . $field_id, $errors, 'The main field should get the blank message.' );
	}

	/**
	 * Test get_display_value formats an international address across lines.
	 */
	public function test_get_display_value_formats_international_address() {
		$field_type = new FrmFieldAddress(
			array(
				'id'           => 10,
				'type'         => 'address',
				'name'         => 'Address',
				'address_type' => 'international',
			)
		);

		$value = array(
			'line1'   => '123 Main St',
			'line2'   => 'Suite 5',
			'city'    => 'Springfield',
			'state'   => 'IL',
			'zip'     => '62704',
			'country' => 'United States',
		);

		$this->assertSame(
			'123 Main St <br/>Suite 5 <br/>Springfield, IL 62704 <br/>United States',
			$field_type->get_display_value( $value ),
			'International address should display as line1, line2, city/state/zip, country.'
		);
	}

	/**
	 * Test get_display_value uses zip before city and collapses the empty line2 for Europe addresses.
	 */
	public function test_get_display_value_formats_europe_address() {
		$field_type = new FrmFieldAddress(
			array(
				'id'           => 10,
				'type'         => 'address',
				'name'         => 'Address',
				'address_type' => 'europe',
			)
		);

		$value = array(
			'line1'   => '10 Downing St',
			'line2'   => '',
			'city'    => 'London',
			'zip'     => 'SW1A 2AA',
			'country' => 'United Kingdom',
		);

		$this->assertSame(
			'10 Downing St <br/>SW1A 2AA London <br/>United Kingdom',
			$field_type->get_display_value( $value ),
			'Europe address should display zip before city and collapse the empty line2.'
		);
	}

	/**
	 * Test get_display_value with a show attribute returns a single sub-field value.
	 */
	public function test_get_display_value_show_att_returns_sub_value() {
		$field_type = new FrmFieldAddress(
			array(
				'id'           => 10,
				'type'         => 'address',
				'name'         => 'Address',
				'address_type' => 'international',
			)
		);

		$value = array(
			'line1' => '123 Main St',
			'city'  => 'Springfield',
		);

		$this->assertSame( 'Springfield', $field_type->get_display_value( $value, array( 'show' => 'city' ) ), 'The show attribute should return only that sub-field value.' );
	}

	/**
	 * Test format_address_for_display with force_array returns the filled value array.
	 */
	public function test_format_address_for_display_force_array_returns_filled_array() {
		$field_type = new FrmFieldAddress(
			array(
				'id'           => 10,
				'type'         => 'address',
				'name'         => 'Address',
				'address_type' => 'international',
			)
		);

		$result = $field_type->format_address_for_display( array( 'line1' => '123 Main St' ), array( 'force_array' => true ) );

		$this->assertIsArray( $result, 'force_array should return an array.' );
		$this->assertSame( '123 Main St', $result['line1'], 'line1 should be preserved.' );
		$this->assertSame( '', $result['country'], 'Missing sub-fields should be filled with empty strings.' );
	}

	/**
	 * Test get_display_value returns an empty string when no meaningful sub-field is populated.
	 */
	public function test_get_display_value_returns_empty_string_for_line2_only() {
		$field_type = new FrmFieldAddress(
			array(
				'id'           => 10,
				'type'         => 'address',
				'name'         => 'Address',
				'address_type' => 'international',
			)
		);

		$this->assertSame( '', $field_type->get_display_value( array( 'line2' => 'Suite 5' ) ), 'An address with only line2 filled should display as empty.' );
	}

	/**
	 * Test get_display_value passes non-array values through unchanged.
	 */
	public function test_get_display_value_passes_string_through() {
		$field_type = new FrmFieldAddress(
			array(
				'id'           => 10,
				'type'         => 'address',
				'name'         => 'Address',
				'address_type' => 'international',
			)
		);

		$this->assertSame( 'plain value', $field_type->get_display_value( 'plain value' ), 'A non-array value should pass through unchanged.' );
	}

	/**
	 * Test address_string_to_array conversions.
	 *
	 * @dataProvider address_string_provider
	 */
	public function test_address_string_to_array( $value, $expected ) {
		$field_type = new FrmFieldAddress(
			array(
				'id'   => 10,
				'type' => 'address',
				'name' => 'Address',
			)
		);

		$this->assertSame( $expected, $field_type->address_string_to_array( $value ), 'The string should map to the address keys in order.' );
	}

	/**
	 * @return void array<(array | string)>>
	 */
	public function address_string_provider() {
		yield 'six parts map in order' => array(
			'123 Main St, Apt 2, Springfield, IL, 62704, United States',
			array(
				'line1'   => '123 Main St',
				'line2'   => 'Apt 2',
				'city'    => 'Springfield',
				'state'   => 'IL',
				'zip'     => '62704',
				'country' => 'United States',
			),
		);
		yield 'short string is padded' => array(
			'123 Main St, Springfield',
			array(
				'line1'   => '123 Main St',
				'line2'   => 'Springfield',
				'city'    => '',
				'state'   => '',
				'zip'     => '',
				'country' => '',
			),
		);
		yield 'long string is truncated' => array(
			'a, b, c, d, e, f, g',
			array(
				'line1'   => 'a',
				'line2'   => 'b',
				'city'    => 'c',
				'state'   => 'd',
				'zip'     => 'e',
				'country' => 'f',
			),
		);
		yield 'non-string returns empty values' => array(
			null,
			array(
				'line1'   => '',
				'line2'   => '',
				'city'    => '',
				'state'   => '',
				'zip'     => '',
				'country' => '',
			),
		);
		yield 'array passes through' => array(
			array( 'line1' => '123 Main St' ),
			array( 'line1' => '123 Main St' ),
		);
	}

	/**
	 * Test prepare_import_value maps CSV strings to address keys.
	 *
	 * @dataProvider import_value_provider
	 */
	public function test_prepare_import_value( $value, $expected ) {
		$field_type = new FrmFieldAddress(
			array(
				'id'   => 10,
				'type' => 'address',
				'name' => 'Address',
			)
		);

		$result = $this->run_private_method( array( $field_type, 'prepare_import_value' ), array( $value, array() ) );

		$this->assertSame( $expected, $result, 'The imported value should map to the expected address parts.' );
	}

	/**
	 * @return void array<(array | string)>>
	 */
	public function import_value_provider() {
		yield 'six parts include line2 and country' => array(
			'123 Main St, Apt 2, Springfield, IL, 62704, United States',
			array(
				'line1'   => '123 Main St',
				'line2'   => 'Apt 2',
				'city'    => 'Springfield',
				'state'   => 'IL',
				'zip'     => '62704',
				'country' => 'United States',
			),
		);
		yield 'five parts with numeric zip last include line2' => array(
			'123 Main St, Apt 2, Springfield, IL, 62704',
			array(
				'line1'   => '123 Main St',
				'line2'   => 'Apt 2',
				'city'    => 'Springfield',
				'state'   => 'IL',
				'zip'     => '62704',
				'country' => '',
			),
		);
		yield 'five parts with country last skip line2' => array(
			'123 Main St, Springfield, IL, 62704, United States',
			array(
				'line1'   => '123 Main St',
				'line2'   => '',
				'city'    => 'Springfield',
				'state'   => 'IL',
				'zip'     => '62704',
				'country' => 'United States',
			),
		);
		yield 'four parts skip line2 and country' => array(
			'123 Main St, Springfield, IL, 62704',
			array(
				'line1'   => '123 Main St',
				'line2'   => '',
				'city'    => 'Springfield',
				'state'   => 'IL',
				'zip'     => '62704',
				'country' => '',
			),
		);
		yield 'too few parts return the exploded value' => array(
			'123 Main St, Springfield',
			array( '123 Main St', 'Springfield' ),
		);
	}

	/**
	 * Test sanitize_value strips tags from every sub-field.
	 */
	public function test_sanitize_value_strips_tags() {
		$field_type = new FrmFieldAddress(
			array(
				'id'   => 10,
				'type' => 'address',
				'name' => 'Address',
			)
		);

		$value = array(
			'line1' => '<script>alert(1)</script>123 Main St',
			'city'  => '<b>Springfield</b>',
		);

		$field_type->sanitize_value( $value );

		$this->assertStringNotContainsString( '<script>', $value['line1'], 'Script tags should be stripped from line1.' );
		$this->assertStringNotContainsString( '<b>', $value['city'], 'Markup should be stripped from city.' );
		$this->assertStringContainsString( '123 Main St', $value['line1'], 'The text content should be kept.' );
	}

	/**
	 * Test the model's processed sub-fields match the expected keys and order per address type.
	 * The frontend renders from the model, so this must agree with the controller behavior.
	 *
	 * @dataProvider processed_sub_fields_provider
	 */
	public function test_get_processed_sub_fields_per_address_type( $address_type, $expected_order ) {
		$field_type = new FrmFieldAddress(
			array(
				'id'           => 10,
				'type'         => 'address',
				'name'         => 'Address',
				'address_type' => $address_type,
			)
		);

		$sub_fields = $this->run_private_method( array( $field_type, 'get_processed_sub_fields' ), array() );

		$this->assertSame( $expected_order, array_keys( $sub_fields ), "Sub-field keys and order should match for the {$address_type} type." );
	}

	/**
	 * @return void array<(array<string> | string)>>
	 */
	public function processed_sub_fields_provider() {
		yield 'international' => array( 'international', array( 'line1', 'line2', 'city', 'state', 'zip', 'country' ) );
		yield 'us' => array( 'us', array( 'line1', 'line2', 'city', 'state', 'zip' ) );
		yield 'europe' => array( 'europe', array( 'line1', 'line2', 'zip', 'city', 'country' ) );
		yield 'generic' => array( 'generic', array( 'line1', 'line2', 'city', 'state', 'zip' ) );
	}

	/**
	 * Test the model uses dropdowns for the US state and the international and Europe country.
	 */
	public function test_get_processed_sub_fields_dropdown_types() {
		$us_field = new FrmFieldAddress(
			array(
				'id'           => 10,
				'type'         => 'address',
				'name'         => 'Address',
				'address_type' => 'us',
			)
		);
		$us_subs  = $this->run_private_method( array( $us_field, 'get_processed_sub_fields' ), array() );
		$this->assertSame( 'select', $us_subs['state']['type'], 'US state should be a dropdown.' );

		foreach ( array( 'international', 'europe' ) as $address_type ) {
			$field_type = new FrmFieldAddress(
				array(
					'id'           => 10,
					'type'         => 'address',
					'name'         => 'Address',
					'address_type' => $address_type,
				)
			);
			$sub_fields = $this->run_private_method( array( $field_type, 'get_processed_sub_fields' ), array() );
			$this->assertSame( 'select', $sub_fields['country']['type'], "Country should be a dropdown for the {$address_type} type." );
		}
	}

	/**
	 * Test extra_field_opts defaults the address type to international.
	 */
	public function test_extra_field_opts_defaults() {
		$field_type = new FrmFieldAddress();
		$options    = $this->run_private_method( array( $field_type, 'extra_field_opts' ), array() );

		$this->assertSame( 'international', $options['address_type'], 'The default address type should be international.' );
		$this->assertArrayHasKey( 'city_desc', $options, 'Sub-field descriptions should be included.' );
		$this->assertSame( '', $options['line1_desc'], 'line1 should default to an empty description.' );
	}
}
