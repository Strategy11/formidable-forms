<?php

class test_FrmSimpleBlocksController extends FrmUnitTest {

	/**
	 * @covers FrmSimpleBlocksController::maybe_remove_fade_on_load_for_block_preview
	 */
	public function test_maybe_remove_fade_on_load_for_block_preview() {
		$form = '<form enctype="multipart/form-data" method="post" class="frm-show-form  frm_pro_form  frm_logic_form  frm-admin-viewing ">';

		$_SERVER['HTTP_ACCEPT']  = 'application/json';
		$_SERVER['CONTENT_TYPE'] = 'application/json';

		if ( is_callable( 'wp_is_json_request' ) ) {
			$this->assertFalse( strpos( $this->maybe_remove_fade_on_load_for_block_preview( $form ), 'frm_logic_form' ) );
		}

		unset( $_SERVER['HTTP_ACCEPT'], $_SERVER['CONTENT_TYPE'] );
		$this->assertTrue( str_contains( $this->maybe_remove_fade_on_load_for_block_preview( $form ), 'frm_logic_form' ) );
	}

	private function maybe_remove_fade_on_load_for_block_preview( $form ) {
		return $this->run_private_method( array( 'FrmSimpleBlocksController', 'maybe_remove_fade_on_load_for_block_preview' ), array( $form ) );
	}
}
