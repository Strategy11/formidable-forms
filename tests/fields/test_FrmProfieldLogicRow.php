<?php

/**
 * @group pro
 * @group fields
 * @group field-logic
 *
 * @since 2.03.05
 * @covers formidable/pro/views/frmpro-fields/field-values.php
 */
class test_FrmProfieldLogicRow extends FrmUnitTest {

	public function test_class_is_tested() {
		$this->assertTrue( true );
	}

	public function test_field_logic_row_no_field_selected() {
		$field_id = FrmField::get_id_by_key( 'text-field' );

		$logic_row = $this->get_logic_row( $field_id );

		$expected = '<input type="text" name="field_options[hide_opt_' . $field_id . '][]" value="" />';

		$this->assertContains( $expected, $logic_row );
	}

	public function test_field_logic_row_text_box_logic_fields_selected() {
		$field_id = FrmField::get_id_by_key( 'text-field' );

		foreach ( $this->fields_with_text_box() as $logic_field_key ) {
			$this->check_single_text_box_logic_field_selected( $field_id, $logic_field_key );
		}
	}

	private function check_single_text_box_logic_field_selected( $field_id, $logic_field_key ) {
		$logic_row = $this->get_logic_row( $field_id, $logic_field_key, 'Show me' );

		$expected = '<input type="text" name="field_options[hide_opt_' . $field_id . '][]" value="Show me" />';

		$this->assertContains( $expected, $logic_row );

	}

	public function test_field_logic_row_checkbox_field_selected() {
		$field_id = FrmField::get_id_by_key( 'text-field' );

		$logic_row = $this->get_logic_row( $field_id, 'checkbox-colors', 'Red' );

		$opening_tag = '<select name="field_options[hide_opt_' . $field_id . '][]">';
		$second_option = '<option value="Red" selected=\'selected\'>Red</option>';
		$last_option = '<option value="Purple">Purple</option>';
		$closing_tag = '</select>';

		$this->assertContains( $opening_tag, $logic_row );
		$this->assertContains( $closing_tag, $logic_row );
		$this->assertContains( $second_option, $logic_row );
		$this->assertContains( $last_option, $logic_row );
	}

	private function get_logic_row( $field_id, $logic_field_key = '', $value = '') {
		$field = FrmField::getOne( $field_id );
		$field = FrmFieldsHelper::setup_edit_vars( $field );

		$meta_name = 0;

		if ( $logic_field_key !== '' ) {
			$logic_field_id = FrmField::get_id_by_key( $logic_field_key );
			$hide_field            = $logic_field_id;
		} else {
			$hide_field            = '';
		}

		$field[ 'hide_field' ] = array( $hide_field );
		$field['hide_field_cond'] = array( '==' );
		$field['hide_opt'] = array( $value );

		$form_id = FrmForm::getIdByKey( 'all_field_types' );
		$form_fields = FrmField::get_all_for_form( $form_id );

		ob_start();
		include( FrmAppHelper::plugin_path() . '/pro/classes/views/frmpro-fields/_logic_row.php' );
		$logic_row = ob_get_contents();
		ob_end_clean();

		return $logic_row;
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
			'lookup' => 'lookup-country',
			'hidden' => 'hidden-field',
			'password' => '9r61y8',
			'tags' => 'tags-field',
		);
	}
}
