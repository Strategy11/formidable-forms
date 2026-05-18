<?php

/**
 * @group gated-content
 */
class test_FrmGatedContentController extends FrmUnitTest {

	public function setUp(): void {
		parent::setUp();
	}

	public function tearDown(): void {
		parent::tearDown();
		// Reset static state so tests don't bleed into one another.
		$this->set_private_property( 'FrmGatedContentController', 'unlocked_post_id', 0 );
	}

	// ── trigger() — create event ──────────────────────────────────────────── //

	/**
	 * trigger() must generate a token and store it in a transient so that
	 * [frm_gated_content] shortcodes on the same or a subsequent redirect request can use it.
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

		$raw_token = FrmGatedTokenHelper::get_raw_token_for_action( $action_id );
		$this->assertNotNull(
			$raw_token,
			'trigger() must store the raw token via FrmGatedTokenHelper::get_raw_token_for_action().'
		);
		$this->assertSame( 32, strlen( $raw_token ) );
	}

	// ── trigger() — update event ──────────────────────────────────────────── //

	/**
	 * On update events trigger() must revoke old tokens for the action+entry pair
	 * before issuing a new one. Only the freshly-generated token should remain in the DB.
	 *
	 * @covers FrmGatedContentController::trigger
	 */
	public function test_trigger_on_update_revokes_old_token_and_generates_new_one() {
		global $wpdb;

		$action_id = wp_insert_post(
			array(
				'post_type'    => 'frm_form_actions',
				'post_excerpt' => 'gated_content',
				'post_status'  => 'publish',
				'post_content' => wp_json_encode( array( 'items' => array() ) ),
			)
		);

		$action  = (object) array( 'ID' => $action_id );
		$entry   = (object) array( 'id' => 55, 'form_id' => 1 );
		$form    = (object) array( 'id' => 1 );

		// Create event — inserts first token.
		FrmGatedContentController::trigger( $action, $entry, $form, 'create' );

		$first_raw  = FrmGatedTokenHelper::get_raw_token_for_action( $action_id );
		$first_hash = hash( 'sha256', $first_raw );

		// Update event — must revoke the first token then insert a new one.
		FrmGatedContentController::trigger( $action, $entry, $form, 'update' );

		// Old token row must be gone.
		$old_row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}frm_gated_tokens WHERE token_hash = %s",
				$first_hash
			)
		);
		$this->assertNull( $old_row, 'The old token must be revoked after an update event.' );

		// Exactly one token must exist for this action+entry pair.
		$count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}frm_gated_tokens WHERE action_id = %d AND entry_id = %d",
				$action_id,
				55
			)
		);
		$this->assertSame( 1, $count, 'Exactly one active token must exist after the update event.' );
	}

	// ── on_action_updated() ───────────────────────────────────────────────── //

	/**
	 * on_action_updated() must delete validation-cache transients for every token
	 * belonging to the saved action so stale item-membership results are not served.
	 *
	 * @covers FrmGatedContentController::on_action_updated
	 */
	public function test_on_action_updated_deletes_validation_cache_for_action_tokens() {
		$item = array( 'type' => 'post', 'id' => 77 );

		$action_id = wp_insert_post(
			array(
				'post_type'    => 'frm_form_actions',
				'post_excerpt' => 'gated_content',
				'post_status'  => 'publish',
				'post_content' => wp_json_encode( array( 'items' => array( $item ) ) ),
			)
		);

		// Generate a token and prime a validation-cache entry for it.
		$raw_token = FrmGatedTokenHelper::generate( $action_id, 1 );
		$hash      = hash( 'sha256', $raw_token );

		FrmGatedTokenHelper::set_validation_cache( $hash, $item['type'], $item['id'], null, $action_id );
		$this->assertNotNull(
			FrmGatedTokenHelper::get_cached_validation( $hash, $item['type'], $item['id'] ),
			'Pre-condition: cache entry must exist before the action is updated.'
		);

		// Simulate a save_post_frm_form_actions callback.
		$post = get_post( $action_id );
		FrmGatedContentController::on_action_updated( $action_id, $post, true );

		$this->assertNull(
			FrmGatedTokenHelper::get_cached_validation( $hash, $item['type'], $item['id'] ),
			'Validation-cache entry must be deleted after on_action_updated() fires.'
		);
	}

	/**
	 * on_action_updated() must be a no-op when called with $update = false (post creation).
	 *
	 * @covers FrmGatedContentController::on_action_updated
	 */
	public function test_on_action_updated_is_noop_on_create() {
		$item = array( 'type' => 'post', 'id' => 88 );

		$action_id = wp_insert_post(
			array(
				'post_type'    => 'frm_form_actions',
				'post_excerpt' => 'gated_content',
				'post_status'  => 'publish',
				'post_content' => wp_json_encode( array( 'items' => array( $item ) ) ),
			)
		);

		$raw_token = FrmGatedTokenHelper::generate( $action_id, 1 );
		$hash      = hash( 'sha256', $raw_token );

		FrmGatedTokenHelper::set_validation_cache( $hash, $item['type'], $item['id'], null, $action_id );

		// $update = false means this is a create, not an update — cache must survive.
		$post = get_post( $action_id );
		FrmGatedContentController::on_action_updated( $action_id, $post, false );

		$this->assertNotNull(
			FrmGatedTokenHelper::get_cached_validation( $hash, $item['type'], $item['id'] ),
			'Validation cache must not be deleted when on_action_updated() fires on create.'
		);
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
							array( 'type' => 'post', 'id' => 1 ),
						),
					)
				),
			)
		);

		FrmFormActionsController::trigger_actions( 'payment-success', $form_id, $entry_id );

		$raw_token = FrmGatedTokenHelper::get_raw_token_for_action( $action_id );
		$this->assertNotNull(
			$raw_token,
			'payment-success event must trigger token generation for a gated content action.'
		);
		$this->assertSame( 32, strlen( $raw_token ) );
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
							array( 'type' => 'post', 'id' => 1 ),
						),
					)
				),
			)
		);

		FrmFormActionsController::trigger_actions( 'payment-success', $form_id, $entry_id );

		$this->assertNull(
			FrmGatedTokenHelper::get_raw_token_for_action( $action_id ),
			'Actions without payment-success in their event list must not generate a token.'
		);
	}
}
