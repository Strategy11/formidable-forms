<?php

/**
 * @group forms
 */
class test_FrmFormsHelper extends FrmUnitTest {

	/**
	 * @covers FrmFormsHelper::maybe_add_sanitize_url_attr
	 */
	public function test_maybe_add_sanitize_url_attr() {
		$form  = $this->factory->form->create_and_get();
		$field = $this->factory->field->create_and_get(
			array(
				'form_id' => $form->id,
				'type'    => 'text',
			)
		);

		// Test that the sanitize_url option gets added.
		$url           = 'https://example.org/?param=[' . $field->id . ']';
		$sanitized_url = FrmFormsHelper::maybe_add_sanitize_url_attr( $url, (int) $form->id );
		$this->assertNotEquals( $url, $sanitized_url );
		$this->assertEquals( 'https://example.org/?param=[' . $field->id . ' sanitize_url=1]', $sanitized_url );

		// Test that a setting does not get overwritten.
		$url           = 'https://example.org/?param=[' . $field->id . ' sanitize_url=0]';
		$sanitized_url = FrmFormsHelper::maybe_add_sanitize_url_attr( $url, (int) $form->id );
		$this->assertEquals( $url, $sanitized_url );

		// Test that other options are preserved.
		$url           = 'https://example.org/?param=[' . $field->id . ' show="field_label"]';
		$sanitized_url = FrmFormsHelper::maybe_add_sanitize_url_attr( $url, (int) $form->id );
		$this->assertNotEquals( $url, $sanitized_url );
		$this->assertEquals( 'https://example.org/?param=[' . $field->id . ' show="field_label" sanitize_url=1]', $sanitized_url );
	}
}
