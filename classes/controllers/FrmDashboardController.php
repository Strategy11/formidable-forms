<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmDashboardController {

	/**
	 * Handle name used for registering controller scripts and style.
	 */
	const PAGE_SLUG = 'formidable-dashboard';

	/**
	 * Option name used to store the dashboard options into db options table.
	 */
	const OPTION_META_NAME = 'frm-dashboard-options';

	/**
	 * Register all of the hooks related to the welcome screen functionality
	 *
	 * @return void
	 */
	public static function load_admin_hooks() {
		add_action( 'admin_menu', __CLASS__ . '::menu', 9 );
	}

	/**
	 * Register Dashboard page tp Formidable admin menu.
	 *
	 * @return void
	 */
	public static function menu() {
		add_submenu_page( 'formidable', 'Formidable | ' . __( 'Dashboard', 'formidable' ), esc_html__( 'Dashboard', 'formidable' ) . wp_kses_post( FrmInboxController::get_notice_count() ), 'frm_view_forms', 'formidable-dashboard', 'FrmDashboardController::route' );
	}

	/**
	 * Triggered from FrmAppController::load_page() with admin_init
	 *
	 * @since 6.8
	 *
	 * @return void
	 */
	public static function load_page() {
		self::remove_admin_notices_on_dashboard();
		self::load_assets();

		add_filter( 'manage_' . sanitize_title( FrmAppHelper::get_menu_name() ) . '_page_formidable-dashboard_columns', 'FrmDashboardController::entries_columns' );
		add_filter( 'frm_show_footer_links', '__return_false' );
		add_filter( 'screen_options_show_screen', '__return_false' );
	}

	/**
	 * Register and enqueue dashboard assets.
	 *
	 * @since 6.8
	 *
	 * @return void
	 */
	public static function load_assets() {
		self::register_assets();
		self::enqueue_assets();
	}

	/**
	 * Init dashboard page.
	 *
	 * @return void
	 */
	public static function route() {
		$latest_available_form = FrmForm::get_latest_form();
		$total_payments        = self::view_args_payments();
		$counters_value        = array(
			'forms'   => FrmForm::get_forms_count(),
			'entries' => FrmEntry::get_entries_count(),
		);

		$dashboard_view = new FrmDashboardHelper(
			array(
				'counters' => array(
					'counters' => self::view_args_counters( $latest_available_form, $counters_value ),
				),
				'license'  => array(),
				'inbox'    => self::view_args_inbox(),
				'entries'  => array(
					'widget-heading'   => __( 'Latest Entries', 'formidable' ),
					'cta'              => array(
						'label' => __( 'View All Entries', 'formidable' ),
						'link'  => admin_url( 'admin.php?page=formidable-entries' ),
					),
					'show-placeholder' => 0 >= (int) $counters_value['entries'],
					'count'            => $counters_value['entries'],
					'placeholder'      => self::view_args_entries_placeholder( $counters_value['forms'] ),
				),
				'payments' => array(
					'show-placeholder' => empty( $total_payments ),
					'placeholder'      => array(
						'copy' => __( 'You don\'t have a payment form setup yet.', 'formidable' ),
						'cta'  => array(
							'link'  => admin_url( 'admin.php?page=formidable-form-templates' ),
							'label' => esc_html__( 'Create a Payment Form', 'formidable' ),
						),
					),
					'counters'         => array(
						array(
							'heading' => __( 'Total Earnings', 'formidable' ),
							'type'    => 'currency',
							'items'   => $total_payments,
						),
					),
				),
				'video'    => array( 'id' => self::get_youtube_embed_video( $counters_value['entries'] ) ),
			)
		);

		$should_display_videos = is_callable( 'FrmProDashboardHelper::should_display_videos' ) ? FrmProDashboardHelper::should_display_videos() : true;

		require FrmAppHelper::plugin_path() . '/classes/views/dashboard/dashboard.php';
	}

	/**
	 * Init top counters widgets view args used to construct FrmDashboardHelper.
	 *
	 * @param false|object $latest_available_form If a form is availble, we utilize its ID to direct the 'Create New Entry' link of the entries counter CTA when no entries exist.
	 * @param array        $counters_value The counter values for "Total Forms" & "Total Entries".
	 *
	 * @return array
	 */
	private static function view_args_counters( $latest_available_form, $counters_value ) {
		$add_entry_cta_link = false !== $latest_available_form && isset( $latest_available_form->id ) ? admin_url( 'admin.php?page=formidable-entries&frm_action=new&form=' . $latest_available_form->id ) : '';

		$lite_counters = array(
			self::view_args_build_counter( __( 'Total Forms', 'formidable' ), array(), $counters_value['forms'] ),
			self::view_args_build_counter(
				__( 'Total Entries', 'formidable' ),
				self::view_args_build_cta(
					__( 'Add Entry', 'formidable' ),
					$add_entry_cta_link,
					self::display_counter_cta( 'entries', $counters_value['entries'], $latest_available_form )
				),
				$counters_value['entries']
			),
		);

		$pro_counters_placeholder = array(
			self::view_args_build_counter(
				__( 'All Views', 'formidable' ),
				self::view_args_build_cta(
					__( 'Learn More', 'formidable' ),
					admin_url( 'admin.php?page=formidable-views' )
				)
			),
			self::view_args_build_counter(
				__( 'Installed Apps', 'formidable' ),
				self::view_args_build_cta(
					__( 'Learn More', 'formidable' ),
					admin_url( 'admin.php?page=formidable-applications' )
				)
			),
		);

		if ( class_exists( 'FrmProDashboardController' ) ) {
			$pro_counters = FrmProDashboardController::get_counters();
			return array_merge( $lite_counters, $pro_counters );
		}

		return array_merge( $lite_counters, $pro_counters_placeholder );
	}

	/**
	 * Build view args for counter widget.
	 *
	 * @param string $heading
	 * @param array  $cta
	 * @param int    $value
	 * @param string $type
	 *
	 * @return array
	 */
	public static function view_args_build_counter( $heading, $cta = array(), $value = 0, $type = 'default' ) {

		$counter_args = array(
			'heading' => $heading,
			'counter' => $value,
			'type'    => 'default',
		);
		if ( ! empty( $cta ) ) {
			$counter_args['cta'] = $cta;
		}

		return $counter_args;
	}

	/**
	 * Build view args for cta.
	 *
	 * @param string $title
	 * @param string $link
	 * @param bool   $display
	 *
	 * @return array
	 */
	public static function view_args_build_cta( $title, $link = '#', $display = true ) {
		return array(
			'title'   => $title,
			'link'    => $link,
			'display' => $display,
		);
	}

	/**
	 * Init total earnings widget view args to FrmDashboardHelper.
	 *
	 * @return array
	 */
	private static function view_args_payments() {

		$prepared_data = array();

		$model_payments = new FrmTransLitePayment();
		$payments       = $model_payments->get_payments_stats();
		foreach ( $payments['total'] as $currency => $total_payments ) {
			if ( 0 < (int) $total_payments ) {
				$prepared_data[] = array(
					'counter_label' => FrmCurrencyHelper::get_currency( $currency ),
					'counter'       => (int) $total_payments,
				);
			}
		}

		return $prepared_data;
	}

	/**
	 * Init view args for entries placeholder.
	 *
	 * @param array $forms_count The total forms count. If there are no any forms yet, we'll have CTA pointing to creating a form.
	 * @return array
	 */
	private static function view_args_entries_placeholder( $forms_count ) {

		if ( ! $forms_count ) {
			$copy = sprintf(
				/* translators: %1$s: HTML start of a tag, %2$s: HTML close a tag */
				__( 'See the %1$sform documentation%2$s for instructions on publishing your form', 'formidable' ),
				'<a target="_blank" href="' . FrmAppHelper::admin_upgrade_link( '', 'knowledgebase/publish-a-form/' ) . '">',
				'</a>'
			);
			return array(
				'background' => 'entries-placeholder',
				'heading'    => __( 'You Have No Entries Yet', 'formidable' ),
				'copy'       => $copy,
				'button'     => array(
					'label' => esc_html__( 'Add New Form', 'formidable' ),
					'link'  => admin_url( 'admin.php?page=' . FrmFormTemplatesController::PAGE_SLUG ),
				),
			);
		}

		$copy = sprintf(
			/* translators: %1$s: HTML start of a tag, %2$s: HTML close a tag */
			__( 'See the %1$sform documentation%2$s for instructions on publishing a form. Once vou have at least one entry you\'ll see it here.', 'formidable' ),
			'<a target="_blank" href="' . FrmAppHelper::admin_upgrade_link( '', 'knowledgebase/publish-a-form/' ) . '">',
			'</a>'
		);
		return array(
			'background' => 'entries-placeholder',
			'heading'    => __( 'You Have No Entries Yet', 'formidable' ),
			'copy'       => $copy,
			'button'     => null,
		);
	}

	/**
	 * A function to handle the counters cta from the top: Total Forms, Total Entries, All Views, Installed Apps.
	 *
	 * @param string       $counter_type
	 * @param int          $counter_value
	 * @param false|object $latest_available_form The form object of the latest form available. If there are at least one form available we show "Add Entry" cta for entries counter.
	 * @return array
	 */
	public static function display_counter_cta( $counter_type, $counter_value, $latest_available_form = false ) {
		if ( $counter_value > 0 || ( 'entries' === $counter_type && false === $latest_available_form ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Hide 3rd party notifications from dashboard
	 *
	 * @return void
	 */
	public static function remove_admin_notices_on_dashboard() {
		if ( false === self::is_dashboard_page() ) {
			return;
		}

		global $wp_filter;
		if ( isset( $wp_filter['admin_notices'] ) ) {
			unset( $wp_filter['admin_notices'] );
		}
	}

	/**
	 * Handle dashboard AJAX requests. Used in FrmHooksController
	 * Action name: dashboard_ajax_action.
	 *
	 * @return void
	 */
	public static function ajax_requests() {
		$dashboard_action = FrmAppHelper::get_post_param( 'dashboard_action', '', 'sanitize_text_field' );

		switch ( $dashboard_action ) {

			case 'welcome-banner-has-closed':
				self::add_welcome_closed_banner_user_id();
				wp_send_json_success();
				break;

			case 'save-subscribed-email':
				$email = FrmAppHelper::get_post_param( 'email', '', 'sanitize_email' );
				self::save_subscribed_email( $email );
				wp_send_json_success();
				break;
		}
	}

	/**
	 * Handle the entries list. Called via add_filter.
	 * Hook name: manage_formidable_page_formidable-dashboard_columns.
	 *
	 * @param array $columns An associative array of column headings.
	 * @return array
	 */
	public static function entries_columns( $columns = array() ) {
		$columns['0_form_id']    = esc_html__( 'Form', 'formidable' );
		$columns['0_name']       = esc_html__( 'Name', 'formidable' );
		$columns['0_user_id']    = esc_html__( 'Author', 'formidable' );
		$columns['0_created_at'] = esc_html__( 'Created on', 'formidable' );
		return $columns;
	}

	/**
	 * Check if user has closed the welcome banner. The status of banner is saved in db options.
	 *
	 * @return bool
	 */
	public static function welcome_banner_has_closed() {
		$user_id                = get_current_user_id();
		$banner_closed_by_users = self::get_closed_welcome_banner_user_ids();

		if ( ! empty( $banner_closed_by_users ) && in_array( $user_id, $banner_closed_by_users, true ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Check if is dashboard page.
	 *
	 * @return bool
	 */
	public static function is_dashboard_page() {
		return FrmAppHelper::is_admin_page( 'formidable-dashboard' );
	}

	/**
	 * Detect if the logged user's email is subscribed. Used for inbox email subscribe.
	 *
	 * @param string $email The logged user's email.
	 * @return bool
	 */
	public static function email_is_subscribed( $email ) {
		$subscribed_emails = self::get_subscribed_emails();
		return in_array( $email, $subscribed_emails, true );
	}

	/**
	 * Register controller assets.
	 *
	 * @return void
	 */
	public static function register_assets() {
		wp_register_script( self::PAGE_SLUG, FrmAppHelper::plugin_url() . '/js/formidable_dashboard.js', array( 'formidable_admin' ), FrmAppHelper::plugin_version(), true );
		wp_register_style( self::PAGE_SLUG, FrmAppHelper::plugin_url() . '/css/admin/dashboard.css', array(), FrmAppHelper::plugin_version() );
	}

	/**
	 * Enqueue controller assets.
	 *
	 * @return void
	 */
	public static function enqueue_assets() {

		if ( ! self::is_dashboard_page() ) {
			return;
		}

		wp_enqueue_style( 'formidable-animations' );
		wp_enqueue_style( self::PAGE_SLUG );
		wp_enqueue_script( self::PAGE_SLUG );
	}

	/**
	 * Init the view args for Inbox widget.
	 *
	 * @return array
	 */
	private static function view_args_inbox() {
		return self::inbox_prepare_messages( FrmInboxController::get_inbox_messages() );
	}

	/**
	 * Prepare inbox messages data.
	 *
	 * @return array
	 */
	private static function inbox_prepare_messages( $data ) {
		foreach ( $data as $key => $messages ) {
			if ( in_array( $key, array( 'unread', 'dismissed' ), true ) ) {
				foreach ( $messages as $key_msg => $message ) {
					$data[ $key ][ $key_msg ]['cta'] = self::inbox_clean_messages_cta( $message['cta'] );
				}
			}
		}
		return $data;
	}

	private static function inbox_clean_messages_cta( $cta ) {

		// remove dismiss button
		$pattern = '/<a[^>]*class="[^"]*frm_inbox_dismiss[^"]*"[^>]*>.*?<\/a>/is';
		return preg_replace( $pattern, ' ', $cta );
	}

	/**
	 * Get the embed YouTube video from YouTube feed api. If there are 0 entries we show the welcome video otherwise latest video from FF YouTube channel is displayed.
	 *
	 * @param int $entries_count The total entries available.
	 * @return string|null The YouTube video ID.
	 */
	private static function get_youtube_embed_video( $entries_count ) {
		$youtube_api    = new FrmYoutubeFeedApi();
		$welcome_video  = $youtube_api->get_video();
		$featured_video = $youtube_api->get_video( 'featured' );

		if ( 0 === (int) $entries_count && false === $welcome_video && false === $featured_video ) {
			return null;
		}
		if ( 0 === (int) $entries_count && false !== $welcome_video ) {
			return isset( $welcome_video['video-id'] ) ? $welcome_video['video-id'] : null;
		}
		// We might receive the most recent video feed as the featured selection.
		if ( isset( $featured_video[0] ) ) {
			return $featured_video[0]['video-id'];
		}
		return isset( $featured_video['video-id'] ) ? $featured_video['video-id'] : null;
	}

	/**
	 * Save subscribed user's email to dashboard options.
	 * Used for Inbox widget - email subscribe.
	 *
	 * @param string $email The user email address.
	 * @return void
	 */
	private static function save_subscribed_email( $email ) {
		$subscribed_emails = self::get_subscribed_emails();
		if ( ! in_array( $email, $subscribed_emails, true ) ) {
			$subscribed_emails[] = $email;
			self::update_dashboard_options( $subscribed_emails, 'inbox-subscribed-emails' );
		}
	}

	/**
	 * Get list of users' ids that have closed the welcome banner.
	 *
	 * @return array
	 */
	private static function get_closed_welcome_banner_user_ids() {
		return self::get_dashboard_options( 'closed-welcome-banner-user-ids' );
	}

	/**
	 * Get list of subscribed emails. The list will contain all emails subscribed via Inbox widget.
	 *
	 * @return array
	 */
	private static function get_subscribed_emails() {
		return self::get_dashboard_options( 'inbox-subscribed-emails' );
	}

	/**
	 * Get the dashboard options from db.
	 *
	 * @param string|null $option_name The dashboard option name. If null it will return all dashboard options.
	 * @return array
	 */
	private static function get_dashboard_options( $option_name = null ) {
		$options = get_option( self::OPTION_META_NAME, array() );
		if ( null !== $option_name && ! isset( $options[ $option_name ] ) ) {
			return array();
		}
		if ( null !== $option_name ) {
			return $options[ $option_name ];
		}
		return $options;
	}

	/**
	 * Update the dashboard options to db.
	 *
	 * @param array  $data
	 * @param string $option_name
	 *
	 * @return void
	 */
	private static function update_dashboard_options( $data, $option_name ) {
		$options                 = self::get_dashboard_options();
		$options[ $option_name ] = $data;
		update_option( self::OPTION_META_NAME, $options, 'no' );
	}

	/**
	 * Save user id to closed banner list.
	 *
	 * @return void
	 */
	private static function add_welcome_closed_banner_user_id() {
		$users_list = self::get_closed_welcome_banner_user_ids();
		$user_id    = get_current_user_id();
		if ( ! in_array( $user_id, $users_list, true ) ) {
			$users_list[] = $user_id;
			self::update_dashboard_options( $users_list, 'closed-welcome-banner-user-ids' );
		}
	}
}
