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

		$action = (object) array( 'ID' => $action_id );
		$entry  = (object) array( 'id' => 1, 'form_id' => 1 );
		$form   = (object) array( 'id' => 1 );

		FrmGatedContentController::trigger( $action, $entry, $form, 'create' );

		$this->assertArrayHasKey(
			$action_id,
			FrmGatedTokenHelper::$tokens,
			'trigger() must cache the raw token in FrmGatedTokenHelper::$tokens.'
		);
		$this->assertSame( 48, strlen( FrmGatedTokenHelper::$tokens[ $action_id ] ) );
	}

	// ── payment-success event ─────────────────────────────────────────────── //

	/**
	 * When a payment succeeds, FrmFormActionsController::trigger_actions() must
	 * dispatch frm_trigger_gated_content_action for any gated content action that
	 * has 'payment-success' in its event list, resulting in a token being generated.
	 *
	 * @covers FrmGatedContentAction::__construct
	 * @covers FrmGatedContentController::trigger
	 */
	public function test_payment_success_event_generates_token() {
		$form_id  = $this->factory->form->create();
		$entry_id = $this->factory->entry->create( array( 'form_id' => $form_id ) );

		$action_id = wp_insert_post(
			array(
				'post_type'    => 'frm_form_actions',
				'post_excerpt' => 'gated_content',
				'post_status'  => 'publish',
				'post_parent'  => $form_id,
				'post_content' => wp_json_encode(
					array(
						'event' => array( 'payment-success' ),
						'items' => array(
							array( 'type' => 'page', 'id' => 1 ),
						),
					)
				),
			)
		);

		FrmFormActionsController::trigger_actions( 'payment-success', $form_id, $entry_id );

		$this->assertArrayHasKey(
			$action_id,
			FrmGatedTokenHelper::$tokens,
			'payment-success event must trigger token generation for a gated content action.'
		);
		$this->assertSame( 48, strlen( FrmGatedTokenHelper::$tokens[ $action_id ] ) );
	}

	/**
	 * A gated content action with only 'create' in its event list must NOT generate
	 * a token when the payment-success event fires.
	 *
	 * @covers FrmGatedContentController::trigger
	 */
	public function test_payment_success_event_skips_non_matching_action() {
		$form_id  = $this->factory->form->create();
		$entry_id = $this->factory->entry->create( array( 'form_id' => $form_id ) );

		$action_id = wp_insert_post(
			array(
				'post_type'    => 'frm_form_actions',
				'post_excerpt' => 'gated_content',
				'post_status'  => 'publish',
				'post_parent'  => $form_id,
				'post_content' => wp_json_encode(
					array(
						'event' => array( 'create' ),
						'items' => array(
							array( 'type' => 'page', 'id' => 1 ),
						),
					)
				),
			)
		);

		FrmFormActionsController::trigger_actions( 'payment-success', $form_id, $entry_id );

		$this->assertArrayNotHasKey(
			$action_id,
			FrmGatedTokenHelper::$tokens,
			'Actions without payment-success in their event list must not generate a token.'
		);
	}
}
