<?php
/**
 * @group pro-views
 * @group pro
 * @since 2.03.05
 */

// TODO: move and rename file

class WP_Test_fieldValuesDropdown extends FrmUnitTest {

	public function test_class_is_tested() {
		$this->assertTrue( true );
	}

	/**
	 * Checks the HTML for the field value part of a field's conditional dropdown
	 * Test fields with text boxes
	 *
	 * @since 2.03.05
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_field_logic_for_fields_with_text_box() {
		foreach ( self::fields_with_text_box() as $field_key ) {
			self::check_single_field_with_text_box( $field_key );
		}
	}

	/**
	 * Checks the HTML for the field value part of a field's conditional dropdown
	 * Test single field text boxes
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	private function check_single_field_with_text_box( $field_key ) {
		list( $logic_field, $field, $field_id, $row_key ) = $this->initialize_field_logic_variables( $field_key, 'checkbox-colors' );

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
		list( $logic_field, $field, $field_id, $row_key ) = $this->initialize_field_logic_variables( 'checkbox-colors', 'text-field' );

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
	 * Testing a Radio Button field as the logic field type
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_field_logic_row_radio_button_field_values(){
		list( $logic_field, $field, $field_id, $row_key ) = $this->initialize_field_logic_variables( 'radio-button-field', 'text-field' );

		$dropdown = $this->get_field_logic_dropdown( $logic_field, $field_id, $field, $row_key );

		$opening_tag = '<select name="field_options[hide_opt_' . $field_id . '][]">';
		$first_option = '<option value=""></option>';
		$last_option = '<option value="Pie">Pie</option>';
		$closing_tag = '</select>';
		$option_number = 4;

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
	public function test_field_logic_row_checkbox_field_values_with_selected_value(){
		list( $logic_field, $field, $field_id, $row_key ) = $this->initialize_field_logic_variables( 'checkbox-colors', 'text-field' );

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
	 * @since 2.03.05
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_field_logic_row_dropdown_field_values(){
		list( $logic_field, $field, $field_id, $row_key ) = $this->initialize_field_logic_variables( 'dropdown-field', 'text-field' );

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
	 * Testing a Scale field as the logic field type
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_field_logic_row_scale_field_values(){
		list( $logic_field, $field, $field_id, $row_key ) = $this->initialize_field_logic_variables( 'scale-field', 'text-field' );

		$dropdown = $this->get_field_logic_dropdown( $logic_field, $field_id, $field, $row_key );

		$opening_tag = '<select name="field_options[hide_opt_' . $field_id . '][]">';
		$first_option = '<option value=""></option>';
		$last_option = '<option value="10">10</option>';
		$closing_tag = '</select>';
		$option_number = 11;

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
		list( $logic_field, $field, $field_id, $row_key ) = $this->initialize_field_logic_variables( 'user-id-field', 'text-field' );

		$dropdown = $this->get_field_logic_dropdown( $logic_field, $field_id, $field, $row_key );

		$opening_tag = '<select name="field_options[hide_opt_' . $field_id . '][]">';
		$first_option = '<option value=""></option>';
		$current_user_option = '<option value="current_user" >Current User</option>';
		$last_option = '<option value="1" >admin</option>';
		$closing_tag = '</select>';
		$option_number = 5;

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
	 * @since 2.03.05
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_field_logic_row_dynamic_field_values(){
		list( $logic_field, $field, $field_id, $row_key ) = $this->initialize_field_logic_variables( 'dynamic-country', 'text-field' );

		$dropdown = $this->get_field_logic_dropdown( $logic_field, $field_id, $field, $row_key );

		$opening_tag = '<select name="field_options[hide_opt_' . $field_id . '][]">';
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
	 * Checks the HTML for the field value part of a field's conditional dropdown
	 * Testing a post status dropdown field as the logic field type
	 *
	 * @since 2.03.05
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_field_logic_row_post_status_field_values(){
		$this->set_current_user_to_1();

		list( $logic_field, $field, $field_id, $row_key ) = $this->initialize_field_logic_variables( 'post-status-dropdown', 'text-field' );

		$dropdown = $this->get_field_logic_dropdown( $logic_field, $field_id, $field, $row_key );

		$opening_tag = '<select name="field_options[hide_opt_' . $field_id . '][]">';
		$first_option = '<option value=""></option>';
		$draft_option = '<option value="draft">Draft</option>';
		$pending_option = '<option value="pending">Pending</option>';
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
	 * @since 2.03.05
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_field_logic_row_post_category_field_values(){
		list( $logic_field, $field, $field_id, $row_key ) = $this->initialize_field_logic_variables( 'parent-dynamic-taxonomy', 'text-field' );

		$dropdown = $this->get_field_logic_dropdown( $logic_field, $field_id, $field, $row_key );

		if ( $this->category_dropdown_has_two_spaces() ) {
			$opening_tag = '<select  name=\'field_options[hide_opt_' . $field_id . '][]\'  >';
		} else {
			$opening_tag = '<select name=\'field_options[hide_opt_' . $field_id . '][]\'  >';
		}
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
	 * Checks the HTML for the field value part of a field's conditional dropdown
	 * Testing a post category field as the logic field type
	 *
	 * @since 2.03.05
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_field_logic_row_post_category_field_values_with_selected_value(){
		list( $logic_field, $field, $field_id, $row_key ) = $this->initialize_field_logic_variables( 'parent-dynamic-taxonomy', 'text-field' );

		// Set selected value
		$field['hide_opt'][ $row_key ] = $selected_value = '1';

		$dropdown = $this->get_field_logic_dropdown( $logic_field, $field_id, $field, $row_key );

		if ( $this->category_dropdown_has_two_spaces() ) {
			$opening_tag = '<select  name=\'field_options[hide_opt_' . $field_id . '][]\'  >';
		} else {
			$opening_tag = '<select name=\'field_options[hide_opt_' . $field_id . '][]\'  >';
		}
		$first_option = '<option value=""> </option>';
		$middle_option = 'Live Music</option>';
		$selected_option = '<option class="level-0" value="1" selected="selected">Uncategorized</option>';
		$closing_tag = '</select>';
		//$option_number = 3; TODO: figure out why the options include child categories

		$this->assertContains( $opening_tag, $dropdown );
		$this->assertContains( $closing_tag, $dropdown );
		$this->assertContains( $first_option, $dropdown );
		$this->assertContains( $middle_option, $dropdown );
		$this->assertContains( $selected_option, $dropdown );
		//$this->assertSame( $option_number, substr_count( $dropdown, '<option' ) );
	}

	/**
	 * Check the free text field in a field's new logic row
	 *
	 * @since 2.03.05
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_new_field_logic_row_free_text_field() {
		list( $logic_field, $field, $field_id, $row_key ) = $this->initialize_field_logic_variables( '', 'checkbox-colors' );

		$dropdown = $this->get_field_logic_dropdown_no_logic_field( $field_id, $field, $row_key );

		$expected = '<input type="text" name="field_options[hide_opt_' . $field_id . '][]" value="" />';

		$this->assertSame( trim( $expected ), trim( $dropdown ) );
	}

	/**
	 * Check the value selector when non-existent field ID is passed in
	 * This may occur after an import
	 *
	 * @since 2.04
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_field_logic_row_non_existent_field_id() {
		$current_field = FrmField::getOne( 'checkbox-colors' );

		$selector_field_id = 999999999;
		$selector_args = array(
			'value' => '',
			'html_name' => 'field_options[hide_opt_' . $current_field->id . '][]',
			'source' => $current_field->type,
		);

		ob_start();
		FrmFieldsHelper::display_field_value_selector( $selector_field_id, $selector_args );
		$dropdown = ob_get_contents();
		ob_end_clean();

		$expected = '<input type="text" name="field_options[hide_opt_' . $current_field->id . '][]" value="" />';

		$this->assertSame( trim( $expected ), trim( $dropdown ) );
	}

	/**
	 * Checks the HTML for the field value part of a field's conditional dropdown when loaded with Ajax
	 * Testing a UserID field as the logic field type
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_field_logic_row_user_id_field_values_ajax(){
		list( $field_id, $logic_field, $logic_field_type ) = $this->initialize_field_logic_variables_ajax_field( 'user-id-field', 'text-field' );

		$dropdown = $this->get_field_logic_dropdown_ajax_field( $field_id, $logic_field, $logic_field_type );

		$opening_tag = '<select name="field_options[hide_opt_' . $field_id . '][]">';
		$first_option = '<option value=""></option>';
		$current_user_option = '<option value="current_user" >Current User</option>';
		$last_option = '<option value="1" >admin</option>';
		$closing_tag = '</select>';
		$option_number = 5;

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
		list( $field_id, $logic_field, $logic_field_type ) = $this->initialize_field_logic_variables_ajax_field( 'dynamic-country', 'text-field' );

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
	 * @since 2.03.05
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
	 * @since 2.03.05
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_action_logic_row_text_field_value_ajax() {
		list( $new_field, $current_field_id, $field_name ) = $this->initialize_action_logic_variables_ajax_field( 'text-field' );

		$dropdown = $this->get_action_logic_dropdown_ajax_field( $new_field, $current_field_id, $field_name );

		$expected = '<input type="text" name="' . $field_name . '" value="" />';

		$this->assertSame( trim( $expected ), trim( $dropdown ) );
	}

	/**
	 * Check the field value box, for a text field, in an action's logic row
	 *
	 * @since 2.03.05
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_action_logic_row_text_field_values() {
		list( $field_name, $meta_name, $new_field ) = $this->initialize_action_logic_variables( 'text-field' );

		$dropdown = $this->get_action_logic_dropdown( $field_name, $meta_name, $new_field, 'Test' );

		$expected = '<input type="text" name="' . $field_name . '" value="Test" />';

		$this->assertSame( trim( $expected ), trim( $dropdown ) );
	}

	/**
	 * Check the field value box for all fields with text boxes in an action's logic row
	 *
	 * @since 2.03.05
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_action_logic_for_fields_with_text_box() {
		foreach ( self::fields_with_text_box() as $field_key ) {
			self::check_action_logic_row_text_box( $field_key );
		}
	}


	/**
	 * Check the field value box in an action's logic row
	 *
	 * @since 2.03.05
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	private function check_action_logic_row_text_box( $field_key ) {
		list( $field_name, $meta_name, $new_field ) = $this->initialize_action_logic_variables( $field_key );

		$dropdown = $this->get_action_logic_dropdown( $field_name, $meta_name, $new_field, 'Test' );

		$expected = '<input type="text" name="' . $field_name . '" value="Test" />';

		$this->assertSame( trim( $expected ), trim( $dropdown ) );
	}


	/**
	 * Check the field value box, for a Checkbox field, in an action's logic row
	 *
	 * @since 2.03.05
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_action_logic_row_checkbox_field_values() {
		list( $field_name, $meta_name, $new_field ) = $this->initialize_action_logic_variables( 'checkbox-colors' );

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
	 * @since 2.03.05
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_action_logic_row_dropdown_field_values() {
		list( $field_name, $meta_name, $new_field ) = $this->initialize_action_logic_variables( 'checkbox-colors' );

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
	 * @since 2.03.05
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_action_logic_row_user_id_field_values() {
		list( $field_name, $meta_name, $new_field ) = $this->initialize_action_logic_variables( 'user-id-field' );

		$dropdown = $this->get_action_logic_dropdown( $field_name, $meta_name, $new_field, '' );

		$opening_tag = '<select name="' . $field_name . '">';
		$first_option = '<option value=""></option>';
		$current_user_option = '<option value="current_user" >Current User</option>';
		$last_option = '<option value="1" >admin</option>';
		$closing_tag = '</select>';
		$option_number = 5;

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
	 * @since 2.03.05
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
	 * @since 2.03.05
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
	 * @since 2.03.05
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
		$pending_option = '<option value="pending">Pending</option>';
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
	 * @since 2.03.05
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_action_logic_row_post_category_field_values() {
		list( $field_name, $meta_name, $new_field ) = $this->initialize_action_logic_variables( 'parent-dynamic-taxonomy' );

		$dropdown = $this->get_action_logic_dropdown( $field_name, $meta_name, $new_field, '' );

		if ( $this->category_dropdown_has_two_spaces() ) {
			$opening_tag = '<select  name=\'' . $field_name . '\'  >';
		} else {
			$opening_tag = '<select name=\'' . $field_name . '\'  >';
		}
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
	 * @since 2.03.05
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_mailchimp_hidden_field_values() {
		list( $html_name, $field ) = $this->initialize_mailchimp_field_value_variables( 'hidden-field' );

		$dropdown = $this->get_mailchimp_field_value_dropdown( $html_name, $field, '' );

		$expected = '<input type="text" name="' . $html_name . '" value="" />';

		$this->assertSame( trim( $expected ), trim( $dropdown ) );
	}

	/**
	 * Check the field value dropdown, for a Dropdown Field, in a Mailchimp action's Group settings
	 *
	 * @since 2.03.05
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_mailchimp_dropdown_field_values() {
		list( $html_name, $field ) = $this->initialize_mailchimp_field_value_variables( 'dropdown-field' );

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
	 * @since 2.03.05
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_mailchimp_dropdown_field_values_selected_option() {
		list( $html_name, $field ) = $this->initialize_mailchimp_field_value_variables( 'dropdown-field' );

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
	 * @since 2.03.05
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	public function test_mailchimp_checkbox_field_values() {
		list( $html_name, $field ) = $this->initialize_mailchimp_field_value_variables( 'checkbox-colors' );

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
	 * @since 2.03.05
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
	 * @since 2.03.05
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
		$current_field = FrmFieldsHelper::convert_field_object_to_flat_array( $field_object );
		$current_field_id = $field_object->id;
		$meta_name = 0;

		return array( $logic_field, $current_field, $current_field_id, $meta_name );
	}

	/**
	 * Initialize the variables used for the field values dropdown when field is selected
	 * in another field's conditional logic and field values are loaded with Ajax
	 *
	 * @since 2.03.05
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
	 * This code is intended to match the deprecated code in field-values.php
	 *
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
		// Get selector field ID
		if ( ! isset( $new_field ) || ! $new_field ) {
			$selector_field_id = 0;
		} else {
			$selector_field_id = (int) $new_field->id;
		}

		$selector_args = array();

		// Get field name
		if ( isset( $field_name ) ) {
			$selector_args[ 'html_name' ] = $field_name;
		} else if ( isset( $current_field_id ) ) {
			$selector_args['html_name'] = 'field_options[hide_opt_' . $current_field_id . '][]';
		} else {
			return '';
		}

		// Get value
		if ( isset( $val ) ) {
			$selector_args['value' ] = $val;
		} else {
			$selector_args['value'] = ( isset( $field ) && isset( $field['hide_opt'][$meta_name] ) ) ? $field['hide_opt'][$meta_name] : '';
		}

		// Get source
		$is_settings_page = ( FrmAppHelper::simple_get( 'frm_action' ) == 'settings' );
		$selector_args['source'] = ( $is_settings_page ) ? 'form_actions' : ( isset( $field_type ) ? $field_type : 'unknown' );

		ob_start();
		FrmFieldsHelper::display_field_value_selector( $selector_field_id, $selector_args );
		$dropdown = ob_get_contents();
		ob_end_clean();

		return $dropdown;
	}

	/**
	 * Get a field's logic values dropdown when loaded with ajax
	 * This code is intended to match the deprecated code in field-values.php
	 *
	 * @param $current_field_id
	 * @param $new_field
	 * @param $field_type
	 *
	 * @return string
	 */
	private function get_field_logic_dropdown_ajax_field( $current_field_id, $new_field, $field_type ) {
		// Get selector field ID
		if ( ! isset( $new_field ) || ! $new_field ) {
			$selector_field_id = 0;
		} else {
			$selector_field_id = (int) $new_field->id;
		}

		$selector_args = array();

		// Get field name
		if ( isset( $field_name ) ) {
			$selector_args[ 'html_name' ] = $field_name;
		} else if ( isset( $current_field_id ) ) {
			$selector_args['html_name'] = 'field_options[hide_opt_' . $current_field_id . '][]';
		} else {
			return '';
		}

		// Get value
		$selector_args['value'] = '';

		// Get source
		$is_settings_page = ( FrmAppHelper::simple_get( 'frm_action' ) == 'settings' );
		$selector_args['source'] = ( $is_settings_page ) ? 'form_actions' : ( isset( $field_type ) ? $field_type : 'unknown' );

		ob_start();
		FrmFieldsHelper::display_field_value_selector( $selector_field_id, $selector_args );
		$dropdown = ob_get_contents();
		ob_end_clean();

		return $dropdown;
	}

	/**
	 * This code is intended to match the deprecated code in field-values.php
	 *
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
		// Get selector field ID
		if ( ! isset( $new_field ) || ! $new_field ) {
			$selector_field_id = 0;
		} else {
			$selector_field_id = (int) $new_field->id;
		}

		$selector_args = array();

		// Get field name
		if ( isset( $field_name ) ) {
			$selector_args[ 'html_name' ] = $field_name;
		} else if ( isset( $current_field_id ) ) {
			$selector_args['html_name'] = 'field_options[hide_opt_' . $current_field_id . '][]';
		} else {
			return '';
		}

		// Get value
		if ( isset( $val ) ) {
			$selector_args['value' ] = $val;
		} else {
			$selector_args['value'] = ( isset( $field ) && isset( $field['hide_opt'][$meta_name] ) ) ? $field['hide_opt'][$meta_name] : '';
		}

		// Get source
		$is_settings_page = ( FrmAppHelper::simple_get( 'frm_action' ) == 'settings' );
		$selector_args['source'] = ( $is_settings_page ) ? 'form_actions' : ( isset( $field_type ) ? $field_type : 'unknown' );

		ob_start();
		FrmFieldsHelper::display_field_value_selector( $selector_field_id, $selector_args );
		$dropdown = ob_get_contents();
		ob_end_clean();

		return $dropdown;
	}

	/**
	 * Get the logic text field when there is no logic field selected yet
	 * This code is intended to match the deprecated code in field-values.php
	 *
	 * @since 2.03.05
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

		// Get selector field ID
		if ( ! isset( $new_field ) || ! $new_field ) {
			$selector_field_id = 0;
		} else {
			$selector_field_id = (int) $new_field->id;
		}

		$selector_args = array();

		// Get field name
		if ( isset( $field_name ) ) {
			$selector_args[ 'html_name' ] = $field_name;
		} else if ( isset( $current_field_id ) ) {
			$selector_args['html_name'] = 'field_options[hide_opt_' . $current_field_id . '][]';
		} else {
			return '';
		}

		// Get value
		if ( isset( $val ) ) {
			$selector_args['value' ] = $val;
		} else {
			$selector_args['value'] = ( isset( $field ) && isset( $field['hide_opt'][$meta_name] ) ) ? $field['hide_opt'][$meta_name] : '';
		}

		// Get source
		$is_settings_page = ( FrmAppHelper::simple_get( 'frm_action' ) == 'settings' );
		$selector_args['source'] = ( $is_settings_page ) ? 'form_actions' : ( isset( $field_type ) ? $field_type : 'unknown' );

		ob_start();
		FrmFieldsHelper::display_field_value_selector( $selector_field_id, $selector_args );
		$dropdown = ob_get_contents();
		ob_end_clean();

		unset( $_GET['frm_action'] );

		return $dropdown;
	}

	/**
	 * Get an action's logic values dropdown when loaded with ajax
	 * This code is intended to match the deprecated code in field-values.php
	 *
	 * @param $new_field
	 * @param $current_field_id
	 * @param $field_name
	 *
	 * @return string
	 */
	private function get_action_logic_dropdown_ajax_field( $new_field, $current_field_id, $field_name ) {
		$_GET['frm_action'] = 'settings';

		// Get selector field ID
		if ( ! isset( $new_field ) || ! $new_field ) {
			$selector_field_id = 0;
		} else {
			$selector_field_id = (int) $new_field->id;
		}

		$selector_args = array();

		// Get field name
		if ( isset( $field_name ) ) {
			$selector_args[ 'html_name' ] = $field_name;
		} else if ( isset( $current_field_id ) ) {
			$selector_args['html_name'] = 'field_options[hide_opt_' . $current_field_id . '][]';
		} else {
			return '';
		}

		// Get value
		$selector_args['value'] = '';

		// Get source
		$is_settings_page = ( FrmAppHelper::simple_get( 'frm_action' ) == 'settings' );
		$selector_args['source'] = ( $is_settings_page ) ? 'form_actions' : ( isset( $field_type ) ? $field_type : 'unknown' );

		ob_start();
		FrmFieldsHelper::display_field_value_selector( $selector_field_id, $selector_args );
		$dropdown = ob_get_contents();
		ob_end_clean();

		unset( $_GET['frm_action'] );

		return $dropdown;
	}

	/**
	 * Get the field value dropdown for an action's conditional logic
	 * This code is intended to match the deprecated code in field-values.php
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

		// Get selector field ID
		if ( ! isset( $new_field ) || ! $new_field ) {
			$selector_field_id = 0;
		} else {
			$selector_field_id = (int) $new_field->id;
		}

		$selector_args = array();

		// Get field name
		if ( isset( $field_name ) ) {
			$selector_args[ 'html_name' ] = $field_name;
		} else if ( isset( $current_field_id ) ) {
			$selector_args['html_name'] = 'field_options[hide_opt_' . $current_field_id . '][]';
		} else {
			return '';
		}

		// Get value
		if ( isset( $val ) ) {
			$selector_args['value' ] = $val;
		} else {
			$selector_args['value'] = ( isset( $field ) && isset( $field['hide_opt'][$meta_name] ) ) ? $field['hide_opt'][$meta_name] : '';
		}

		// Get source
		$is_settings_page = ( FrmAppHelper::simple_get( 'frm_action' ) == 'settings' );
		$selector_args['source'] = ( $is_settings_page ) ? 'form_actions' : ( isset( $field_type ) ? $field_type : 'unknown' );

		ob_start();
		FrmFieldsHelper::display_field_value_selector( $selector_field_id, $selector_args );
		$dropdown = ob_get_contents();
		ob_end_clean();

		unset( $_GET['frm_action'] );

		return $dropdown;
	}

	/**
	 * Get a field value dropdown for a MailChimp action
	 * This code is intended to match the deprecated code in field-values.php
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

		// Get selector field ID
		if ( ! isset( $new_field ) || ! $new_field ) {
			$selector_field_id = 0;
		} else {
			$selector_field_id = (int) $new_field->id;
		}

		$selector_args = array();

		// Get field name
		if ( isset( $field_name ) ) {
			$selector_args[ 'html_name' ] = $field_name;
		} else if ( isset( $current_field_id ) ) {
			$selector_args['html_name'] = 'field_options[hide_opt_' . $current_field_id . '][]';
		} else {
			return '';
		}

		// Get value
		$selector_args['value' ] = $val;

		// Get source
		$is_settings_page = ( FrmAppHelper::simple_get( 'frm_action' ) == 'settings' );
		$selector_args['source'] = ( $is_settings_page ) ? 'form_actions' : ( isset( $field_type ) ? $field_type : 'unknown' );

		ob_start();
		FrmFieldsHelper::display_field_value_selector( $selector_field_id, $selector_args );
		$dropdown = ob_get_contents();
		ob_end_clean();

		unset( $_GET['frm_action'] );

		return $dropdown;
	}

	private function fields_with_text_box() {
		return array(
			'text' => 'text-field',
			'textarea' => 'paragraph-field',
			'email' => 'email-field',
			'url' => 'website-field',
			'number' => 'number-field',
			'phone' => 'phone-number',
			'date' => 'date-field',
			'time' => 'time-field',
			'image' => 'image-url',
			'lookup' => 'lookup-country',
			'hidden' => 'hidden-field',
			'password' => '9r61y8',
			'tags' => 'tags-field',
		);
	}

	private function wp_version_number() {
		include(  get_home_path()). '/wp-includes/version.php';
		return $wp_db_version;
	}

	private function category_dropdown_has_two_spaces() {
		return $this->wp_version_number() > 36180;
	}
}
