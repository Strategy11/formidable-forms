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

		$monthly_email = new FrmMonthlyEmail();
		$monthly_email->send();

		self::set_last_send_date( 'monthly' );
	}

	public static function send_yearly_email() {
		error_log( 'Sending yearly email' );

		$yearly_email = new FrmYearlyEmail();
		$yearly_email->send();

		self::set_last_send_date( 'yearly' );
	}

	public static function send_license_expired_email() {
		error_log( 'Sending license expired email' );

		$license_email = new FrmLicenseExpiredEmail();
		$license_email->send();

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
		// TODO:
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

	public static function show_comparison( $diff ) {
		if ( ! $diff ) {
			return;
		}

		if ( $diff > 0 ) {
			$image = 'arrow-up.png';
			$color = '#12b76a';
		} else {
			$image = 'arrow-down.png';
			$color = '#f04438';
		}

		$displayed_value = round( $diff * 100 );
		if ( ! $displayed_value ) {
			$displayed_value = $diff > 0 ? 1 : -1; // Do not show 0 value.
		}

		printf(
			'<span style="color: %1$s; font-size: 0.75em; font-weight: 700;">
					<img src="%2$s" alt="" style="vertical-align: text-bottom;" /><span style="display: inline-block; line-height: 1.33;">%3$s</span>
				</span>',
			esc_attr( $color ),
			FrmAppHelper::plugin_url() . '/images/' . $image,
			intval( $displayed_value ) . '%'
		);
	}

	public static function get_section_style() {
		return 'padding: 3em 4.375em; border-bottom: 1px solid #eaecf0;';
	}

	public static function get_heading2_style() {
		return 'font-size: 1.125em; line-height: 1.33em; margin: 0 0 1.33em;';
	}

	/**
	 * Adds custom data to URL.
	 *
	 * @param string $url The URL.
	 * @return string
	 */
	public static function add_url_data( $url ) {
		$data = array(
			'utm_medium' => 'summary-email',
			'utm_content' => 'link',
		);

		return add_query_arg( $data, $url );
	}

	public static function get_latest_inbox_message() {
		$inbox    = new FrmInbox();
		$messages = $inbox->get_messages( 'filter' );
		if ( ! $messages || ! is_array( $messages ) ) {
			return false;
		}

		foreach ( $messages as $message ) {
			if ( 'news' !== $message['type'] ) {
				continue;
			}

			return $message;
		}

		return false;
	}
}
