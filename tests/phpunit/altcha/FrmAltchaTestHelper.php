<?php
/**
 * Shared helpers for ALTCHA tests.
 *
 * @package Formidable
 */

/**
 * Builds solved and broken payloads without running the browser widget.
 */
class FrmAltchaTestHelper {

	/**
	 * Counter used for test challenges, so the solution is known without searching.
	 *
	 * @var int
	 */
	const COUNTER = 7;

	/**
	 * Create a cheap challenge with a known counter.
	 *
	 * @param array $args Extra challenge arguments.
	 *
	 * @return array
	 */
	public static function challenge( $args = array() ) {
		return FrmAltchaChallengeHelper::create(
			array_merge(
				array(
					'max_number' => 10,
					'counter'    => self::COUNTER,
					'cost'       => 1,
				),
				$args
			)
		);
	}

	/**
	 * Solve a challenge the way the widget does and encode the payload.
	 *
	 * @param array $challenge Challenge from FrmAltchaChallengeHelper::create().
	 * @param int   $counter   Counter to submit.
	 *
	 * @return string
	 */
	public static function solve( $challenge, $counter = self::COUNTER ) {
		$parameters = $challenge['parameters'];
		$key        = FrmAltchaChallengeHelper::derive_key( hex2bin( $parameters['nonce'] ), hex2bin( $parameters['salt'] ), $counter, $parameters['cost'] );

		return self::encode(
			array(
				'challenge' => $challenge,
				'solution'  => array(
					'counter'    => $counter,
					'derivedKey' => bin2hex( $key ),
					'time'       => 12.3,
				),
			)
		);
	}

	/**
	 * Decode a payload into an array.
	 *
	 * @param string $payload Base64 JSON.
	 *
	 * @return array<string, mixed>
	 */
	public static function decode( $payload ) {
		return json_decode( base64_decode( $payload ), true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
	}

	/**
	 * Encode data as a payload, the same way the widget does.
	 *
	 * @param mixed $data Payload data.
	 *
	 * @return string
	 */
	public static function encode( $data ) {
		return base64_encode( (string) wp_json_encode( $data ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}
}
