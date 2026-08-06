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
	 * WP_Post object for the dummy gated content action.
	 *
	 * @var WP_Post
	 */
	private $action;

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
		$this->action    = get_post( $this->action_id );
	}

	public function tearDown(): void {
		parent::tearDown();

		// Clean up any URL/cookie state and per-request caches.
		unset( $_GET['access_code'] );

		foreach ( array_keys( $_COOKIE ) as $name ) {
			if ( str_starts_with( $name, 'frm_gc_' ) ) {
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
		$token = FrmGatedTokenHelper::generate( $this->action, (object) array( 'id' => 1 ), 'create' );

		$this->assertIsString( $token );
		// wp_generate_password(32, false) returns exactly 32 alphanumeric characters.
		$this->assertSame( 32, strlen( $token ) );
	}

	/**
	 * @covers FrmGatedTokenHelper::generate
	 */
	public function test_generate_persists_hash_to_db() {
		global $wpdb;

		$token = FrmGatedTokenHelper::generate( $this->action, (object) array( 'id' => 1 ), 'create' );

		$row = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM %i WHERE token_hash = %s',
				$wpdb->prefix . 'frm_gated_tokens',
				FrmGatedTokenHelper::hash_token( $token )
			)
		);

		$this->assertNotNull( $row, 'Token row not found in DB.' );
		$this->assertSame( $this->action_id, (int) $row->action_id );
	}

	/**
	 * @covers FrmGatedTokenHelper::generate
	 */
	public function test_generate_caches_token_for_same_request() {
		FrmGatedTokenHelper::generate( $this->action, (object) array( 'id' => 1 ), 'create' );

		$cached = FrmGatedTokenHelper::get_raw_token_for_action( $this->action_id );
		$this->assertNotNull( $cached, 'Token transient not set after generate().' );
		$this->assertIsString( $cached );
	}

	// ── validate_access_code() ───────────────────────────────────────────── //

	/**
	 * @covers FrmGatedTokenHelper::validate_access_code
	 */
	public function test_validate_access_code_returns_token_for_valid_item() {
		$token = FrmGatedTokenHelper::generate( $this->action, (object) array( 'id' => 1 ), 'create' );

		$this->assertInstanceOf(
			FrmGatedToken::class,
			FrmGatedTokenHelper::validate_access_code( $token, FrmGatedItem::make( $this->item ) )
		);
	}

	/**
	 * @covers FrmGatedTokenHelper::validate_access_code
	 */
	public function test_validate_access_code_returns_null_for_wrong_item_id() {
		$token = FrmGatedTokenHelper::generate( $this->action, (object) array( 'id' => 1 ), 'create' );

		$wrong_id_item = FrmGatedItem::make(
			array(
				'type' => $this->item['type'],
				'id'   => 999,
			)
		);
		$this->assertNotInstanceOf(
			\FrmGatedToken::class,
			FrmGatedTokenHelper::validate_access_code( $token, $wrong_id_item )
		);
	}

	/**
	 * @covers FrmGatedTokenHelper::validate_access_code
	 */
	public function test_validate_access_code_returns_null_for_wrong_item_type() {
		$token = FrmGatedTokenHelper::generate( $this->action, (object) array( 'id' => 1 ), 'create' );

		$wrong_type_item = FrmGatedItem::make(
			array(
				'type' => 'frm_file',
				'id'   => $this->item['id'],
			)
		);
		$this->assertNotInstanceOf(
			\FrmGatedToken::class,
			FrmGatedTokenHelper::validate_access_code( $token, $wrong_type_item )
		);
	}

	/**
	 * @covers FrmGatedTokenHelper::validate_access_code
	 */
	public function test_validate_access_code_returns_false_for_expired_token() {
		global $wpdb;

		$token = FrmGatedTokenHelper::generate( $this->action, (object) array( 'id' => 1 ), 'create' );

		// Back-date the expiry to the past.
		$wpdb->update(
			$wpdb->prefix . 'frm_gated_tokens',
			array( 'expired_at' => time() - 3600 ),
			array( 'token_hash' => FrmGatedTokenHelper::hash_token( $token ) ),
			array( '%d' ),
			array( '%s' )
		);

		// Evict the row cache so validate() reads the updated row.
		$this->reset_helper_caches();

		$this->assertNotInstanceOf(
			\FrmGatedToken::class,
			FrmGatedTokenHelper::validate_access_code( $token, FrmGatedItem::make( $this->item ) )
		);
	}

	/**
	 * @covers FrmGatedTokenHelper::validate_access_code
	 */
	public function test_validate_access_code_returns_null_for_nonexistent_token() {
		$this->assertNotInstanceOf(
			\FrmGatedToken::class,
			FrmGatedTokenHelper::validate_access_code( 'not-a-real-token', FrmGatedItem::make( $this->item ) )
		);
	}

	// ── get_valid_token() — resolution order ──────────────────────────────── //

	/**
	 * When no token source is present, get_valid_token() must return null.
	 *
	 * @covers FrmGatedTokenHelper::get_valid_token
	 */
	public function test_get_valid_token_returns_null_when_no_token_present() {
		$result = FrmGatedTokenHelper::get_valid_token( FrmGatedItem::make( $this->item ) );
		$this->assertNotInstanceOf( \FrmGatedToken::class, $result );
	}

	/**
	 * A raw token in the `access_code` URL param must be resolved and validated.
	 *
	 * @covers FrmGatedTokenHelper::get_valid_token
	 */
	public function test_get_valid_token_resolves_via_url_param() {
		$_GET['access_code'] = FrmGatedTokenHelper::generate( $this->action, (object) array( 'id' => 1 ), 'create' );
		$this->reset_helper_caches();

		$result = FrmGatedTokenHelper::get_valid_token( FrmGatedItem::make( $this->item ) );

		$this->assertInstanceOf( 'FrmGatedToken', $result );
	}

	/**
	 * A wrong item ID in the URL param must cause get_valid_token() to return null.
	 *
	 * @covers FrmGatedTokenHelper::get_valid_token
	 */
	public function test_get_valid_token_returns_null_for_url_param_with_wrong_item() {
		$_GET['access_code'] = FrmGatedTokenHelper::generate( $this->action, (object) array( 'id' => 1 ), 'create' );
		$this->reset_helper_caches();

		// Request a different item ID than the one in the action.
		$wrong_item = FrmGatedItem::make(
			array(
				'type' => $this->item['type'],
				'id'   => 9999,
			)
		);
		$result     = FrmGatedTokenHelper::get_valid_token( $wrong_item );

		$this->assertNotInstanceOf( \FrmGatedToken::class, $result );
	}

	/**
	 * A hash stored in an frm_gc_* cookie must be resolved and validated.
	 *
	 * @covers FrmGatedTokenHelper::get_valid_token
	 */
	public function test_get_valid_token_resolves_via_cookie() {
		$_COOKIE[ 'frm_gc_' . $this->item['type'] . '_' . $this->item['id'] ] = FrmGatedTokenHelper::generate( $this->action, (object) array( 'id' => 1 ), 'create' );
		$this->reset_helper_caches();

		$result = FrmGatedTokenHelper::get_valid_token( FrmGatedItem::make( $this->item ) );

		$this->assertInstanceOf( 'FrmGatedToken', $result );
	}

	/**
	 * A token stored in the DB under the current user's ID must be found and validated.
	 *
	 * @covers FrmGatedTokenHelper::get_valid_token
	 */
	public function test_get_valid_token_resolves_via_user_db() {
		$user_id = $this->factory->user->create();

		// Log in as the user before generating so generate() captures the user_id.
		wp_set_current_user( $user_id );
		FrmGatedTokenHelper::generate( $this->action, (object) array( 'id' => 1 ), 'create' );

		// Clear caches so the URL/cookie paths do not interfere.
		$this->reset_helper_caches();

		$result = FrmGatedTokenHelper::get_valid_token( FrmGatedItem::make( $this->item ) );

		$this->assertInstanceOf( 'FrmGatedToken', $result );
	}

	/**
	 * When no core source finds a token, the frm_obtain_gated_token filter fires and
	 * its return value is used.
	 *
	 * @covers FrmGatedTokenHelper::get_valid_token
	 */
	public function test_get_valid_token_falls_back_to_filter() {
		$raw_token  = FrmGatedTokenHelper::generate( $this->action, (object) array( 'id' => 1 ), 'create' );
		$hash       = FrmGatedTokenHelper::hash_token( $raw_token );
		$row        = FrmGatedTokenHelper::get_row_by_hash( $hash );
		$stub_token = new FrmGatedToken( $row );

		add_filter(
			'frm_obtain_gated_token',
			static function () use ( $stub_token ) {
				return $stub_token;
			},
			10
		);

		$this->reset_helper_caches();
		$result = FrmGatedTokenHelper::get_valid_token( FrmGatedItem::make( $this->item ) );

		remove_all_filters( 'frm_obtain_gated_token' );

		$this->assertSame( $stub_token, $result );
	}
}
