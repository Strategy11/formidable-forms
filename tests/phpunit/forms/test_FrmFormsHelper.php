<?php

/**
 * @group forms
 */
class test_FrmFormsHelper extends FrmUnitTest {

	public $factory;
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

	private function assert_maybe_add_sanitize_url_attr( $expected, $url, $message = '' ) {
		$this->assertEquals( $expected, FrmFormsHelper::maybe_add_sanitize_url_attr( $url, (int) $this->form->id ), $message );
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

	private function assert_get_plan_required( $expected, $categories ) {
		$link = compact( 'categories' );
		$this->assertEquals( $expected, FrmFormsHelper::get_plan_required( $link ) );
	}

	/**
	 * @covers FrmFormsHelper::get_form_style
	 */
	public function test_get_form_style() {
		// Test null and 'default' form values.
		$this->assertEquals( '1', FrmFormsHelper::get_form_style( null ) );
		$this->assertEquals( '1', FrmFormsHelper::get_form_style( 'default' ) );

		// Test object form values.
		// Test "disable Formidable styling" first.
		$form = $this->create_form_with_custom_style_value( '0' );
		$this->assertEquals( '0', FrmFormsHelper::get_form_style( $form ) );

		$form = $this->create_form_with_custom_style_value( '' );
		$this->assertEquals( '', FrmFormsHelper::get_form_style( $form ) );

		// Create a style and test a custom style value as well.
		$frm_style = new FrmStyle();
		$style_id  = $this->factory->post->create(
			array(
				'post_type'    => 'frm_styles',
				'post_content' => FrmAppHelper::prepare_and_encode( $frm_style->get_defaults() ),
			)
		);

		$form = $this->create_form_with_custom_style_value( $style_id );
		$this->assertEquals( $style_id, FrmFormsHelper::get_form_style( $form ) );

		// Test array form values.
		$form = $form->options;
		$this->assertEquals( $style_id, FrmFormsHelper::get_form_style( $form ) );

		unset( $form['custom_style'] );
		$this->assertEquals( '1', FrmFormsHelper::get_form_style( $form ) );

		$form['custom_style'] = '';
		$this->assertEquals( '', FrmFormsHelper::get_form_style( $form ) );

		$form['custom_style'] = '0';
		$this->assertEquals( '0', FrmFormsHelper::get_form_style( $form ) );
	}

	private function create_form_with_custom_style_value( $custom_style ) {
		return $this->factory->form->create_and_get(
			array(
				'options' => array(
					'custom_style' => $custom_style,
				),
			)
		);
	}
}
