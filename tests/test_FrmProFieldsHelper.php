<?php

/**
 * @group ajax
 */
class WP_Test_FrmProFieldsHelper extends FrmAjaxUnitTest {

	/**
	* @covers FrmProFieldsHelper::update_for_repeat
	* @covers FrmProFieldsHelper::move_fields_to_form
	* @covers FrmProFieldsHelper::move_entries_to_child_form
	* @covers FrmProFieldsHelper::move_entries_to_parent_form
	*/
	function test_toggle_repeat(){
		/*2. Switch to regular
			- move child fields to parent form √
			- move child entries to parent form √
			- child entries deleted √
			- child form deleted √
			- form_select and repeat updated √

		3. Switch to repeating
			- child form created w/correct parent_form_id
			- move child fields to child form
			- child entries created from parent data
			- form_select updated and repeat updated*/

		$form_id = $this->factory->form->get_id_by_key( 'all_field_types' );
		$section_fields = $fields = FrmField::get_all_types_in_form( $form_id, 'divider' );

		//$section_fields = $this->get_all_field_types_for_form_key( $this->all_fields_form_key, 2, 'divider' );
		foreach ( $section_fields as $section ) {
			if ( FrmField::is_repeating_field( $section ) ) {
				$repeating_section = $section;
				break;
			}
		}

		// Start with repeating section, switch to non-repeating
		$args = self::_set_post_values( $repeating_section );
		try {
			$this->_handleAjax( 'frm_toggle_repeat' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		self::_check_if_child_form_deleted( $args['form_id'] );
		self::_check_if_fields_moved( $args['children'], $args['parent_form_id'], $to_repeat );

		// Switch back to repeating now
	}

	function _set_post_values( $repeating_section ) {
		$args = array(
			'action'    => 'frm_toggle_repeat',
            'nonce'     => wp_create_nonce('frm_ajax'),
			'form_id' => $repeating_section->field_options['form_select'],
			'parent_form_id' => $repeating_section->form_id,
			'checked' => 0,
			'field_id' => $repeating_section->id
		);

		//$children = $this->get_all_fields_for_form_key( $this->repeat_sec_form_key );
		$form_id = $this->factory->form->get_id_by_key( 'rep_sec_form' );
		$children = FrmField::get_all_for_form( $form_id, '', 'include' );

		$args['children'] = array();
		foreach ( $children as $child ) {
			$args['children'][] = $child->id;
		}

		$_POST = $args;
		return $args;
	}

	function _check_if_child_form_deleted( $id ) {
		$child_form = FrmForm::getOne( $id );
		$this->assertEmpty( $child_form, 'Child form was not deleted when switching from repeating to non-repeating section');
	}

	function _check_if_fields_moved( $children, $form_id, $to_repeat ){
		if ( $to_repeat ) {
			$msg = 'repeatable';
		} else {
			$msg = 'non-repeatable';
		}

		//$child_fields = $this->get_all_fields_for_form_key( $this->repeat_sec_form_key );
		$form_id = $this->factory->form->get_id_by_key( 'rep_sec_form' );
		$child_fields = FrmField::get_all_for_form( $form_id, '', 'include' );

		foreach ( $child_fields as $child ) {
			$this->assertTrue( $child->form_id == $form_id, 'Child fields were not moved to the correct form when switching divider to ' . $msg . ' field.');
		}
	}
}