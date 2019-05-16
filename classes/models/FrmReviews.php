<?php

class FrmReviews {

	private $option_name = 'frm_reviewed';

	private $review_status = array();

	/**
	 * Add admin notices as needed for reviews
	 *
	 * @since 3.04.03
	 */
	public function review_request() {

		// Only show the review request to high-level users on Formidable pages
		if ( ! current_user_can( 'frm_change_settings' ) || ! FrmAppHelper::is_formidable_admin() ) {
			return;
		}

		// Verify that we can do a check for reviews
		$this->set_review_status();

		// Check if it has been dismissed or if we can ask later
		$dismissed = $this->review_status['dismissed'];
		if ( $dismissed === 'later' && $this->review_status['asked'] < 3 ) {
			$dismissed = false;
		}

		$week_ago = ( $this->review_status['time'] + WEEK_IN_SECONDS ) <= time();

		if ( empty( $dismissed ) && $week_ago ) {
			$this->review();
		}
	}

	/**
	 * When was the review request last dismissed?
	 *
	 * @since 3.04.03
	 */
	private function set_review_status() {
		$user_id = get_current_user_id();
		$review  = get_user_meta( $user_id, $this->option_name, true );
		$default = array(
			'time'      => time(),
			'dismissed' => false,
			'asked'     => 0,
		);

		if ( empty( $review ) ) {
			// Set the review request to show in a week
			update_user_meta( $user_id, $this->option_name, $default );
		}

		$review              = array_merge( $default, (array) $review );
		$review['asked']     = (int) $review['asked'];
		$this->review_status = $review;
	}

	/**
	 * Maybe show review request
	 *
	 * @since 3.04.03
	 */
	private function review() {

		// show the review request 3 times, depending on the number of entries
		$show_intervals = array( 50, 200, 500 );
		$asked          = $this->review_status['asked'];

		if ( ! isset( $show_intervals[ $asked ] ) ) {
			return;
		}

		$entries = FrmEntry::getRecordCount();
		$count   = $show_intervals[ $asked ];
		$user    = wp_get_current_user();

		// Only show review request if the site has collected enough entries
		if ( $entries < $count ) {
			// check the entry count again in a week
			$this->review_status['time'] = time();
			update_user_meta( $user->ID, $this->option_name, $this->review_status );

			return;
		}

		if ( $entries <= 100 ) {
			// round to the nearest 10
			$entries = floor( $entries / 10 ) * 10;
		} else {
			// round to the nearest 50
			$entries = floor( $entries / 50 ) * 50;
		}
		$name = $user->first_name;
		if ( ! empty( $name ) ) {
			$name = ' ' . $name;
		}

		// We have a candidate! Output a review message.
		include( FrmAppHelper::plugin_path() . '/classes/views/shared/review.php' );
	}

	/**
	 * Save the request to hide the review
	 *
	 * @since 3.04.03
	 */
	public function dismiss_review() {
		FrmAppHelper::permission_check( 'frm_change_settings' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$user_id = get_current_user_id();
		$review  = get_user_meta( $user_id, $this->option_name, true );
		if ( empty( $review ) ) {
			$review = array();
		}

		if ( isset( $review['dismissed'] ) && $review['dismissed'] === 'done' ) {
			// if feedback was submitted, don't update it again when the review is dismissed
			wp_die();
		}

		$dismissed           = FrmAppHelper::get_post_param( 'link', 'no', 'sanitize_text_field' );
		$review['time']      = time();
		$review['dismissed'] = $dismissed === 'done' ? true : 'later';
		$review['asked']     = isset( $review['asked'] ) ? $review['asked'] + 1 : 1;

		update_user_meta( $user_id, $this->option_name, $review );
		wp_die();
	}
}
