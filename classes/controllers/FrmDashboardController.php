<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmDashboardController {

	/**
	 * Handle name used for registering controller scripts and style.
	 *
	 * @var string Handle name used for wp_register_script|wp_register_style
	 */
	public static $assets_handle_name = 'formidable-dashboard';

	/**
	 * Welcome banner cookie name. When welcome banner is closed we store its status into a cookie.
	 *
	 * @var string
	 */
	private static $banner_closed_cookie_name = 'frm-welcome-banner-closed';

	/**
	 * Option name used to store the dashboard options into db options table.
	 *
	 * @var string
	 */
	private static $option_meta_name = 'frm-dashboard-options';

	/**
	 * Register Dashboard page tp Formidable admin menu.
	 *
	 * @return void
	 */
	public static function menu() {
		add_submenu_page( 'formidable', 'Formidable | ' . __( 'Dashboard', 'formidable' ), __( 'Dashboard', 'formidable' ) . FrmInboxController::get_notice_count(), 'frm_view_forms', 'formidable-dashboard', 'FrmDashboardController::route' );
		if ( ! self::is_dashboard_page() ) {
			return;
		}
		add_filter( 'manage_' . sanitize_title( FrmAppHelper::get_menu_name() ) . '_page_formidable-dashboard_columns', 'FrmDashboardController::entries_columns' );
	}

	/**
	 * Init dashboard page.
	 *
	 * @return void
	 */
	public static function route() {
		$latest_available_form = FrmFormsController::get_latest_form();
		$total_payments        = self::view_args_payments();
		$counters_value        = array(
			'forms'   => FrmFormsController::get_forms_count(),
			'entries' => FrmEntriesController::get_entries_count(),
		);

		$dashboard_view = new FrmDashboardView(
			array(
				'counters' => array(
					'template-type' => '',
					'counters'      => self::view_args_counters( $latest_available_form, $counters_value ),
				),
				'license'  => self::view_args_license(),
				'inbox'    => self::view_args_inbox(),
				'entries'  => array(
					'widget-heading'   => esc_html__( 'Latest Entries', 'formidable' ),
					'cta'              => array(
						'label' => esc_html__( 'View all entries', 'formidable' ),
						'link'  => admin_url( 'admin.php?page=formidable-entries' ),
					),
					'show-placeholder' => 0 < (int) $counters_value['entries'] ? false : true,
					'count'            => $counters_value['entries'],
					'placeholder'      => self::view_args_entries_placeholder( $counters_value['forms'] ),
				),
				'payments' => array(
					'template-type'    => 'full-width',
					'show-placeholder' => empty( $total_payments ),
					'placeholder'      => array(
						'copy' => esc_html__( 'You don\'t have a payment form setup yet.', 'formidable' ),
						'cta'  => array(
							'classname' => 'frm-trigger-new-form-modal',
							'link'      => '#',
							'label'     => esc_html__( 'Create a Payment Form', 'formidable' ),
						),
					),
					'counters'         => array(
						array(
							'heading' => esc_html__( 'Total earnings', 'formidable' ),
							'type'    => 'currency',
							'items'   => $total_payments,
						),
					),
				),
				'video'    => array( 'id' => self::get_youtube_embed_video( $counters_value['entries'] ) ),
			),
		);
		require FrmAppHelper::plugin_path() . '/classes/views/dashboard/dashboard.php';
	}

	/**
	 * Init top counters widgets view args used to construct FrmDashboardView.
	 *
	 * @param object|false $latest_available_form. If a form is availble, we utilize its ID to direct the 'Create New Entry' link of the entries counter CTA when no entries exist.
	 * @param array $counters_value. The counter values for "Total Forms" & "Total Entries"
	 *
	 * @return array
	 */
	private static function view_args_counters( $latest_available_form, $counters_value ) {
		$add_entry_cta_link = false !== $latest_available_form && isset( $latest_available_form->id ) ? admin_url( 'admin.php?page=formidable-entries&frm_action=new&form=' . $latest_available_form->id ) : '';

		$lite_counters = array(
			array(
				'heading' => esc_html__( 'Total Forms', 'formidable' ),
				'type'    => 'default',
				'counter' => $counters_value['forms'],
			),
			array(
				'heading' => esc_html__( 'Total Entries', 'formidable' ),
				'type'    => 'default',
				'cta'     => array(
					'display' => self::display_counter_cta( 'entries', $counters_value['entries'], $latest_available_form ),
					'title'   => esc_html__( 'Add Entry', 'formidable' ),
					'link'    => $add_entry_cta_link,
				),
				'counter' => $counters_value['entries'],
			),
		);

		$pro_counters_placeholder = array(
			array(
				'heading'  => esc_html__( 'All Views', 'formidable' ),
				'counter'  => 0,
				'type'     => 'default',
				'disabled' => true,
				'tooltip'  => esc_html__( 'Views are available with a PRO plan only', 'formidable' ),
			),
			array(
				'heading'  => esc_html__( 'Installed Apps', 'formidable' ),
				'counter'  => 0,
				'type'     => 'default',
				'disabled' => true,
				'tooltip'  => esc_html__( 'Aplications are available with a PRO plan only', 'formidable' ),
			),
		);

		if ( class_exists( 'FrmProDashboardController' ) ) {
			$pro_counters = FrmProDashboardController::get_counters();
			return array_merge( $lite_counters, $pro_counters );
		}

		return array_merge( $lite_counters, $pro_counters_placeholder );

	}

	/**
	 * Init total earnings widget view args to construct FrmDashboardView.
	 *
	 * @return array
	 */
	private static function view_args_payments() {

		$prepared_data = array();

		if ( ! is_callable( 'FrmTransLiteAppHelper::get_payments_data' ) ) {
			return $prepared_data;
		}

		$payments = FrmTransLiteAppHelper::get_payments_data();
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
	 * Init the view args for LITE license.
	 *
	 * @return array
	 */
	private static function view_args_license() {
		if ( class_exists( 'FrmProDashboardController' ) ) {
			return FrmProDashboardController::view_args_license();
		}
		return array(
			'heading' => 'License Key',
			'copy'    => esc_html__( 'You\'re using Formidable Forms Lite - no license needed. Enjoy!', 'formidable' ) . ' 🙂',
			'buttons' => array(
				array(
					'label'  => esc_html__( 'Connect Account', 'formidable' ),
					'link'   => FrmAddonsController::connect_link(),
					'action' => 'default',
					'type'   => 'primary',
				),
				array(
					'label'  => esc_html__( 'Get Formidable PRO', 'formidable' ),
					'link'   => FrmAppHelper::admin_upgrade_link( 'settings-license' ),
					'action' => 'default',
					'type'   => 'secondary',
				),
			),
		);
	}

	/**
	 * Init view args for entries placeholder.
	 *
	 * @param array $forms_count The total forms count. If there are no any forms yet, we'll have CTA pointing to creating a form.
	 * @return array
	 */
	private static function view_args_entries_placeholder( $forms_count ) {

		if ( 0 === (int) $forms_count ) {
			$copy = sprintf(
				/* translators: %1$s: HTML start of a & b tag, %2$s: HTML close b & a tag */
				esc_html__( 'See the %1$sform documentation%2$s for instructions on publishin your form', 'formidable' ),
				'<a target="_blank" href="' . FrmAppHelper::admin_upgrade_link( '', 'knowledgebase/publish-a-form/' ) . '"><b>',
				'</b></a>'
			);
			return array(
				'background' => 'entries-placeholder',
				'heading'    => esc_html__( 'You Have No Entries Yet', 'formidable' ),
				'copy'       => $copy,
				'button'     => array(
					'label'     => esc_html__( 'Add New Form', 'formidable' ),
					'link'      => '#',
					'classname' => 'frm-trigger-new-form-modal',
				),
			);
		}

		$copy = sprintf(
			/* translators: %1$s: HTML start of a & b tag, %2$s: HTML close b & a tag */
			esc_html__( 'See the %1$sform documentation%2$s for instructions on publishing your form. once vou nave at least one entry you\'ll see it here.', 'formidable' ),
			'<a target="_blank" href="' . FrmAppHelper::admin_upgrade_link( '', 'knowledgebase/publish-a-form/' ) . '"><b>',
			'</b></a>'
		);
		return array(
			'background' => 'entries-placeholder',
			'heading'    => 'You Have No Entries Yet',
			'copy'       => $copy,
			'button'     => null,
		);
	}

	/**
	 * A function to handle the counters cta from the top: Total Forms, Total Entries, All Views, Installed Apps.
	 *
	 * @param string $counter_type
	 * @param int $counter_value
	 * @param object|false $latest_available_form The form object of the latest form available. If there are at least one form available we show "Add Entry" cta for entries counter.
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
		if ( 'formidable-dashboard' !== FrmAppHelper::simple_get( 'page', 'sanitize_title' ) ) {
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

			case 'welcome-banner-cookie':
				if ( true === self::ajax_set_cookie_banner( FrmAppHelper::get_post_param( 'banner_has_closed' ) ) ) {
					echo wp_json_encode( array( 'success' => true ) );
					wp_die();
				}
				echo wp_json_encode( array( 'success' => false ) );
				wp_die();
				break;

			case 'save-subscribed-email':
				$email = FrmAppHelper::get_post_param( 'email' );
				self::save_subscribed_email( $email );
				echo wp_json_encode( array( 'success' => true ) );
				wp_die();
				break;
		}
	}

	/**
	 * Handle the entries list. Called via add_filter.
	 * Hook name: manage_formidable_page_formidable-dashboard_columns.
	 *
	 * @param array $columns.
	 * @return array
	 */
	public static function entries_columns( $columns = array() ) {

		$form_id = FrmForm::get_current_form_id();

		if ( $form_id ) {
			self::get_columns_for_form( $form_id, $columns );
		} else {
			$columns[ $form_id . '_form_id' ] = esc_html__( 'Form', 'formidable' );
			$columns[ $form_id . '_name' ]    = esc_html__( 'Name', 'formidable' );
			$columns[ $form_id . '_user_id' ] = esc_html__( 'Author', 'formidable' );
		}

		$columns[ $form_id . '_created_at' ] = esc_html__( 'Created on', 'formidable' );
		$columns[ $form_id . '_updated_at' ] = esc_html__( 'Updated on', 'formidable' );

		return $columns;

	}

	/**
	 * Check if user has closed the welcome banner. The status of banner is saved in a cookie: self::$banner_closed_cookie_name.
	 *
	 * @return boolean
	 */
	public static function welcome_banner_has_closed() {
		if ( isset( $_COOKIE[ self::$banner_closed_cookie_name ] ) ) {
			list( $cookie_value, $expiration_time ) = explode( '|', sanitize_text_field( wp_unslash( $_COOKIE[ self::$banner_closed_cookie_name ] ) ) );
			if ( 1 === (int) $cookie_value ) {
				// Refresh welcome banner cookie if it will expire in less 45 days.
				if ( (int) $expiration_time < time() + ( 45 * 24 * 60 * 60 ) ) {
					self::ajax_set_cookie_banner( 1 );
				}
				return true;
			}
			return false;
		}
		return false;
	}

	/**
	 * Check if is dashboard page.
	 *
	 * @return boolean
	 */
	public static function is_dashboard_page() {
		if ( 'formidable-dashboard' === FrmAppHelper::simple_get( 'page', 'sanitize_title' ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Detect if the logged user's email is subscribed. Used for inbox email subscribe.
	 *
	 * @param string $email The logged user's email.
	 * @return boolean
	 */
	public static function email_is_subscribed( $email ) {
		$subscribed_emails = self::get_subscribed_emails();
		return false !== array_search( $email, $subscribed_emails, true ) ? true : false;
	}

	/**
	 * Register controller assets.
	 *
	 * @return void
	 */
	public static function register_assets() {
		wp_register_script( self::$assets_handle_name, FrmAppHelper::plugin_url() . '/js/formidable_dashboard.js', array( 'formidable_admin' ), FrmAppHelper::plugin_version(), true );
		wp_register_style( self::$assets_handle_name, FrmAppHelper::plugin_url() . '/css/admin/dashboard.css', array(), FrmAppHelper::plugin_version() );
	}

	/**
	 * Enqueue controller assets.
	 *
	 * @return void
	 */
	public static function enqueue_assets() {

		if ( false === self::is_dashboard_page() ) {
			return;
		}

		wp_enqueue_style( self::$assets_handle_name );
		wp_enqueue_script( self::$assets_handle_name );
	}

	/**
	 * Init the view args for Inbox widged.
	 *
	 * @return array
	 */
	private static function view_args_inbox() {
		return FrmInboxController::get_inbox_messages();
	}

	/**
	 * Get the embed YouTube video from YouTube feed api. If there are 0 entries we show the welcome video otherwise latest video from FF YouTube channel is displayed.
	 *
	 * @param int $entries_count The total entries available.
	 * @return string The YouTube video ID.
	 */
	private static function get_youtube_embed_video( $entries_count ) {
		$youtube_api   = new FrmYoutubeFeedApi();
		$welcome_video = $youtube_api->get_welcome_video();
		$latest_video  = $youtube_api->get_latest_video();

		if ( 0 === (int) $entries_count && false === $welcome_video && false === $latest_video ) {
			return null;
		}
		if ( 0 === (int) $entries_count && false !== $welcome_video ) {
			return $welcome_video['video-id'];
		}
		return $latest_video[0]['video-id'];

	}

	/**
	 * Save subscribed user's email to dashboard options.
	 * Used for Inbox widget - email subscribe.
	 *
	 * @param string $email.
	 * @return void
	 */
	private static function save_subscribed_email( $email ) {
		$subscribed_emails = self::get_subscribed_emails();
		$options           = self::get_dashboard_options();
		if ( false === array_search( $email, $subscribed_emails, true ) ) {
			$subscribed_emails[]                = $email;
			$options['inbox-subscribed-emails'] = $subscribed_emails;
			self::update_dashboard_options( $options );
			return;
		}
	}

	/**
	 * Get list of subscribed emails. The list will contain all emails subscribed via Inbox widget.
	 *
	 * @return array
	 */
	private static function get_subscribed_emails() {
		$options = self::get_dashboard_options();
		if ( ! isset( $options['inbox-subscribed-emails'] ) ) {
			return array();
		}
		return $options['inbox-subscribed-emails'];
	}

	/**
	 * Get the dashboard options from db.
	 *
	 * @return array
	 */
	private static function get_dashboard_options() {
		return get_option( self::$option_meta_name, array() );
	}

	/**
	 * Update the dashboard options to db.
	 *
	 * @return void
	 */
	private static function update_dashboard_options( $data ) {
		update_option( self::$option_meta_name, $data, 'no' );
	}

	/**
	 * When the welcome banner is closed, we save its status into a cookie.
	 * Cookie name: self::$banner_closed_cookie_name.
	 *
	 * @return boolean
	 */
	private static function ajax_set_cookie_banner( $banner_has_closed ) {
		if ( 1 === (int) $banner_has_closed ) {
			$expiration_time = time() + ( 400 * 24 * 60 * 60 ); // 400 days. Maximum expiration time allowed by Chrome.
			setcookie( self::$banner_closed_cookie_name, 1 . '|' . $expiration_time, $expiration_time );
			return true;
		}
		return false;
	}
}
