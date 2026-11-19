<?php
/**
 * Creates and verifies ALTCHA v3 proof-of-work challenges.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * ALTCHA challenge protocol, compatible with the altcha 3.x widget.
 *
 * The server picks a secret counter, derives a key from the nonce, salt and counter with
 * PBKDF2, and publishes the first half of that key as the key prefix. The widget can only
 * find the counter by deriving keys for counter 0, 1, 2 and so on until one starts with the
 * prefix. That search is the proof of work.
 *
 * Every challenge parameter is signed with HMAC so a visitor cannot lower the cost, move
 * the expiry, or pick their own prefix. The parameters also carry a key signature, an HMAC
 * of the full derived key, so a solution is verified with two HMACs instead of repeating
 * the PBKDF2 derivation.
 *
 * @since x.x
 */
class FrmAltchaChallengeHelper {

	/**
	 * Key derivation algorithm. PBKDF2 is native in both PHP and the browser's Web Crypto API.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	const ALGORITHM = 'PBKDF2/SHA-256';

	/**
	 * Derived key length in bytes.
	 *
	 * @since x.x
	 *
	 * @var int
	 */
	const KEY_LENGTH = 32;

	/**
	 * PBKDF2 iterations for each guess the widget makes.
	 *
	 * @since x.x
	 *
	 * @var int
	 */
	const COST = 100;

	/**
	 * Largest counter the widget's uint32 counter mode can encode.
	 *
	 * @since x.x
	 *
	 * @var int
	 */
	const MAX_COUNTER = 4294967295;

	/**
	 * Longest base64 payload accepted. A genuine payload is well under 1 KB.
	 *
	 * @since x.x
	 *
	 * @var int
	 */
	const MAX_PAYLOAD_LENGTH = 4096;

	/**
	 * Option holding the site's master secret.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	const SECRET_OPTION = 'frm_altcha_secret';

	/**
	 * Create a signed challenge for the widget.
	 *
	 * @since x.x
	 *
	 * @param array $args {
	 *     Optional overrides, mainly for tests.
	 *
	 *     @type int $max_number Largest secret counter.
	 *     @type int $expires    Lifetime in seconds.
	 *     @type int $cost       PBKDF2 iterations per guess.
	 *     @type int $counter    A fixed counter instead of a random one.
	 * }
	 *
	 * @return array{parameters: array<string, int|string>, signature: string}
	 */
	public static function create( $args = array() ) {
		$settings = FrmAltchaHelper::get_settings();
		$levels   = FrmAltchaHelper::difficulty_levels();
		$defaults = array(
			'max_number' => $levels[ $settings['difficulty'] ],
			'expires'    => $settings['expires'] * MINUTE_IN_SECONDS,
			'cost'       => self::COST,
		);

		/**
		 * Filters the proof-of-work settings for a new ALTCHA challenge.
		 *
		 * @since x.x
		 *
		 * @param array $args {
		 *
		 *     @type int $max_number Largest secret counter. Visitors try about half of this on average.
		 *     @type int $expires    Seconds before the challenge expires.
		 *     @type int $cost       PBKDF2 iterations per guess.
		 * }
		 */
		$args = apply_filters( 'frm_altcha_challenge_args', array_merge( $defaults, $args ) );

		$max_number = max( 1, min( self::MAX_COUNTER, (int) $args['max_number'] ) );
		$cost       = max( 1, (int) $args['cost'] );
		$counter    = isset( $args['counter'] ) ? (int) $args['counter'] : random_int( (int) ceil( $max_number / 2 ), $max_number );
		$nonce      = random_bytes( 16 );
		$salt       = random_bytes( 16 );
		$key        = self::derive_key( $nonce, $salt, $counter, $cost );

		$parameters = array(
			'algorithm'    => self::ALGORITHM,
			'cost'         => $cost,
			'expiresAt'    => time() + max( 1, (int) $args['expires'] ),
			'keyLength'    => self::KEY_LENGTH,
			'keyPrefix'    => bin2hex( substr( $key, 0, intdiv( self::KEY_LENGTH, 2 ) ) ),
			'keySignature' => self::hmac( $key, 'key' ),
			'nonce'        => bin2hex( $nonce ),
			'salt'         => bin2hex( $salt ),
		);

		return array(
			'parameters' => $parameters,
			'signature'  => self::hmac( self::canonical_json( $parameters ), 'signature' ),
		);
	}

	/**
	 * Verify a payload submitted by the widget.
	 *
	 * This checks the signature, expiry and solution only. Replay protection is separate, see
	 * FrmAltchaReplayHelper, so a payload can be checked before it is spent.
	 *
	 * @since x.x
	 *
	 * @param mixed $payload Base64 encoded JSON from the widget's hidden input.
	 *
	 * @return array{verified: bool, reason: string, id: string, expires: int}
	 */
	public static function verify( $payload ) {
		$result = array(
			'verified' => false,
			'reason'   => 'malformed',
			'id'       => '',
			'expires'  => 0,
		);

		if ( ! is_string( $payload ) || '' === $payload ) {
			$result['reason'] = 'missing';
			return $result;
		}

		$data = self::decode_payload( $payload );

		if ( false === $data ) {
			return $result;
		}

		$parameters = $data['challenge']['parameters'];
		$signature  = $data['challenge']['signature'];
		$solution   = $data['solution'];

		// The ID is derived from the signature, which is unique per challenge, for the replay guard.
		$result['id']      = hash( 'sha256', $signature );
		$result['expires'] = (int) $parameters['expiresAt'];

		if ( ! hash_equals( self::hmac( self::canonical_json( $parameters ), 'signature' ), $signature ) ) {
			$result['reason'] = 'invalid-signature';
			return $result;
		}

		// Only reject an expired challenge after the signature proves the expiry is genuine.
		if ( $result['expires'] < time() ) {
			$result['reason'] = 'expired';
			return $result;
		}

		$derived_key = hex2bin( $solution['derivedKey'] );

		if ( false === $derived_key || ! hash_equals( (string) $parameters['keySignature'], self::hmac( $derived_key, 'key' ) ) ) {
			$result['reason'] = 'invalid-solution';
			return $result;
		}

		$result['verified'] = true;
		$result['reason']   = '';

		return $result;
	}

	/**
	 * Decode and shape-check a payload. Anything unexpected is rejected before any HMAC work.
	 *
	 * @since x.x
	 *
	 * @param string $payload Base64 encoded JSON.
	 *
	 * @return array|false
	 */
	private static function decode_payload( $payload ) {
		if ( strlen( $payload ) > self::MAX_PAYLOAD_LENGTH ) {
			return false;
		}

		$json = base64_decode( $payload, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

		if ( false === $json ) {
			return false;
		}

		$data = json_decode( $json, true, 5 );

		if ( ! is_array( $data ) || ! isset( $data['challenge'], $data['solution'] ) || ! is_array( $data['challenge'] ) || ! is_array( $data['solution'] ) ) {
			return false;
		}

		$challenge = $data['challenge'];

		if ( ! isset( $challenge['parameters'], $challenge['signature'] ) || ! is_array( $challenge['parameters'] ) || ! self::is_hex( $challenge['signature'], 64 ) ) {
			return false;
		}

		if ( ! self::valid_parameters( $challenge['parameters'] ) ) {
			return false;
		}

		$solution = $data['solution'];
		$counter  = $solution['counter'] ?? null;

		if ( ! is_int( $counter ) || $counter < 0 || $counter > self::MAX_COUNTER || ! self::is_hex( $solution['derivedKey'] ?? null, self::KEY_LENGTH * 2 ) ) {
			return false;
		}

		return $data;
	}

	/**
	 * Check that the parameters are the ones this class issues, with the expected types.
	 *
	 * The signature check is what proves they are genuine. This only stops type juggling.
	 *
	 * @since x.x
	 *
	 * @param array $parameters Challenge parameters from the payload.
	 *
	 * @return bool
	 */
	private static function valid_parameters( $parameters ) {
		$expected = array( 'algorithm', 'cost', 'expiresAt', 'keyLength', 'keyPrefix', 'keySignature', 'nonce', 'salt' );
		$keys     = array_keys( $parameters );
		sort( $keys );

		if ( $keys !== $expected ) {
			return false;
		}

		return self::ALGORITHM === $parameters['algorithm']
		&& self::KEY_LENGTH === $parameters['keyLength']
		&& is_int( $parameters['cost'] )
		&& is_int( $parameters['expiresAt'] )
		&& self::is_hex( $parameters['keyPrefix'], self::KEY_LENGTH )
		&& self::is_hex( $parameters['keySignature'], 64 )
		&& self::is_hex( $parameters['nonce'], 32 )
		&& self::is_hex( $parameters['salt'], 32 );
	}

	/**
	 * Whether a value is a lowercase hex string of an exact length.
	 *
	 * @since x.x
	 *
	 * @param mixed $value  Value to check.
	 * @param int   $length Expected number of hex characters.
	 *
	 * @return bool
	 */
	private static function is_hex( $value, $length ) {
		return is_string( $value ) && strlen( $value ) === $length && 1 === preg_match( '/^[0-9a-f]+$/', $value );
	}

	/**
	 * Derive the key the widget searches for. Matches the widget's PBKDF2 worker in uint32 counter mode.
	 *
	 * @since x.x
	 *
	 * @param string $nonce   Raw nonce bytes.
	 * @param string $salt    Raw salt bytes.
	 * @param int    $counter Secret counter.
	 * @param int    $cost    PBKDF2 iterations.
	 *
	 * @return string Raw key bytes.
	 */
	public static function derive_key( $nonce, $salt, $counter, $cost ) {
		// The password is the nonce followed by the counter as a big-endian 32-bit integer.
		return hash_pbkdf2( 'sha256', $nonce . pack( 'N', $counter ), $salt, $cost, self::KEY_LENGTH, true );
	}

	/**
	 * JSON with sorted keys, the same canonical form the ALTCHA libraries sign.
	 *
	 * @since x.x
	 *
	 * @param array $parameters Challenge parameters.
	 *
	 * @return string
	 */
	private static function canonical_json( $parameters ) {
		ksort( $parameters );
		return (string) wp_json_encode( $parameters, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	}

	/**
	 * HMAC-SHA256 as hex, with a purpose-specific key derived from the site secret.
	 *
	 * Separate keys for the parameter signature and the key signature mean one can never
	 * be passed off as the other.
	 *
	 * @since x.x
	 *
	 * @param string $data    Data to sign.
	 * @param string $purpose Either 'signature' or 'key'.
	 *
	 * @return string
	 */
	private static function hmac( $data, $purpose ) {
		$key = hash_hmac( 'sha256', 'frm-altcha-' . $purpose, self::secret(), true );
		return hash_hmac( 'sha256', $data, $key );
	}

	/**
	 * The site's master secret, created on first use. It is never output.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	private static function secret() {
		$secret = get_option( self::SECRET_OPTION );

		if ( is_string( $secret ) && 64 === strlen( $secret ) ) {
			return $secret;
		}

		$secret = bin2hex( random_bytes( 32 ) );

		if ( add_option( self::SECRET_OPTION, $secret, '', false ) ) {
			return $secret;
		}

		// Another request created the secret first, so use that one.
		wp_cache_delete( self::SECRET_OPTION, 'options' );
		$stored = get_option( self::SECRET_OPTION );

		if ( is_string( $stored ) && 64 === strlen( $stored ) ) {
			return $stored;
		}

		// The stored value is unusable, so replace it.
		update_option( self::SECRET_OPTION, $secret, false );

		return $secret;
	}
}
