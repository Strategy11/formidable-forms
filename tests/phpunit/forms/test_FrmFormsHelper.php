<?php

/**
 * @group forms
 */
class test_FrmFormsHelper extends FrmUnitTest {

	/**
	 * @var stdClass|null
	 */
	private $form;

	/**
	 * @covers FrmFormsHelper::maybe_add_sanitize_url_attr
	 */
	public function test_maybe_add_sanitize_url_attr() {
		$this->form = $this->factory->form->create_and_get();
		$field_id   = $this->factory->field->create(
			array(
				'form_id' => $this->form->id,
				'type'    => 'text',
			)
		);

		$this->assert_maybe_add_sanitize_url_attr(
			'https://example.org/?param=[' . $field_id . ' sanitize_url=1]',
			'https://example.org/?param=[' . $field_id . ']',
			'The sanitize_url=1 option should get added if it is missing.'
		);

		$this->assert_maybe_add_sanitize_url_attr(
			'https://example.org/?param=[' . $field_id . ' sanitize_url=0]',
			'https://example.org/?param=[' . $field_id . ' sanitize_url=0]',
			'Nothing should change if the setting already exists.'
		);

		$this->assert_maybe_add_sanitize_url_attr(
			'https://example.org/?param=[' . $field_id . ' show="field_label" sanitize_url=1]',
			'https://example.org/?param=[' . $field_id . ' show="field_label"]',
			'Other shortcodes options need to stay when the sanitize_url=1 option is added.'
		);

		$this->assert_maybe_add_sanitize_url_attr(
			'https://example.org/?param=[if ' . $field_id . ' equals="value"][' . $field_id . ' sanitize_url=1][else]redirect2[/if ' . $field_id . ']',
			'https://example.org/?param=[if ' . $field_id . ' equals="value"][' . $field_id . '][else]redirect2[/if ' . $field_id . ']',
			'An if conditional and else shortcode should not be modified by a call to maybe_add_sanitize_url_attr.'
		);

		$this->assert_maybe_add_sanitize_url_attr(
			'[' . $field_id . ']',
			'[' . $field_id . ']',
			'The sanitize_url=1 option should only be automatically applied to URL parameters.'
		);

		$url_field_id = $this->factory->field->create(
			array(
				'form_id' => $this->form->id,
				'type'    => 'url',
			)
		);
		$this->assert_maybe_add_sanitize_url_attr(
			'[' . $url_field_id . ']?param=[' . $field_id . ' sanitize_url=1]',
			'[' . $url_field_id . ']?param=[' . $field_id . ']'
		);
	}

	/**
	 * @param string $expected
	 * @param string $url
	 * @param string $message
	 */
	private function assert_maybe_add_sanitize_url_attr( $expected, $url, $message = '' ) {
		$this->assertSame( $expected, FrmFormsHelper::maybe_add_sanitize_url_attr( $url, (int) $this->form->id ), $message );
	}

	/**
	 * @covers FrmFormsHelper::get_plan_required
	 */
	public function test_get_plan_required() {
		$this->assert_get_plan_required( 'free', array( 'Category1', 'free' ) );
		$this->assert_get_plan_required( 'Elite', array( 'Category1', 'Elite', 'Category2' ) );
		$this->assert_get_plan_required( 'Plus', array( 'Category1', 'Creator' ) );
		$this->assert_get_plan_required( 'Plus', array( 'Plus', 'Category2' ) );
	}

	/**
	 * @param string $expected
	 * @param array $categories
	 */
	private function assert_get_plan_required( $expected, $categories ) {
		$link = compact( 'categories' );
		$this->assertSame( $expected, FrmFormsHelper::get_plan_required( $link ) );
	}

	/**
	 * @covers FrmFormsHelper::get_form_style
	 */
	public function test_get_form_style() {
		// Test null and 'default' form values.
		$this->assertSame( 1, FrmFormsHelper::get_form_style( null ) );
		$this->assertSame( 1, FrmFormsHelper::get_form_style( 'default' ) );

		// Test object form values.
		// Test "disable Formidable styling" first.
		$form = $this->create_form_with_custom_style_value( '0' );
		$this->assertSame( '0', FrmFormsHelper::get_form_style( $form ) );

		$form = $this->create_form_with_custom_style_value( '' );
		$this->assertSame( '', FrmFormsHelper::get_form_style( $form ) );

		// Create a style and test a custom style value as well.
		$frm_style = new FrmStyle();
		$style_id  = $this->factory->post->create(
			array(
				'post_type'    => 'frm_styles',
				'post_content' => FrmAppHelper::prepare_and_encode( $frm_style->get_defaults() ),
			)
		);

		$form = $this->create_form_with_custom_style_value( $style_id );
		$this->assertSame( $style_id, FrmFormsHelper::get_form_style( $form ) );

		// Test array form values.
		$form = $form->options;
		$this->assertSame( $style_id, FrmFormsHelper::get_form_style( $form ) );

		unset( $form['custom_style'] );
		$this->assertSame( 1, FrmFormsHelper::get_form_style( $form ) );

		$form['custom_style'] = '';
		$this->assertSame( '', FrmFormsHelper::get_form_style( $form ) );

		$form['custom_style'] = '0';
		$this->assertSame( '0', FrmFormsHelper::get_form_style( $form ) );
	}

	/**
	 * @param string $custom_style
	 */
	private function create_form_with_custom_style_value( $custom_style ) {
		return $this->factory->form->create_and_get(
			array(
				'options' => array(
					'custom_style' => $custom_style,
				),
			)
		);
	}

	/**
	 * The invalid error message should include a list of links that jump to each field that failed validation.
	 *
	 * @covers FrmFormsHelper::get_invalid_error_message
	 */
	public function test_get_invalid_error_message_builds_clickable_field_links() {
		$this->form = $this->factory->form->create_and_get();

		$text_id     = $this->create_field_with_key( 'text', 'my_text' );
		$select_id   = $this->create_field_with_key( 'select', 'my_select' );
		$checkbox_id = $this->create_field_with_key( 'checkbox', 'my_checkbox' );
		$radio_id    = $this->create_field_with_key( 'radio', 'my_radio' );

		$message = FrmFormsHelper::get_invalid_error_message(
			array(
				'form'   => $this->form,
				'errors' => array(
					'field' . $text_id     => 'Text is required',
					'field' . $select_id   => 'Select is required',
					'field' . $checkbox_id => 'Checkbox is required',
					'field' . $radio_id    => 'Radio is required',
				),
			)
		);

		// The base invalid message is wrapped in a span, and the links are inside a list.
		$this->assertStringContainsString( '<ul>', $message );
		$this->assertStringContainsString( '<li><a href="#field_my_text">Text is required</a></li>', $message );
		$this->assertStringContainsString( '<li><a href="#field_my_select">Select is required</a></li>', $message );

		// Checkbox and radio links target the first option so focus lands on a real input.
		$this->assertStringContainsString( '<li><a href="#field_my_checkbox-0">Checkbox is required</a></li>', $message );
		$this->assertStringContainsString( '<li><a href="#field_my_radio-0">Radio is required</a></li>', $message );
	}

	/**
	 * Non-field errors such as 'form' or 'spam' have no input to link to and should be skipped.
	 *
	 * @covers FrmFormsHelper::get_invalid_error_message
	 */
	public function test_get_invalid_error_message_skips_non_field_errors() {
		$this->form = $this->factory->form->create_and_get();
		$text_id    = $this->create_field_with_key( 'text', 'linkable_text' );

		$message = FrmFormsHelper::get_invalid_error_message(
			array(
				'form'   => $this->form,
				'errors' => array(
					'form'             => 'There was a problem with your submission.',
					'spam'             => 'Your entry appears to be spam!',
					'field' . $text_id => 'Text is required',
				),
			)
		);

		$this->assertStringContainsString( '<li><a href="#field_linkable_text">Text is required</a></li>', $message );
		$this->assertStringNotContainsString( 'appears to be spam', $message );
		$this->assertStringNotContainsString( '<a href="#field_spam', $message );
	}

	/**
	 * With no field errors there should be no list at all, only the base message.
	 *
	 * @covers FrmFormsHelper::get_invalid_error_message
	 */
	public function test_get_invalid_error_message_without_errors_has_no_list() {
		$this->form = $this->factory->form->create_and_get();

		$message = FrmFormsHelper::get_invalid_error_message( array( 'form' => $this->form ) );

		$this->assertStringNotContainsString( '<ul>', $message );
		$this->assertStringNotContainsString( '<li>', $message );
	}

	/**
	 * Error text and IDs must be escaped so a malicious field key or message cannot inject markup.
	 *
	 * @covers FrmFormsHelper::get_invalid_error_message
	 */
	public function test_get_invalid_error_message_escapes_output() {
		$this->form = $this->factory->form->create_and_get();
		$text_id    = $this->create_field_with_key( 'text', 'escape_test' );

		$message = FrmFormsHelper::get_invalid_error_message(
			array(
				'form'   => $this->form,
				'errors' => array(
					'field' . $text_id => '<script>alert(1)</script>',
				),
			)
		);

		$this->assertStringNotContainsString( '<script>alert(1)</script>', $message );
		$this->assertStringContainsString( '&lt;script&gt;', $message );
	}

	/**
	 * A repeater style error key (field{id}-{row_meta}-{row}) should link to the field in the correct row.
	 *
	 * @covers FrmFormsHelper::get_invalid_error_message
	 */
	public function test_get_invalid_error_message_handles_repeater_row_keys() {
		$this->form = $this->factory->form->create_and_get();
		$text_id    = $this->create_field_with_key( 'text', 'repeated_text' );

		$message = FrmFormsHelper::get_invalid_error_message(
			array(
				'form'   => $this->form,
				'errors' => array(
					'field' . $text_id . '-55-2' => 'Repeated text is required',
				),
			)
		);

		$this->assertStringContainsString( '<li><a href="#field_repeated_text-2">Repeated text is required</a></li>', $message );
	}

	/**
	 * @param string $type
	 * @param string $field_key
	 *
	 * @return int
	 */
	private function create_field_with_key( $type, $field_key ) {
		return $this->factory->field->create(
			array(
				'form_id'   => $this->form->id,
				'type'      => $type,
				'field_key' => $field_key,
			)
		);
	}
}
