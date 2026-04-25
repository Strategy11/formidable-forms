<?php

/**
 * @group gated-content
 */
class test_FrmGatedContentController extends FrmUnitTest {

	public function setUp(): void {
		parent::setUp();
		FrmGatedTokenHelper::$tokens = array();
	}

	// ── trigger() ─────────────────────────────────────────────────────────── //

	/**
	 * trigger() must generate a token and cache it in FrmGatedTokenHelper::$tokens
	 * so that [frm_gated_content] shortcodes on the same request can use it.
	 *
	 * @covers FrmGatedContentController::trigger
	 */
	public function test_trigger_generates_and_caches_token() {
		$action_id = wp_insert_post(
			array(
				'post_type'    => 'frm_form_actions',
				'post_excerpt' => 'gated_content',
				'post_status'  => 'publish',
				'post_content' => wp_json_encode( array( 'items' => array() ) ),
			)
		);

		$action        = (object) array( 'ID' => $action_id );
		$entry         = (object) array( 'id' => 1, 'form_id' => 1 );
		$form          = (object) array( 'id' => 1 );

		FrmGatedContentController::trigger( $action, $entry, $form, 'create' );

		$this->assertArrayHasKey(
			$action_id,
			FrmGatedTokenHelper::$tokens,
			'trigger() must cache the raw token in FrmGatedTokenHelper::$tokens.'
		);
		$this->assertSame( 48, strlen( FrmGatedTokenHelper::$tokens[ $action_id ] ) );
	}
}
