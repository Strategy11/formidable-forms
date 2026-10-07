<?php

/**
 * @covers FrmSpamEntriesHelper
 * @covers FrmEntry
 */
class test_FrmSpamCompatibility extends FrmUnitTest {

	/**
	 * An old active add-on prevents spam writes through every entry creation and moderation path.
	 */
	#[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
	#[\PHPUnit\Framework\Attributes\PreserveGlobalState( false )]
	public function test_old_addon_blocks_spam_storage() {
		if ( class_exists( 'FrmViewsAppHelper' ) ) {
			$this->markTestSkipped( 'This test requires Views to be inactive.' );
		}

		$form_id = $this->factory->form->create();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		wp_get_current_user()->add_cap( 'frm_edit_entries' );
		$entry_id = $this->factory->entry->create( array( 'form_id' => $form_id ) );
		require dirname( __DIR__ ) . '/fixtures/spam-compatibility/old-views.php';

		$this->assertSame( array( 'Formidable Views' ), FrmSpamEntriesHelper::get_incompatible_addons() );
		$this->assertFalse( FrmSpamEntriesHelper::can_store_spam() );
		$force_save = '__return_true';
		add_filter( 'frm_save_spam_entry', $force_save );

		try {
			$this->assertFalse( FrmSpamEntriesHelper::should_save( 'denylist' ) );
			$this->assertFalse( FrmSpamEntriesHelper::maybe_flag_submission( $form_id, 'denylist' ) );
		} finally {
			remove_filter( 'frm_save_spam_entry', $force_save );
		}
		$this->assertFalse(
			FrmEntry::create(
				array(
					'form_id'  => $form_id,
					'is_draft' => 4,
				)
			)
		);
		$this->assertFalse( FrmEntry::update( $entry_id, array( 'is_draft' => 4 ) ) );
		$this->assertFalse( FrmSpamEntriesHelper::mark_as_spam( $entry_id ) );
		FrmSpamEntriesHelper::set_status( $entry_id, 4 );
		$this->assertSame( 0, (int) FrmEntry::getOne( $entry_id )->is_draft );
		ob_start();

		try {
			FrmSettingsController::captcha_settings();
			$html = ob_get_contents();
		} finally {
			ob_end_clean();
		}
		$this->assertStringContainsString( 'Update Formidable Views to save spam entries.', $html );
	}

	/**
	 * Old Pro is incompatible even when the active Views version supports spam.
	 */
	#[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
	#[\PHPUnit\Framework\Attributes\PreserveGlobalState( false )]
	public function test_old_pro_with_current_views() {
		if ( class_exists( 'FrmProAppHelper' ) || class_exists( 'FrmViewsAppHelper' ) ) {
			$this->markTestSkipped( 'This test requires Pro and Views to be inactive.' );
		}
		require dirname( __DIR__ ) . '/fixtures/spam-compatibility/old-pro.php';
		require dirname( __DIR__ ) . '/fixtures/spam-compatibility/current-views.php';
		$this->assertSame( array( 'Formidable Pro' ), FrmSpamEntriesHelper::get_incompatible_addons() );
		$this->assertFalse( FrmSpamEntriesHelper::can_store_spam() );
	}

	/**
	 * A current add-on explicitly declares support, so ordinary saved-spam handling still works.
	 */
	#[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
	#[\PHPUnit\Framework\Attributes\PreserveGlobalState( false )]
	public function test_current_addon_supports_spam() {
		if ( class_exists( 'FrmProAppHelper' ) || class_exists( 'FrmViewsAppHelper' ) ) {
			$this->markTestSkipped( 'This test requires Views to be inactive.' );
		}
		require dirname( __DIR__ ) . '/fixtures/spam-compatibility/current-views.php';
		require dirname( __DIR__ ) . '/fixtures/spam-compatibility/current-pro.php';
		$this->assertSame( array(), FrmSpamEntriesHelper::get_incompatible_addons() );
		$this->assertTrue( FrmSpamEntriesHelper::can_store_spam() );
		$this->assertSame( 30, apply_filters( 'frm_spam_retention_days', 30 ) );
	}
	/**
	 * Early feature builds must not enable unsafe cleanup merely by declaring spam support.
	 *
	 * @return void
	 */
	#[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
	#[\PHPUnit\Framework\Attributes\PreserveGlobalState( false )]
	public function test_earlier_spam_pro_disables_storage_and_cleanup() {
		if ( class_exists( 'FrmProAppHelper' ) ) {
			$this->markTestSkipped( 'This test requires Pro to be inactive.' );
		}
		require dirname( __DIR__ ) . '/fixtures/spam-compatibility/earlier-spam-pro.php';
		$this->assertFalse( FrmSpamEntriesHelper::can_store_spam() );
		$this->assertSame( 0, apply_filters( 'frm_spam_retention_days', 30 ) );
	}
}
