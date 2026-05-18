<?php

/**
 * @group gated-content
 */
class test_FrmGatedTokenHelper extends FrmUnitTest {

	/**
	 * WP post ID of a dummy gated content action used across tests.
	 *
	 * @var int
	 */
	private $action_id;

	/**
	 * A content item registered in the dummy action.
	 *
	 * @var array{type: string, id: int}
	 */
	private $item = array(
		'type' => 'post',
		'id'   => 99,
	);

	public function setUp(): void {
		parent::setUp();

		// Create a minimal gated content action post so get_post() resolves it.
		$this->action_id = wp_insert_post(
			array(
				'post_type'    => 'frm_form_actions',
				'post_excerpt' => 'gated_content',
				'post_status'  => 'publish',
				'post_content' => wp_json_encode(
					array(
						'items' => array( $this->item ),
					)
				),
			)
		);
	}

	public function tearDown(): void {
		parent::tearDown();

		// Clean up any URL/cookie state and per-request caches.
		unset( $_GET['access_code'] );
		foreach ( array_keys( $_COOKIE ) as $name ) {
			if ( 0 === strpos( $name, 'frm_gc_' ) ) {
				unset( $_COOKIE[ $name ] );
			}
		}
		$this->reset_helper_caches();
		wp_set_current_user( 0 );
	}

	/**
	 * Clear FrmGatedTokenHelper's per-request static caches so tests are isolated.
	 */
	private function reset_helper_caches() {
		$this->set_private_property( 'FrmGatedTokenHelper', 'row_cache', array() );
		$this->set_private_property( 'FrmGatedTokenHelper', 'generated_tokens', array() );
	}

	// ── generate() ────────────────────────────────────────────────────────── //

	/**
	 * @covers FrmGatedTokenHelper::generate
	 */
	public function test_generate_returns_raw_token_string() {
		$token = FrmGatedTokenHelper::generate( $this->action_id, 1 );

		$this->assertIsString( $token );
		// wp_generate_password(32, false) returns exactly 32 alphanumeric characters.
		$this->assertSame( 32, strlen( $token ) );
	}

	/**
	 * @covers FrmGatedTokenHelper::generate
	 */
	public function test_generate_persists_hash_to_db() {
		global $wpdb;

		$token = FrmGatedTokenHelper::generate( $this->action_id, 1 );
		$hash  = hash( 'sha256', $token );

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}frm_gated_tokens WHERE token_hash = %s",
				$hash
			)
		);

		$this->assertNotNull( $row, 'Token row not found in DB.' );
		$this->assertEquals( $this->action_id, (int) $row->action_id );
	}

	/**
	 * @covers FrmGatedTokenHelper::generate
	 */
	public function test_generate_caches_token_for_same_request() {
		FrmGatedTokenHelper::generate( $this->action_id, 1 );

		$cached = FrmGatedTokenHelper::get_raw_token_for_action( $this->action_id );
		$this->assertNotNull( $cached, 'Token transient not set after generate().' );
		$this->assertIsString( $cached );
	}

	// ── validate_access_code() ───────────────────────────────────────────── //

	/**
	 * @covers FrmGatedTokenHelper::validate_access_code
	 */
	public function test_validate_access_code_returns_token_for_valid_item() {
		$token = FrmGatedTokenHelper::generate( $this->action_id, 1 );

		$this->assertInstanceOf(
			FrmGatedToken::class,
			FrmGatedTokenHelper::validate_access_code( $token, $this->item['type'], $this->item['id'] )
		);
	}

	/**
	 * @covers FrmGatedTokenHelper::validate_access_code
	 */
	public function test_validate_access_code_returns_null_for_wrong_item_id() {
		$token = FrmGatedTokenHelper::generate( $this->action_id, 1 );

		$this->assertNull(
			FrmGatedTokenHelper::validate_access_code( $token, $this->item['type'], 999 )
		);
	}

	/**
	 * @covers FrmGatedTokenHelper::validate_access_code
	 */
	public function test_validate_access_code_returns_null_for_wrong_item_type() {
		$token = FrmGatedTokenHelper::generate( $this->action_id, 1 );

		$this->assertNull(
			FrmGatedTokenHelper::validate_access_code( $token, 'frm_file', $this->item['id'] )
		);
	}

	/**
	 * @covers FrmGatedTokenHelper::validate_access_code
	 */
	public function test_validate_access_code_returns_false_for_expired_token() {
		global $wpdb;

		$token = FrmGatedTokenHelper::generate( $this->action_id, 1 );
		$hash  = hash( 'sha256', $token );

		// Back-date the expiry to the past.
		$wpdb->update(
			$wpdb->prefix . 'frm_gated_tokens',
			array( 'expired_at' => time() - 3600 ),
			array( 'token_hash' => $hash ),
			array( '%d' ),
			array( '%s' )
		);

		// Evict the row cache so validate() reads the updated row.
		$this->reset_helper_caches();

		$this->assertNull(
			FrmGatedTokenHelper::validate_access_code( $token, $this->item['type'], $this->item['id'] )
		);
	}

	/**
	 * @covers FrmGatedTokenHelper::validate_access_code
	 */
	public function test_validate_access_code_returns_null_for_nonexistent_token() {
		$this->assertNull(
			FrmGatedTokenHelper::validate_access_code( 'not-a-real-token', $this->item['type'], $this->item['id'] )
		);
	}

	// ── get_valid_token() — resolution order ──────────────────────────────── //

	/**
	 * When no token source is present, get_valid_token() must return null.
	 *
	 * @covers FrmGatedTokenHelper::get_valid_token
	 */
	public function test_get_valid_token_returns_null_when_no_token_present() {
		$result = FrmGatedTokenHelper::get_valid_token( $this->item['id'], $this->item['type'] );
		$this->assertNull( $result );
	}

	/**
	 * A raw token in the `access_code` URL param must be resolved and validated.
	 *
	 * @covers FrmGatedTokenHelper::get_valid_token
	 */
	public function test_get_valid_token_resolves_via_url_param() {
		$raw_token           = FrmGatedTokenHelper::generate( $this->action_id, 1 );
		$_GET['access_code'] = $raw_token;
		$this->reset_helper_caches();

		$result = FrmGatedTokenHelper::get_valid_token( $this->item['id'], $this->item['type'] );

		$this->assertInstanceOf( 'FrmGatedToken', $result );
	}

	/**
	 * A wrong item ID in the URL param must cause get_valid_token() to return null.
	 *
	 * @covers FrmGatedTokenHelper::get_valid_token
	 */
	public function test_get_valid_token_returns_null_for_url_param_with_wrong_item() {
		$raw_token           = FrmGatedTokenHelper::generate( $this->action_id, 1 );
		$_GET['access_code'] = $raw_token;
		$this->reset_helper_caches();

		// Request a different item ID than the one in the action.
		$result = FrmGatedTokenHelper::get_valid_token( 9999, $this->item['type'] );

		$this->assertNull( $result );
	}

	/**
	 * A hash stored in an frm_gc_* cookie must be resolved and validated.
	 *
	 * @covers FrmGatedTokenHelper::get_valid_token
	 */
	public function test_get_valid_token_resolves_via_cookie() {
		$raw_token = FrmGatedTokenHelper::generate( $this->action_id, 1 );
		$hash      = hash( 'sha256', $raw_token );

		$_COOKIE[ 'frm_gc_' . $this->action_id ] = $hash;
		$this->reset_helper_caches();

		$result = FrmGatedTokenHelper::get_valid_token( $this->item['id'], $this->item['type'] );

		$this->assertInstanceOf( 'FrmGatedToken', $result );
	}

	/**
	 * A token stored in the DB under the current user's ID must be found and validated.
	 *
	 * @covers FrmGatedTokenHelper::get_valid_token
	 */
	public function test_get_valid_token_resolves_via_user_db() {
		$user_id = $this->factory->user->create();

		// Generate token tied to this user (simulates Registration add-on behaviour).
		FrmGatedTokenHelper::generate( $this->action_id, 1, $user_id );

		// Log in as that user; clear caches so the URL/cookie paths do not interfere.
		wp_set_current_user( $user_id );
		$this->reset_helper_caches();

		$result = FrmGatedTokenHelper::get_valid_token( $this->item['id'], $this->item['type'] );

		$this->assertInstanceOf( 'FrmGatedToken', $result );
	}

	/**
	 * When no core source finds a token, the frm_obtain_gated_token filter fires and
	 * its return value is used.
	 *
	 * @covers FrmGatedTokenHelper::get_valid_token
	 */
	public function test_get_valid_token_falls_back_to_filter() {
		$raw_token   = FrmGatedTokenHelper::generate( $this->action_id, 1 );
		$hash        = hash( 'sha256', $raw_token );
		$row         = FrmGatedTokenHelper::get_row_by_hash( $hash );
		$stub_token  = new FrmGatedToken( $row );

		add_filter(
			'frm_obtain_gated_token',
			static function () use ( $stub_token ) {
				return $stub_token;
			},
			10
		);

		$this->reset_helper_caches();
		$result = FrmGatedTokenHelper::get_valid_token( $this->item['id'], $this->item['type'] );

		remove_all_filters( 'frm_obtain_gated_token' );

		$this->assertSame( $stub_token, $result );
	}

	// ── Validation transient cache ────────────────────────────────────────── //

	/**
	 * set_validation_cache() must persist a transient that get_cached_validation() retrieves.
	 *
	 * @covers FrmGatedTokenHelper::set_validation_cache
	 * @covers FrmGatedTokenHelper::get_cached_validation
	 */
	public function test_set_and_get_cached_validation() {
		$hash = str_repeat( 'a', 64 ); // synthetic 64-char hex hash

		FrmGatedTokenHelper::set_validation_cache( $hash, $this->item['type'], $this->item['id'], null, $this->action_id );

		$cached = FrmGatedTokenHelper::get_cached_validation( $hash, $this->item['type'], $this->item['id'] );

		$this->assertIsArray( $cached );
		$this->assertSame( $this->item['type'], $cached['item_type'] );
		$this->assertSame( $this->item['id'], $cached['item_id'] );
		$this->assertSame( $this->action_id, $cached['action_id'] );
		$this->assertNull( $cached['expired_at'] );
	}

	/**
	 * get_cached_validation() must return null when no transient exists for the given key.
	 *
	 * @covers FrmGatedTokenHelper::get_cached_validation
	 */
	public function test_get_cached_validation_returns_null_on_miss() {
		$result = FrmGatedTokenHelper::get_cached_validation( 'not-a-stored-hash', $this->item['type'], $this->item['id'] );
		$this->assertNull( $result );
	}

	/**
	 * set_validation_cache() uses remaining token lifetime as TTL when expired_at is set.
	 *
	 * @covers FrmGatedTokenHelper::set_validation_cache
	 */
	public function test_set_validation_cache_stores_with_future_expiry() {
		$hash      = str_repeat( 'b', 64 );
		$future    = time() + 3600;

		FrmGatedTokenHelper::set_validation_cache( $hash, $this->item['type'], $this->item['id'], $future, $this->action_id );

		$cached = FrmGatedTokenHelper::get_cached_validation( $hash, $this->item['type'], $this->item['id'] );
		$this->assertIsArray( $cached );
		$this->assertSame( $future, $cached['expired_at'] );
	}

	/**
	 * delete_validation_cache_for_token() must remove the transient for every item in the action.
	 *
	 * @covers FrmGatedTokenHelper::delete_validation_cache_for_token
	 */
	public function test_delete_validation_cache_for_token_removes_entries() {
		$raw_token = FrmGatedTokenHelper::generate( $this->action_id, 1 );
		$hash      = hash( 'sha256', $raw_token );

		FrmGatedTokenHelper::set_validation_cache( $hash, $this->item['type'], $this->item['id'], null, $this->action_id );
		$this->assertNotNull(
			FrmGatedTokenHelper::get_cached_validation( $hash, $this->item['type'], $this->item['id'] ),
			'Pre-condition: cache must exist before delete.'
		);

		FrmGatedTokenHelper::delete_validation_cache_for_token( $hash, $this->action_id );

		$this->assertNull(
			FrmGatedTokenHelper::get_cached_validation( $hash, $this->item['type'], $this->item['id'] ),
			'Cache entry must be gone after delete_validation_cache_for_token().'
		);
	}

	/**
	 * delete_validation_cache_for_token() resolves action_id from the DB when it is not supplied.
	 *
	 * @covers FrmGatedTokenHelper::delete_validation_cache_for_token
	 */
	public function test_delete_validation_cache_for_token_resolves_action_id_from_db() {
		$raw_token = FrmGatedTokenHelper::generate( $this->action_id, 1 );
		$hash      = hash( 'sha256', $raw_token );

		FrmGatedTokenHelper::set_validation_cache( $hash, $this->item['type'], $this->item['id'], null, $this->action_id );

		// No action_id passed — must look it up from the token row.
		FrmGatedTokenHelper::delete_validation_cache_for_token( $hash );

		$this->assertNull(
			FrmGatedTokenHelper::get_cached_validation( $hash, $this->item['type'], $this->item['id'] )
		);
	}

	/**
	 * delete_validation_cache_for_action() must remove transients for all tokens belonging to the action.
	 *
	 * @covers FrmGatedTokenHelper::delete_validation_cache_for_action
	 */
	public function test_delete_validation_cache_for_action_removes_all_token_entries() {
		// Two tokens for the same action.
		$raw1  = FrmGatedTokenHelper::generate( $this->action_id, 1 );
		$raw2  = FrmGatedTokenHelper::generate( $this->action_id, 2 );
		$hash1 = hash( 'sha256', $raw1 );
		$hash2 = hash( 'sha256', $raw2 );

		FrmGatedTokenHelper::set_validation_cache( $hash1, $this->item['type'], $this->item['id'], null, $this->action_id );
		FrmGatedTokenHelper::set_validation_cache( $hash2, $this->item['type'], $this->item['id'], null, $this->action_id );

		$this->assertNotNull( FrmGatedTokenHelper::get_cached_validation( $hash1, $this->item['type'], $this->item['id'] ) );
		$this->assertNotNull( FrmGatedTokenHelper::get_cached_validation( $hash2, $this->item['type'], $this->item['id'] ) );

		FrmGatedTokenHelper::delete_validation_cache_for_action( $this->action_id );

		$this->assertNull(
			FrmGatedTokenHelper::get_cached_validation( $hash1, $this->item['type'], $this->item['id'] ),
			'Cache for token 1 must be deleted by delete_validation_cache_for_action().'
		);
		$this->assertNull(
			FrmGatedTokenHelper::get_cached_validation( $hash2, $this->item['type'], $this->item['id'] ),
			'Cache for token 2 must be deleted by delete_validation_cache_for_action().'
		);
	}
}
