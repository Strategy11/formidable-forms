<?php

/**
 * Class FrmAntiSpam.
 *
 * This token class generates tokens that are used in our Anti-Spam checking.
 *
 * @since xx.xx
 */
class FrmAntiSpam {

	/**
	 * Initialise the actions for the Anti-spam.
	 *
	 * @since xx.xx
	 */
	public function init() {
		add_filter( 'frm_entry_form', array( $this, 'add_token_to_form' ), 10, 3 );
	}

	/**
	 * Return a valid token.
	 *
	 * @since xx.xx
	 *
	 * @param mixed $current True to use current time, otherwise a timestamp string.
	 *
	 * @return string Token.
	 */
	public function get( $current = true ) {

		// If $current was not passed, or it is true, we use the current timestamp.
		// If $current was passed in as a string, we'll use that passed in timestamp.
		if ( $current !== true ) {
			$time = $current;
		} else {
			$time = time();
		}

		// Format the timestamp to be less exact, as we want to deal in days.
		// June 19th, 2020 would get formatted as: 1906202017125.
		// Day of the month, month number, year, day number of the year, week number of the year.
		$token_date = gmdate( 'dmYzW', $time );

		// Combine our token date and our token salt, and md5 it.
		$form_token_string = md5( $token_date . $this->get_antispam_secret_key() );

		return $form_token_string;
	}

	private function get_antispam_secret_key() {
		$secret_key = get_option( 'frm_antispam_secret_key' );

		// If we already have the secret, send it back.
		if ( false !== $secret_key ) {
			return base64_decode( $secret_key ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		}

		// We don't have a secret, so let's generate one.
		$secret_key = sodium_crypto_secretbox_keygen();
		add_option( 'frm_antispam_secret_key', base64_encode( $secret_key ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

		return $secret_key;
	}

	/**
	 * Generate the array of valid tokens to check for. These include two days
	 * before the current date to account for long cache times.
	 *
	 * These two filters are available if a user wants to extend the times.
	 * 'frm_form_token_check_before_today'
	 * 'frm_form_token_check_after_today'
	 *
	 * @since xx.xx
	 *
	 * @return array Array of all valid tokens to check against.
	 */
	public function get_valid_tokens() {

		$current_date = time();

		// Create our array of times to check before today. A user with a longer
		// cache time can extend this. A user with a shorter cache time can remove times.
		$valid_token_times_before = apply_filters(
			'frm_form_token_check_before_today',
			array(
				( 2 * DAY_IN_SECONDS ), // Two days ago.
				( 1 * DAY_IN_SECONDS ), // One day ago.
			)
		);

		// Mostly to catch edge cases like the form page loading and submitting on two different days.
		// This probably won't be filtered by users too much, but they could extend it.
		$valid_token_times_after = apply_filters(
			'frm_form_token_check_after_today',
			array(
				( 45 * MINUTE_IN_SECONDS ), // Add in 45 minutes past today to catch some midnight edge cases.
			)
		);

		// Built up our valid tokens.
		$valid_tokens = array();

		// Add in all the previous times we check.
		foreach ( $valid_token_times_before as $time ) {
			$valid_tokens[] = $this->get( $current_date - $time );
		}

		// Add in our current date.
		$valid_tokens[] = $this->get( $current_date );

		// Add in the times after our check.
		foreach ( $valid_token_times_after as $time ) {
			$valid_tokens[] = $this->get( $current_date + $time );
		}

		return $valid_tokens;
	}

	/**
	 * Check if the given token is valid or not.
	 *
	 * Tokens are valid for some period of time (see frm_token_validity_in_hours
	 * and frm_token_validity_in_days to extend the validation period).
	 * By default tokens are valid for day.
	 *
	 * @since xx.xx
	 *
	 * @param string $token Token to validate.
	 *
	 * @return bool Whether the token is valid or not.
	 */
	public function verify( $token ) {

		// Check to see if our token is inside of the valid tokens.
		return in_array( $token, $this->get_valid_tokens(), true );
	}

	/**
	 * Add the token field to the form.
	 *
	 * @since xx.xx
	 *
	 * @param object $form
	 * @param string $form_action
	 * @param array  $errors
	 */
	public function add_token_to_form( $form, $form_action, $errors ) {
		?>
		<input type="hidden" name="antispam_token" value="<?php echo esc_attr( $this->get() ); ?>" />
		<?php
	}

	/**
	 * Validate Anti-spam if enabled.
	 *
	 * @since xx.xx
	 *
	 * @return bool|string True or a string with the error.
	 */
	public function validate() {

		$run_antispam = true;
		if ( ! apply_filters( 'frm_run_antispam', $run_antispam ) ) {
			return;
		}

		$token = FrmAppHelper::get_param( 'antispam_token', '', 'post', 'sanitize_text_field' );

		// If the antispam setting is enabled and we don't have a token, bail.
		if ( ! $token ) {
			return $this->process_antispam_filter( $this->get_missing_token_message() );
		}

		// Verify the token.
		if ( ! $this->verify( $token ) ) {
			return $this->process_antispam_filter( $this->get_invalid_token_message() );
		}

		return $this->process_antispam_filter( true );
	}

	/**
	 * Helper to run our filter on all the responses for the antispam checks.
	 *
	 * @since xx.xx
	 *
	 * @param bool|string $is_valid Is valid entry or not.
	 *
	 * @return bool|string Is valid or message.
	 */
	private function process_antispam_filter( $is_valid ) {
		return apply_filters( 'frm_process_antispam', $is_valid );
	}

	/**
	 * Helper to get the missing token message.
	 *
	 * @since xx.xx
	 *
	 * @return string missing token message.
	 */
	private function get_missing_token_message() {
		return esc_html__( 'This page isn\'t loading JavaScript properly, and the form will not be able to submit.', 'formidable' ) . $this->maybe_get_support_text();
	}

	/**
	 * Helper to get the invalid token message.
	 *
	 * @since xx.xx
	 *
	 * @return string Invalid token message.
	 */
	private function get_invalid_token_message() {
		return esc_html__( 'Form token is invalid. Please refresh the page.', 'formidable' ) . $this->maybe_get_support_text();
	}

	/**
	 * If a user is a super admin, add a support link to the message.
	 *
	 * @since xx.xx
	 *
	 * @return string Support text if super admin, empty string if not.
	 */
	private function maybe_get_support_text() {

		// If user isn't a super admin, don't return any text.
		if ( ! is_super_admin() ) {
			return '';
		}

		// If the user is an admin, return text with a link to support.
		// We add a space here to seperate the sentences, but outside of the localized
		// text to avoid it being removed.
		return ' ' . sprintf(
			// translators: %1$s start link, %2$s end link.
			esc_html__( 'Please check out our %1$stroubleshooting guide%2$s for details on resolving this issue.', 'formidable' ),
			'<a href="https://formidableforms.com/knowledgebase/">',
			'</a>'
		);
	}
}
