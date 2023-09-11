<?php
/**
 * In-plugin summary emails helper
 *
 * @since x.x
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Class FrmSummaryEmailsHelper
 */
class FrmSummaryEmailsHelper {

	const MONTHLY = 'monthly';

	const YEARLY = 'yearly';

	const LICENSE_EXPIRED = 'license';

	/**
	 * Summary emails option name.
	 *
	 * @var string
	 */
	public static $option_name = 'frm_summary_emails_options';

	/**
	 * Checks if summary emails are enabled.
	 *
	 * @return bool
	 */
	private static function is_enabled() {
		$frm_settings = FrmAppHelper::get_settings();
		return ! empty( $frm_settings->summary_emails ) && ! empty( $frm_settings->summary_emails_recipients );
	}

	/**
	 * Gets summary emails options.
	 *
	 * @return array
	 */
	private static function get_options() {
		$options = get_option( self::$option_name );
		if ( ! $options ) {
			$default_options = array(
				'last_' . self::MONTHLY         => gmdate( 'Y-m-d', strtotime( '-15 days' ) ), // Do not send email within 15 days after updating.
				'last_' . self::YEARLY          => '',
				'last_' . self::LICENSE_EXPIRED => '',
				'renewal_date'                  => '',
			);

			self::save_options( $default_options );
			return $default_options;
		}

		return $options;
	}

	/**
	 * Saves summary emails options.
	 *
	 * @param array $options Options data.
	 */
	private static function save_options( $options ) {
		update_option( self::$option_name, $options );
	}

	/**
	 * Checks if should send summary emails.
	 *
	 * @return array|false Return array of emails should be sent, or `false` if not send any emails.
	 */
	public static function should_send_emails() {
		if ( ! self::is_enabled() ) {
			return false;
		}

		$emails       = array();
		$current_date = gmdate( 'Y-m-d' );

		// Check for license expired email.
		$last_expired = self::get_last_sent_date( 'license' ); // TODO: clear this sent date after renewing.
		if ( ! $last_expired ) {
			// License expired email hasn't been sent. Check for the license.
			if ( FrmAddonsController::is_license_expired() ) {
				$emails[] = self::LICENSE_EXPIRED;
			}
		}

		// Check for monthly or yearly email.
		$last_monthly = self::get_last_sent_date( 'monthly' );
		$last_yearly  = self::get_last_sent_date( 'yearly' );
		$last_stats   = max( $last_monthly, $last_yearly );

		// Do not send any email if it isn't enough 30 days from the last stats email.
		if ( $last_stats && 30 > self::get_date_diff( $current_date, $last_stats ) ) {
			return $emails;
		}

		if ( $last_yearly ) {
			// If this isn't the first yearly email, send the new one after 1 year.
			if ( $last_yearly && 365 <= self::get_date_diff( $current_date, $last_yearly ) ) {
				$emails[] = self::YEARLY;
				return $emails;
			}
		} else {
			// If no yearly email has been sent, send it if it's less than 45 days until the renewal date.
			$renewal_date = self::get_renewal_date();
			if ( $renewal_date && 45 >= self::get_date_diff( $current_date, $renewal_date ) ) {
				$emails[] = self::YEARLY;
				return $emails;
			}
		}

		// If it isn't time for yearly email, it's time for monthly email.
		$emails[] = self::MONTHLY;

		return $emails;
	}

	/**
	 * Sends monthly email.
	 */
	public static function send_monthly() {
		$monthly_email = new FrmMonthlyEmail();

		if ( $monthly_email->send() ) {
			self::set_last_sent_date( self::MONTHLY );
		}
	}

	/**
	 * Sends yearly email.
	 */
	public static function send_yearly() {
		$yearly_email = new FrmYearlyEmail();

		if ( $yearly_email->send() ) {
			self::set_last_sent_date( self::YEARLY );
		}
	}

	/**
	 * Sends license expired email.
	 */
	public static function send_license_expired() {
		$license_email = new FrmLicenseExpiredEmail();

		if ( $license_email->send() ) {
			self::set_last_sent_date( self::LICENSE_EXPIRED );
		}
	}

	/**
	 * Gets the renewal date and save to options. If it doesn't exist, get the created date of the lowest ID form then plus 1 year.
	 *
	 * @return string
	 */
	private static function get_renewal_date() {
		$options = self::get_options();
		if ( ! empty( $options['renewal_date'] ) ) {
			return $options['renewal_date'];
		}

		$license_info = FrmAddonsController::get_primary_license_info();
		if ( ! empty( $license_info['expires'] ) ) {
			$renewal_date = gmdate( 'Y-m-d', $license_info['expires'] );

			$options['renewal_date'] = $renewal_date;
			self::save_options( $options );
			return $renewal_date;
		}

		$first_form_date = self::get_earliest_form_created_date();
		if ( $first_form_date ) {
			$renewal_date = gmdate( 'Y-m-d', strtotime( $first_form_date . '+365 days' ) );

			$options['renewal_date'] = $renewal_date;
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
	private static function get_date_diff( $date1, $date2 ) {
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
	private static function get_last_sent_date( $type ) {
		$options = self::get_options();
		if ( empty( $options[ 'last_' . $type ] ) ) {
			return false;
		}

		return $options[ 'last_' . $type ];
	}

	/**
	 * Sets the last sent date of an email type.
	 *
	 * @param string $type Email type.
	 */
	private static function set_last_sent_date( $type ) {
		$options = self::get_options();

		$options[ 'last_' . $type ] = gmdate( 'Y-m-d' );
		self::save_options( $options );
	}

	/**
	 * Gets the created date of earliest form.
	 *
	 * @return string
	 */
	private static function get_earliest_form_created_date() {
		return FrmDb::get_var(
			'frm_forms',
			array(),
			'created_at',
			array( 'order_by' => 'id ASC' )
		);
	}

	/**
	 * Gets summary data in a date range.
	 *
	 * @param string $from_date From date.
	 * @param string $to_date   To date.
	 * @return array
	 */
	public static function get_summary_data( $from_date, $to_date ) {
		$data = array(
			'top_forms' => self::get_top_forms( $from_date, $to_date ),
			'entries'   => self::get_entries_count( $from_date, $to_date ),
		);

		return apply_filters( 'frm_summary_data', $data, compact( 'from_date', 'to_date' ) );
	}

	/**
	 * Gets entries count in a date range.
	 *
	 * @param string $from_date From date.
	 * @param string $to_date   To date.
	 * @return int
	 */
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

	/**
	 * Gets top forms in a date range.
	 *
	 * @param string $from_date From date.
	 * @param string $to_date   To date.
	 * @param int    $limit     Limit the result. Default is 10.
	 * @return array            Contains `form_id`, `form_name`, and `items_count`.
	 */
	private static function get_top_forms( $from_date, $to_date, $limit = 10 ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT fr.id AS form_id, fr.name AS form_name, COUNT(*) as items_count
						FROM {$wpdb->prefix}frm_items AS it INNER JOIN {$wpdb->prefix}frm_forms AS fr ON it.form_id = fr.id
						WHERE it.created_at BETWEEN %s AND %s AND it.is_draft = 0
						GROUP BY form_id ORDER BY items_count DESC LIMIT %d",
				$from_date,
				$to_date,
				intval( $limit )
			)
		);
	}

	/**
	 * Shows the comparison HTML in the email.
	 *
	 * @param float $diff Percentage of difference.
	 */
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
			esc_url( FrmAppHelper::plugin_url() . '/images/' . $image ),
			intval( $displayed_value ) . '%'
		);
	}

	/**
	 * Gets section CSS in the email.
	 *
	 * @return string
	 */
	public static function get_section_style() {
		return 'padding: 3em 4.375em; border-bottom: 1px solid #eaecf0;';
	}

	/**
	 * Gets h2 CSS in the email.
	 *
	 * @return string
	 */
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

	/**
	 * Gets the latest inbox message.
	 *
	 * @return array|false
	 */
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

	/**
	 * Gets out of date plugin names.
	 *
	 * @return array
	 */
	public static function get_out_of_date_plugins() {
		$update_data = FrmAddonsController::check_update( '' );
		if ( ! $update_data ) {
			return array();
		}

		$plugins = array();
		foreach ( $update_data->response as $plugin_data ) {
			if ( version_compare( $plugin_data->new_version, $plugin_data->version, '>' ) ) {
				$plugins[] = FrmAppHelper::get_menu_name() . ' ' . $plugin_data->display_name;
			}
		}

		return $plugins;
	}
}
