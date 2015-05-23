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

		if ( FrmAppHelper::pro_is_installed() ) {
			$expected_date_script = <<<EXPECTED
$(document).on('focusin','#field_date14', function(){
$.datepicker.setDefaults($.datepicker.regional['']);
$(this).datepicker($.extend($.datepicker.regional[''],{dateFormat:'mm/dd/yy',changeMonth:true,changeYear:true,yearRange:'2000:2020',defaultDate:''}));
});
EXPECTED;
			$expected_is_included = strpos( $output, $expected_date_script );
			$this->assertTrue( $expected_is_included !== false, 'The date script is missing' );

			$expected_form_script = <<<SCRIPT
$(document).on('submit.formidable','.frm-show-form',frmFrontForm.submitForm);
SCRIPT;
			$expected_is_included = strpos( $output, $expected_form_script );
			$this->assertTrue( $expected_is_included !== false, 'The form script is missing' );
		}
	}
}