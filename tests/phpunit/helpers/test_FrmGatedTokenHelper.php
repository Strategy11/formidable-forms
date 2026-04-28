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
		'type' => 'page',
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

	// ── generate() ────────────────────────────────────────────────────────── //

	/**
	 * @covers FrmGatedTokenHelper::generate
	 */
	public function test_generate_returns_raw_token_string() {
		$token = FrmGatedTokenHelper::generate( $this->action_id, 1 );

		$this->assertIsString( $token );
		$this->assertSame( 48, strlen( $token ) );
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

	// ── validate_hash() ───────────────────────────────────────────────────── //

	/**
	 * @covers FrmGatedTokenHelper::validate_hash
	 */
	public function test_validate_hash_returns_true_for_valid_item() {
		$token = FrmGatedTokenHelper::generate( $this->action_id, 1 );
		$hash  = hash( 'sha256', $token );

		$this->assertTrue(
			FrmGatedTokenHelper::validate_hash( $hash, $this->item['id'], $this->item['type'] )
		);
	}

	/**
	 * @covers FrmGatedTokenHelper::validate_hash
	 */
	public function test_validate_hash_returns_false_for_wrong_item_id() {
		$token = FrmGatedTokenHelper::generate( $this->action_id, 1 );
		$hash  = hash( 'sha256', $token );

		$this->assertFalse(
			FrmGatedTokenHelper::validate_hash( $hash, 999, $this->item['type'] )
		);
	}

	/**
	 * @covers FrmGatedTokenHelper::validate_hash
	 */
	public function test_validate_hash_returns_false_for_wrong_item_type() {
		$token = FrmGatedTokenHelper::generate( $this->action_id, 1 );
		$hash  = hash( 'sha256', $token );

		$this->assertFalse(
			FrmGatedTokenHelper::validate_hash( $hash, $this->item['id'], 'frm_file' )
		);
	}

	/**
	 * @covers FrmGatedTokenHelper::validate_hash
	 */
	public function test_validate_hash_returns_false_for_expired_token() {
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

		$this->assertFalse(
			FrmGatedTokenHelper::validate_hash( $hash, $this->item['id'], $this->item['type'] )
		);
	}

	/**
	 * @covers FrmGatedTokenHelper::validate_hash
	 */
	public function test_validate_hash_returns_false_for_nonexistent_hash() {
		$this->assertFalse(
			FrmGatedTokenHelper::validate_hash( 'not-a-real-hash', $this->item['id'], $this->item['type'] )
		);
	}
}
