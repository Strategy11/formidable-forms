<?php

/**
 * @group database
 */
class test_FrmMigrate extends FrmUnitTest {

	/**
	 * @covers FrmMigrate::upgrade
	 * @todo Check if style was created
	 */
    public function test_upgrade( ) {
		$frmdb = new FrmMigrate();
		$frmdb->upgrade( 25 );

		$this->do_tables_exist();

		$new_version = get_option( 'frm_db_version' );
		$this->assertEquals( $new_version, FrmAppHelper::$db_version );
    }

	/**
	 * @covers FrmMigrate::collation
	 */
	public function test_collation() {
		global $wpdb;
		if ( $wpdb->has_cap( 'collation' ) ) {
			$this->assert_collation();
		}
	}

	private function assert_collation() {
		global $wpdb;
		$frmdb = new FrmMigrate();
		$collation = $frmdb->collation();

		if ( ! empty( $wpdb->charset ) ) {
			$this->assertNotEmpty( strpos( $collation, 'DEFAULT CHARACTER SET' ) );
		}

		if ( ! empty( $wpdb->collate ) ) {
			$this->assertNotEmpty( strpos( $collation, 'COLLATE' ) );
		}
	}

	/**
	 * @covers FrmMigrate::migrate_to_16
	 */
	function test_migrate_from_12_to_current() {
		$this->frm_install();

		update_option( 'frm_db_version', 12 );

		// Create new contact-db12 form on site
		$form_values = array(
			'form_key' => 'contact-db12-copy',
			'name'     => 'Contact DB12 Copy',
			'description' => '',
			'status'      => 'published',
			'options'     => array(
				'custom_style'  => '1',
				'notification' => array(
					'email_to' => 'emailto@test.com,tester@mail.com',
					'reply_to' => 'replyto@test.com',
					'reply_to_name' => 'Reply to me',
					'cust_reply_to' => '',
					'cust_reply_to_name' => '',
					'plain_text' => 1,
					'inc_user_info' => 1,
					'email_message' => 'This is my email message. [default-message]',
					'email_subject' => 'The subject',
					'update_email' => 2,
				),
			),
		);

		FrmForm::create( $form_values );

		// migrate data
		FrmAppController::install();

		$form = FrmForm::getOne( 'contact-db12-copy' );

		$form_actions = FrmFormAction::get_action_for_form( $form->id, 'email' );

		$this->assertTrue( ! isset( $form->options['notification'] ), 'The migrated notification settings are not cleared from form.' );

		$this->assertEquals( 1, count( $form_actions ), 'Old form settings are not converted to email action.' );
		foreach ( $form_actions as $action ) {
			$this->assertTrue( strpos( $action->post_content['email_to'], 'emailto@test.com' ) !== false );
		}
	}

	/**
	 * @covers FrmMigrate::uninstall
	 */
	public function test_uninstall() {
		$this->set_user_by_role( 'administrator' );

		$frmdb = new FrmMigrate();
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
