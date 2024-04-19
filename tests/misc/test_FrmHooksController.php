<?php

/**
 * @group hooks
 */
class test_FrmHooksController extends FrmUnitTest {

	public function test_trigger_load_form_hooks() {
		FrmHooksController::trigger_load_form_hooks();
		$expected_hooks = array(
			'frm_field_input_html'  => 'FrmFieldsController::input_html',
			'frm_field_type'        => 'FrmFieldsController::change_type',
			'frm_field_value_saved' => 'FrmFieldsController::check_value',
		);

		foreach ( $expected_hooks as $tag => $function ) {
			$has_filter = has_filter( $tag, $function );
			$this->assertTrue( $has_filter !== false, 'The ' . $tag . ' hook is not loaded' );
		}
	}
}
