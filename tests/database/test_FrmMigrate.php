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
		$frmdb->upgrade();

		self::do_tables_exist();

		$new_version = get_option( 'frm_db_version' );
		$this->assertEquals( $new_version, FrmAppHelper::plugin_version() . '-' . FrmAppHelper::$db_version );

		// Check for auto contact form.
		$form = FrmForm::getOne( 'contact-form' );
		$this->assertEquals( $form->form_key, 'contact-form' );
	}

	/**
	 * Test to make sure a migration isn't run again
	 *
	 * @covers FrmMigrate::migrate_data
	 * @covers FrmMigrate::migrate_to_17
	 */
	public function test_migrate_to_17() {
		$form_id = $this->factory->form->create();
		$field = $this->factory->field->create_and_get( array(
			'type' => 'text',
			'form_id' => $form_id,
			'field_options' => array(
				'size' => '10', // the old size in characters
			),
		) );
		$this->assertNotEmpty( $field );
		$field_id = $field->id;

		$frmdb = new FrmMigrate();
		update_option( 'frm_db_version', 16 ); // trigger migration 17
		$frmdb->upgrade();

		$field = $this->factory->field->get_object_by_id( $field_id );
		$expected_size = '90px';
		$this->assertEquals( $expected_size, $field->field_options['size'] );

		// set it to a numeric value
		$expected_size = '10';
		$field->field_options['size'] = $expected_size;
		FrmField::update( $field_id, array( 'field_options' => $field->field_options ) );
		$field = $this->factory->field->get_object_by_id( $field_id );
		$this->assertEquals( $expected_size, $field->field_options['size'] );

		// make sure 17 does not fire and change the size again
		update_option( 'frm_db_version', 20 );
		$frmdb->upgrade();

		$field = $this->factory->field->get_object_by_id( $field_id );
		$this->assertEquals( $expected_size, $field->field_options['size'] );

		update_option( 'frm_db_version', FrmAppHelper::plugin_version() . '-' . FrmAppHelper::$db_version );
		$frmdb->upgrade();

		$field = $this->factory->field->get_object_by_id( $field_id );
		$this->assertEquals( $expected_size, $field->field_options['size'] );

		$frmdb->upgrade();

		$field = $this->factory->field->get_object_by_id( $field_id );
		$this->assertEquals( $expected_size, $field->field_options['size'] );
	}

	/**
	 * @covers FrmMigrate::migrate_to_86
	 */
	public function test_migrate_to_86() {
		$form_id = $this->factory->form->create();
		$sizes = array(
			'10px' => '10px',
			'10'   => '10',
			'1024' => '1024',
			'1024px' => round( 1024 / 9 ),
		);
		$field_ids = array();
		foreach ( $sizes as $start_size => $new_size ) {
			$field_id = $this->factory->field->create( array(
				'type' => 'text',
				'form_id' => $form_id,
				'field_options' => array(
					'size' => $start_size,
				),
			) );
			$field_ids[ $start_size ] = $field_id;
		}

		$frmdb = new FrmMigrate();
		$this->run_private_method( array( $frmdb, 'migrate_to_86' ), array() );

		foreach ( $sizes as $size => $expected ) {
			$field = $this->factory->field->get_object_by_id( $field_ids[ $size ] );
			$this->assertNotEmpty( $field );

			$new_size = $field->field_options['size'];
			$this->assertEquals( $expected, $new_size );
		}
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
			$this->assertTrue( strpos( $collation, 'DEFAULT CHARACTER SET' ) !== false );
		}

		if ( ! empty( $wpdb->collate ) ) {
			$this->assertTrue( strpos( $collation, 'COLLATE' ) !== false );
		}
	}

	/**
	 * @covers FrmMigrate::migrate_to_16
	 */
	function test_migrate_from_12_to_current() {
		self::frm_install();

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
		self::do_tables_exist( false );

		$this->assertEmpty( get_option('frm_db_version', true ) );
		$this->assertEmpty( get_option('frm_options', true ) );

		// TODO: Check if roles exist FrmAppHelper::frm_capabilities()
		// TODO: Check if any posts exist for extra types
		// TODO: Check if transients exist: frmpro_css, frm_options, frmpro_options, %frm_form_fields%
	}
}
