<?php
/**
 * @group pro-views
 * @since 2.03.04
 */

class WP_Test_fieldValuesDropdown extends FrmUnitTest {

	// TODO: test text, dropdown, checkboxes, userID, dynamic, lookup, post status, post category
	// TODO: test $new_field not set - pick up here

	public function test_class_is_tested() {
		$this->assertTrue( true );
	}

	/**
	 * Checks the HTML for the field value part of a field's conditional dropdown
	 * Testing a Single Line Text field as the logic field type
	 *
	 * @since 2.03.04
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_conditional_logic_row_text_field_values(){
		list( $logic_field, $field, $field_id, $row_key ) = $this->initialize_field_logic_variables( '493ito', 'uc580i' );

		// Set selected value
		$field['hide_opt'][ $row_key ] = $selected_value = 'Show me';

		$dropdown = $this->get_field_logic_dropdown_contents( $logic_field, $field_id, $field, $row_key );

		$expected = '<input type="text" name="field_options[hide_opt_' . $field_id . '][]" value="' . $selected_value . '" />';

		$this->assertSame( trim( $expected ), trim( $dropdown ) );

	}

	/**
	 * Checks the HTML for the field value part of a field's conditional dropdown
	 * Testing a Checkbox field as the logic field type
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_conditional_logic_row_checkbox_field_values(){
		list( $logic_field, $field, $field_id, $row_key ) = $this->initialize_field_logic_variables( 'uc580i', '493ito' );

		$dropdown = $this->get_field_logic_dropdown_contents( $logic_field, $field_id, $field, $row_key );

		$opening_tag = '<select name="field_options[hide_opt_' . $field_id . '][]">';
		$first_option = '<option value=""></option>';
		$last_option = '<option value="Purple">Purple</option>';
		$closing_tag = '</select>';
		$option_number = 5;

		$this->assertContains( $opening_tag, $dropdown );
		$this->assertContains( $closing_tag, $dropdown );
		$this->assertContains( $first_option, $dropdown );
		$this->assertContains( $last_option, $dropdown );
		$this->assertSame( $option_number, substr_count( $dropdown, '<option' ) );

	}

	/**
	 * Checks the HTML for the field value part of a field's conditional dropdown
	 * Testing a Checkbox field as the logic field type
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_conditional_logic_row_checkbox_field_values_with_selected_Value(){
		list( $logic_field, $field, $field_id, $row_key ) = $this->initialize_field_logic_variables( 'uc580i', '493ito' );

		// Set selected value
		$field['hide_opt'][ $row_key ] = 'Blue';

		$dropdown = $this->get_field_logic_dropdown_contents( $logic_field, $field_id, $field, $row_key );

		$opening_tag = '<select name="field_options[hide_opt_' . $field_id . '][]">';
		$first_option = '<option value=""></option>';
		$last_option = '<option value="Purple">Purple</option>';
		$selected_option = '<option value="Blue" selected=\'selected\'>Blue</option>';
		$closing_tag = '</select>';
		$option_number = 5;

		$this->assertContains( $opening_tag, $dropdown );
		$this->assertContains( $closing_tag, $dropdown );
		$this->assertContains( $first_option, $dropdown );
		$this->assertContains( $last_option, $dropdown );
		$this->assertContains( $selected_option, $dropdown );
		$this->assertSame( $option_number, substr_count( $dropdown, '<option' ) );

	}

	/**
	 * Checks the HTML for the field value part of a field's conditional dropdown
	 * Testing a Dropdown field as the logic field type
	 *
	 * @since 2.03.04
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_conditional_logic_row_dropdown_field_values(){
		list( $logic_field, $field, $field_id, $row_key ) = $this->initialize_field_logic_variables( '54tffk', '493ito' );

		$dropdown = $this->get_field_logic_dropdown_contents( $logic_field, $field_id, $field, $row_key );

		$opening_tag = '<select name="field_options[hide_opt_' . $field_id . '][]">';
		$first_option = '<option value=""></option>';
		$last_option = '<option value="Ace Ventura">Ace Ventura</option>';
		$closing_tag = '</select>';
		$option_number = 3;

		$this->assertContains( $opening_tag, $dropdown );
		$this->assertContains( $closing_tag, $dropdown );
		$this->assertContains( $first_option, $dropdown );
		$this->assertContains( $last_option, $dropdown );
		$this->assertSame( $option_number, substr_count( $dropdown, '<option' ) );

	}

	/**
	 * Checks the HTML for the field value part of a field's conditional dropdown
	 * Testing a UserID field as the logic field type
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_conditional_logic_row_user_id_field_values(){
		list( $logic_field, $field, $field_id, $row_key ) = $this->initialize_field_logic_variables( 't1eqkj', '493ito' );

		$dropdown = $this->get_field_logic_dropdown_contents( $logic_field, $field_id, $field, $row_key );

		$opening_tag = '<select name="field_options[hide_opt_' . $field_id . '][]">';
		$first_option = '<option value=""> </option>';
		$current_user_option = '<option value="current_user" >Current User</option>';
		$last_option = '<option value="1" >admin</option>';
		$closing_tag = '</select>';
		$option_number = 3;

		$this->assertContains( $opening_tag, $dropdown );
		$this->assertContains( $closing_tag, $dropdown );
		$this->assertContains( $first_option, $dropdown );
		$this->assertContains( $current_user_option, $dropdown );
		$this->assertContains( $last_option, $dropdown );
		$this->assertSame( $option_number, substr_count( $dropdown, '<option' ) );

	}

	/**
	 * Checks the HTML for the field value part of a field's conditional dropdown
	 * Testing a level 1 Dynamic Dropdown field as the logic field type
	 *
	 * @since 2.03.04
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_conditional_logic_row_dynamic_field_values(){
		list( $logic_field, $field, $field_id, $row_key ) = $this->initialize_field_logic_variables( 'dynamic-country', '493ito' );

		$dropdown = $this->get_field_logic_dropdown_contents( $logic_field, $field_id, $field, $row_key );

		$opening_tag = '<select name="field_options[hide_opt_' . $field_id . '][]">';
		$first_option = '<option value="">Anything</option>';
		$last_option = '>Brazil</option>';
		$closing_tag = '</select>';
		$option_number = 3;

		$this->assertContains( $opening_tag, $dropdown );
		$this->assertContains( $closing_tag, $dropdown );
		$this->assertContains( $first_option, $dropdown );
		$this->assertContains( $last_option, $dropdown );
		$this->assertSame( $option_number, substr_count( $dropdown, '<option' ) );

	}

	/**
	 * Checks the HTML for the field value part of a field's conditional dropdown
	 * Testing a Lookup Dropdown field as the logic field type
	 *
	 * @since 2.03.04
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_conditional_logic_row_lookup_field_values(){
		list( $logic_field, $field, $field_id, $row_key ) = $this->initialize_field_logic_variables( 'lookup-country', '493ito' );

		$dropdown = $this->get_field_logic_dropdown_contents( $logic_field, $field_id, $field, $row_key );

		$expected = '<input type="text" name="field_options[hide_opt_' . $field_id . '][]" value="" />';

		$this->assertSame( trim( $expected ), trim( $dropdown ) );

	}

	/**
	 * Checks the HTML for the field value part of a field's conditional dropdown
	 * Testing a post status dropdown field as the logic field type
	 *
	 * @since 2.03.04
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_conditional_logic_row_post_status_field_values(){
		$this->set_current_user_to_1();

		list( $logic_field, $field, $field_id, $row_key ) = $this->initialize_field_logic_variables( 'post-status-dropdown', '493ito' );

		$dropdown = $this->get_field_logic_dropdown_contents( $logic_field, $field_id, $field, $row_key );

		$opening_tag = '<select name="field_options[hide_opt_' . $field_id . '][]">';
		$first_option = '<option value=""></option>';
		$draft_option = '<option value="draft">Draft</option>';
		$pending_option = '<option value="pending">Pending Review</option>';
		$private_option = '<option value="private">Private</option>';
		$publish_option = '<option value="publish">Published</option>';
		$closing_tag = '</select>';
		$option_number = 5;

		$this->assertContains( $opening_tag, $dropdown );
		$this->assertContains( $closing_tag, $dropdown );
		$this->assertContains( $first_option, $dropdown );
		$this->assertContains( $draft_option, $dropdown );
		$this->assertContains( $pending_option, $dropdown );
		$this->assertContains( $private_option, $dropdown );
		$this->assertContains( $publish_option, $dropdown );
		$this->assertSame( $option_number, substr_count( $dropdown, '<option' ) );
	}

	/**
	 * Checks the HTML for the field value part of a field's conditional dropdown
	 * Testing a post category field as the logic field type
	 *
	 * @since 2.03.04
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_conditional_logic_row_post_category_field_values(){
		list( $logic_field, $field, $field_id, $row_key ) = $this->initialize_field_logic_variables( 'parent-dynamic-taxonomy', '493ito' );

		$dropdown = $this->get_field_logic_dropdown_contents( $logic_field, $field_id, $field, $row_key );

		$opening_tag = '<select  name=\'field_options[hide_opt_' . $field_id . '][]\'  >';
		$first_option = '<option value=""> </option>';
		$middle_option = 'Live Music</option>';
		$closing_tag = '</select>';
		//$option_number = 3; TODO: figure out why the options include child categories

		$this->assertContains( $opening_tag, $dropdown );
		$this->assertContains( $closing_tag, $dropdown );
		$this->assertContains( $first_option, $dropdown );
		$this->assertContains( $middle_option, $dropdown );
		//$this->assertSame( $option_number, substr_count( $dropdown, '<option' ) );

	}

	private function initialize_field_logic_variables( $logic_field_key, $edit_field_key ) {
		$logic_field = FrmField::getOne( $logic_field_key );
		$field_object = FrmField::getOne( $edit_field_key );
		$current_field = FrmProFieldsHelper::convert_field_object_to_flat_array( $field_object );
		$current_field_id = $field_object->id;
		$meta_name = 0;

		return array( $logic_field, $current_field, $current_field_id, $meta_name );
	}

	/**
	 * @param object $new_field - logic field
	 * @param int|string $current_field_id - field ID that we are editing
	 * @param array $field - field we are editing
	 * @param int $meta_name - index of the logic row
	 *
	 * Some variables like $is_settings_page, $field_name, and $val are not set when coming from field conditional logic
	 *
	 * @return string
	 */
	private function get_field_logic_dropdown_contents( $new_field, $current_field_id, $field, $meta_name ) {
		ob_start();
		require( FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-fields/field-values.php' );
		$dropdown = ob_get_contents();
		ob_end_clean();

		return $dropdown;
	}
}