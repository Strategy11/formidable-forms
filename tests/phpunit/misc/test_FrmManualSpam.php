<?php

/**
 * @covers FrmSpamEntriesController
 * @covers FrmSpamEntriesHelper
 */
class test_FrmManualSpam extends FrmUnitTest {

	/**
	 * Untrusted submissions cannot choose the spam status.
	 *
	 * @return void
	 */
	public function test_public_spam_status_requires_a_server_flag() {
		$form     = $this->factory->form->create();
		$original = $_POST;

		try {
			foreach ( array( 0, $this->factory->user->create( array( 'role' => 'subscriber' ) ) ) as $user ) {
				wp_set_current_user( $user );
				$_POST = array(
					'form_id'  => $form,
					'is_draft' => 4,
				);
				$id    = FrmEntry::create( $_POST );
				$this->assertSame( 0, (int) FrmEntry::getOne( $id )->is_draft, 'An unflagged public submission should be published.' );
				global $frm_vars;
				unset( $frm_vars['saved_entries'] );
				$this->assertNotFalse( FrmEntry::update( $id, $_POST ), 'A normal public update should remain available.' );
				$this->assertSame( 0, (int) FrmEntry::getOne( $id )->is_draft, 'A public update should not manufacture spam.' );
			}
		} finally {
			$_POST = $original;
		}

		wp_set_current_user( 0 );
		$id = FrmEntry::create_entry_from_xml(
			array(
				'form_id'  => $form,
				'is_draft' => 4,
			)
		);
		$this->assertSame( 4, (int) FrmEntry::getOne( $id )->is_draft, 'A trusted import should preserve its spam status.' );
	}

	/**
	 * Status preservation reuses both entry caches without another database read.
	 *
	 * @return void
	 */
	public function test_update_status_reuses_cached_entries() {
		global $wpdb;
		$form = $this->factory->form->create();
		$id   = $this->factory->entry->create( array( 'form_id' => $form ) );
		FrmSpamEntriesHelper::mark_as_spam( $id );

		foreach ( array( false, true ) as $meta ) {
			FrmEntry::clear_cache();
			FrmEntry::getOne( $id, $meta );
			$queries = $wpdb->num_queries;
			$status  = $this->run_private_method( array( 'FrmEntry', 'get_is_draft_value_for_update' ), array( $id, array( 'form_id' => $form ) ) );
			$this->assertSame( 4, $status, 'An implicitly updated spam entry should stay spam.' );
			$this->assertSame( $queries, $wpdb->num_queries, 'A cached entry should not require another status query.' );
		}

		FrmEntry::clear_cache();
		$status = $this->run_private_method( array( 'FrmEntry', 'get_is_draft_value_for_update' ), array( $id, array( 'form_id' => $form ) ) );
		$this->assertSame( 4, $status, 'Uncached spam should also retain its status.' );
		$status = $this->run_private_method( array( 'FrmEntry', 'get_is_draft_value_for_update' ), array( $id, array( 'is_draft' => 4 ) ) );
		$this->assertSame( 4, $status, 'Ignoring an untrusted spam status should not restore existing spam.' );
		$status = $this->run_private_method( array( 'FrmEntry', 'get_is_draft_value_for_update' ), array( $id, array( 'is_draft' => 0 ) ) );
		$this->assertSame( 0, $status, 'An explicitly requested restoration should remain possible.' );
	}

	/**
	 * A failed restoration must not show success or trigger restoration actions.
	 *
	 * @return void
	 */
	public function test_not_spam_reports_failed_status_update() {
		global $wpdb;
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		wp_get_current_user()->add_cap( 'frm_edit_entries' );
		wp_get_current_user()->add_cap( 'frm_delete_entries' );
		wp_get_current_user()->add_cap( 'frm_view_entries' );
		$form = $this->factory->form->create();
		$id   = $this->factory->entry->create( array( 'form_id' => $form ) );
		FrmSpamEntriesHelper::mark_as_spam( $id );
		$original    = $_POST;
		$_POST       = array(
			'id'                 => $id,
			'frm_not_spam_nonce' => wp_create_nonce( 'frm_not_spam' ),
		);
		$fail_update = static function ( $query ) use ( $wpdb ) {
			return str_starts_with( $query, 'UPDATE ' ) && str_contains( $query, $wpdb->prefix . 'frm_items' ) ? '' : $query;
		};
		$actions     = did_action( 'frm_entry_marked_not_spam' );
		add_filter( 'query', $fail_update );
		ob_start();

		try {
			FrmSpamEntriesController::not_spam();
			$html = ob_get_contents();
		} finally {
			ob_end_clean();
			remove_filter( 'query', $fail_update );
			$_POST = $original;
		}

		$this->assertStringContainsString( 'Unable to restore entry', $html, 'A failed write should show the error modal.' );
		$this->assertStringNotContainsString( 'The entry was marked as not spam.', $html, 'A failed write must not report success.' );
		$this->assertSame( $actions, did_action( 'frm_entry_marked_not_spam' ), 'Restoration actions must not run after a failed write.' );
		$this->assertTrue( FrmSpamEntriesHelper::is_spam( FrmEntry::getOne( $id ) ), 'The entry should still be spam.' );
	}

	/**
	 * Both query formats use the same status semantics.
	 *
	 * @return void
	 */
	public function test_both_and_all_match_for_array_and_string_queries() {
		global $wpdb;
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		wp_get_current_user()->add_cap( 'frm_edit_entries' );
		$form  = $this->factory->form->create();
		$field = $this->factory->field->create(
			array(
				'form_id' => $form,
				'type'    => 'text',
			)
		);
		$ids   = array();

		foreach ( range( 0, 4 ) as $status ) {
			$id = $this->factory->entry->create(
				array(
					'form_id'  => $form,
					'is_draft' => $status,
				)
			);
			FrmEntryMeta::add_entry_meta( $id, $field, '', 'Match' );
			$ids[ $status ] = (string) $id;
		}

		$legacy = $this->factory->entry->create( array( 'form_id' => $form ) );
		$wpdb->update( $wpdb->prefix . 'frm_items', array( 'is_draft' => null ), array( 'id' => $legacy ) );
		FrmEntryMeta::add_entry_meta( $legacy, $field, '', 'Match' );
		$ids['legacy'] = (string) $legacy;

		foreach ( array( array( 'it.field_id' => $field ), $wpdb->prepare( 'it.field_id = %d', $field ) ) as $where ) {
			$both = FrmEntryMeta::getEntryIds( $where, '', '', true, array( 'is_draft' => 'both' ) );
			$this->assertEqualsCanonicalizing( array( $ids[0], $ids[1] ), $both, 'Both should include only submitted and draft entries for either query format.' );
			$all = FrmEntryMeta::getEntryIds( $where, '', '', true, array( 'is_draft' => 'all' ) );
			$this->assertEqualsCanonicalizing( array_values( $ids ), $all, 'All should include spam and abandonment statuses for either query format.' );
		}
	}

	/**
	 * Marking spam preserves entry metadata and hides its child entries too.
	 */
	public function test_manual_spam_preserves_metadata_and_moves_children() {
		$form_id  = $this->factory->form->create();
		$entry_id = $this->factory->entry->create(
			array(
				'form_id'     => $form_id,
				'description' => array(
					'browser'  => 'Test browser',
					'referrer' => 'https://example.com/',
				),
			)
		);
		$child_id = $this->factory->entry->create(
			array(
				'form_id'        => $form_id,
				'parent_item_id' => $entry_id,
			)
		);

		$this->assertTrue( FrmSpamEntriesHelper::mark_as_spam( $entry_id ) );
		$entry = FrmEntry::getOne( $entry_id );
		$this->assertTrue( FrmSpamEntriesHelper::is_spam( $entry ) );
		$this->assertTrue( FrmSpamEntriesHelper::is_spam( FrmEntry::getOne( $child_id ) ) );
		$this->assertTrue( FrmSpamEntriesHelper::is_manual_spam( $entry ) );
		$this->assertSame( 'Manual review', FrmSpamEntriesHelper::get_source_label( $entry ) );
		$description = $entry->description;
		FrmAppHelper::unserialize_or_decode( $description );
		$this->assertGreaterThanOrEqual( time() - 5, $description['spam_marked_at'], 'Manual spam should record when it was marked.' );
		$this->assertLessThanOrEqual( time(), $description['spam_marked_at'], 'The marking date should not be in the future.' );
		$this->assertSame( 'Test browser', $description['browser'] );
		$this->assertSame( 'https://example.com/', $description['referrer'] );
		$this->assertFalse( FrmSpamEntriesHelper::mark_as_spam( $entry_id ) );
	}

	/**
	 * Drafts and missing entries cannot be manually moved to spam.
	 */
	public function test_invalid_entries_are_not_marked() {
		$form_id  = $this->factory->form->create();
		$entry_id = $this->factory->entry->create(
			array(
				'form_id'  => $form_id,
				'is_draft' => 1,
			)
		);
		$this->assertFalse( FrmSpamEntriesHelper::mark_as_spam( $entry_id ) );
		$this->assertSame( 1, (int) FrmEntry::getOne( $entry_id )->is_draft );
		$this->assertFalse( FrmSpamEntriesHelper::mark_as_spam( 0 ) );
	}

	/**
	 * The row and detail actions require moderation permission and have an entry-specific nonce.
	 */
	public function test_moderation_links() {
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		wp_get_current_user()->add_cap( 'frm_edit_entries' );
		wp_get_current_user()->add_cap( 'frm_delete_entries' );
		wp_get_current_user()->add_cap( 'frm_view_entries' );
		$entry = (object) array(
			'id'       => 123,
			'is_draft' => 0,
		);
		$rows  = FrmSpamEntriesController::row_actions( array(), $entry );
		$this->assertArrayHasKey( 'mark_spam', $rows );
		$sidebar = FrmSpamEntriesController::sidebar_actions( array(), array( 'entry' => $entry ) );
		$this->assertArrayHasKey( 'frm_mark_spam', $sidebar );
		parse_str( wp_parse_url( html_entity_decode( $sidebar['frm_mark_spam']['url'] ), PHP_URL_QUERY ), $query );
		$this->assertSame( 'mark_spam', $query['frm_action'] );
		$this->assertNotFalse( wp_verify_nonce( $query['_wpnonce'], 'frm_mark_spam_123' ) );
		$this->assertFalse( wp_verify_nonce( $query['_wpnonce'], 'frm_mark_spam_456' ) );

		$entry->is_draft = 1;
		$this->assertArrayNotHasKey( 'mark_spam', FrmSpamEntriesController::row_actions( array(), $entry ) );
		$entry->is_draft = 0;
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'subscriber' ) ) );
		$this->assertArrayNotHasKey( 'mark_spam', FrmSpamEntriesController::row_actions( array(), $entry ) );
		$this->assertArrayNotHasKey( 'frm_mark_spam', FrmSpamEntriesController::sidebar_actions( array(), array( 'entry' => $entry ) ) );
	}

	/**
	 * An invalid nonce cannot change the entry status, even for an administrator.
	 */
	public function test_invalid_nonce_does_not_mark_spam() {
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		wp_get_current_user()->add_cap( 'frm_edit_entries' );
		wp_get_current_user()->add_cap( 'frm_delete_entries' );
		wp_get_current_user()->add_cap( 'frm_view_entries' );
		$form_id  = $this->factory->form->create();
		$entry_id = $this->factory->entry->create( array( 'form_id' => $form_id ) );
		$original = $_GET;
		$_GET     = array(
			'id'       => $entry_id,
			'_wpnonce' => 'invalid',
		);
		ob_start();

		try {
			FrmSpamEntriesController::mark_spam();
		} finally {
			ob_end_clean();
			$_GET = $original;
		}
		$this->assertFalse( FrmSpamEntriesHelper::is_spam( FrmEntry::getOne( $entry_id ) ) );
	}

	/**
	 * Manually moderated entries do not offer to repeat previously executed actions.
	 */
	public function test_manual_spam_notice_does_not_offer_actions() {
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		wp_get_current_user()->add_cap( 'frm_edit_entries' );
		wp_get_current_user()->add_cap( 'frm_delete_entries' );
		wp_get_current_user()->add_cap( 'frm_view_entries' );
		$form_id  = $this->factory->form->create();
		$entry_id = $this->factory->entry->create( array( 'form_id' => $form_id ) );
		FrmSpamEntriesHelper::mark_as_spam( $entry_id );
		$add_action = static function () {
			return array(
				(object) array(
					'ID'         => 123,
					'post_title' => 'Existing email action',
				),
			);
		};
		add_filter( 'frm_not_spam_pending_actions', $add_action );
		ob_start();

		try {
			FrmSpamEntriesController::show_spam_notice(
				array(
					'id'   => $entry_id,
					'form' => FrmForm::getOne( $form_id ),
				)
			);
			$html = ob_get_contents();
		} finally {
			ob_end_clean();
			remove_filter( 'frm_not_spam_pending_actions', $add_action );
		}
		$this->assertStringContainsString( 'manually marked as spam', $html );
		$this->assertStringNotContainsString( 'Form actions did not run', $html );
		$this->assertStringNotContainsString( 'frm_not_spam_actions[]', $html );
	}
	/**
	 * A valid request marks spam, and restoring it ignores requests to rerun actions.
	 */
	public function test_mark_and_restore_without_repeating_actions() {
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		wp_get_current_user()->add_cap( 'frm_edit_entries' );
		wp_get_current_user()->add_cap( 'frm_delete_entries' );
		wp_get_current_user()->add_cap( 'frm_view_entries' );
		$form_id          = $this->factory->form->create();
		$entry_id         = $this->factory->entry->create( array( 'form_id' => $form_id ) );
		$original_get     = $_GET;
		$original_post    = $_POST;
		$restored_actions = null;
		$capture          = static function ( $id, $action_ids ) use ( &$restored_actions ) {
			$restored_actions = $action_ids;
		};
		add_action( 'frm_entry_marked_not_spam', $capture, 10, 2 );
		$pending = static function () {
			return array(
				123 => (object) array(
					'ID'         => 123,
					'post_title' => 'Existing email action',
				),
			);
		};
		add_filter( 'frm_not_spam_pending_actions', $pending );
		$_GET = array(
			'id'       => $entry_id,
			'_wpnonce' => wp_create_nonce( 'frm_mark_spam_' . $entry_id ),
		);
		ob_start();

		try {
			FrmSpamEntriesController::mark_spam();
			$this->assertTrue( FrmSpamEntriesHelper::is_spam( FrmEntry::getOne( $entry_id ) ) );
			$_POST = array(
				'id'                   => $entry_id,
				'frm_not_spam_nonce'   => wp_create_nonce( 'frm_not_spam' ),
				'frm_not_spam_actions' => array( 123 ),
			);
			FrmSpamEntriesController::not_spam();
		} finally {
			ob_end_clean();
			$_GET  = $original_get;
			$_POST = $original_post;
			remove_action( 'frm_entry_marked_not_spam', $capture );
			remove_filter( 'frm_not_spam_pending_actions', $pending );
		}
		$this->assertSame( 0, (int) FrmEntry::getOne( $entry_id )->is_draft );
		$this->assertSame( array(), $restored_actions );
	}

	/**
	 * A valid nonce does not grant moderation permission to a subscriber.
	 */
	public function test_permission_is_required_to_mark_spam() {
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'subscriber' ) ) );
		$form_id  = $this->factory->form->create();
		$entry_id = $this->factory->entry->create( array( 'form_id' => $form_id ) );
		$original = $_GET;
		$_GET     = array(
			'id'       => $entry_id,
			'_wpnonce' => wp_create_nonce( 'frm_mark_spam_' . $entry_id ),
		);
		ob_start();

		try {
			FrmSpamEntriesController::mark_spam();
		} finally {
			ob_end_clean();
			$_GET = $original;
		}
		$this->assertFalse( FrmSpamEntriesHelper::is_spam( FrmEntry::getOne( $entry_id ) ) );
	}
}
