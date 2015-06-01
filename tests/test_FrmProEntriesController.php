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

        ob_start();
        do_action( 'wp_footer' );
        $output = ob_get_contents();
        ob_end_clean();

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
			//self::test_single_form_formresults( $i, $form_key );
		}
	}

	public function _test_single_form_formresults( $array_key, $form_key ) {
		// Get form id by key
		$form_id = $this->factory->form->get_id_by_key( $form_key );

		// Check number of entries
		$actual_entry_count = 3;

		// Get number of entries
		$entry_count = FrmEntry::getRecordCount( $form_id );
		$this->assertTrue( $entry_count == $actual_entry_count, 'Entries are not being retrieved correctly. Retrieved ' . $entry_count . ' but should have ' . $actual_entry_count . ' for form ' . $this->all_fields_form_key );

		// Get number of fields
		$actual_field_count = 36;//Includes 3 fields from repeating section form
		// Probably exclude specific field types (embed form, divider, end divider, html, ...);
		$field_count = count( FrmField::get_all_for_form( $form_id ) );
		$this->assertTrue( $field_count == $actual_field_count, 'Fields are not being retrieved correctly. Retrieved ' . $field_count . ', but should have ' . $actual_field_count );

		// Regular form (no post or custom fields)
		// This will always test parameters in the same order. What about different combinations of parameters?
		$params_to_test = array( '', ' google=1', ' fields=x');
		$params = '';
		foreach ( $params_to_test as $param ) {
			$params .= $param;
			self::check_formresults_values( $form_id, $entry_count, $field_count, $params );
		}

		// Check for the correct number of rows and columns
		// Check w/ and w/o posts, custom fields, Dynamic fields, etc.
	}

	function _check_formresults_values( $form_id, $expected_row_num, $expected_col_num, $params='' ) {
		$param_text = $params ? $params : ' no';
		$formresults = do_shortcode('[formresults id="' . $form_id . '" ' . $params . ']');

		// Correct number of rows
		$actual_row_num = substr_count( $formresults, '<tr');
		$this->assertEquals( $actual_row_num, $expected_row_num, 'Formresults (with' . $params . ' parameters) is not showing the correct number of rows/entries' );

		// Correct number of columns
		$actual_col_num = substr_count( $formresults, '<th')/2;
		$this->assertEquals( $actual_col_num, $expected_col_num, 'Formresults (with' . $params . ' parameters) is not showing the correct number of columns' );

		// Check if edit link is showing up
		// Check pagination
	}
}