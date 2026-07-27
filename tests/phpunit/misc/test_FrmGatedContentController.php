<?php

/**
 * @group gated-content
 */
class test_FrmGatedContentController extends FrmUnitTest {

	public function tearDown(): void {
		parent::tearDown();
		// Reset static state so tests don't bleed into one another.
		$this->set_private_property( 'FrmGatedContentController', 'unlocked_post_id', 0 );
	}

	// ── trigger() — create event ──────────────────────────────────────────── //

	/**
	 * Calling trigger() must generate a token and store it in a transient so that
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

		$action = get_post( $action_id );
		$entry  = (object) array(
			'id'      => 1,
			'form_id' => 1,
		);
		$form   = (object) array( 'id' => 1 );

		FrmGatedContentController::trigger( $action, $entry, $form, 'create' );

		$raw_token = FrmGatedTokenHelper::get_raw_token_for_action( $action_id );
		$this->assertNotNull(
			$raw_token,
			'trigger() must store the raw token via FrmGatedTokenHelper::get_raw_token_for_action().'
		);
		$this->assertSame( 32, strlen( $raw_token ) );
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
		$form_id = $this->factory->form->create();

		// Insert the action BEFORE creating the entry so that trigger_create_actions()
		// (fired by FrmEntry::create) warms the frm_actions cache with the real action.
		// If the action is inserted after entry creation the cache is primed with an
		// empty result and the subsequent payment-success trigger never finds it.
		$action_id = wp_insert_post(
			array(
				'post_type'    => 'frm_form_actions',
				'post_excerpt' => 'gated_content',
				'post_status'  => 'publish',
				'menu_order'   => $form_id,
				'post_content' => wp_json_encode(
					array(
						'event' => array( 'payment-success' ),
						'items' => array(
							array(
								'type' => 'post',
								'id'   => 1,
							),
						),
					)
				),
			)
		);

		$entry_id = $this->factory->entry->create( array( 'form_id' => $form_id ) );

		FrmFormActionsController::trigger_actions( 'payment-success', $form_id, $entry_id );

		$raw_token = FrmGatedTokenHelper::get_raw_token_for_action( $action_id );
		$this->assertNotNull(
			$raw_token,
			'payment-success event must trigger token generation for a gated content action.'
		);
		$this->assertSame( 32, strlen( $raw_token ) );
	}

	// ── maybe_unlock_post() ─────────────────────────────────────────────────── //

	/**
	 * Taxonomy archive pages must not be force-404'd.
	 *
	 * Before the fix, get_queried_object_id() on an archive returned a term ID which
	 * get_post() silently resolved to an unrelated post — potentially a private one —
	 * causing force_404() to fire on a perfectly valid archive page.
	 *
	 * @covers FrmGatedContentController::maybe_unlock_post
	 */
	public function test_maybe_unlock_post_skips_on_taxonomy_archive() {
		$cat_id = $this->factory->category->create();
		$this->set_front_end( get_category_link( $cat_id ) );

		$this->assertFalse( is_singular(), 'Prerequisite: category archive must not be singular.' );
		$this->assertFalse( is_404(), 'Prerequisite: category archive must not start as a 404.' );

		FrmGatedContentController::maybe_unlock_post();

		$this->assertFalse( is_404(), 'maybe_unlock_post() must not force-404 a taxonomy archive.' );
	}

	/**
	 * A user who holds a CPT-specific read-private cap must not be force-404'd.
	 *
	 * Before the fix the check always used the built-in `read_private_posts` cap.
	 * CPTs with a custom capability_type map that abstract name to e.g. `read_private_books`.
	 * Users who have `read_private_books` but not `read_private_posts` were incorrectly blocked.
	 *
	 * @covers FrmGatedContentController::maybe_unlock_post
	 */
	public function test_maybe_unlock_post_respects_cpt_read_private_cap() {
		register_post_type(
			'frm_gc_test_book',
			array(
				'capability_type' => array( 'book', 'books' ),
				'map_meta_cap'    => true,
				'public'          => false,
			)
		);

		$post = $this->factory->post->create_and_get(
			array(
				'post_type'   => 'frm_gc_test_book',
				'post_status' => 'private',
			)
		);

		// Subscriber has read_private_books (the CPT cap) but NOT read_private_posts.
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		$user    = new WP_User( $user_id );
		$user->add_cap( 'read_private_books' );
		wp_set_current_user( $user_id );

		// Simulate a singular WP query for this private CPT post.
		global $wp_query;
		$wp_query->is_singular    = true;
		$wp_query->queried_object = $post;

		FrmGatedContentController::maybe_unlock_post();

		$this->assertFalse(
			is_404(),
			'A user with the CPT-specific read_private cap must not be force-404d by maybe_unlock_post().'
		);

		// Restore query state.
		$wp_query->is_singular    = false;
		$wp_query->queried_object = null;
		wp_set_current_user( 0 );
		unregister_post_type( 'frm_gc_test_book' );
	}

	// ── payment-success event ─────────────────────────────────────────────── //

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
				'menu_order'   => $form_id,
				'post_content' => wp_json_encode(
					array(
						'event' => array( 'create' ),
						'items' => array(
							array(
								'type' => 'post',
								'id'   => 1,
							),
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
