<?php

/**
 * @group forms
 */
class test_FrmForm extends FrmUnitTest {

	public function setUp(): void {
		parent::setUp();
		$this->create_users();
	}

	/**
	 * @covers FrmForm::create
	 */
	public function test_create() {
		$values  = FrmFormsHelper::setup_new_vars( false );
		$form_id = FrmForm::create( $values );
		$this->assertTrue( is_numeric( $form_id ) );
		$this->assertNotEmpty( $form_id );
	}

	/**
	 * @covers FrmForm::duplicate
	 */
	public function test_duplicate() {
		$form = $this->factory->form->get_object_by_id( $this->all_fields_form_key );
		$id   = FrmForm::duplicate( $form->id );
		$this->assertTrue( is_numeric( $id ) );
		$this->assertNotEmpty( $id );

		// check the number of form actions
		$original_actions = FrmFormAction::get_action_for_form( $form->id );
		$new_actions      = FrmFormAction::get_action_for_form( $id );
		$this->assertEquals( count( $original_actions ), count( $new_actions ) );
	}

	protected function _check_if_child_fields_duplicate( $old_child_forms, $new_child_forms ) {
		// Just check the first form
		$old_child_form = reset( $old_child_forms );
		$new_child_form = reset( $new_child_forms );

		// Get all fields in each form
		$old_child_form_fields = FrmField::get_all_for_form( $old_child_form->id );
		$new_child_form_fields = FrmField::get_all_for_form( $new_child_form->id );

		// Check if there are the same number of child form fields in the duplicated child form
		$this->assertEquals( count( $old_child_form_fields ), count( $new_child_form_fields ), 'When a form is duplicated, the fields in the repeating section are not duplicated correctly.' ); // phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
	}

	/**
	 * @covers FrmForm::destroy
	 */
	public function test_destroy() {
		$forms = FrmForm::getAll();
		$this->assertNotEmpty( count( $forms ) );

		foreach ( $forms as $form ) {
			if ( $form->is_template ) {
				continue;
			}

			FrmForm::destroy( $form->id );
			$form_exists = FrmForm::getOne( $form->id );
			$this->assertEmpty( $form_exists, 'Failed to delete form ' . $form->form_key );

			$subforms_exist = FrmForm::getAll( array( 'parent_form_id' => $form->id ) );
			$this->assertEmpty( $subforms_exist, 'Failed to delete child forms for parent form ' . $form->form_key );
		}
	}

	/**
	 * @group visibility
	 *
	 * @covers FrmForm::is_visible_to_user
	 */
	public function test_is_form_visible_to_user() {
		$this->assert_form_is_visible( 'administrator', 'editor', 'Administrator can view a form set to editor' );
		$this->assert_form_is_hidden( 'editor', 'administrator', 'Editor cannot view form set to administrator' );
		$this->assert_form_is_visible( 'editor', array( 'administrator', 'editor' ), 'Editor can view form set to both administrator and editor' );

		// The Logged-In Users option is actually an empty string
		$this->assert_form_is_visible( 'editor', '', 'Editor can view form set to logged in users' );
		$this->assert_form_is_visible( 'subscriber', '', 'Subscriber can view form set to logged in users' );
		$this->assert_form_is_visible( 'subscriber', '', 'Subscriber can view form set to logged in users' );
		$this->assert_form_is_hidden( 'loggedout', '', 'Logged out user cannot view form set to logged in users' );

		$this->assert_form_is_hidden( 'loggedout', 'editor', 'Logged out user cannot view form set to editors' );

		// Array options are expected to only match directly
		$this->assert_form_is_hidden( 'editor', array( 'subscriber' ), 'Editors should not set a form assigned to subscribers' );
		$this->assert_form_is_hidden( 'editor', array( 'contributor', 'author' ), 'Editors should not set a form assigned to contributors and authors' );
		$this->assert_form_is_hidden( 'subscriber', array( 'editor', 'author' ), 'Contributors should not set a form assigned to editors and authors' );
		$this->assert_form_is_hidden( 'subscriber', array( 'author', 'administrator' ), 'Contributors should not set a form assigned to authors and administrators' );

		// test custom roles
		$this->assert_form_is_visible( 'formidable_custom_role', 'formidable_custom_role', 'Custom role should be able to see a form assigned to it' );
		$this->assert_form_is_visible( 'formidable_custom_role', '', 'Custom role should be able to see a form assigned to logged in users' );
		$this->assert_form_is_hidden( 'formidable_custom_role', array( 'administrator' ), 'Custom role should not be able to see a form not assigned to it' );
		$this->assert_form_is_hidden( 'formidable_custom_role', array( 'editor', 'subscriber' ), 'Custom role should not be able to see a form not assigned to it' );
	}

	/**
	 * @param string       $capability
	 * @param array|string $visibility
	 *
	 * @return bool
	 */
	private function form_is_visible( $capability, $visibility ) {
		$form = FrmForm::getOne( 'contact-db12' );

		$this->use_frm_role( $capability );

		$form->logged_in                 = 1;
		$form->options['logged_in_role'] = $visibility;
		return FrmForm::is_visible_to_user( $form );
	}

	/**
	 * @param string       $capability
	 * @param array|string $visibility
	 * @param string       $message
	 */
	private function assert_form_is_visible( $capability, $visibility, $message = '' ) {
		$this->assertTrue( $this->form_is_visible( $capability, $visibility ), $message );
	}

	/**
	 * @param string       $capability
	 * @param array|string $visibility
	 * @param string       $message
	 */
	private function assert_form_is_hidden( $capability, $visibility, $message = '' ) {
		$this->assertFalse( $this->form_is_visible( $capability, $visibility ), $message );
	}

	/**
	 * @covers FrmForm::sanitize_field_opt
	 */
	public function test_sanitize_field_opt() {
		$this->assert_sanitize_field_opt_calc( '', '<div></div>', 'HTML should be stripped from calculations' );

		$original_value = '[189] > 1 && [189] < 5 ? 20 : [189] > 5 && [189] < 8 ? 21 : 0';
		$this->assert_sanitize_field_opt_calc( $original_value, $original_value, 'comparisons should not be detected as unsafe html tags' );

		$safe_less_than_comparison = '50 < 100 ? 1 : 0';
		$this->assert_sanitize_field_opt_calc( $safe_less_than_comparison, $safe_less_than_comparison );
		$this->assert_sanitize_field_opt_calc( $safe_less_than_comparison, '50<100 ? 1 : 0', 'unspaced comparisons will be padded by a space to avoid strip_tags issues.' );
		$this->assert_sanitize_field_opt_calc( $safe_less_than_comparison, '50 <100 ? 1 : 0' );
		$this->assert_sanitize_field_opt_calc( $safe_less_than_comparison, '50< 100 ? 1 : 0' );

		$original_value = '50>100 ? 1 : 0';
		$this->assert_sanitize_field_opt_calc( $original_value, $original_value, 'greater than comparisons do not get stripped, so they do not get any additional string padding.' ); // phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong

		$original_value = '50 >100 ? 1 : 0';
		$this->assert_sanitize_field_opt_calc( $original_value, $original_value );

		$safe_less_than_equals_comparison = '50 <= 100 ? 1 : 0';
		$this->assert_sanitize_field_opt_calc( $safe_less_than_equals_comparison, $safe_less_than_equals_comparison );
		$this->assert_sanitize_field_opt_calc( '50 <= 100 ? 1 : 0', '50<=100 ? 1 : 0' );

		$original_value = '50 >=100 ? 1 : 0';
		$this->assert_sanitize_field_opt_calc( $original_value, $original_value );

		$original_value = '50>= 100 ? 1 : 0';
		$this->assert_sanitize_field_opt_calc( $original_value, $original_value );

		$original_value = '50>=100 ? 1 : 0';
		$this->assert_sanitize_field_opt_calc( $original_value, $original_value );
	}

	private function assert_sanitize_field_opt_calc( $expected, $original_value, $message = '' ) {
		$value = $original_value;
		$this->sanitize_field_opt( 'calc', $value );
		$this->assertEquals( $expected, $value, $message );
	}

	private function sanitize_field_opt( $opt, &$value ) {
		return $this->run_private_method(
			array( 'FrmForm', 'sanitize_field_opt' ),
			array( $opt, &$value )
		);
	}

	/**
	 * @covers FrmForm::normalize_calc_spaces
	 */
	public function test_normalize_calc_spaces() {
		$this->assertEquals( '5 < 10', $this->normalize_calc_spaces( '5<10' ) );
		$this->assertEquals( '5 < 10', $this->normalize_calc_spaces( '5 <10' ) );
		$this->assertEquals( '5 < 10', $this->normalize_calc_spaces( '5< 10' ) );
		$this->assertEquals( '1 < 2 && 3 < 4 && 5 < 6', $this->normalize_calc_spaces( '1<2 && 3<4 && 5<6' ) );
		$this->assertEquals( '5 <= 10', $this->normalize_calc_spaces( '5<=10' ) );
		$this->assertEquals( '5 <= 10', $this->normalize_calc_spaces( '5 <=10' ) );
		$this->assertEquals( '5 <= 10', $this->normalize_calc_spaces( '5<= 10' ) );
		$this->assertEquals( '1 <= 2 && 3 <= 4 && 5 <= 6', $this->normalize_calc_spaces( '1<=2 && 3<=4 && 5<=6' ) );
	}

	private function normalize_calc_spaces( $calc ) {
		return $this->run_private_method( array( 'FrmForm', 'normalize_calc_spaces' ), array( $calc ) );
	}

	/**
	 * @covers FrmForm::getName
	 */
	public function test_getName() {
		$form_name = 'Test form';
		$form_id   = $this->factory->form->create( array( 'name' => $form_name ) );
		$name      = FrmForm::getName( (string) $form_id );
		$this->assertEquals( $form_name, $name );
	}

	/**
	 * @covers FrmForm::getOne
	 * @covers FrmForm::prepare_form_row_data
	 */
	public function test_getOne() {
		// Test to make sure a form with no options column value still has an array $form->options value.
		$form_id = $this->create_a_form_with_an_empty_options_column();
		$form    = FrmForm::getOne( $form_id );
		$this->assertIsArray( $form->options );
		$this->assertArrayHasKey( 'custom_style', $form->options );

		// Test a regular form. $form->options should be an array and it should not be empty.
		$form = FrmForm::getOne( 'contact-with-email' );
		$this->assertIsArray( $form->options );
		$this->assertNotEmpty( $form->options );
	}

	/**
	 * Create a form with no options column value.
	 *
	 * @return int
	 */
	private function create_a_form_with_an_empty_options_column() {
		global $wpdb;
		$form_id = $this->factory->form->create();
		$wpdb->update(
			$wpdb->prefix . 'frm_forms',
			array(
				'options' => '',
			),
			array(
				'id' => $form_id,
			)
		);
		FrmForm::clear_form_cache();
		return $form_id;
	}
}
