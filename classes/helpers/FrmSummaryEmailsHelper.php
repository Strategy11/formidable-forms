<?php
/**
 * In-plugin summary emails helper
 *
 * @since x.x
 * @package Formidable
 */

/**
 * Class FrmSummaryEmailsHelper
 */
class FrmSummaryEmailsHelper {

	public static $option_name = 'frm_summary_emails_options';

	private static $options;

	/**
	 * Checks if summary emails are enabled.
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		$frm_settings = FrmAppHelper::get_settings();
		return ! empty( $frm_settings->summary_emails ) && ! empty( $frm_settings->summary_emails_recipients );
	}

	private static function get_options() {
		$default_options = array(
			'last_monthly' => '',
			'last_yearly'  => '',
			'last_license' => '',
			'renewal'      => '',
		);
		if ( ! self::$options ) {
			self::$options = get_option( self::$option_name, $default_options );
		}
		return self::$options;
	}

	private static function save_options( $options ) {
		update_option( 'frm_summary_emails_options', $options );
	}

	public static function send_monthly_email() {
		error_log( 'Sending monthly email' );
		self::set_last_send_date( 'monthly' );
	}

	public static function send_yearly_email() {
		error_log( 'Sending yearly email' );

		$yearly_email = new FrmYearlySummaryEmail();
		$yearly_email->send();

		self::set_last_send_date( 'yearly' );
	}

	public static function send_license_expired_email() {
		error_log( 'Sending license expired email' );
		self::set_last_send_date( 'license' );
	}

	/**
	 * Gets the renewal date and save to options. If it doesn't exist, get the created date of the lowest ID form then plus 1 year.
	 *
	 * @return string
	 */
	public static function get_renewal_date() {
		$options = self::get_options();
		if ( ! empty( $options['renewal'] ) ) {
			return $options['renewal'];
		}

		$license_info = FrmAddonsController::get_primary_license_info();
		if ( ! empty( $license_info['expires'] ) ) {
			$renewal_date       = date( 'Y-m-d', $license_info['expires'] );
			$options['renewal'] = $renewal_date;
			self::save_options( $options );
			return $renewal_date;
		}

		$first_form_date = self::get_lowest_form_created_date();
		if ( $first_form_date ) {
			$renewal_date       = date( 'Y-m-d', strtotime( $first_form_date ) );
			$options['renewal'] = $renewal_date;
			self::save_options( $options );
			return $renewal_date;
		}

		return false;
	}

	/**
	 * Gets date object.
	 *
	 * @param string|DateTime $date Date string or object.
	 * @return DateTime|false
	 */
	private static function get_date_obj( $date ) {
		if ( $date instanceof DateTime ) {
			return $date;
		}

		return date_create( $date );
	}

	/**
	 * Gets the days different between 2 dates.
	 *
	 * @param string|DateTime $date1 Date 1.
	 * @param string|DateTime $date2 Date 2.
	 * @return int|false
	 */
	public static function get_date_diff( $date1, $date2 ) {
		$date1 = self::get_date_obj( $date1 );
		if ( ! $date1 ) {
			return false;
		}

		$date2 = self::get_date_obj( $date2 );
		if ( ! $date2 ) {
			return false;
		}

		return date_diff( $date1, $date2 )->days;
	}

	/**
	 * Gets sent date of the last monthly, yearly, or license expired email.
	 *
	 * @param string $type Accepts `monthly`, `yearly`, or `license`.
	 * @return string|false
	 */
	public static function get_last_sent_date( $type ) {
		$options = self::get_options();
		if ( empty( $options[ 'last_' . $type ] ) ) {
			return false;
		}

		return $options[ 'last_' . $type ];
	}

	public static function set_last_send_date( $type ) {

	}

	private static function get_lowest_form_created_date() {
		return FrmDb::get_var(
			'frm_forms',
			array(),
			'created_at',
			array( 'order_by' => 'id ASC' )
		);
	}

	public static function get_summary_data( $from_date, $to_date ) {
		$data = array(
			'top_forms' => self::get_top_forms( $from_date, $to_date ),
			'entries'   => self::get_entries_count( $from_date, $to_date ),
			'payments'  => 0, // TODO: Remove this. This should be added with filter.
		);

		return apply_filters( 'frm_summary_data', $data, compact( 'from_date', 'to_date' ) );
	}

	private static function get_entries_count( $from_date, $to_date ) {
		return FrmDb::get_count(
			'frm_items',
			array(
				'created_at >' => $from_date, // The `=` is added after `>` in the query.
				'created_at <' => $to_date,
				'is_draft'     => 0,
			)
		);
	}

	private static function get_top_forms( $from_date, $to_date, $limit = 10 ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT form_id, COUNT(*) as items_count FROM {$wpdb->prefix}frm_items
						WHERE created_at >= %s AND created_at <= %s AND is_draft = 0
						GROUP BY form_id ORDER BY items_count DESC LIMIT %d",
				$from_date,
				$to_date,
				intval( $limit )
			)
		);
	}
}
