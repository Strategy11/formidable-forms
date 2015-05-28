<?php

class WP_Test_FrmDb extends FrmUnitTest {

	/**
	 * @covers FrmDb::upgrade
	 * @todo Check if style was created
	 */
    public function test_upgrade( ) {
        global $wpdb;
		$frmdb = new FrmDb();
		$frmdb->upgrade( 25 );

		$this->do_tables_exist();

		$new_version = get_option( 'frm_db_version' );
		$this->assertEquals( $new_version, FrmAppHelper::$db_version );
    }

	/**
	 * @covers FrmDb::collation
	 */
	public function test_collation() {
		global $wpdb;
		if ( $wpdb->has_cap( 'collation' ) ) {
			$this->assert_collation();
		}
	}

	private function assert_collation() {
		global $wpdb;
		$frmdb = new FrmDb();
		$collation = $frmdb->collation();

		if ( ! empty( $wpdb->charset ) ) {
			$this->assertNotEmpty( strpos( $collation, 'DEFAULT CHARACTER SET' ) );
		}

		if ( ! empty( $wpdb->collate ) ) {
			$this->assertNotEmpty( strpos( $collation, 'COLLATE' ) );
		}
	}

	/**
	 * @covers FrmDb::migrate_to_17
	 */
	function test_migrate_from_12_to_17() {
		$this->frm_install();

		update_option( 'frm_db_version', 12 );

		$form = FrmForm::getOne( 'contact-db12' );
		$this->assertNotEmpty( $form );
		$this->assertTrue( is_numeric( $form->id ) );
		$notification = array( 0 => array(
			'email_to' => 'emailto@test.com', 'also_email_to' => array(1,2),
			'reply_to' => 'replyto@test.com', 'reply_to_name' => 'Reply to me',
			'cust_reply_to' => '', 'cust_reply_to_name' => '', 'plain_text' => 1,
			'email_message' => 'This is my email message. [default-message]',
			'email_subject' => 'The subject', 'update_email' => 2, 'inc_user_info' => 1,
		) );
		$form->options['notification'] = $notification;

		global $wpdb;
		$updated = $wpdb->update( $wpdb->prefix . 'frm_forms', array( 'options' => maybe_serialize( $form->options ) ), array( 'id' => $form->id ) );
		FrmForm::clear_form_cache();
		$this->assertEquals( $updated, 1 );

		$form = FrmForm::getOne( 'contact-db12' );

		$this->assertNotEmpty( $form->options, 'The form settings are empty' );
		$this->assertTrue( isset( $form->options['notification'] ), 'The old notification settings are missing' );
		$this->assertEquals( $form->options['notification'][0]['email_to'], 'emailto@test.com' );

		// migrate data
		FrmAppController::install();

		$form_actions = FrmFormActionsHelper::get_action_for_form( $form->id, 'email' );
		foreach ( $form_actions as $action ) {
			$this->assertTrue( strpos( $action->post_content['email_to'], 'emailto@test.com' ) !== false );
		}
	}

	/**
	 * @covers FrmDb::uninstall
	 */
	public function test_uninstall() {
		$this->set_as_user_role( 'administrator' );

		$frmdb = new FrmDb();
		$uninstalled = $frmdb->uninstall();
		$this->assertTrue( $uninstalled );

		$this->markTestIncomplete( 'Make sure uninstall is complete' );
		$this->do_tables_exist( false );

		$this->assertEmpty( get_option('frm_db_version', true ) );
		$this->assertEmpty( get_option('frm_options', true ) );

		// TODO: Check if roles exist FrmAppHelper::frm_capabilities()
		// TODO: Check if any posts exist for extra types
		// TODO: Check if transients exist: frmpro_css, frm_options, frmpro_options, %frm_form_fields%
	}
}