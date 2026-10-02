<?php

/**
 * @covers FrmSpamEntriesController
 * @covers FrmSpamEntriesHelper
 */
class test_FrmManualSpam extends FrmUnitTest {

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
