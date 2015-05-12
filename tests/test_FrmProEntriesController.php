<?php

class WP_Test_FrmProEntriesContrller extends FrmUnitTest {
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

		$form = do_shortcode( '[formidable id="contact-with-email"]' );
		$this->assertNotEmpty( $form );

        ob_start();
        do_action( 'wp_footer' );
        $output = ob_get_contents();
        ob_end_clean();

		//echo $output;
		// Check for date output	
	}
}