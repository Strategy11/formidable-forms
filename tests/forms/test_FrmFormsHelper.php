<?php

/**
 * @group forms
 */
class test_FrmFormsHelper extends FrmUnitTest {

	/**
	 * @var stdClass|null $form
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
}
