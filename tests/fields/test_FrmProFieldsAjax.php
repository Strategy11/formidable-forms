<?php

/**
 * @group ajax
 * @group pro
 */
class WP_Test_FrmProFieldsAjax extends FrmAjaxUnitTest {

	function test_ajax_data_options() {
		$this->check_dependent_taxonomies();
	}

	function check_dependent_taxonomies() {
		$parent_field     = $this->factory->field->get_object_by_id( 'parent-dynamic-taxonomy' );
		$child_field      = $this->factory->field->get_object_by_id( 'child-dynamic-taxonomy' );

		$parent_options = FrmProFieldsHelper::get_category_options( $parent_field );
		$this->assertNotEmpty( $parent_options );

		$parent_category = array_search( 'Altered Thurzdaze', $parent_options );
		$parent_category2 = array_search( 'Estate Info', $parent_options );

		$response = $this->get_dependent_data_response( array(
			'parent_field'    => $parent_field,
			'selected_option' => array( $parent_category, $parent_category2 ),
			'child_field'     => $child_field,
		) );

		// we are expecting 2 child categories in the select options
		preg_match_all( '@<option value=\"(.*)\">(.*)</option>@', $response, $matches );
		$this->assertEquals( count( $matches[0] ), 3 );

		$grandchild_field = $this->factory->field->get_object_by_id( 'grandchild-dynamic-taxonomy' );
		$response = $this->get_dependent_data_response( array(
			'parent_field'    => $child_field,
			'selected_option' => $matches[1][1],
			'child_field'     => $grandchild_field,
		) );
		preg_match_all( '@<input type="checkbox" (.*) />@', $response, $matches );
		$this->assertEquals( count( $matches[0] ), 1 );
	}

	function get_dependent_data_response( $atts ) {
		$_POST = array(
			'action'            => 'frm_fields_ajax_data_options',
			'trigger_field_id'  => $atts['parent_field']->id,
			'entry_id'          => $atts['selected_option'], //array|int
			'field_id'          => $atts['child_field']->id,
			'container_id'      => 'frm_field_' . $atts['child_field']->id . '_container',
			'linked_field_id'   => 'taxonomy',
			'default_value'     => '',
			'prev_val'          => ''
		);

		try {
			$this->_handleAjax( 'frm_fields_ajax_data_options' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$response = $this->_last_response;
		$this->assertNotEmpty( $response );

		return $response;
	}

	/**
	 * @covers FrmProFieldsController::update_field_after_move
	 */
	function test_update_field_after_move() {
		$this->set_as_user_role( 'administrator' );

		$action = 'frm_update_field_after_move';

		$repeating_field = FrmField::getOne( 'repeating-section' );
		$old_form_id = $repeating_field->form_id;
		$new_form_id = $repeating_field->field_options['form_select'];
		$field = $this->factory->field->create_and_get( array( 'form_id' => $old_form_id ) );

		$_POST = array(
			'action'  => $action,
			'field'   => $field->id,
			'form_id' => $new_form_id,
			'section_id' => $repeating_field->id,
			'nonce'   => wp_create_nonce( 'frm_ajax' ),
		);

		try {
			$this->_handleAjax( 'frm_update_field_after_move' );
		} catch ( WPAjaxDieStopException $e ) {
			// We expected this, do nothing
		}

		$updated_field =  $this->factory->field->get_object_by_id( $field->id );
		$this->assertEquals( $new_form_id, $updated_field->form_id );
	}

	/**
	 * Test duplicating a divider field (not repeating)
	 *
	 * @covers FrmFieldsController::duplicate
	 * @covers FrmProFieldsController::duplicate_section
	 */
	function test_duplicating_divider_field() {
		wp_set_current_user( $this->user_id );
		$this->assertTrue(is_numeric($this->form_id));

		$divider_field = self::get_field_by_key( 'pro-fields-divider' );
		$children = self::get_divider_children( $divider_field );

		$_POST = array(
			'action' => 'frm_duplicate_field',
			'nonce' => wp_create_nonce('frm_ajax'),
			'field_id' => $divider_field->id,
			'form_id' => $this->form_id,
			'children' => $children,
		);

		try {
			$this->_handleAjax( 'frm_duplicate_field' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		self::check_duplicated_divider_and_children( $children );
	}

	// Get children from a divider field object
	function get_divider_children( $divider_field ) {
		$field_array = get_object_vars( $divider_field );

		return FrmProField::get_children( $field_array );
	}

	// Check a duplicated divider and its children
	function check_duplicated_divider_and_children( $original_children ) {
		global $wpdb;
		$newest_field_id = $wpdb->insert_id;

		self::check_for_end_divider( $newest_field_id );

		$divider_id = $newest_field_id - count( $original_children ) - 1;
		self::check_duplicated_divider( $divider_id );

		$num_children = count( $original_children );
		for ( $i = 0; $i<$num_children; $i++ ) {

			$get_field_id = $divider_id + $i + 1;

			self::check_duplicated_field_values( $get_field_id, $original_children[ $i ], $divider_id );
		}

	}

	// Check for an end divider (when a divider is duplicated)
	function check_for_end_divider( $newest_field_id ) {
		$last_field_added = FrmField::getOne( $newest_field_id );
		$this->assertEquals( 'end_divider', $last_field_added->type, 'When a section is duplicated, the last field added should be an end divider' );
	}

	// Check for a start divider (when a divider is duplicated)
	function check_duplicated_divider( $field_id ) {
		$divider = FrmField::getOne( $field_id );

		$this->assertEquals( 'divider', $divider->type, 'Duplicating divider not working as expected.' );

		self::check_in_section_variable( $divider, 0 );
	}

	// Check fields inside of duplicated section
	function check_duplicated_field_values( $get_field_id, $original_field_id, $new_divider_id ) {
		$new_field = FrmField::getOne( $get_field_id );
		$original_field = FrmField::getOne( $original_field_id );

		// Check field type
		$this->assertEquals( $original_field->type, $new_field->type, 'Field in section is not duplicated correctly.' );

		// Check in_section variable
		self::check_in_section_variable( $new_field, $new_divider_id );
		$this->assertTrue( $new_field->field_options['in_section'] != 0, 'in section variable set to 0 when a section is duplicated.');
	}
}
