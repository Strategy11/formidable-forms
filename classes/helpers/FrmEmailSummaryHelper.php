<?php
/**
 * In-plugin summary emails helper
 *
 * @since 6.7
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Class FrmEmailSummaryHelper
 */
class FrmEmailSummaryHelper {

	const MONTHLY = 'monthly';

	const YEARLY = 'yearly';

	/**
	 * Number of days to send the next monthly email.
	 */
	const MONTHLY_PERIOD = 30;

	/**
	 * Number of days to send the next yearly email.
	 */
	const YEARLY_PERIOD = 365;

	/**
	 * Number of days before renewal date to send yearly email.
	 */
	const BEFORE_RENEWAL_PERIOD = 45;

	/**
	 * Number of days before sending the first summary email after upgrade plugin.
	 */
	const DELAY_AFTER_UPGRADE = 15;

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
	public static function is_enabled() {
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
				// Do not send email within 15 days after updating.
				'last_' . self::MONTHLY => self::get_date_from_today( '-' . self::DELAY_AFTER_UPGRADE . ' days' ),
				'last_' . self::YEARLY  => '',
				'renewal_date'          => '',
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
		$current_date = self::get_date_from_today();

		// Check for monthly or yearly email.
		$last_monthly = self::get_last_sent_date( 'monthly' );
		$last_yearly  = self::get_last_sent_date( 'yearly' );
		$last_stats   = max( $last_monthly, $last_yearly );

		// Do not send any email if it isn't enough 30 days from the last stats email.
		if ( $last_stats && self::MONTHLY_PERIOD > self::get_date_diff( $current_date, $last_stats ) ) {
			return $emails;
		}

		if ( $last_yearly ) {
			// If this isn't the first yearly email, send the new one after 1 year.
			if ( $last_yearly && self::YEARLY_PERIOD <= self::get_date_diff( $current_date, $last_yearly ) ) {
				$emails[] = self::YEARLY;
				return $emails;
			}
		} else {
			// If no yearly email has been sent, send it if it's less than 45 days until the renewal date.
			$renewal_date = self::get_renewal_date();
			if ( $renewal_date && self::BEFORE_RENEWAL_PERIOD >= self::get_date_diff( $current_date, $renewal_date ) ) {
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
		$monthly_email = new FrmEmailMonthly();

		if ( $monthly_email->send() ) {
			self::set_last_sent_date( self::MONTHLY );
		}
	}

	/**
	 * Sends yearly email.
	 */
	public static function send_yearly() {
		$yearly_email = new FrmEmailYearly();

		if ( $yearly_email->send() ) {
			self::set_last_sent_date( self::YEARLY );
		}
	}

	/**
	 * Gets the renewal date and save to options. If it doesn't exist, get the created date of the lowest ID form then plus 1 year.
	 *
	 * @return string
	 */
	private static function get_renewal_date() {
		$options = self::get_options();

		// Get cached value from options.
		if ( ! empty( $options['renewal_date'] ) ) {
			return $options['renewal_date'];
		}

		// Return the actual renewal date if it exists.
		$license_info = FrmAddonsController::get_primary_license_info();
		if ( ! empty( $license_info['expires'] ) ) {
			$renewal_date = gmdate( 'Y-m-d', $license_info['expires'] );

			$options['renewal_date'] = $renewal_date;
			self::save_options( $options );
			return $renewal_date;
		}

		// If renewal date doesn't exist, get from the first form creation date.
		$first_form_date = self::get_earliest_form_created_date();
		if ( $first_form_date ) {
			$renewal_date = gmdate( 'Y-m-d', strtotime( $first_form_date . '+' . self::YEARLY_PERIOD . ' days' ) );

			// If the first form is more than 1 year in the past, set renewal date to the next 45 days.
			if ( $renewal_date < self::get_date_from_today() ) {
				$renewal_date = self::get_date_from_today( '+' . self::BEFORE_RENEWAL_PERIOD . ' days' );
			}

			$options['renewal_date'] = $renewal_date;
			self::save_options( $options );
			return $renewal_date;
		}

		return false;
	}

	/**
	 * Gets date object.
	 *
	 * @param DateTime|string $date Date string or object.
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
	 * @param DateTime|string $date1 Date 1.
	 * @param DateTime|string $date2 Date 2.
	 * @return false|int
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
	 * Gets sent date of the last monthly or yearly email.
	 *
	 * @param string $type Accepts `monthly`, `yearly`.
	 * @return false|string
	 */
	public static function get_last_sent_date( $type ) {
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
	 * @param mixed  $value Set custom value. If this is null, set the current date.
	 */
	public static function set_last_sent_date( $type, $value = null ) {
		$options = self::get_options();

		$options[ 'last_' . $type ] = null === $value ? self::get_date_from_today() : '';
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
	 * Gets payments data.
	 *
	 * @param string $from_date From date.
	 * @param string $to_date   To date.
	 * @return array            Contains `count` and `total`.
	 */
	public static function get_payments_data( $from_date, $to_date ) {
		$payment = new FrmTransLitePayment();
		return $payment->get_payments_stats( $from_date, $to_date );
	}

	/**
	 * Gets entries count in a date range.
	 *
	 * @param string $from_date From date.
	 * @param string $to_date   To date.
	 * @return int
	 */
	public static function get_entries_count( $from_date, $to_date ) {
		return FrmDb::get_count(
			'frm_items',
			array(
				// The `=` is added after `>` in the query.
				'created_at >' => $from_date,
				'created_at <' => $to_date . ' 23:59:59',
				'is_draft'     => 0,
			)
		);
	}

	/**
	 * Gets top forms in a date range.
	 *
	 * @param string $from_date From date.
	 * @param string $to_date   To date.
	 * @param int    $limit     Limit the result. Default is 5.
	 * @return array            Contains `form_id`, `form_name`, and `items_count`.
	 */
	public static function get_top_forms( $from_date, $to_date, $limit = 5 ) {
		global $wpdb;

		$result = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT fr.id AS form_id, fr.name AS form_name, COUNT(*) as items_count
						FROM {$wpdb->prefix}frm_items AS it INNER JOIN {$wpdb->prefix}frm_forms AS fr ON it.form_id = fr.id
						WHERE it.created_at BETWEEN %s AND %s AND it.is_draft = 0
						GROUP BY form_id ORDER BY items_count DESC LIMIT %d",
				$from_date,
				$to_date . ' 23:59:59',
				intval( $limit )
			)
		);

		// Remove slashes from form name.
		foreach ( $result as &$value ) {
			$value->form_name = wp_unslash( $value->form_name );
		}

		return $result;
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
			$arrow = '&uarr;';
			$color = '#12b76a';
		} else {
			$arrow = '&darr;';
			$color = '#f04438';
		}

		$displayed_value = round( $diff * 100 );
		if ( ! $displayed_value ) {
			// Do not show 0 value.
			$displayed_value = $diff > 0 ? 1 : -1;
		}

		printf(
			'<span style="color: %1$s; font-size: 0.75em; font-weight: 700;">
					%2$s<span style="display: inline-block; line-height: 1.33;">%3$s</span>
				</span>',
			esc_attr( $color ),
			esc_html( $arrow ),
			intval( $displayed_value ) . '%'
		);
	}

	/**
	 * Gets section CSS in the email.
	 *
	 * @param string $border_pos Border position. Default is `top`. Set to empty if no border.
	 * @return string
	 */
	public static function get_section_style( $border_pos = 'top' ) {
		if ( $border_pos ) {
			$border = 'border-' . $border_pos . ': 1px solid #eaecf0;';
		} else {
			$border = '';
		}
		return 'padding: 3em 4.375em;' . $border;
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
	 * Gets CSS for button.
	 *
	 * @return string
	 */
	public static function get_button_style( $display_block = false ) {
		return 'display: ' . ( $display_block ? 'block' : 'inline-block' ) . '; font-size: 0.875em; line-height: 2.4; border-radius: 1.2em; border: 1px solid #d0d5dd; font-weight: 600; text-align: center; margin-top: 2.6em; color: #1d2939; text-decoration: none; padding-left: 1em; padding-right: 1em;';
	}

	/**
	 * Shows the section heading with icon.
	 *
	 * @param string $icon Icon file name, without file path and extension. Use .png image.
	 * @param string $text Heading text.
	 */
	public static function section_heading_with_icon( $icon, $text ) {
		?>
		<h2 style="<?php echo esc_attr( self::get_heading2_style() ); ?>">
			<img style="vertical-align: bottom; height: 24px; width: auto;" src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/' . $icon . '.png' ); ?>" alt="<?php echo esc_attr( $icon ); ?>" />
			<span style="display: inline-block; vertical-align: text-bottom;"><?php echo esc_html( $text ); ?></span>
		</h2>
		<?php
	}

	/**
	 * Gets Formidable URL with tracking params.
	 *
	 * @param string       $url  The URL.
	 * @param array|string $args Custom tracking args if is array, or `utm_content` if is string.
	 * @return string
	 */
	public static function get_frm_url( $url, $args = array() ) {
		if ( is_array( $args ) ) {
			$args = wp_parse_args(
				$args,
				array(
					'medium'  => 'summary-email',
					'content' => 'link',
				)
			);
		} else {
			$args = array(
				'medium'  => 'summary-email',
				'content' => $args,
			);
		}

		return FrmAppHelper::admin_upgrade_link( $args, $url );
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

		$messages = array_reverse( $messages );
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
		if ( ! $update_data || ! is_object( $update_data ) || empty( $update_data->response ) ) {
			return array();
		}

		$plugins = array();
		foreach ( $update_data->response as $plugin_data ) {
			$plugins[] = $plugin_data->display_name;
		}

		return $plugins;
	}

	/**
	 * Processes inbox CTA button before showing in email.
	 *
	 * @param string $button_html Button HTML. This usually contains 1 button and 1 dismiss button.
	 * @return string
	 */
	public static function process_inbox_cta_button( $button_html ) {
		// Remove dismiss button.
		$button_html = preg_replace( '/<a[^>]*class="[^"]*\bfrm_inbox_dismiss\b[^"]*"[^>]*>[^<]*<\/a>/', '', $button_html );

		// Replace link utm.
		$button_html = str_replace( 'utm_medium=inbox', 'utm_medium=summary-email', $button_html );

		if ( strpos( $button_html, 'style="' ) ) {
			// Maybe this button contains inline style.
			return $button_html;
		}

		// Add inline CSS for specific button types.
		if ( strpos( $button_html, 'frm-button-primary' ) ) {
			$button_html = str_replace( '<a', '<a style="' . self::get_button_style() . '"', $button_html );
		}

		return $button_html;
	}

	/**
	 * Gets the localized date with the date diff from today.
	 *
	 * @param string $date_diff Date diff string. By default, this is empty, the result will be the current date.
	 * @return string
	 */
	public static function get_date_from_today( $date_diff = '' ) {
		if ( ! $date_diff ) {
			return FrmAppHelper::get_localized_date( 'Y-m-d', gmdate( 'Y-m-d H:i:s' ) );
		}
		return FrmAppHelper::get_localized_date( 'Y-m-d', gmdate( 'Y-m-d H:i:s', strtotime( $date_diff ) ) );
	}

	/**
	 * Maybe remove recipients from setting from API.
	 *
	 * @since 6.8
	 *
	 * @param string $recipients Recipients.
	 * @return void
	 */
	public static function maybe_remove_recipients_from_api( &$recipients ) {
		$api    = new FrmFormApi();
		$addons = $api->get_api_info();
		if ( empty( $addons['no_emails'] ) ) {
			return;
		}

		$skip_emails = is_string( $addons['no_emails'] ) ? explode( ',', $addons['no_emails'] ) : (array) $addons['no_emails'];

		$recipients = array_map( 'trim', explode( ',', $recipients ) );
		$recipients = array_diff( $recipients, $skip_emails );
		$recipients = implode( ',', $recipients );
	}

	/**
	 * Echos string in plain text email.
	 *
	 * @since 6.8
	 *
	 * @param string $string string.
	 */
	public static function plain_text_echo( $string ) {
		echo wp_strip_all_tags( $string ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
