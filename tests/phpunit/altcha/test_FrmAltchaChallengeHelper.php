<?php
/**
 * Challenge creation and verification.
 *
 * @package Formidable
 */

require_once __DIR__ . '/FrmAltchaTestHelper.php';

/**
 * @group altcha
 */
class test_FrmAltchaChallengeHelper extends FrmUnitTest {

	/**
	 * @covers FrmAltchaChallengeHelper::create
	 */
	public function test_create_returns_signed_widget_parameters() {
		$challenge = FrmAltchaChallengeHelper::create();
		$params    = $challenge['parameters'];

		$this->assertSame( array( 'parameters', 'signature' ), array_keys( $challenge ) );
		$this->assertSame( array( 'algorithm', 'cost', 'expiresAt', 'keyLength', 'keyPrefix', 'keySignature', 'nonce', 'salt' ), array_keys( $params ) );
		$this->assertSame( 'PBKDF2/SHA-256', $params['algorithm'] );
		$this->assertSame( 32, $params['keyLength'] );
		$this->assertSame( 32, strlen( $params['keyPrefix'] ) );
		$this->assertMatchesRegularExpression( '/^[0-9a-f]{64}$/', $challenge['signature'] );
		$this->assertEqualsWithDelta( time() + 20 * MINUTE_IN_SECONDS, $params['expiresAt'], 5 );
	}

	/**
	 * Two challenges never share a nonce, salt or signature.
	 *
	 * @covers FrmAltchaChallengeHelper::create
	 */
	public function test_challenges_are_unique() {
		$first  = FrmAltchaChallengeHelper::create();
		$second = FrmAltchaChallengeHelper::create();

		$this->assertNotSame( $first['parameters']['nonce'], $second['parameters']['nonce'] );
		$this->assertNotSame( $first['parameters']['salt'], $second['parameters']['salt'] );
		$this->assertNotSame( $first['signature'], $second['signature'] );
	}

	/**
	 * @covers FrmAltchaChallengeHelper::verify
	 */
	public function test_solved_challenge_verifies() {
		$challenge = FrmAltchaTestHelper::challenge();
		$result    = FrmAltchaChallengeHelper::verify( FrmAltchaTestHelper::solve( $challenge ) );

		$this->assertTrue( $result['verified'] );
		$this->assertSame( '', $result['reason'] );
		$this->assertSame( hash( 'sha256', $challenge['signature'] ), $result['id'] );
		$this->assertSame( $challenge['parameters']['expiresAt'], $result['expires'] );
	}

	/**
	 * The key prefix is the first half of the derived key, so a search finds the counter.
	 *
	 * @covers FrmAltchaChallengeHelper::create
	 * @covers FrmAltchaChallengeHelper::derive_key
	 */
	public function test_key_prefix_matches_secret_counter() {
		$challenge = FrmAltchaTestHelper::challenge();
		$params    = $challenge['parameters'];
		$found     = null;

		for ( $counter = 0; $counter <= 10; $counter++ ) {
			$key = bin2hex( FrmAltchaChallengeHelper::derive_key( hex2bin( $params['nonce'] ), hex2bin( $params['salt'] ), $counter, $params['cost'] ) );

			if ( str_starts_with( $key, $params['keyPrefix'] ) ) {
				$found = $counter;
				break;
			}
		}

		$this->assertSame( FrmAltchaTestHelper::COUNTER, $found );
	}

	/**
	 * Lowering the cost, moving the expiry or swapping the prefix breaks the signature.
	 *
	 * @covers FrmAltchaChallengeHelper::verify
	 */
	public function test_tampered_parameters_are_rejected() {
		$payload = FrmAltchaTestHelper::solve( FrmAltchaTestHelper::challenge() );
		$changes = array(
			'cost'      => 2,
			'expiresAt' => time() + DAY_IN_SECONDS,
			'keyPrefix' => str_repeat( '0', 32 ),
		);

		foreach ( $changes as $key => $value ) {
			$data                                    = FrmAltchaTestHelper::decode( $payload );
			$data['challenge']['parameters'][ $key ] = $value;
			$result                                  = FrmAltchaChallengeHelper::verify( FrmAltchaTestHelper::encode( $data ) );

			$this->assertFalse( $result['verified'], $key );
			$this->assertSame( 'invalid-signature', $result['reason'], $key );
		}
	}

	/**
	 * @covers FrmAltchaChallengeHelper::verify
	 */
	public function test_wrong_solution_is_rejected() {
		$challenge     = FrmAltchaTestHelper::challenge();
		$wrong_counter = FrmAltchaChallengeHelper::verify( FrmAltchaTestHelper::solve( $challenge, FrmAltchaTestHelper::COUNTER + 1 ) );
		$this->assertFalse( $wrong_counter['verified'] );
		$this->assertSame( 'invalid-solution', $wrong_counter['reason'] );

		$data                           = FrmAltchaTestHelper::decode( FrmAltchaTestHelper::solve( $challenge ) );
		$data['solution']['derivedKey'] = str_repeat( 'a', 64 );
		$guessed                        = FrmAltchaChallengeHelper::verify( FrmAltchaTestHelper::encode( $data ) );
		$this->assertFalse( $guessed['verified'] );
		$this->assertSame( 'invalid-solution', $guessed['reason'] );
	}

	/**
	 * A challenge signed by another site is rejected.
	 *
	 * @covers FrmAltchaChallengeHelper::verify
	 */
	public function test_other_site_secret_is_rejected() {
		$payload = FrmAltchaTestHelper::solve( FrmAltchaTestHelper::challenge() );

		update_option( FrmAltchaChallengeHelper::SECRET_OPTION, str_repeat( 'b', 64 ), false );
		$result = FrmAltchaChallengeHelper::verify( $payload );

		$this->assertFalse( $result['verified'] );
		$this->assertSame( 'invalid-signature', $result['reason'] );
	}

	/**
	 * @covers FrmAltchaChallengeHelper::verify
	 */
	public function test_expired_challenge_is_rejected() {
		$payload = FrmAltchaTestHelper::solve( FrmAltchaTestHelper::challenge( array( 'expires' => 1 ) ) );
		sleep( 2 );

		$result = FrmAltchaChallengeHelper::verify( $payload );

		$this->assertFalse( $result['verified'] );
		$this->assertSame( 'expired', $result['reason'] );
	}

	/**
	 * @covers FrmAltchaChallengeHelper::verify
	 */
	public function test_malformed_payloads_are_rejected() {
		$valid           = FrmAltchaTestHelper::decode( FrmAltchaTestHelper::solve( FrmAltchaTestHelper::challenge() ) );
		$string_counter  = $valid;
		$extra_parameter = $valid;
		$upper_key       = $valid;

		$string_counter['solution']['counter']           = '7';
		$extra_parameter['challenge']['parameters']['x'] = 1;
		$upper_key['solution']['derivedKey']             = strtoupper( $valid['solution']['derivedKey'] );

		$payloads = array(
			'missing'        => '',
			'array'          => array( 'a' ),
			'not base64'     => '%%%',
			'not json'       => base64_encode( 'nope' ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			'widget test'    => FrmAltchaTestHelper::encode(
				array(
				'challenge' => null,
				'solution'  => null,
				'test'      => true,
				)
			),
			'v1 payload'     => FrmAltchaTestHelper::encode(
				array(
				'algorithm' => 'SHA-256',
				'challenge' => 'a',
				'number'    => 1,
				'salt'      => 's',
				'signature' => 'x',
				)
			),
			'string counter' => FrmAltchaTestHelper::encode( $string_counter ),
			'extra param'    => FrmAltchaTestHelper::encode( $extra_parameter ),
			'upper key'      => FrmAltchaTestHelper::encode( $upper_key ),
			'too long'       => str_repeat( 'A', FrmAltchaChallengeHelper::MAX_PAYLOAD_LENGTH + 4 ),
		);

		foreach ( $payloads as $label => $payload ) {
			$result = FrmAltchaChallengeHelper::verify( $payload );
			$this->assertFalse( $result['verified'], $label );
			$this->assertContains( $result['reason'], array( 'missing', 'malformed' ), $label );
		}
	}
}
