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
}
