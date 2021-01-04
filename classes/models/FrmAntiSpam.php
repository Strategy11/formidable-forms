<?php

/**
 * Class Token.
 *
 * This token class generates tokens that are used in our Anti-Spam checking.
 *
 * @since 4.09.03
 */
class FrmAntiSpam {

	/**
	 * Initialise the actions for the Anti-spam.
	 *
	 * @since 4.09.03
	 */
	public function init() {

		add_filter( 'wpforms_frontend_form_atts', [ $this, 'add_token_to_form_atts' ] );
	}

	/**
	 * Return a valid token.
	 *
	 * @since 4.09.03
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
		$form_token_string = md5( $token_date . \WPForms\Helpers\Crypto::get_secret_key() );

		return $form_token_string;
	}

	/**
	 * Generate the array of valid tokens to check for. These include two days
	 * before the current date to account for long cache times.
	 *
	 * These two filters are available if a user wants to extend the times.
	 * 'wpforms_form_token_check_before_today'
	 * 'wpforms_form_token_check_after_today'
	 *
	 * @since 4.09.03
	 *
	 * @return array Array of all valid tokens to check against.
	 */
	public function get_valid_tokens() {

		$current_date = time();

		// Create our array of times to check before today. A user with a longer
		// cache time can extend this. A user with a shorter cache time can remove times.
		$valid_token_times_before = apply_filters(
			'wpforms_form_token_check_before_today',
			[
				( 2 * DAY_IN_SECONDS ), // Two days ago.
				( 1 * DAY_IN_SECONDS ), // One day ago.
			]
		);

		// Mostly to catch edge cases like the form page loading and submitting on two different days.
		// This probably won't be filtered by users too much, but they could extend it.
		$valid_token_times_after = apply_filters(
			'wpforms_form_token_check_after_today',
			[
				( 45 * MINUTE_IN_SECONDS ), // Add in 45 minutes past today to catch some midnight edge cases.
			]
		);

		// Built up our valid tokens.
		$valid_tokens = [];

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
	 * Tokens are valid for some period of time (see wpforms_token_validity_in_hours
	 * and wpforms_token_validity_in_days to extend the validation period).
	 * By default tokens are valid for day.
	 *
	 * @since 4.09.03
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
	 * Add the token to the form attributes.
	 *
	 * @since 4.09.03
	 *
	 * @param array $attrs Form attributes.
	 *
	 * @return array Form attributes.
	 */
	public function add_token_to_form_atts( array $attrs ) {

		$attrs['atts']['data-token'] = $this->get();

		return $attrs;
	}

	/**
	 * Validate Anti-spam if enabled.
	 *
	 * @since 4.09.03
	 *
	 * @param array $form_data Form data.
	 * @param array $fields    Fields.
	 * @param array $entry     Form entry.
	 *
	 * @return bool|string True or a string with the error.
	 */
	public function validate( array $form_data, array $fields, array $entry ) {

		// Bail out if we don't have the antispam setting.
		if ( empty( $form_data['settings']['antispam'] ) ) {
			return true;
		}

		// If the antispam setting is enabled and we don't have a token, bail.
		if ( ! isset( $entry['token'] ) ) {
			return $this->process_antispam_filter( $this->get_missing_token_message(), compact( 'fields' ) );
		}

		// Verify the token.
		if ( ! $this->verify( $entry['token'] ) ) {
			return $this->process_antispam_filter( $this->get_invalid_token_message(), compact( 'fields' ) );
		}

		return $this->process_antispam_filter( true, compact( 'fields' ) );
	}

	/**
	 * @return bool|string
	 */
	private function is_valid() {
		$token = FrmAppHelper::get_param( 'token', '', 'get', 'sanitize_text_field' );
		if ( ! isset( $entry['token'] ) ) {
			return $this->process_antispam_filter( $this->get_missing_token_message(), compact( 'fields' ) );
		}

		// Verify the token.
		if ( ! $this->verify( $entry['token'] ) ) {
			return $this->process_antispam_filter( $this->get_invalid_token_message(), compact( 'fields' ) );
		}

		return $this->process_antispam_filter( true, compact( 'fields' ) );
	}

	/**
	 * Helper to run our filter on all the responses for the antispam checks.
	 *
	 * @since 4.09.03
	 *
	 * @param bool|string $is_valid Is valid entry or not.
	 * @param array       $vars     Includes fields.
	 *
	 * @return bool|string Is valid or message.
	 */
	private function process_antispam_filter( $is_valid, $vars ) {
		return apply_filters( 'frm_process_antispam', $is_valid, $vars );
	}

	/**
	 * Helper to get the missing token message.
	 *
	 * @since 4.09.03
	 *
	 * @return string missing token message.
	 */
	private function get_missing_token_message() {
		return esc_html__( 'This page isn\'t loading JavaScript properly, and the form will not be able to submit.', 'formidable' ) . $this->maybe_get_support_text();
	}

	/**
	 * Helper to get the invalid token message.
	 *
	 * @since 4.09.03
	 *
	 * @return string Invalid token message.
	 */
	private function get_invalid_token_message() {
		return esc_html__( 'Form token is invalid. Please refresh the page.', 'formidable' ) . $this->maybe_get_support_text();
	}

	/**
	 * If a user is a super admin, add a support link to the message.
	 *
	 * @since 4.09.03
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
			esc_html__( 'Please check out our %1$stroubleshooting guide%2$s for details on resolving this issue.', 'wpforms-lite' ),
			'<a href="https://formidableforms.com/knowledgebase/">',
			'</a>'
		);
	}
}
