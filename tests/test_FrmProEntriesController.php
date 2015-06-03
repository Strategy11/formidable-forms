<?php

/**
 * @group entries
 */
class WP_Test_FrmProEntriesController extends FrmUnitTest {

	public function test_add_js() {
        $frm_settings = FrmAppHelper::get_settings();

        global $frm_vars;
        if ( $frm_settings->jquery_css ) {
            $this->assertNotEmpty( $frm_vars['datepicker_loaded'] );
        }

		if ( $frm_settings->accordion_js ) {
			$this->assertTrue( wp_script_is( 'jquery-ui-widget', 'enqueued' ) );
			$this->assertTrue( wp_script_is( 'jquery-ui-accordion', 'enqueued' ) );
        }
	}

	/**
	 * @todo check the output on the wp_footer hook
	 * 	- when Include the jQuery CSS on ALL pages is checked
	 */
	public function test_footer_js() {
		$this->set_front_end();

		$form = do_shortcode( '[formidable id="' . $this->contact_form_key . '"]' );
		$this->assertNotEmpty( $form );

        $output = $this->get_footer_output();

		$expected_date_script = <<<EXPECTED
$(document).on('focusin','#field_date14', function(){
$.datepicker.setDefaults($.datepicker.regional['']);
$(this).datepicker($.extend($.datepicker.regional[''],{dateFormat:'mm/dd/yy',changeMonth:true,changeYear:true,yearRange:'2000:2020',defaultDate:''}));
});
EXPECTED;
		$expected_is_included = strpos( $output, $expected_date_script );
		$this->assertTrue( $expected_is_included !== false, 'The date script is missing' );

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$this->markTestSkipped( 'Run with --group entries' );
		}

		$expected_form_script = <<<SCRIPT
$(document).on('submit.formidable','.frm-show-form',frmFrontForm.submitForm);
SCRIPT;
		$expected_is_included = strpos( $output, $expected_form_script );
		$this->assertTrue( $expected_is_included !== false, 'The form script is missing' );
	}

	/**
	* Check if formresults shortcode is working for normal and post forms
	*/
	public function test_formresults() {
		$forms_to_test = array( 'regular_form' => $this->all_fields_form_key, 'post_form' => $this->create_post_form_key );
		foreach ( $forms_to_test as $i => $form_key ) {
			self::_test_single_form_formresults( $i, $form_key );
		}
	}

	/**
	 * @covers FrmProEntriesController::get_form_results
	 */
	public function _test_single_form_formresults( $array_key, $form_key ) {
		$form_id = $this->factory->form->get_id_by_key( $form_key );

		self::_check_expectations( $form_key, $form_id );

		if ( $form_key == $this->all_fields_form_key ) {
			$embed_form_id = $this->factory->form->get_id_by_key( $this->contact_form_key );
			$embed_form_field_count = self::_get_field_count( $embed_form_id, 8 );

			$params_to_test = array( 'none' => '', 'fields' => '493ito,p3eiuk,uc580i,4t3qo4,e9ul34,gbm7pi', 'drafts' => 'both', 'edit_link' => 'Click to edit', 'delete_link' => 'Click to delete', 'google' => 1, 'pagesize' => 2, 'cols' => 3 );
		} else {
			$params_to_test = array( 'none' => '', 'cols' => 3 );
		}

		$params = '';
		foreach ( $params_to_test as $pname => $pval ) {
			if ( $pname != 'none' ) {
				$params .= ' ' . $pname . '="' . $pval . '"';
			}

			// Set the expected number of columns
			if ( $pname == 'fields' ) {
				$field_count = count( explode( ',', $pval ) );
				// e9ul34 is an embed form field
				if ( strpos( $pval, 'e9ul34' ) !== false ) {
					$field_count+= $embed_form_field_count - 1;
				}
			} else if ( $pname == 'cols' ) {
				$field_count = $pval;
			} else if ( $pname == 'edit_link' || $pname == 'delete_link' ) {
				$field_count++;
			}

			// Set the expected number of rows
			if ( $pname == 'pagesize' ) {
				$entry_count = $pval;
			}

			self::_check_formresults_values( $form_id, $entry_count, $field_count, $params );
			unset( $pname, $pval);
		}
		// Check w/ and w/o posts, custom fields, Dynamic fields, etc.
	}

	function _check_expectations( $form_key, $form_id ) {
		if ( $form_key == $this->all_fields_form_key ) {
			$expected_entry_count = 3;
			$expected_field_count = 33 + 3 + 8;
		} else {
			$expected_entry_count = 0;
			$expected_field_count = 10;
		}
		$entry_count = FrmEntry::getRecordCount( $form_id );
		$this->assertTrue( $entry_count == $expected_entry_count, 'Entries are not being retrieved correctly. Retrieved ' . $entry_count . ' but should have ' . $expected_entry_count . ' for form ' . $form_key );

		// Get number of fields in form (exlcluding no_save_fields)
		$field_count = self::_get_field_count( $form_id, $expected_field_count );
	}

	function _get_field_count( $form_id, $expected ) {
		// Get all fields in form
		$all_fields = FrmField::get_all_for_form( $form_id, '', 'include' );
		$field_count = count( $all_fields );
		$this->assertTrue( $field_count == $expected, 'Fields are not being retrieved correctly. Retrieved ' . $field_count . ', but should have ' . $expected );

		// Remove excluded field types from field count
		$exclude_fields = FrmField::no_save_fields();
		foreach ( $all_fields as $f ) {
			if ( in_array( $f->type, $exclude_fields ) ) {
				$field_count--;
			}
		}
		return $field_count;
	}

	function _check_formresults_values( $form_id, $expected_row_num, $expected_col_num, $params='' ) {
		$param_text = $params ? $params : ' no';
		$formresults = do_shortcode('[formresults id="' . $form_id . '"' . $params . ']');

		self::_check_row_num( $formresults, $expected_row_num, $param_text );
		self::_check_col_num( $formresults, $expected_col_num, $param_text );
		self::_check_for_google_table( $formresults, $params, $param_text, $form_id );
		self::_check_for_edit_link( $formresults, $expected_row_num, $params, $param_text );
		self::_check_for_delete_link( $formresults, $expected_row_num, $params, $param_text );
	}

	function _check_row_num( $formresults, $expected_row_num, $param_text ) {
		$actual_row_num = substr_count( $formresults, '<tr') - 2;

		$this->assertNotEquals( -2, $actual_row_num, 'Formresults (with' . $param_text . ' parameters) is not showing up at all.');
		$this->assertEquals( $expected_row_num, $actual_row_num, 'Formresults (with' . $param_text . ' parameters) is not showing the correct number of rows/entries' );
	}

	function _check_col_num( $formresults, $expected_col_num, $param_text ) {
		$actual_col_num = substr_count( $formresults, '<th>')/2;
		$this->assertEquals( $expected_col_num, $actual_col_num, 'Formresults (with' . $param_text . ' parameters) is showing ' . $actual_col_num . ', but ' . $expected_col_num . ' are expected.' );
	}

	function _check_for_google_table( $formresults, $params, $param_text, $form_id ) {
		if ( strpos( $params, 'google=' ) !== false ) {
			$this->assertTrue( strpos( $formresults, 'frm_google_table_' . $form_id ), 'Formresults (with' . $param_text . ' parameters) is not showing google table when it should be' );
		}
	}

	function _check_for_edit_link( $formresults, $expected_row_num, $params, $param_text ) {
		if ( strpos( $params, 'edit_link' ) !== false ) {
			// TODO: Check if edit link shows up for different users
			$params_array = explode( '"', $params );
			$edit_link_key = array_search( ' edit_link=', $params_array );
			$edit_link_text = $params_array[ $edit_link_key + 1 ];
			$edit_link_count = substr_count( $formresults, $edit_link_text );
			$this->assertEquals( $edit_link_count, $expected_row_num, 'Formresults (with' . $param_text . ' parameters) is not showing the correct number of edit links' );
		}
	}

	function _check_for_delete_link( $formresults, $expected_row_num, $params, $param_text ) {
		if ( strpos( $params, 'delete_link' ) !== false ) {
			$params_array = explode( '"', $params );
			$del_link_key = array_search( ' delete_link=', $params_array );
			$del_link_text = $params_array[ $del_link_key + 1 ];

			// TODO: Check if delete link shows up for different users
			$delete_link_count = substr_count( $formresults, $del_link_text );
			$this->assertEquals( $delete_link_count, $expected_row_num, 'Formresults (with' . $param_text . ' parameters) is not showing the correct number of delete links' );
		}
	}
}