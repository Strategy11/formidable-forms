<?php

/**
 * @group ajax
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
		/*2. Switch to regular
			- move child fields to parent form √
			- move child entries to parent form √
			- child entries deleted √
			- child form deleted √
			- form_select and repeat updated √
			- check if correct form_id is echoed

		3. Switch to repeating
			- child form created w/correct parent_form_id √
			- move child fields to child form √
			- child entries created from parent data
			- form_select updated and repeat updated*/

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
		$this->assertNotEmpty( $child_ids, 'There are no fields to toggle to repeating' );

		self::_switch_to_not_repeating( $repeating_section, $child_ids );
		self::_switch_to_repeating( $repeating_section, $child_ids );

		// Again, but update the form this time
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

		self::_check_if_fields_moved( $args['parent_form_id'], $args['children'], 'non-repeatable' );
		self::_check_if_child_entries_moved( $args );
		self::_check_repeat_options_updated( $repeating_section, 0, '', 'non-repeatable' );
		self::_check_echo_value( '' );
	}

	function _set_post_values( $repeating_section, $form_id, $child_ids ) {
		$checked = $form_id ? 0 : 1;
		$args = array(
			'action'    => 'frm_toggle_repeat',
            'nonce'     => wp_create_nonce('frm_ajax'),
			'form_id' 	=> $form_id,
			'parent_form_id' => $repeating_section->form_id,
			'checked' 	=> 0,
			'field_id' 	=> $repeating_section->id,
			'children' 	=> $child_ids,
			'checked'	=> $checked
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
	*/
	function _check_if_child_entries_moved( $args ){
		global $wpdb;

		// First check if old frm_items are gone from child form
		$items = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "frm_items WHERE form_id=" . $args['form_id'] );
		$this->assertEmpty( $items, 'Rows in wp_frm_items were not deleted when switching from repeating to non-repeating.');

		// Check if frm_item_metas were moved to parent entries
		$new_child_metas = FrmDb::get_col( $wpdb->prefix . 'frm_item_metas m LEFT JOIN ' . $wpdb->prefix . 'frm_items it ON it.id=m.item_id', array( 'field_id' => $args['children'] ), 'it.form_id', array( 'order_by' => 'it.created_at ASC' ) );

		$this->assertNotEmpty( $new_child_metas, 'No entries to check (when switching divider to non-repeatable).');
		foreach ( $new_child_metas as $new_form_id ) {
			$this->assertEquals( $args['parent_form_id'], $new_form_id, 'Child entries are not moved to parent form (' . $args['parent_form_id'] . ') when a divider is switched from repeating to non-repeating.');
		}

		// Make sure frm_item_metas for repeating section are cleaned up
		$rep_meta_values = $wpdb->get_col( "SELECT meta_value FROM " . $wpdb->prefix . "frm_item_metas WHERE field_id=" . $args['field_id'] );
		$this->assertEmpty( $rep_meta_values, 'frm_item_metas for repeating section were not deleted when switching to non-repeatable.');

		self::_check_if_child_form_deleted( $args['form_id'] );
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
		$this->assertEmpty( $expected_form_select, $new_repeat->field_options['form_select'], 'Form_select not updated when divider is switched to ' . $msg );
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
		}
		echo 'get and post';
		print_r( $_GET );
		print_r( $_POST );

		$new_form_id = self::_check_if_new_form_created( $old_count, $args );
		self::_check_if_fields_moved( $new_form_id, $args['children'], 'repeatable' );
		self::_check_if_child_entries_created( $args );
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
	function _check_if_child_entries_created( $args ) {
		global $wpdb;

		// Check for value in repeating section
		$rep_meta_values = $wpdb->get_col( "SELECT meta_value FROM " . $wpdb->prefix . "frm_item_metas WHERE field_id=" . $args['field_id'] );
		//print_r( $rep_meta_values );

		// TODO: Check number of frm_items with ID of new form ID
		// TODO: Check for frm_item_metas connected to those frm_itesm
	}
}