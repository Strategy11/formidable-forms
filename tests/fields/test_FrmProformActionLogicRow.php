<?php

/**
 * @group pro
 * @group pro-views
 */
class test_FrmProformActionLogicRow extends FrmUnitTest {

	public function test_class_is_tested() {
		$this->assertTrue( true );
	}

	/**
	 * Test an action logic row with no field selected
	 *
	 * @since 2.03.05
	 *
	 * @covers formidable/pro/views/frmpro-forms/_logic_row.php
	 */
	public function test_action_logic_row_no_field_selected() {
		$logic_row = $this->get_logic_row();

		$name = 'frm_form_action[1234][post_content][conditions][0][hide_opt]';
		$expected_value_selector = '<input type="text" name="' . $name . '" value="" /></span>';

		$this->assertContains( $expected_value_selector, $logic_row );
	}

	/**
	 * Test an action logic row with a text box field selected
	 *
	 * @since 2.03.05
	 *
	 * @covers formidable/pro/views/frmpro-forms/_logic_row.php
	 */
	public function test_action_logic_row_text_box_fields_selected() {
		foreach ( self::fields_with_text_box() as $field_key ) {
			self::check_action_logic_row_text_box_field_selected( $field_key );
		}
	}

	/**
	 * Checks the HTML for the field value part of a field's conditional dropdown
	 * Test single field text boxes
	 *
	 * @covers formidable/pro/views/frmpro-fields/field-values.php
	 */
	private function check_action_logic_row_text_box_field_selected( $field_key ) {
		$logic_row = $this->get_logic_row( $field_key, 'Show me' );

		$name = 'frm_form_action[1234][post_content][conditions][0][hide_opt]';
		$expected_value_selector = '<input type="text" name="' . $name . '" value="Show me" /></span>';

		$this->assertContains( $expected_value_selector, $logic_row );
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

	/**
	 * Test an action logic row with checkbox field selected
	 *
	 * @since 2.03.05
	 *
	 * @covers formidable/pro/views/frmpro-forms/_logic_row.php
	 */
	public function test_action_logic_row_checkbox_field_selected() {
		$logic_row = $this->get_logic_row( 'checkbox-colors', '' );

		$name = 'frm_form_action[1234][post_content][conditions][0][hide_opt]';
		$opening_tag = '<select name="' . $name . '">';
		$second_option = '<option value="Red">Red</option>';
		$last_option = '<option value="Purple">Purple</option>';
		$closing_tag = '</select>';

		$this->assertContains( $opening_tag, $logic_row );
		$this->assertContains( $closing_tag, $logic_row );
		$this->assertContains( $second_option, $logic_row );
		$this->assertContains( $last_option, $logic_row );
	}

	/**
	 * Test an action logic row with checkbox field and value selected
	 *
	 * @since 2.03.05
	 *
	 * @covers formidable/pro/views/frmpro-forms/_logic_row.php
	 */
	public function test_action_logic_row_checkbox_field_value_selected() {
		$logic_row = $this->get_logic_row( 'checkbox-colors', 'Red' );

		$name = 'frm_form_action[1234][post_content][conditions][0][hide_opt]';

		$opening_tag = '<select name="' . $name . '">';
		$second_option = '<option value="Red" selected=\'selected\'>Red</option>';
		$last_option = '<option value="Purple">Purple</option>';
		$closing_tag = '</select>';

		$this->assertContains( $opening_tag, $logic_row );
		$this->assertContains( $closing_tag, $logic_row );
		$this->assertContains( $second_option, $logic_row );
		$this->assertContains( $last_option, $logic_row );
	}

	private function get_logic_row( $field_key = '', $value = '') {
		$key             = '1234';
		$meta_name       = 0;
		$id              = 'frm_logic_' . $key . '_' . $meta_name;
		$form_id         = FrmForm::getIdByKey( 'all_field_types' );
		$name            = 'frm_form_action[' . $key . '][post_content][conditions][' . $meta_name . ']';
		$names           = array(
			'hide_field'      => $name . '[hide_field]',
			'hide_field_cond' => $name . '[hide_field_cond]',
			'hide_opt'        => $name . '[hide_opt]',
		);
		$field[ 'type' ] =
		$onchange = "frmGetFieldValues(this.value,'" . $key . "','" . $meta_name . "','','" . $names[ 'hide_opt' ] . "')";
		$form_fields     = FrmField::get_all_for_form( $form_id );
		$exclude_fields  = array_merge( FrmField::no_save_fields(), array( 'file', 'rte', 'date' ) );
		$showlast        = '';
		$type            = 'form';

		if ( $field_key === '' ) {
			$condition = array( 'hide_field_cond' => '==', 'hide_field' => '' );
		} else {
			$condition = array(
				'hide_field_cond' => '==',
				'hide_field' => FrmField::get_id_by_key( $field_key ),
				'hide_opt' => $value,
			);
		}


		ob_start();
		include( FrmAppHelper::plugin_path() . '/pro/classes/views/frmpro-forms/_logic_row.php' );
		$logic_row = ob_get_contents();
		ob_end_clean();

		return $logic_row;
	}
}
