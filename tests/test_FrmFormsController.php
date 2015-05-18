<?php

class WP_Test_FrmFormsController extends FrmUnitTest {

	function test_trigger_load_form_hooks() {
		FrmFormsController::trigger_load_form_hooks();
		$expected_hooks = array(
			'frm_field_type' => 'FrmFieldsController::change_type',
			'frm_field_input_html' => 'FrmFieldsController::input_html',
			'frm_field_value_saved' => 'FrmFieldsController::check_value',
		);

		foreach ( $expected_hooks as $tag => $function ) {
			$has_filter = has_filter( $tag, $function );
			$this->assertTrue( $has_filter !== false, 'The ' . $tag .' hook is not loaded' );
		}
	}

	function test_register_widgets() {
		global $wp_widget_factory;
		$this->assertTrue( isset( $wp_widget_factory->widgets['FrmShowForm'] ) );
	}

	function test_head() {
		$this->set_front_end();
		$edit_in_place = wp_script_is( 'formidable-editinplace', 'enqueued' );
		$this->assertFalse( $edit_in_place, 'The edit-in-place script should not be enqueued' );

		/*
		$this->set_admin_screen( 'formidable-edit' );

		$edit_in_place = wp_script_is( 'formidable-editinplace', 'enqueued' );
		$this->assertTrue( $edit_in_place, 'The edit-in-place script was not enqueued' );

		if ( wp_is_mobile() ) {
			$touchpunch = wp_script_is( 'jquery-touch-punch', 'enqueued' );
			$this->assertTrue( $touchpunch, 'The touch punch script was not enqueued' );
		}
		*/
	}

	function test_get_form_shortcode() {
		$form = FrmFormsController::get_form_shortcode( array( 'id' => $this->contact_form_key ) );
		$this->assertNotEmpty( strpos( $form, '<form ' ), 'The form is missing' );
	}
}