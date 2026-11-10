<?php

/**
 * @group altcha
 *
 * @covers FrmAltchaReplayHelper
 * @covers FrmMigrate
 */
class test_FrmAltchaReplayHelper extends FrmUnitTest {

	public function setUp(): void {
		parent::setUp();
		FrmAltchaReplayHelper::uninstall();
		remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );

		try {
			FrmAltchaReplayHelper::uninstall();
		} finally {
			add_filter( 'query', array( $this, '_drop_temporary_tables' ) );
		}

		$this->start_transaction();
	}

	public function tearDown(): void {
		FrmAltchaReplayHelper::uninstall();
		parent::tearDown();
	}

	/**
	 * Settings, challenge requests, provider selection and cleanup do not create replay storage.
	 */
	public function test_unused_sites_do_not_create_storage() {
		global $wpdb;
		$migrate = new FrmMigrate();
		$migrate->upgrade();
		FrmAltchaChallengeHelper::create(
			array(
				'counter' => 7,
				'cost'    => 1,
			)
		);

		foreach ( array( 'recaptcha', 'hcaptcha', 'turnstile' ) as $provider ) {
			FrmCaptchaFactory::get_settings_object( $provider );
		}

		$this->assertFalse( FrmAltchaReplayHelper::is_used( hash( 'sha256', 'unused' ) ), 'An unused challenge should not be claimed.' );
		FrmAltchaReplayHelper::purge();
		FrmCronController::schedule_events();

		$previous = $wpdb->suppress_errors();

		try {
			$result = $wpdb->query( $wpdb->prepare( 'SELECT 1 FROM %i', FrmAltchaReplayHelper::table_name() ) );
		} finally {
			$wpdb->suppress_errors( $previous );
		}

		$this->assertFalse( $result, 'Unused ALTCHA should not create a replay table.' );
		$this->assertFalse( wp_next_scheduled( FrmAltchaReplayHelper::PURGE_HOOK ), 'Unused ALTCHA should not schedule cleanup.' );
	}

	/**
	 * Simulate a database user that cannot create replay storage.
	 *
	 * @param string $query The query about to execute.
	 *
	 * @return string
	 */
	public function reject_replay_table_creation( $query ) {
		if ( str_contains( $query, 'CREATE' ) && str_contains( $query, FrmAltchaReplayHelper::table_name() ) ) {
			return 'SELECT 1 FROM frm_missing_replay_table';
		}

		return $query;
	}

	/**
	 * Missing CREATE privileges must block a claim rather than disable replay protection.
	 */
	public function test_failed_table_creation_blocks_claims() {
		global $wpdb;
		$previous = $wpdb->suppress_errors();
		add_filter( 'query', array( $this, 'reject_replay_table_creation' ) );

		try {
			$result = FrmAltchaReplayHelper::claim( hash( 'sha256', 'failed-create' ), time() + 60 );
		} finally {
			remove_filter( 'query', array( $this, 'reject_replay_table_creation' ) );
			$wpdb->suppress_errors( $previous );
		}

		$this->assertFalse( $result, 'A claim should fail when storage cannot be created.' );
		$this->assertFalse( wp_next_scheduled( FrmAltchaReplayHelper::PURGE_HOOK ), 'Failed creation should not schedule cleanup.' );
	}

	/**
	 * Claims use compact binary hashes without adding WordPress options.
	 */
	public function test_claim_uses_binary_hash_and_rejects_duplicates() {
		global $wpdb;
		$id      = hash( 'sha256', 'binary-replay' );
		$expires = time() + HOUR_IN_SECONDS;

		$this->assertTrue( FrmAltchaReplayHelper::claim( $id, $expires ), 'The first claim should succeed.' );
		$this->assertFalse( FrmAltchaReplayHelper::claim( $id, $expires + 60 ), 'A second claim should fail.' );
		$this->assertTrue( FrmAltchaReplayHelper::is_used( $id ), 'A claimed challenge should be used.' );

		$row = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT HEX(challenge_hash) AS hash, LENGTH(challenge_hash) AS bytes, expires_at FROM %i WHERE challenge_hash = UNHEX(%s)',
				$wpdb->prefix . 'frm_altcha_replays',
				$id
			)
		);
		$this->assertSame( strtoupper( $id ), $row->hash, 'The complete challenge hash should be stored.' );
		$this->assertSame( 32, (int) $row->bytes, 'The hash should occupy 32 bytes.' );
		$this->assertSame( $expires, (int) $row->expires_at, 'A duplicate claim should leave the original expiration intact.' );
	}

	/**
	 * Invalid hashes must not be truncated or padded into valid binary keys.
	 */
	public function test_invalid_hashes_cannot_be_claimed() {
		foreach ( array( '', 'not-hex', str_repeat( 'a', 63 ), str_repeat( 'a', 65 ), str_repeat( 'g', 64 ) ) as $id ) {
			$this->assertFalse( FrmAltchaReplayHelper::claim( $id, time() + 60 ), 'Malformed hashes should not be claimed.' );
			$this->assertFalse( FrmAltchaReplayHelper::is_used( $id ), 'Malformed hashes should not match a claim.' );
		}
	}

	/**
	 * Cleanup drains more than one batch and preserves unexpired claims.
	 */
	public function test_cleanup_removes_multiple_batches() {
		$expires = time() - 60;

		for ( $i = 0; $i <= FrmAltchaReplayHelper::PURGE_BATCH_SIZE; $i++ ) {
			FrmAltchaReplayHelper::claim( hash( 'sha256', 'expired-replay-' . $i ), $expires );
		}

		$live = hash( 'sha256', 'unexpired-replay' );
		FrmAltchaReplayHelper::claim( $live, time() + HOUR_IN_SECONDS );
		FrmAltchaReplayHelper::purge();

		$this->assertFalse( FrmAltchaReplayHelper::is_used( hash( 'sha256', 'expired-replay-0' ) ), 'The first cleanup batch should be deleted.' );
		$last = hash( 'sha256', 'expired-replay-' . FrmAltchaReplayHelper::PURGE_BATCH_SIZE );
		$this->assertFalse( FrmAltchaReplayHelper::is_used( $last ), 'Rows beyond the first batch should also be deleted.' );
		$this->assertTrue( FrmAltchaReplayHelper::is_used( $live ), 'Unexpired claims should survive cleanup.' );
	}

	/**
	 * The first accepted solution creates the compact indexed schema.
	 */
	public function test_first_claim_creates_storage_and_indexes() {
		global $wpdb;
		$id = hash( 'sha256', 'first-replay' );
		$this->assertTrue( FrmAltchaReplayHelper::claim( $id, time() + HOUR_IN_SECONDS ), 'The first replay claim should succeed.' );
		$table   = FrmAltchaReplayHelper::table_name();
		$columns = $wpdb->get_results( $wpdb->prepare( 'SHOW COLUMNS FROM %i', $table ), OBJECT_K );

		$this->assertSame( 'binary(32)', $columns['challenge_hash']->Type, 'Replay hashes should use a binary column.' );
		$this->assertCount( 2, $columns, 'Replay storage should contain only the hash and expiration.' );
		$this->assertNotFalse( wp_next_scheduled( FrmAltchaReplayHelper::PURGE_HOOK ), 'The first replay should schedule cleanup.' );
		$index_rows = $wpdb->get_results( $wpdb->prepare( 'SHOW INDEX FROM %i', $table ), ARRAY_A );
		$indexed    = array();

		foreach ( $index_rows as $index ) {
			$indexed[ $index['Key_name'] ] = $index['Column_name'];
		}

		$this->assertSame( 'challenge_hash', $indexed['PRIMARY'], 'The challenge hash should prevent duplicate claims.' );
		$this->assertSame( 'expires_at', $indexed['expires_at'], 'Expiration cleanup should have its own index.' );
	}

	/**
	 * Uninstall removes optional replay storage and its scheduled cleanup.
	 */
	public function test_uninstall_removes_replay_table() {
		global $wpdb;
		FrmAltchaReplayHelper::claim( hash( 'sha256', 'uninstall-replay' ), time() + HOUR_IN_SECONDS );
		$table = FrmAltchaReplayHelper::table_name();
		$this->set_user_by_role( 'administrator' );
		do_action( 'frm_after_uninstall' );
		$suppressed = $wpdb->suppress_errors();

		try {
			$result = $wpdb->query( $wpdb->prepare( 'SELECT 1 FROM %i', $table ) );
		} finally {
			$wpdb->suppress_errors( $suppressed );
		}

		$this->assertFalse( $result, 'The replay table should no longer be queryable after uninstall.' );
		$this->assertFalse( wp_next_scheduled( FrmAltchaReplayHelper::PURGE_HOOK ), 'Uninstall should remove cleanup scheduling.' );
	}
}
