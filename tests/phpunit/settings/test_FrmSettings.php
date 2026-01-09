<?php

/**
 * @group settings
 */
class Test_FrmSettings extends FrmUnitTest {
	private $frm_settings;

	public function setUp(): void {
		parent::setUp();

		// Assign the FrmAppHelper class to a property for easier reference in tests.
		$this->frm_settings = FrmAppHelper::get_settings();

		// Ensure the test property exists for these tests.
		$this->frm_settings->example_key = '';
	}

	/**
	 * Test updating a setting successfully.
	 */
	public function test_update_setting_success() {
		$key      = 'example_key';
		$value    = 'New Value';
		$sanitize = 'sanitize_text_field';
		$result   = $this->frm_settings->update_setting( $key, $value, $sanitize );

		// Assert the setting was updated successfully
		$this->assertTrue( $result, 'The setting should be updated successfully.' );
		$this->assertEquals( $value, $this->frm_settings->{$key}, 'The value of the setting should match the new value.' );
	}

	/**
	 * Test updating a setting with an invalid key.
	 */
	public function test_update_setting_invalid_key() {
		// 'invalid_key' is assumed not to be a property of FrmSettings.
		$key      = 'invalid_key';
		$value    = 'Some Value';
		$sanitize = 'sanitize_text_field';

		// Try to update the setting with an invalid key.
		$result = $this->frm_settings->update_setting( $key, $value, $sanitize );

		$this->assertFalse( $result, 'The method should return false when attempting to update a non-existent setting.' );
	}

	/**
	 * Test updating a setting with an invalid sanitization function.
	 */
	public function test_update_setting_invalid_sanitize() {
		// 'example_key' is a valid property, but 'non_existent_sanitize_function' is not callable.
		$key      = 'example_key';
		$value    = 'Another Value';
		$sanitize = 'non_existent_sanitize_function';

		// Try to update the setting with an invalid sanitization function.
		$result = $this->frm_settings->update_setting( $key, $value, $sanitize );

		$this->assertFalse( $result, 'The method should return false when an invalid sanitization function is used.' );
	}
}
