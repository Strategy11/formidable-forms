<?php

/**
 * @group ajax
 * @group pro
 */
class WP_Test_FrmProFieldsControllerAjax extends FrmAjaxUnitTest {

	public function setUp() {
		parent::setUp();

		$this->user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $this->user_id );

	}

	/**
	 * @covers FrmProFieldsController::toggle_repeat
	 */
	function test_toggle_repeat(){
		/*
		1. Start with repeating, switch to regular
			- move child fields to parent form √
			- move child entries to parent form √
			- child entries deleted √
			- child form deleted √
			- form_select and repeat updated √
			- check if correct form_id is echoed √

		2. Switch to repeating
			- child form created w/correct parent_form_id √
			- move child fields to child form √
			- child entries created from parent data √
			- form_select updated and repeat updated √
			- check if correct form_id is echoed
		*/

		$form_id = $this->factory->form->get_id_by_key( 'all_field_types' );
		$section_fields = FrmField::get_all_types_in_form( $form_id, 'divider' );

		foreach ( $section_fields as $section ) {
			if ( FrmField::is_repeating_field( $section ) ) {
				$repeating_section = $section;
				break;
			}
		}

		$child_form_id = $repeating_section->field_options['form_select'];
		$children = FrmField::get_all_for_form( $child_form_id, '', 'include' );
		$child_ids = array();
		foreach ( $children as $child ) {
			$child_ids[] = $child->id;
		}
		$this->assertNotEmpty( $child_ids, 'There were no fields retrieved for the repeating section form' );

		self::_switch_to_not_repeating( $repeating_section, $child_ids );
		self::_switch_to_repeating( $repeating_section, $child_ids );

		// TODO: Again, but update the form this time
		// Update form
		/*self::_switch_to_not_repeating( $repeating_section, $child_ids );
		// Update form
		self::_switch_to_repeating( $repeating_section, $child_ids );
		// Update form*/
	}

	function _switch_to_not_repeating( $repeating_section, $child_ids ) {
		$args = self::_set_post_values( $repeating_section, $repeating_section->field_options['form_select'], $child_ids );

		try {
		    $this->_handleAjax( 'frm_toggle_repeat' );
		} catch ( WPAjaxDieStopException $e ) {
		    // We expected this, do nothing.
		}

		self::_check_if_child_form_deleted( $args['form_id'] );
		self::_check_if_fields_moved( $args['parent_form_id'], $args['children'], 'non-repeatable' );
		self::_check_if_child_entries_moved( $args );
		self::_check_repeat_options_updated( $repeating_section, 0, '', 'non-repeatable' );
	}

	function _set_post_values( $repeating_section, $form_id, $child_ids ) {
		$checked = $form_id ? 0 : 1;
		$args = array(
			'action'    => 'frm_toggle_repeat',
            'nonce'     => wp_create_nonce('frm_ajax'),
			'form_id' 	=> $form_id,
			'parent_form_id' => $repeating_section->form_id,
			'field_id' 	=> $repeating_section->id,
			'children' 	=> $child_ids,
			'checked'	=> $checked,
			'field_name'	=> $repeating_section->name
		);

		$_POST = $args;
		return $args;
	}

	/**
	* @covers FrmProFieldsHelper::move_fields_to_form
	*/
	function _check_if_fields_moved( $expected_form_id, $child_fields, $msg ){
		// Get the form ID for all the child fields
		global $wpdb;
		$query = "SELECT form_id FROM " . $wpdb->prefix . "frm_fields WHERE id IN (" . implode( ',', $child_fields ) . ")";
		$field_form_ids = $wpdb->get_col( $query );

		// Check the returned count count to make sure fields weren't deleted
		$this->assertEquals( count( $child_fields ), count( $field_form_ids ), 'Child fields may have been deleted when switching divider to ' . $msg . ' field.' );

		// Check if the form_id on each of the fields is correct
		foreach ( $field_form_ids as $actual_form_id ) {
			$this->assertTrue( $actual_form_id == $expected_form_id, 'Child fields were not moved to the correct form when switching divider to ' . $msg . ' field.');
		}
	}

	/**
	* @covers FrmProFieldsHelper::move_entries_to_parent_form
	* Checks if entries are moved correctly when switching from repeating to non-repeating
	*
	* When switching from repeating to non-repeating, only the first frm_item_metas for each child entry should remain.
	* On the remaining frm_item_meta, the item_id is the only thing that should change.
	*/
	function _check_if_child_entries_moved( $args ){
		global $wpdb;

		self::_check_repeating_section_metas( $args );
		self::_check_if_child_items_deleted( $args );
		self::_check_if_child_metas_moved( $args );
		self::_check_if_extra_child_metas_deleted( $args );
	}

	function _check_if_child_items_deleted( $args ){
		global $wpdb;

		// Check if old frm_items are gone from child form
		$items = $wpdb->get_results( "SELECT id FROM " . $wpdb->prefix . "frm_items WHERE form_id=" . $args['form_id'] );
		$this->assertEmpty( $items, 'Rows in wp_frm_items were not deleted when switching from repeating to non-repeating.');
	}

	function _check_repeating_section_metas( $args ) {
		global $wpdb;

		// Make sure frm_item_metas for repeating section are cleaned up
		$rep_meta_values = $wpdb->get_col( "SELECT meta_value FROM " . $wpdb->prefix . "frm_item_metas WHERE field_id=" . $args['field_id'] );
		$this->assertEmpty( $rep_meta_values, 'frm_item_metas for repeating section were not deleted when switching to non-repeatable.');
	}

	function _check_if_child_metas_moved( $args ){
		global $wpdb;

		// Check if frm_item_metas were moved to parent entries
		$new_child_metas = FrmDb::get_results( $wpdb->prefix . 'frm_item_metas m LEFT JOIN ' . $wpdb->prefix . 'frm_items it ON it.id=m.item_id', array( 'field_id' => $args['children'] ), 'm.field_id,m.item_id,it.form_id', array( 'order_by' => 'it.created_at ASC' ) );

		$this->assertNotEmpty( $new_child_metas, 'No entries to check (when switching divider to non-repeatable).');

		foreach ( $new_child_metas as $item_meta ) {
			$this->assertEquals( $args['parent_form_id'], $item_meta->form_id, 'The item_id in frm_item_metas is not switched to parent entry ID when a divider is switched from repeating to non-repeating.');
		}
	}

	// Check if there are the correct number of item_metas (all should have been deleted except one for each parent entry)
	function _check_if_extra_child_metas_deleted( $args ) {
		global $wpdb;

		// Get number of entries in parent form
		$parent_entries = FrmDb::get_results( $wpdb->prefix . 'frm_items', array( 'form_id' => $args['parent_form_id'] ), 'id' );
		$entries_for_one_field = FrmDb::get_results( $wpdb->prefix . 'frm_item_metas', array( 'field_id' => reset( $args['children'] ) ), 'item_id' );

		$this->assertEquals( count( $parent_entries ), count( $entries_for_one_field ), 'Child item_metas were not deleted when switching a repeating section to non-repeating. Only the item_metas for the first row of each repeating section should be saved.');
	}

	/**
	* @covers FrmProFieldsHelper::move_entries_to_parent_form
	*/
	function _check_if_child_form_deleted( $id ) {
		$child_form = FrmForm::getOne( $id );
		$this->assertEmpty( $child_form, 'Child form was not deleted when switching from repeating to non-repeating section');
	}

	/**
	* @covers update_for_repeat
	*/
	function _check_repeat_options_updated( $repeating_section, $expected_repeat, $expected_form_select, $msg ) {
		$new_repeat = FrmField::getOne( $repeating_section->id );

		// Check repeat option
		$this->assertEquals( $expected_repeat, $new_repeat->field_options['repeat'], 'The repeat option is not updated when a divider is switched to ' . $msg );

		// Check form_select
		$this->assertEquals( $expected_form_select, $new_repeat->field_options['form_select'], 'Form_select not updated when divider is switched to ' . $msg );
	}

	function _switch_to_repeating( $repeating_section, $child_ids ) {
		// Get count of current forms
		global $wpdb;
		$all_forms = $wpdb->get_col( "SELECT id FROM " . $wpdb->prefix . "frm_forms" );
		$old_count = count( $all_forms );

		$args = self::_set_post_values( $repeating_section, '', $child_ids );

		try {
		    $this->_handleAjax( 'frm_toggle_repeat' );
		} catch ( WPAjaxDieContinueException $e ) {
		    // We expected this, do nothing.
			unset( $e );
		}

		$new_form_id = self::_check_if_new_form_created( $old_count, $args );

		// Check if correct form ID is echoed
		$response = $this->_last_response;
		$this->assertEquals( $response, $new_form_id, 'The incorrect form ID is echoed when switching to a repeating section.' );

		self::_check_if_fields_moved( $new_form_id, $args['children'], 'repeatable' );
		self::_check_if_child_entries_created( $args, $new_form_id );
		self::_check_repeat_options_updated( $repeating_section, 1, $new_form_id, 'repeatable' );
	}

	/**
	* @covers FrmProFieldsController::toggle_repeat
	*/
	function _check_if_new_form_created( $old_count, $args ) {
		global $wpdb;
		$all_forms = $wpdb->get_col( "SELECT id FROM " . $wpdb->prefix . "frm_forms" );
		$new_count = count( $all_forms );
		$this->assertEquals( $old_count + 1, $new_count, 'A new form is not created when switching divider to repeatable.');

		// Get ID of new form
		$new_form_id = max( $all_forms );

		// Check parent_form_id
		$new_form = FrmForm::getOne( $new_form_id );
		$this->assertEquals( $args['parent_form_id'], $new_form->parent_form_id, 'parent_form_id is not set correctly when a new form is created when switching to a repeating section.' );

		return $new_form_id;
	}

	/**
	* @covers FrmProFieldsHelper::move_entries_to_child_form
	*/
	function _check_if_child_entries_created( $args, $child_form_id ) {
		global $wpdb;

		// Check for value in repeating section
		$rep_meta_values = $wpdb->get_col( "SELECT meta_value FROM " . $wpdb->prefix . "frm_item_metas WHERE field_id=" . $args['field_id'] );
		$this->assertNotEmpty( $rep_meta_values, 'When switching from non-repeating to repeating, the repeating section frm_item_metas is not saving the IDs of the child entries.');

		// Check if entries were created in child form
		$child_items = FrmEntry::getAll( array( 'it.form_id' => $child_form_id) );
		$parent_items = FrmEntry::getAll( array( 'it.form_id' => $args['parent_form_id'] ) );
		$this->assertEquals( count( $parent_items ), count( $child_items ), 'When switching from non-repeating to repeating section, child entries are not created. ');

		// Check if entries in child form match IDs saved in repeating section frm_item_metas
		$child_ids = array_keys( $child_items );
		$this->assertEquals( $child_ids, $rep_meta_values, 'When switching from non-repeating to repeating, created entry IDs do not match IDs saved in repeating section field frm_item_metas.');

		// Check if the item_id for child field frm_item_metas was updated to match new child entry IDs
		$new_child_metas = FrmDb::get_col( $wpdb->prefix . 'frm_item_metas m LEFT JOIN ' . $wpdb->prefix . 'frm_items it ON it.id=m.item_id', array( 'field_id' => $args['children'] ), 'DISTINCT m.item_id', array( 'order_by' => 'it.created_at ASC' ) );
		$this->assertEquals( $child_ids, $new_child_metas, 'When switching from non-repeating to repeating, the item_id is not updated on frm_item_metas for child fields' );
	}
	/**
	* @covers FrmProFieldsController::ajax_data_options
	*/
	function test_frm_fields_ajax_data_options(){
		self::_check_dependent_dynamic_fields();

		// Test three levels
		// Repeating field
		// Test hierarchical category fields
		// Test with no parent val selected
	}

	function _check_dependent_dynamic_fields() {
		$tests = array(
			'checkbox',
			'checkbox_prev_val',
			'checkbox_default_val',
			'checkbox_readonly_default_val',
			'select',
			'select_prev_val',
			'select_default_val',
			'select_readonly_default_val',
			'radio',
			'radio_prev_val',
			'radio_default_val',
			'multi_radio',
			'multi_radio_readonly_default_val',
		);

		$args = self::_get_dynamic_fields_args();

		$post_vals = self::_get_dynamic_posted_vals( $args );

		foreach ( $tests as $test ) {
			$_POST = $post_vals;

			self::_modify_dynamic_posted_vals( $test, $args );
			self::_modify_dynamic_field_for_test( $test, $args );

			try {
			    $this->_handleAjax( 'frm_fields_ajax_data_options' );
			} catch ( WPAjaxDieContinueException $e ) {
			    // Nothing was echoed
			}

			// The data returned from the ajax request
			$response = $this->_last_response;

			self::_check_dynamic_field_options( $response, $args, $test );
			self::_check_dynamic_field_value( $response, $args, $test );
			self::_check_dynamic_field_default_value( $response, $args, $test );

			$this->_last_response = '';
		}
	}
	function _get_dynamic_fields_args() {
		$args = array();

		// Get the Dynamic field
		$field_id = FrmField::get_id_by_key( 'dynamic-state' );
		$args['field'] = FrmField::getOne( $field_id );

		// Get the Parent field
		$parent_field_id = FrmField::get_id_by_key( 'dynamic-country' );
		$args['parent_field'] = FrmField::getOne( $parent_field_id );
		$values = array( 'hide_field' => array(), 'form_select' => $args['parent_field']->field_options['form_select'], 'restrict' => '' );
		$args['parent_options'] = FrmProDynamicFieldsController::get_independent_options( $values, $args['field'], '');

		foreach ( $args['parent_options'] as $key => $opt ) {
			if ( $opt == 'United States' ) {
				$args['us_id'] = $key;
				break;
			}
		}

		self::_get_the_join_field( $args );
		self::_get_the_expected_dynamic_field_entry_ids( $args );
		self::_get_the_prev_val( $args );

		return $args;
	}
	function _get_dynamic_posted_vals( $args ) {

		$post_vals = array(
			'trigger_field_id' => $args['parent_field']->id,
			'entry_id' => $args['us_id'],
			'field_id' => $args['field']->id,
			'container_id' => 'frm_field_' . $args['field']->id . '_container',
			'linked_field_id' => $args['field']->field_options['form_select'],
			'default_value' => '',
			'prev_val' => ''
		);

		return $post_vals;
	}

	function _modify_dynamic_posted_vals( $test, $args ) {
		if ( strpos( $test, 'prev_val' ) !== false ) {
			$_POST['prev_val'] = $args['prev_val'];
		} else if ( strpos( $test, 'default_val' ) !== false ) {
			$_POST['default_value'] = $args['prev_val'];
		} else if ( $test == 'multi-radio' ) {
			$_POST['entry_id'] = array_keys( $args['parent_options'] );
		}
	}

	function _modify_dynamic_field_for_test( $test, $args ) {
		$update_field = false;
			if ( strpos( $test, 'readonly' ) !== false ) {
			$update_field = true;
			$new_field_options = $args['field']->field_options;
			$new_field_options['read_only'] = true;
		}

		if ( in_array( $test, array( 'radio', 'select' ) ) ) {
			$update_field = true;
			$new_field_options = $args['field']->field_options;
			$new_field_options['read_only'] = false;
			$new_field_options['data_type'] = $test;
			$args['field']->field_options['data_type'] = $test;
		}

		if ( $update_field ) {
			$field_values = array(
				'field_options' => $new_field_options
			);

			FrmField::update( $args['field']->id, $field_values );
		}
	}

	function _check_dynamic_field_options( $response, $args, $test ) {
		$expected_ids = self::_package_expected_ids( $args );

		// Check if the options are correct
		foreach ( $expected_ids as $e ) {
			self::_check_for_value_id_substring( $e, $response, $test );
			self::_check_for_readonly_input( $e, $response, $test );
			self::_check_for_input_id_substring( $e, $response, $test );
		}

		self::_make_sure_brazil_options_are_not_present( $args, $response, $test );
	}
	function _package_expected_ids( $args ) {
		if ( is_array( $_POST['entry_id'] ) ) {
			$expected_ids = array();
			foreach ( $_POST['entry_id'] as $e ) {
				$expected_ids = array_merge( $expected_ids, $args['expected_ids'][ $e ] );
			}
		} else if ( ! is_array( $_POST['entry_id'] ) ) {
			$expected_ids = $args['expected_ids'][ $_POST['entry_id'] ];
		}

		return $expected_ids;
	}
	function _check_for_value_id_substring( $e, $response, $test ) {
		// Check for the value="ID" substring
		$substring = 'value="' . $e . '"';
		$this->assertTrue( strpos( $response, $substring ) !== false, 'The substring ' . $substring . ' didn\'t show up in the field in the ' . $test . ' test.' );
	}

	function _check_for_readonly_input( $e, $response, $test ) {
		if ( strpos( $test, 'readonly' ) !== false ) {
			$substring = '<input type="hidden"';

			$this->assertTrue( strpos( $response, $substring ) !== false, 'The substring ' . $substring . ' didn\'t show up in the field in the ' . $test . ' test.' );
		}
	}

	function _check_for_input_id_substring( $e, $response, $test ) {
		if ( strpos( $test, 'checkbox' ) !== false || strpos( $test, 'radio' ) !== false ) {
			$substring = 'id="field_dynamic-state-' . $e . '"';
		} else if ( strpos( $test, 'select' ) !== false ) {
			$substring = '<select';
		}

		$this->assertTrue( strpos( $response, $substring ) !== false, 'The substring ' . $substring . ' didn\'t show up in the field in the ' . $test . ' test.' );
	}

	// Make sure Sao Paulo isn't in the options when only US States should be showing
	function _make_sure_brazil_options_are_not_present( $args, $response, $test ) {
		if ( $_POST['entry_id'] == $args['us_id'] ) {
			$substring = 'Sao Paulo';
			$this->assertFalse( strpos( $response, $substring ), 'Sao Paulo is showing up in the Dynamic field options when it shouldn\'t be (' . $test . ' test).' );
		}
	}

	function _check_dynamic_field_value( $response, $args, $test ) {
		$selected_vals = array();

		if ( strpos( $test, 'prev_val' ) !== false || strpos( $test, 'default_val' ) !== false ) {
			$selected_vals = $args['prev_val'];
		}

		foreach ( $selected_vals as $selected_val ) {
			self::_check_if_dynamic_field_value_selected( $test, $response, $selected_val );
		}

		if ( empty( $selected_vals ) ) {
			self::_make_sure_no_values_selected( $test, $response );
		}
	}

	function _check_if_dynamic_field_value_selected( $test, $response, $selected_val ) {

		if ( strpos( $test, 'checkbox' ) !== false || strpos( $test, 'radio' ) !== false ) {
			$substring = 'value="' . $selected_val . '"  checked="checked"';

		} else if ( strpos( $test, 'select' ) !== false ) {
			$substring = 'value="' . $selected_val . '" selected="selected"';

		}

		$this->assertTrue( strpos( $response, $substring ) !== false, 'The correct value was not selected in the dependent Dynamic field in the ' . $test . ' test.' );

	}

	function _make_sure_no_values_selected( $test, $response ) {
		if ( strpos( $test, 'checkbox' ) !== false || strpos( $test, 'radio' ) !== false ) {
			// Checkbox/radio with no values selected
			$substring = 'checked="true"';
			$this->assertFalse( strpos( $response, $substring ), 'A value was selected in a dependent Dynamic field when it shouldn\'t be in the ' . $test . ' test.' );

		} else if ( strpos( $test, 'select' ) !== false ) {
			// Dropdown with no option selected
			$substring = '<option value="" selected="selected"> </option>';
			$this->assertTrue( strpos( $response, $substring ) !== false, 'The correct value was not selected in the dependent Dynamic field in the ' . $test . ' test.' );
		}
	}

	// Make sure data-frmval is set when it should be
	function _check_dynamic_field_default_value( $response, $args, $test ) {
		$default_vals = array();
			if ( strpos( $test, 'default_val' ) !== false ) {
			$default_vals = $args['prev_val'];
		}
			foreach ( $default_vals as $default ) {
			$substring = 'data-frmval="[&quot;' . $default . '&quot;]"';

			$this->assertTrue( strpos( $response, $substring ) !== false, 'The correct value was not selected in the dependent Dynamic field in the ' . $test . ' test.' );
		}
	}

	// Get the joining field (for example, the Dynamic Country field in the State form)
	function _get_the_join_field( &$args ) {
		$linked_field = FrmField::getOne( $args['field']->field_options['form_select'] );
		$join_fields = FrmField::get_all_types_in_form( $linked_field->form_id, 'data' );
		foreach ( $join_fields as $j ) {
			if ( $j->field_options['form_select'] == $args['parent_field']->field_options['form_select'] ) {
				$args['join_field'] = $j;
				break;
			}
		}
	}

	// Get all entry IDs for selected option
	function _get_the_expected_dynamic_field_entry_ids( &$args ) {
		global $wpdb;

		$args['expected_ids'] = array();
		foreach ( $args['parent_options'] as $entry_id => $opt ) {
			$query = 'SELECT item_id FROM ' . $wpdb->prefix . 'frm_item_metas WHERE field_id=' . $args['join_field']->id . ' AND meta_value=' . $entry_id;
			$args['expected_ids'][ $entry_id ] = $wpdb->get_col( $query );
		}
	}

	function _get_the_prev_val( &$args ) {
		$state_array = $args['expected_ids'][ $args['us_id'] ];
		$random_key = array_rand( $state_array );
		$args['prev_val'] = array( $state_array[ $random_key ] );
	}

	/**
	 * @covers FrmProFieldsController::get_field_values()
	 *
	 * @since 2.03.05
	 */
	function test_get_field_values_for_form_action_logic() {
		$field_id = FrmField::get_id_by_key( 'text-field' );
		$name = 'frm_form_action[12][post_content][conditions][][hide_opt]';

		$_POST = array(
			'action' => 'frm_get_field_values',
			'field_id' => $field_id,
			'current_field' => '12',// action ID
			'name' => $name,
			't' => '',
			'form_action' => 'update_settings',
			'nonce'     => wp_create_nonce('frm_ajax'),
		);

		try {
			$this->_handleAjax( 'frm_get_field_values' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$expected = '<input type="text" name="' . $name . '" value="" />';
		$response = $this->_last_response;
		$this->assertEquals( $expected, $response );
	}

	/**
	 * Check the value selector returned, with Ajax, for a paragraph field logic row
	 *
	 * @covers FrmProFieldsController::get_field_values()
	 *
	 * field_id is the ID of the logic field
	 * current_field is the ID of the source field (the field being edited)
	 * name is the value selector name. It is always blank in this situation
	 * t is the source field type
	 * form_action is update or create
	 *
	 */
	function test_get_paragraph_field_values_for_field_logic() {
		$logic_field_id = FrmField::get_id_by_key( 'p3eiuk' );
		$source_field_id = FrmField::get_id_by_key( 'text-field' );

		$this->set_post_values_for_field_logic_value_selector( $logic_field_id, $source_field_id, 'text', 'create' );

		try {
			$this->_handleAjax( 'frm_get_field_values' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$expected = '<input type="text" name="field_options[hide_opt_' . $source_field_id . '][]" value="" />';
		$response = $this->_last_response;
		$this->assertEquals( $expected, $response );

	}

	/**
	 * Check the value selector returned, with Ajax, for a time field logic row
	 *
	 * @covers FrmProFieldsController::get_field_values()
	 *
	 * field_id is the ID of the logic field
	 * current_field is the ID of the source field (the field being edited)
	 * name is the value selector name. It is always blank in this situation
	 * t is the source field type
	 * form_action is update or create
	 *
	 */
	function test_get_time_field_values_for_field_logic() {
		$logic_field_id = FrmField::get_id_by_key( 'time-field' );
		$source_field_id = FrmField::get_id_by_key( 'text-field' );

		$this->set_post_values_for_field_logic_value_selector( $logic_field_id, $source_field_id );

		try {
			$this->_handleAjax( 'frm_get_field_values' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$expected = '<input type="text" name="field_options[hide_opt_' . $source_field_id . '][]" value="" />';
		$response = $this->_last_response;
		$this->assertEquals( $expected, $response );

	}

	/**
	 * Check the value selector returned, with Ajax, for a Lookup field logic row
	 *
	 * @covers FrmProFieldsController::get_field_values()
	 *
	 * field_id is the ID of the logic field
	 * current_field is the ID of the source field (the field being edited)
	 * name is the value selector name. It is always blank in this situation
	 * t is the source field type
	 * form_action is update or create
	 *
	 */
	function test_get_lookup_field_values_for_field_logic() {
		$logic_field_id = FrmField::get_id_by_key( 'lookup-country' );
		$source_field_id = FrmField::get_id_by_key( 'text-field' );

		$this->set_post_values_for_field_logic_value_selector( $logic_field_id, $source_field_id );

		try {
			$this->_handleAjax( 'frm_get_field_values' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$expected = '<input type="text" name="field_options[hide_opt_' . $source_field_id . '][]" value="" />';
		$response = $this->_last_response;
		$this->assertEquals( $expected, $response );

	}

	/**
	 * Check the value selector returned, with Ajax, for a scale field logic row
	 *
	 * @covers FrmProFieldsController::get_field_values()
	 *
	 * field_id is the ID of the logic field
	 * current_field is the ID of the source field (the field being edited)
	 * name is the value selector name. It is always blank in this situation
	 * t is the source field type
	 * form_action is update or create
	 *
	 */
	function test_get_scale_field_values_for_field_logic() {
		$logic_field_id = FrmField::get_id_by_key( 'qbrd2o' );
		$source_field_id = FrmField::get_id_by_key( 'text-field' );

		$this->set_post_values_for_field_logic_value_selector( $logic_field_id, $source_field_id );

		try {
			$this->_handleAjax( 'frm_get_field_values' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$dropdown = $this->_last_response;

		$opening_tag = '<select name="field_options[hide_opt_' . $source_field_id . '][]">';
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
	 * Check the value selector returned, with Ajax, for a Dynamic field logic row
	 *
	 * @covers FrmProFieldsController::get_field_values()
	 *
	 * field_id is the ID of the logic field
	 * current_field is the ID of the source field (the field being edited)
	 * name is the value selector name. It is always blank in this situation
	 * t is the source field type
	 * form_action is update or create
	 *
	 */
	function test_get_dynamic_field_values_for_field_logic() {
		$logic_field_id = FrmField::get_id_by_key( 'dynamic-country' );
		$source_field_id = FrmField::get_id_by_key( 'text-field' );

		$this->set_post_values_for_field_logic_value_selector( $logic_field_id, $source_field_id );

		try {
			$this->_handleAjax( 'frm_get_field_values' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$dropdown = $this->_last_response;

		$opening_tag = '<select name="field_options[hide_opt_' . $source_field_id . '][]">';
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
	 * Check the value selector returned, with Ajax, for a Dynamic field logic row
	 *
	 * @covers FrmProFieldsController::get_field_values()
	 *
	 * field_id is the ID of the logic field
	 * current_field is the ID of the source field (the field being edited)
	 * name is the value selector name. It is always blank in this situation
	 * t is the source field type
	 * form_action is update or create
	 *
	 */
	function test_get_dynamic_field_values_for_dynamic_field_logic() {
		$logic_field_id = FrmField::get_id_by_key( 'dynamic-country' );
		$source_field_id = FrmField::get_id_by_key( 'dynamic-state' );

		$this->set_post_values_for_field_logic_value_selector( $logic_field_id, $source_field_id, 'data' );

		try {
			$this->_handleAjax( 'frm_get_field_values' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$dropdown = $this->_last_response;

		$opening_tag = '<select name="field_options[hide_opt_' . $source_field_id . '][]">';
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
	 * Check the value selector returned, with Ajax, for a UserID field logic row
	 *
	 * @covers FrmProFieldsController::get_field_values()
	 *
	 * field_id is the ID of the logic field
	 * current_field is the ID of the source field (the field being edited)
	 * name is the value selector name. It is always blank in this situation
	 * t is the source field type
	 * form_action is update or create
	 *
	 */
	function test_get_user_id_field_values_for_field_logic() {
		$logic_field_id = FrmField::get_id_by_key( 'user-id-field' );
		$source_field_id = FrmField::get_id_by_key( 'text-field' );

		$this->set_post_values_for_field_logic_value_selector( $logic_field_id, $source_field_id );

		try {
			$this->_handleAjax( 'frm_get_field_values' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$dropdown = $this->_last_response;

		$opening_tag = '<select name="field_options[hide_opt_' . $source_field_id . '][]">';
		$first_option = '<option value=""></option>';
		$current_user_option = '<option value="current_user" >Current User</option>';
		$last_option = '<option value="1" >admin</option>';
		$closing_tag = '</select>';

		$this->assertContains( $opening_tag, $dropdown );
		$this->assertContains( $closing_tag, $dropdown );
		$this->assertContains( $first_option, $dropdown );
		$this->assertContains( $current_user_option, $dropdown );
		$this->assertContains( $last_option, $dropdown );

	}

	/**
	 * Set the $_POST variable for a field logic value selector
	 *
	 * @since 2.03.05
	 *
	 * @param string $logic_field_id
	 * @param string $source_field_id
	 * @param string $source_type
	 * @param string $action
	 */
	private function set_post_values_for_field_logic_value_selector( $logic_field_id, $source_field_id, $source_type = 'text', $action = 'update' ) {
		$_POST = array(
			'action' => 'frm_get_field_values',
			'field_id' => $logic_field_id,
			'current_field' => $source_field_id,
			'name' => '',
			't' => $source_type,
			'form_action' => $action,
			'nonce'     => wp_create_nonce( 'frm_ajax' ),
		);
	}
}
