<?php
/**
 * @group free
 * @group views
 * @group shortcodes
 * @group field-shortcodes
 */
class test_FrmFieldShortcodes extends FrmUnitTest {

	protected $test_form;
	protected $test_entry;

	public function setUp(): void {
		parent::setUp();

		$this->test_form  = $this->get_form_for_test();
		$this->test_entry = $this->get_entry_for_test();
	}

	/**
	 * Test the [x] shortcode, where x is a field ID
	 *
	 * @since 2.05
	 */
	public function test_single_field_id_shortcodes() {
		$field_values = $this->get_expected_field_values();

		foreach ( $field_values as $field_key => $expected_value ) {
			$shortcode = '[' . FrmField::get_id_by_key( $field_key ) . ']';

			$actual_value = $this->get_actual_value( $shortcode );

			$this->assertSame( $expected_value, $actual_value, 'The [' . $field_key . '] shortcode is not returning the expected value.' );
		}
	}

	protected function get_form_for_test() {
		return FrmForm::getOne( 'free_field_types' );
	}

	protected function get_entry_for_test() {
		$new_entry = array(
			'form_id'     => FrmForm::get_id_by_key( 'free_field_types' ),
			'item_key'    => 'free_entry_key',
			'description' => array(
				'browser'  => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:37.0) Gecko/20100101 Firefox/37.0',
				'referrer' => 'http://localhost:8888/features/wp-admin/admin-ajax.php?action=frm_forms_preview&form=boymfd',
			),
			'created_at'  => '2015-05-12 19:30:23',
			'item_meta'   => $this->expected_free_meta(),
		);

		$entry_id = $this->factory->entry->create_object( $new_entry );

		return FrmEntry::getOne( $entry_id, true );
	}

	protected function expected_free_meta() {
		return array(
			FrmField::get_id_by_key( 'free-text-field' )   => 'Test Testerson',
			FrmField::get_id_by_key( 'free-paragraph-field' ) => "Test\r\nMiddle\r\nTesterson",
			FrmField::get_id_by_key( 'free-checkboxes' )   => array( 'Red', 'Green' ),
			FrmField::get_id_by_key( 'free-radio-button-field' ) => 'cookies',
			FrmField::get_id_by_key( 'free-dropdown-field' ) => 'Ace Ventura',
			FrmField::get_id_by_key( 'free-email-field' )  => 'jamie@mail.com',
			FrmField::get_id_by_key( 'free-website-field' ) => 'http://www.jamie.com',
			FrmField::get_id_by_key( 'free-number-field' ) => '11',
			FrmField::get_id_by_key( 'free-phone-field' )  => '1231231234',
			FrmField::get_id_by_key( 'free-hidden-field' ) => '',
		);
	}

	protected function get_expected_field_values() {
		return array(
			'free-text-field'         => 'Test Testerson',
			'free-paragraph-field'    => "<p>Test<br />\nMiddle<br />\nTesterson</p>\n",
			'free-checkboxes'         => 'Red, Green',
			'free-radio-button-field' => 'cookies',
			'free-dropdown-field'     => 'Ace Ventura',
			'free-email-field'        => 'jamie@mail.com',
			'free-website-field'      => 'http://www.jamie.com',
			'free-number-field'       => '11',
			'free-phone-field'        => '1231231234',
			'free-hidden-field'       => '',
			'free-user-id-field'      => '',
		);
	}

	protected function get_actual_value( $value ) {
		return apply_filters( 'frm_content', $value, $this->test_form, $this->test_entry );
	}
}
