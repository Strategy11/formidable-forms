<?php

/**
 * @group entries
 *
 * @covers FrmEntriesController
 */
#[\PHPUnit\Framework\Attributes\Group( 'entries' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmEntriesController::class )]
class test_FrmEntriesController extends FrmUnitTest {

	public function test_delete_entry_after_save() {
		$save_form = $this->create_form();
		$this->assertEmpty( $save_form->options['no_save'] );

		$entry_key = 'test' . $save_form->id . 'entry1';
		$post_id   = $this->create_post_entry( $save_form, $entry_key );
		$this->assertNotEmpty( FrmEntry::getOne( $entry_key ) );

		$post = get_post( $post_id );
		$this->assertNotEmpty( $post );
		$this->assertSame( 'publish', $post->post_status );

		$no_save_form = $this->create_form( array( 'no_save' => 1 ) );
		$this->assertNotEmpty( $no_save_form->options['no_save'] );

		$entry_key    = 'test' . $no_save_form->id . 'entry2';
		$created_post = $this->create_post_entry( $no_save_form, $entry_key );
		$this->assertEmpty( FrmEntry::getOne( $entry_key ), 'Entry was not deleted' );

		$post = get_post( $created_post );
		$this->assertNotEmpty( $post );
		$this->assertSame( 'publish', $post->post_status );
	}

	/**
	 * @param array $options
	 */
	private function create_form( $options = array() ) {
		return $this->factory->form->create_and_get(
			array(
				'options' => $options,
			)
		);
	}

	private function create_post_entry( $form, $entry_key ) {
		$exists = FrmEntry::get_id_by_key( $entry_key );

		if ( $exists ) {
			FrmEntry::destroy( $exists );
		}

		$new_post          = $this->factory->post->create_and_get();
		$_POST             = $this->factory->field->generate_entry_array( $form );
		$_POST['item_key'] = $entry_key;
		$_POST['action']   = 'create';
		$_POST['post_id']  = $new_post->ID;
		FrmEntriesController::process_entry();

		$entry = FrmEntry::getOne( $entry_key );
		$this->assertNotEmpty( $entry );
		$this->assertSame( (int) $entry->post_id, $new_post->ID );

		FrmFormsController::get_form( $form, false, false ); // This is where the entry is deleted

		return $new_post->ID;
	}

	public function test_hidden_columns() {
		// Confirm that a string option value doesn't trigger a fatal error.
		$columns = FrmEntriesController::hidden_columns( '' );
		$this->assertIsArray( $columns );
	}
	/**
	 * Only the Spam tab replaces entry status with the reason column.
	 */
	public function test_spam_tab_columns() {
		FrmAppHelper::set_current_screen_and_hook_suffix();
		$original = $_GET;
		try {
			$_GET    = array();
			$columns = FrmEntriesController::manage_columns( array() );
			$this->assertArrayHasKey( '0_is_draft', $columns );
			$this->assertArrayNotHasKey( '0_spam_reason', $columns );
			$_GET['entry_status'] = 'spam';
			$columns              = FrmEntriesController::manage_columns( array() );
			$this->assertArrayHasKey( '0_spam_reason', $columns );
			$this->assertArrayNotHasKey( '0_is_draft', $columns );
		} finally {
			$_GET = $original;
		}
	}
	/**
	 * Stored source keys are presented as readable spam reasons in the sidebar.
	 */
	public function test_spam_reason_in_sidebar() {
		$form_id  = $this->factory->form->create();
		$entry_id = $this->factory->entry->create(
			array(
				'form_id'     => $form_id,
				'is_draft'    => 4,
				'description' => array(
					'spam_source' => 'denylist',
					'test_sample' => true,
				),
			)
		);
		ob_start();
		try {
			FrmEntriesController::entry_sidebar( FrmEntry::getOne( $entry_id ) );
			$html = ob_get_contents();
		} finally {
			ob_end_clean();
		}
		$this->assertStringContainsString( 'Spam reason', $html );
		$this->assertStringContainsString( 'Denylist', $html );
		$this->assertStringNotContainsString( 'Spam_source', $html );
		$this->assertStringNotContainsString( 'Test_sample', $html );
	}
}
