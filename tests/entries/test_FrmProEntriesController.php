<?php

/**
 * @group entries
 * @group pro
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

		$expected_date_script = 'var frmDates=';

		$expected_is_included = strpos( $output, $expected_date_script );
		$this->assertTrue( $expected_is_included !== false, 'The date script is missing in output: ' . $output );

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$this->markTestSkipped( 'Run with --group entries' );
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$expected_form_script = "/wp-content/plugins/formidable/js/formidable{$suffix}.js";
		$expected_is_included = strpos( $output, $expected_form_script );
		$this->assertTrue( $expected_is_included !== false, 'The form script is missing in output: ' . $output );
	}

	/**
	* Check if formresults shortcode is working for normal and post forms
	*/
	public function test_formresults() {
		// Set current post (needed for edit link)
		$this->go_to_new_post();
		$this->set_current_user_to_1();

		$forms_to_test = array( 'regular_form' => $this->all_fields_form_key, 'post_form' => $this->create_post_form_key );
		foreach ( $forms_to_test as $form_key ) {
			self::_test_single_form_formresults( $form_key );
		}
	}

	/**
	 * @covers FrmProEntriesController::get_form_results
	 */
	public function _test_single_form_formresults( $form_key ) {
		$args['form_key'] = $form_key;
		$args['form_id'] = $this->factory->form->get_id_by_key( $form_key );

		$args['field_count'] = $args['entry_count'] = 0;
		self::_set_and_check_expectations( $args );

		if ( $form_key == $this->all_fields_form_key ) {
			$fields = 'text-field,paragraph-field,checkbox-colors,radio-button-field,embed-form-field,repeating-text';
		} else {
			$fields = 'yi6yvm';
		}

		// No google=1
		$args['params_to_test'] = array(
			'none' => '',
			'fields' => $fields,
			'drafts' => 'both',
			'edit_link' => 'Click to edit',
			'delete_link' => 'Click to delete',
			'cols' => 3,
			'user_id' => 'current'
		);
		// TODO: Add testing for sort, style, no_entries, clickable, user_id
		self::_test_params( $args );

		// With google=1
		$args['params_to_test'] = array(
			'google' => 1,
			'fields' => $fields,
			'drafts' => 'both',
			'edit_link' => 'Click to edit',
			'delete_link' => 'Click to delete',
			'pagesize' => 2,
			'cols' => 3,
			'user_id' => 'current'
		);
		self::_test_params( $args );
	}

	function _set_and_check_expectations( &$args ) {
		if ( $args['form_key'] == $this->all_fields_form_key ) {
			$expected_entry_count = 4;
		} else {
			$expected_entry_count = 0;
		}
		$args['entry_count'] = FrmEntry::getRecordCount( $args['form_id'] );
		$this->assertTrue( $args['entry_count'] == $expected_entry_count, 'Entries are not being retrieved correctly. Retrieved ' . $args['entry_count'] . ' but should have ' . $expected_entry_count . ' for form ' . $args['form_key'] );

		// Get number of fields in form (exlcluding no_save_fields)
		$args['field_count'] = self::_get_field_count( $args['form_key'] );
	}

	function _get_field_count( $form_key ) {
		// Get all fields in form
		$all_fields = $this->get_all_fields_for_form_key( $form_key );
		$field_count = count( $all_fields );

		// Remove excluded field types from field count
		$exclude_fields = FrmField::no_save_fields();
		foreach ( $all_fields as $f ) {
			if ( in_array( $f->type, $exclude_fields ) ) {
				$field_count--;
			}
		}
		return $field_count;
	}


	function _test_params( $args ) {
		if ( $args['form_key'] == $this->all_fields_form_key ) {
			$embed_form_field_count = self::_get_field_count( $this->contact_form_key  );
		}

		$args['current_params'] = '';
		foreach ( $args['params_to_test'] as $pname => $pval ) {
			if ( $pname != 'none' ) {
				$args['current_params'] .= ' ' . $pname . '="' . $pval . '"';
			}

			// Set the expected number of columns
			if ( $pname == 'fields' ) {
				$args['field_count'] = count( explode( ',', $pval ) );
				// embed-form-field is an embed form field
				if ( strpos( $pval, 'embed-form-field' ) !== false ) {
					$args['field_count']+= $embed_form_field_count - 1;
				}
			} else if ( $pname == 'cols' ) {
				$args['field_count'] = $pval;
			} else if ( $pname == 'edit_link' || $pname == 'delete_link' ) {
				$args['field_count']++;
			}

			// Set the expected number of rows
			if ( $pname == 'pagesize' ) {
				$args['entry_count'] = $pval;
			}

			self::_check_formresults_values( $args );
			unset( $pname, $pval);
		}
		// Check w/ and w/o posts, custom fields, Dynamic fields, etc.
	}

	function _check_formresults_values( $args ) {
		$param_msg_text = $args['current_params'] ? $args['current_params'] : ' no';
		$formresults = do_shortcode('[formresults id="' . $args['form_id'] . '"' . $args['current_params'] . ']');

		self::_check_row_num( $formresults, $args['entry_count'], $param_msg_text, $args );
		self::_check_col_num( $formresults, $args['field_count'], $param_msg_text, $args );
		self::_check_for_google_table( $formresults, $args, $param_msg_text, $args );
		self::_check_for_edit_link( $formresults, $param_msg_text, $args );
		self::_check_for_delete_link( $formresults, $args['entry_count'], $args['current_params'], $param_msg_text );
	}

	function _check_row_num( $formresults, $expected_row_num, $param_msg_txt, $args ) {
		$actual_row_num = substr_count( $formresults, '<tr') - 2;

		$this->assertNotEquals( -2, $actual_row_num, 'Formresults (with' . $param_msg_txt . ' parameters) is not showing up at all for form ' . $args['form_key'] . '.');
		$this->assertEquals( $expected_row_num, $actual_row_num, 'Formresults (with' . $param_msg_txt . ' parameters) is showing ' . $actual_row_num . ' rows, but ' . $expected_row_num . ' are expected for form ' . $args['form_key'] . '.' );
	}

	function _check_col_num( $formresults, $expected_col_num, $param_text, $args ) {
		$actual_col_num = substr_count( $formresults, '<th>')/2;
		$this->assertEquals( $expected_col_num, $actual_col_num, 'Formresults (with' . $param_text . ' parameters) is showing ' . $actual_col_num . ', but ' . $expected_col_num . ' are expected for field ' . $args['form_key'] . '.' );
	}

	function _check_for_google_table( $formresults, $params, $param_text, $args ) {
		if ( strpos( $args['current_params'], 'google=' ) !== false ) {
			$this->assertTrue( strpos( $formresults, 'frm_google_table_' . $args['form_id'] ), 'Formresults (with' . $param_text . ' parameters) is not showing google table when it should be' );
		}
	}

	function _check_for_edit_link( $formresults, $param_text, $args ) {
		if ( strpos( $args['current_params'], 'edit_link' ) !== false ) {
			// TODO: Check if edit link shows up for different users
			$params_array = explode( '"', $args['current_params'] );
			$edit_link_key = array_search( ' edit_link=', $params_array );
			$edit_link_text = $params_array[ $edit_link_key + 1 ];
			$edit_link_count = substr_count( $formresults, $edit_link_text ) - 2;

			$this->markTestIncomplete( 'check_for_edit_link test is not working yet.' );
			$this->assertEquals( $edit_link_count, $args['entry_count'], 'Formresults (with' . $param_text . ' parameters) is showing ' . $edit_link_count . ' edit links, but ' . $args['entry_count'] . ' are expected for form ' . $args['form_key'] );
		}
	}

	function _check_for_delete_link( $formresults, $expected_row_num, $params, $param_text ) {
		if ( strpos( $params, 'delete_link' ) !== false ) {
			$params_array = explode( '"', $params );
			$del_link_key = array_search( ' delete_link=', $params_array );
			$del_link_text = $params_array[ $del_link_key + 1 ];

			// TODO: Check if delete link shows up for different users
			$delete_link_count = substr_count( $formresults, $del_link_text ) - 2;

			$this->markTestIncomplete( 'check_for_delete_link test is not working yet.' );
			$this->assertEquals( $delete_link_count, $expected_row_num, 'Formresults (with' . $param_text . ' parameters) is not showing the correct number of delete links' );
		}
	}

	/**
	* @covers FrmProEntriesController::get_field_value_shortcode
	* TODO: Test with post fields, IP, and user_id parameters
	*/
	function test_get_field_value_shortcode(){
		$tests = array( 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13 );
		$field_id = FrmField::get_id_by_key( 'text-field' );
		$entry_id = FrmEntry::get_id_by_key( 'jamie_entry_key' );

		foreach ( $tests as $test ) {
			$sc_atts = self::_setup_frm_field_value_sc_atts( $test, $field_id, $entry_id );
			$sc_atts_list = self::_get_sc_atts_list( $sc_atts );
			$expected_result = self::_get_expected_frm_field_value( $test );

			$value = FrmProEntriesController::get_field_value_shortcode( $sc_atts );

			$this->assertEquals( $expected_result, $value, 'The frm-field-value shortcode is not retrieving the correct value with the following parameters: ' . $sc_atts_list );
		}
	}

	/**
	 * Test with a time field, no format set
	 * @covers FrmProEntriesController::get_field_value_shortcode
	 */
	function test_get_field_value_shortcode_for_time_fields_no_format(){
		$atts = array( 'field_id' => 'time-field' );

		$value = FrmProEntriesController::get_field_value_shortcode( $atts );
		$expected_value = '8:00 AM';
		$this->assertEquals( $expected_value, $value, 'The frm-field-value shortcode is not retrieving the correct value with the following parameters: ' . implode( ',', $atts ) );
	}

	/**
	 * Tests a time field with format:"gA"
	 * @covers FrmProEntriesController::get_field_value_shortcode
	 */
	function test_get_field_value_shortcode_for_time_fields_with_format(){
		$atts = array(
			'field_id' => 'time-field',
			'format' => 'gA',
		);

		$value = FrmProEntriesController::get_field_value_shortcode( $atts );
		$expected_value = '8AM';
		$this->assertEquals( $expected_value, $value, 'The frm-field-value shortcode is not retrieving the correct value with the following parameters: ' . implode( ',', $atts ) );
	}

	/**
	* Set up various atts for the frm-field-value function
	* Test 0: no parameters
	* Test 1: field_id=ID entry=ID
	* Test 2: field_id=ID entry_id=ID
	* Test 3: field_id=ID entry=Key
	* Test 4: field_id=ID entry_id=Key
	* Test 5: field_id=ID entry=entry_param with ID in URL
	* Test 6: field_id=ID entry_id=entry_param with ID in URL
	* Test 7: field_id=ID entry=entry_param with key in URL
	* Test 8: field_id=ID entry_id=entry_param with key in URL
	* Test 9: same as test 1 but with field key
	* Test 10: same as test 2 but with field key
	* Test 11: same as test 3 but with field key
	* Test 12: same as test 4 but with field key
	* Test 13: field_id=ID entry=entry_param with no param in URL (tests default param)
	*/
	function _setup_frm_field_value_sc_atts( $test, $field_id, $entry_id ){
		$entry_key = 'jamie_entry_key';

		// Make sure previous URL params are cleared
		if ( isset( $_GET['my_param'] ) ) {
			unset( $_GET['my_param'] );
		}

		// Use field key for tests 9 - 12
		if ( $test > 8 && $test < 13 ) {
			$field_id = 'text-field';
		}
		$sc_atts = array(
			'field_id' => $field_id
		);

		if ( in_array( $test, array( 1, 2, 5, 6, 9, 10 ) ) ) {
			// Tests 1, 2, 5, and 6 use the entry ID
			$entry = $entry_id;
		} else if ( in_array( $test, array( 3, 4, 7, 8, 11, 12 ) ) ) {
			// Tests 3, 4, 7, and 8 use the entry key
			$entry = $entry_key;
		}

		// Test 5-8 pull the entry param from the URL
		if ( in_array( $test, array( 5, 6, 7, 8 ) ) ) {
			$_GET = array( 'my_param' => $entry );
			$entry = 'my_param';
		}

		if ( $test === 0 ) {
			// Test with no atts
			$sc_atts = array();

		} else if ( in_array( $test, array( 2, 4, 6, 8, 10, 12 ) ) ) {
			// Test with entry_id for reverse compatibility
			$sc_atts['entry_id'] = $entry;

		} else if ( in_array( $test, array( 1, 3, 5, 7, 9, 11 ) ) ) {
			// Test with entry parameter
			$sc_atts['entry'] = $entry;
		} else if ( $test == 13 ) {
			$sc_atts['entry'] = 'my_param';
			$sc_atts['default'] = 'Name';
		}

		return $sc_atts;
	}

	function _get_expected_frm_field_value( $test ){
		if ( $test === 0 ) {
			$e_result = 'You are missing options in your shortcode. field_id is required.';
		} else if ( $test > 0 && $test < 13 ) {
			$e_result = 'Jamie';
		} else if ( $test == 13 ) {
			$e_result = 'Name';
		}

		return $e_result;
	}

	function _get_sc_atts_list( $sc_atts ) {
		$sc_atts_list = '';
		foreach ( $sc_atts as $key => $val ) {
			$sc_atts_list .= $key . ':' . $val . ', ';
		}
		$sc_atts_list = rtrim( $sc_atts_list );
		return $sc_atts_list;
	}

	/**
	* @covers FrmProEntriesController::get_frm_field_value_entry
	* TODO: Test with post fields, IP, and user_id parameters
	*/
	function test_get_frm_field_value_entry(){
		$tests = array( 1, 2, 3, 4, 5, 6, 7, 8, 9 );
		$field = FrmField::getOne( 'text-field' );
		$entry_id = $this->factory->entry->get_id_by_key( 'jamie_entry_key' );

		foreach ( $tests as $test ) {
			$msg = '';
			$atts = self::_setup_frm_field_value_entry_atts( $test, $entry_id, $msg );
			$expected_result = self::_get_expected_entry_result( $test, $entry_id );
			$entry = self::_do_get_frm_field_value_entry( $field, $atts );

			$this->assertEquals( $expected_result, $entry, 'The get_frm_field_value_entry function is not retrieving the correct entry with the following parameters: ' . $msg );
		}
	}

	/**
	* Set up entry attribute for get_frm_field_value_entry
	*
	* Test 1: entry=ID
	* Test 2: entry=Key
	* Test 3: entry=entry_param with ID in URL
	* Test 4: entry=entry_param with key in URL
	* Test 5: entry=entry_param with empty param in URL
	* Test 6: entry=entry_param with nothing in URL
	* Test 7: entry=incorrect ID
	* Test 8: entry=incorrect key
	* Test 9: entry=entry_param with incorrect key in URL
	*/
	function _setup_frm_field_value_entry_atts( $test, $entry_id, &$msg ){
		$atts = array(
			'user_id' => false,
			'ip'	=> false
		);
		$entry_key = 'jamie_entry_key';

		// Make sure previous URL params are cleared
		if ( isset( $_GET['my_param'] ) ) {
			unset( $_GET['my_param'] );
		}

		if ( $test == 1 ) {
			$atts['entry'] = $entry_id;
			$msg = 'entry=ID';

		} else if ( $test == 2 ) {
			$atts['entry'] = $entry_key;
			$msg = 'entry=key';

		} else if ( $test == 3 ) {
			$atts['entry'] = 'my_param';
			$_GET['my_param'] = $entry_id;
			$msg = 'entry=entry_param with ID in URL';

		} else if ( $test == 4 ) {
			$atts['entry'] = 'my_param';
			$_GET['my_param'] = $entry_key;
			$msg = 'entry=entry_param with key in URL';

		} else if ( $test == 5 ) {
			$atts['entry'] = 'my_param';
			$_GET['my_param'] = '';
			$msg = 'entry=entry_param with empty param in URL';

		} else if ( $test == 6 ) {
			$atts['entry'] = 'my_param';
			$msg = 'entry=entry_param with no param in URL';

		} else if ( $test == 7 ) {
			$atts['entry'] = 99999999;
			$msg = 'entry=incorrect ID';

		} else if ( $test == 8 ) {
			$atts['entry'] = 'djf98j98jfo';
			$msg = 'entry=incorrect key';

		} else if ( $test == 9 ) {
			$atts['entry'] = 'my_param';
			$_GET['my_param'] = 'djf98j98jfo';
			$msg = 'entry=entry_param with incorrect key in URL';
		}

		return $atts;
	}

	function _get_expected_entry_result( $test, $entry_id ) {
		if ( $test > 0 && $test < 5 ) {
			$e_result = FrmDb::get_row( 'frm_items', array( 'id' => $entry_id ), 'post_id, id', array( 'order_by' => 'created_at DESC' ) );
		} else if ( $test >= 5 ) {
			$e_result = false;
		}
		return $e_result;
	}

	function _do_get_frm_field_value_entry( $field, $atts ){
		$class = new ReflectionClass('FrmProEntriesController');
		$method = $class->getMethod('get_frm_field_value_entry');
		$method->setAccessible(true);
		$entry = $method->invokeArgs( null, array( $field, &$atts ) );
		return $entry;
	}
}
