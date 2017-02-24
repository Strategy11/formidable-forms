<?php
/**
 * @group pro-views
 * @since 2.03.04
 */

class WP_Test_fieldValuesDropdown extends FrmUnitTest {

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
	public function test_field_logic_row_text_field_values(){
		list( $logic_field, $field, $field_id, $row_key ) = $this->initialize_field_logic_variables( '493ito', 'uc580i' );

		// Set selected value
		$field['hide_opt'][ $row_key ] = $selected_value = 'Show me';

		$dropdown = $this->get_field_logic_dropdown( $logic_field, $field_id, $field, $row_key );

		$expected = '<input type="text" name="field_options[hide_opt_' . $field_id . '][]" value="' . $selected_value . '" />';

		$this->assertSame( trim( $expected ), trim( $dropdown ) );

	}

	/**
	 * Checks the HTML for the field value part of a field's conditional dropdown
	 * Testing a Checkbox field as the logic field type
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_field_logic_row_checkbox_field_values(){
		list( $logic_field, $field, $field_id, $row_key ) = $this->initialize_field_logic_variables( 'uc580i', '493ito' );

		$dropdown = $this->get_field_logic_dropdown( $logic_field, $field_id, $field, $row_key );

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
	public function test_field_logic_row_checkbox_field_values_with_selected_Value(){
		list( $logic_field, $field, $field_id, $row_key ) = $this->initialize_field_logic_variables( 'uc580i', '493ito' );

		// Set selected value
		$field['hide_opt'][ $row_key ] = 'Blue';

		$dropdown = $this->get_field_logic_dropdown( $logic_field, $field_id, $field, $row_key );

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
	public function test_field_logic_row_dropdown_field_values(){
		list( $logic_field, $field, $field_id, $row_key ) = $this->initialize_field_logic_variables( '54tffk', '493ito' );

		$dropdown = $this->get_field_logic_dropdown( $logic_field, $field_id, $field, $row_key );

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
	public function test_field_logic_row_user_id_field_values(){
		list( $logic_field, $field, $field_id, $row_key ) = $this->initialize_field_logic_variables( 't1eqkj', '493ito' );

		$dropdown = $this->get_field_logic_dropdown( $logic_field, $field_id, $field, $row_key );

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
	public function test_field_logic_row_dynamic_field_values(){
		list( $logic_field, $field, $field_id, $row_key ) = $this->initialize_field_logic_variables( 'dynamic-country', '493ito' );

		$dropdown = $this->get_field_logic_dropdown( $logic_field, $field_id, $field, $row_key );

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
	public function test_field_logic_row_lookup_field_values(){
		list( $logic_field, $field, $field_id, $row_key ) = $this->initialize_field_logic_variables( 'lookup-country', '493ito' );

		$dropdown = $this->get_field_logic_dropdown( $logic_field, $field_id, $field, $row_key );

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
	public function test_field_logic_row_post_status_field_values(){
		$this->set_current_user_to_1();

		list( $logic_field, $field, $field_id, $row_key ) = $this->initialize_field_logic_variables( 'post-status-dropdown', '493ito' );

		$dropdown = $this->get_field_logic_dropdown( $logic_field, $field_id, $field, $row_key );

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
	public function test_field_logic_row_post_category_field_values(){
		list( $logic_field, $field, $field_id, $row_key ) = $this->initialize_field_logic_variables( 'parent-dynamic-taxonomy', '493ito' );

		$dropdown = $this->get_field_logic_dropdown( $logic_field, $field_id, $field, $row_key );

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

	/**
	 * Check the free text field in a field's new logic row
	 *
	 * @since 2.03.04
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_new_field_logic_row_free_text_field() {
		list( $logic_field, $field, $field_id, $row_key ) = $this->initialize_field_logic_variables( '', 'uc580i' );

		$dropdown = $this->get_field_logic_dropdown_no_logic_field( $field_id, $field, $row_key );

		$expected = '<input type="text" name="field_options[hide_opt_' . $field_id . '][]" value="" />';

		$this->assertSame( trim( $expected ), trim( $dropdown ) );
	}

	/**
	 * Checks the HTML for the field value part of a field's conditional dropdown when loaded with Ajax
	 * Testing a UserID field as the logic field type
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_field_logic_row_user_id_field_values_ajax(){
		list( $field_id, $logic_field, $logic_field_type ) = $this->initialize_field_logic_variables_ajax_field( 't1eqkj', '493ito' );

		$dropdown = $this->get_field_logic_dropdown_ajax_field( $field_id, $logic_field, $logic_field_type );

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
	 * Checks the HTML for the field value part of a field's conditional dropdown when loaded with Ajax
	 * Testing a Dynamic field as the logic field type
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_field_logic_row_dynamic_field_values_ajax(){
		list( $field_id, $logic_field, $logic_field_type ) = $this->initialize_field_logic_variables_ajax_field( 'dynamic-country', '493ito' );

		$dropdown = $this->get_field_logic_dropdown_ajax_field( $field_id, $logic_field, $logic_field_type );

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
	 * Check the free text field in an action's new logic row
	 *
	 * @since 2.03.04
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_new_action_logic_row_free_text_field() {
		list( $value, $row_key, $field, $field_name ) = $this->initialize_action_logic_variables_no_field();

		$dropdown = $this->get_action_logic_dropdown_no_logic_field( $value, $row_key, $field, $field_name );

		$expected = '<input type="text" name="' . $field_name . '" value="" />';

		$this->assertSame( trim( $expected ), trim( $dropdown ) );
	}

	/**
	 * Check the field value box, for a text field, in an action's new logic row
	 *
	 * @since 2.03.04
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_action_logic_row_text_field_value_ajax() {
		list( $new_field, $current_field_id, $field_name ) = $this->initialize_action_logic_variables_ajax_field( '493ito' );

		$dropdown = $this->get_action_logic_dropdown_ajax_field( $new_field, $current_field_id, $field_name );

		$expected = '<input type="text" name="' . $field_name . '" value="" />';

		$this->assertSame( trim( $expected ), trim( $dropdown ) );
	}

	/**
	 * Check the field value box, for a text field, in an action's logic row
	 *
	 * @since 2.03.04
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_action_logic_row_text_field_values() {
		list( $field_name, $meta_name, $new_field ) = $this->initialize_action_logic_variables( '493ito' );

		$dropdown = $this->get_action_logic_dropdown( $field_name, $meta_name, $new_field, 'Test' );

		$expected = '<input type="text" name="' . $field_name . '" value="Test" />';

		$this->assertSame( trim( $expected ), trim( $dropdown ) );
	}

	/**
	 * Check the field value box, for a Checkbox field, in an action's logic row
	 *
	 * @since 2.03.04
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_action_logic_row_checkbox_field_values() {
		list( $field_name, $meta_name, $new_field ) = $this->initialize_action_logic_variables( 'uc580i' );

		$dropdown = $this->get_action_logic_dropdown( $field_name, $meta_name, $new_field, '' );

		$opening_tag = '<select name="' . $field_name . '">';
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
	 * Check the field value box, for a Dropdown field, in an action's logic row
	 *
	 * @since 2.03.04
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_action_logic_row_dropdown_field_values() {
		list( $field_name, $meta_name, $new_field ) = $this->initialize_action_logic_variables( 'uc580i' );

		$dropdown = $this->get_action_logic_dropdown( $field_name, $meta_name, $new_field, 'Blue' );

		$opening_tag = '<select name="' . $field_name . '">';
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
	 * Check the field value box, for a UserID field, in an action's logic row
	 *
	 * @since 2.03.04
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_action_logic_row_user_id_field_values() {
		list( $field_name, $meta_name, $new_field ) = $this->initialize_action_logic_variables( 't1eqkj' );

		$dropdown = $this->get_action_logic_dropdown( $field_name, $meta_name, $new_field, '' );

		$opening_tag = '<select name="' . $field_name . '">';
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
	 * Check the field value box, for a Dynamic field, in an action's logic row
	 *
	 * @since 2.03.04
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_action_logic_row_dynamic_field_values() {
		list( $field_name, $meta_name, $new_field ) = $this->initialize_action_logic_variables( 'dynamic-country' );

		$dropdown = $this->get_action_logic_dropdown( $field_name, $meta_name, $new_field, '' );

		$opening_tag = '<select name="' . $field_name . '">';
		$first_option = '<option value=""></option>';
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
	 * Check the field value box, for a Lookup field, in an action's logic row
	 *
	 * @since 2.03.04
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_action_logic_row_lookup_field_values() {
		list( $field_name, $meta_name, $new_field ) = $this->initialize_action_logic_variables( 'lookup-country' );

		$dropdown = $this->get_action_logic_dropdown( $field_name, $meta_name, $new_field, '' );

		$expected = '<input type="text" name="' . $field_name . '" value="" />';

		$this->assertSame( trim( $expected ), trim( $dropdown ) );
	}

	/**
	 * Check the field value box, for a Post Status field, in an action's logic row
	 *
	 * @since 2.03.04
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_action_logic_row_post_status_field_values() {
		$this->set_current_user_to_1();

		list( $field_name, $meta_name, $new_field ) = $this->initialize_action_logic_variables( 'post-status-dropdown' );

		$dropdown = $this->get_action_logic_dropdown( $field_name, $meta_name, $new_field, '' );

		$opening_tag = '<select name="' . $field_name . '">';
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
	 * Check the field value box, for a Post Category field, in an action's logic row
	 *
	 * @since 2.03.04
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_action_logic_row_post_category_field_values() {
		list( $field_name, $meta_name, $new_field ) = $this->initialize_action_logic_variables( 'parent-dynamic-taxonomy' );

		$dropdown = $this->get_action_logic_dropdown( $field_name, $meta_name, $new_field, '' );

		$opening_tag = '<select  name=\'' . $field_name . '\'  >';
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

	/**
	 * Check the field value dropdown, for a Hidden Field, in a Mailchimp action's Group settings
	 *
	 * @since 2.03.04
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_mailchimp_hidden_field_values() {
		list( $html_name, $field ) = $this->initialize_mailchimp_field_value_variables( 'rkax03' );

		$dropdown = $this->get_mailchimp_field_value_dropdown( $html_name, $field, '' );

		$expected = '<input type="text" name="' . $html_name . '" value="" />';

		$this->assertSame( trim( $expected ), trim( $dropdown ) );
	}

	/**
	 * Check the field value dropdown, for a Dropdown Field, in a Mailchimp action's Group settings
	 *
	 * @since 2.03.04
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_mailchimp_dropdown_field_values() {
		list( $html_name, $field ) = $this->initialize_mailchimp_field_value_variables( '54tffk' );

		$dropdown = $this->get_mailchimp_field_value_dropdown( $html_name, $field, '' );

		$opening_tag = '<select name="' . $html_name . '">';
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
	 * Check the field value dropdown, for a Dropdown Field with a selected value, in a Mailchimp action's Group settings
	 *
	 *
	 * @since 2.03.04
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_mailchimp_dropdown_field_values_selected_option() {
		list( $html_name, $field ) = $this->initialize_mailchimp_field_value_variables( '54tffk' );

		$dropdown = $this->get_mailchimp_field_value_dropdown( $html_name, $field, 'William Wells' );

		$opening_tag = '<select name="' . $html_name . '">';
		$first_option = '<option value=""></option>';
		$selected_option = '<option value="William Wells" selected=\'selected\'>William Wells</option>';
		$last_option = '<option value="Ace Ventura">Ace Ventura</option>';
		$closing_tag = '</select>';
		$option_number = 3;

		$this->assertContains( $opening_tag, $dropdown );
		$this->assertContains( $closing_tag, $dropdown );
		$this->assertContains( $first_option, $dropdown );
		$this->assertContains( $selected_option, $dropdown );
		$this->assertContains( $last_option, $dropdown );
		$this->assertSame( $option_number, substr_count( $dropdown, '<option' ) );
	}

	/**
	 * Check the field value dropdown, for a Checkbox Field, in a Mailchimp action's Group settings
	 *
	 * @since 2.03.04
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_mailchimp_checkbox_field_values() {
		list( $html_name, $field ) = $this->initialize_mailchimp_field_value_variables( 'uc580i' );

		$dropdown = $this->get_mailchimp_field_value_dropdown( $html_name, $field, '' );

		$opening_tag = '<select name="' . $html_name . '">';
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
	 * Check the field value dropdown, for a Dynamic Field, in a Mailchimp action's Group settings
	 *
	 * @since 2.03.04
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_mailchimp_dynamic_field_values() {
		list( $html_name, $field ) = $this->initialize_mailchimp_field_value_variables( 'dynamic-country' );

		$dropdown = $this->get_mailchimp_field_value_dropdown( $html_name, $field, '' );

		$opening_tag = '<select name="' . $html_name . '">';
		$first_option = '<option value=""></option>';
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
	 * Initialize the variables used for a field's conditional logic field value dropdown
	 *
	 * @since 2.03.04
	 *
	 * @param $logic_field_key
	 * @param $edit_field_key
	 *
	 * @return array
	 */
	private function initialize_field_logic_variables( $logic_field_key, $edit_field_key ) {

		if ( $logic_field_key !== '' ) {
			$logic_field = FrmField::getOne( $logic_field_key );
		} else {
			$logic_field = '';
		}

		$field_object = FrmField::getOne( $edit_field_key );
		$current_field = FrmProFieldsHelper::convert_field_object_to_flat_array( $field_object );
		$current_field_id = $field_object->id;
		$meta_name = 0;

		return array( $logic_field, $current_field, $current_field_id, $meta_name );
	}

	/**
	 * Initialize the variables used for the field values dropdown when field is selected
	 * in another field's conditional logic and field values are loaded with Ajax
	 *
	 * @since 2.03.04
	 *
	 * @param string $logic_field_key
	 * @param string $edit_field_key
	 *
	 * @return array
	 */
	private function initialize_field_logic_variables_ajax_field( $logic_field_key, $edit_field_key ) {

		$current_field_id = FrmField::get_id_by_key( $edit_field_key );
		$new_field = FrmField::getOne( $logic_field_key );
		$field_type = $new_field->type;

		return array( $current_field_id, $new_field, $field_type );
	}

	/**
	 * Initialize the variables used for the field values dropdown in an action's logic row
	 *
	 * @param $logic_field_key
	 *
	 * @return array
	 */
	private function initialize_action_logic_variables( $logic_field_key ) {
		$logic_field = FrmField::getOne( $logic_field_key );

		$row_key = 0;
		$field_name = 'frm_email_action[13827][post_content][conditions][' . $row_key . '][hide_opt]';

		return array( $field_name, $row_key, $logic_field );
	}

	/**
	 * Initialize the variables used for the field values dropdown in an action's logic row
	 * when no field is selected
	 *
	 * @return array
	 */
	private function initialize_action_logic_variables_no_field() {
		$value = '';
		$row_key = 0;
		$field = array( 'hide_opt' => array( $row_key => $value ) );
		$field_name = 'frm_email_action[13827][post_content][conditions][' . $row_key . '][hide_opt]';

		return array( $value, $row_key, $field, $field_name );
	}

	/**
	 * Initialize the logic variables for a field action when a field is selected
	 * and options are loaded with ajax
	 *
	 * @param $field_key
	 *
	 * @return array
	 */
	private function initialize_action_logic_variables_ajax_field( $field_key ) {
		$new_field = FrmField::getOne( $field_key );
		$current_field_id = $new_field->id;
		$field_name = 'frm_email_action[13827][post_content][conditions][0][hide_opt]';

		return array( $new_field, $current_field_id, $field_name );
	}

	/**
	 * Initialize the variables for a field value dropdown in MailChimp action
	 *
	 * @param $field_key
	 *
	 * @return array
	 */
	private function initialize_mailchimp_field_value_variables( $field_key ) {
		$html_name = '';
		$field = FrmField::getOne( $field_key );

		return array( $html_name, $field );
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
	private function get_field_logic_dropdown( $new_field, $current_field_id, $field, $meta_name ) {
		ob_start();
		require( FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-fields/field-values.php' );
		$dropdown = ob_get_contents();
		ob_end_clean();

		return $dropdown;
	}

	/**
	 * Get a field's logic values dropdown when loaded with ajax
	 *
	 * @param $current_field_id
	 * @param $new_field
	 * @param $field_type
	 *
	 * @return string
	 */
	private function get_field_logic_dropdown_ajax_field( $current_field_id, $new_field, $field_type ) {
		$anything = 'Anything';
		$is_settings_page = false;

		ob_start();
		require( FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-fields/field-values.php' );
		$dropdown = ob_get_contents();
		ob_end_clean();

		return $dropdown;
	}

	/**
	 * @param int|string $current_field_id - field ID that we are editing
	 * @param array $field - field we are editing
	 * @param int $meta_name - index of the logic row
	 *
	 * Some variables like $new_field, $is_settings_page, $field_name, and $val are not set when coming from a new row
	 * of field conditional logic
	 *
	 * @return string
	 */
	private function get_field_logic_dropdown_no_logic_field( $current_field_id, $field, $meta_name ) {
		ob_start();
		require( FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-fields/field-values.php' );
		$dropdown = ob_get_contents();
		ob_end_clean();

		return $dropdown;
	}

	/**
	 * Get the logic text field when there is no logic field selected yet
	 *
	 * @since 2.03.04
	 *
	 * @param $val
	 * @param $meta_name
	 * @param $field
	 * @param $field_name
	 *
	 * @return string
	 */
	private function get_action_logic_dropdown_no_logic_field( $val, $meta_name, $field, $field_name ) {
		$_GET['frm_action'] = 'settings';

		ob_start();
		require( FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-fields/field-values.php' );
		$dropdown = ob_get_contents();
		ob_end_clean();

		unset( $_GET['frm_action'] );

		return $dropdown;
	}

	/**
	 * Get an action's logic values dropdown when loaded with ajax
	 *
	 * @param $new_field
	 * @param $current_field_id
	 * @param $field_name
	 *
	 * @return string
	 */
	private function get_action_logic_dropdown_ajax_field( $new_field, $current_field_id, $field_name ) {
		$_GET['frm_action'] = 'settings';
		$anything = '';
		$is_settings_page = true;

		ob_start();
		require( FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-fields/field-values.php' );
		$dropdown = ob_get_contents();
		ob_end_clean();

		unset( $_GET['frm_action'] );

		return $dropdown;
	}

	/**
	 * Get the field value dropdown for an action's conditional logic
	 *
	 * The following variables are passed in: $field, $field_name, $meta_name, $new_field, and $val
	 *
	 * @param $field_name
	 * @param $meta_name
	 * @param $new_field
	 * @param $val
	 *
	 * @return string
	 */
	private function get_action_logic_dropdown( $field_name, $meta_name, $new_field, $val ) {
		$_GET['frm_action'] = 'settings';
		$field = array( 'hide_opt' => array( $meta_name => $val ) );

		ob_start();
		require( FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-fields/field-values.php' );
		$dropdown = ob_get_contents();
		ob_end_clean();

		unset( $_GET['frm_action'] );

		return $dropdown;
	}

	/**
	 * Get a field value dropdown for a MailChimp action
	 *
	 * The following variables are passed in: $field_name, $new_field, and $val
	 *
	 * @param $field_name
	 * @param $new_field
	 * @param $val
	 *
	 * @return string
	 */
	private function get_mailchimp_field_value_dropdown( $field_name, $new_field, $val ) {
		$_GET['frm_action'] = 'settings';

		ob_start();
		require( FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-fields/field-values.php' );
		$dropdown = ob_get_contents();
		ob_end_clean();

		unset( $_GET['frm_action'] );

		return $dropdown;
	}
}