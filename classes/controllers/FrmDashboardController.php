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

	private static $banner_closed_cookie_name = 'frm-welcome-banner-closed';

	public static function menu() {
		add_submenu_page( 'formidable', 'Formidable | ' . __( 'Dashboard', 'formidable' ), __( 'Dashboard', 'formidable' ), 'frm_view_forms', 'formidable-dashboard', 'FrmDashboardController::route' );
	}

	public static function route() {
		$dashboard_view = new FrmDashboardView(
			array(
				'counters' => array(
					'template-type' => '',
					'counters'      => self::view_args_counters(),
				),
				'license'  => self::view_args_licence(),
			),
		);
		require FrmAppHelper::plugin_path() . '/classes/views/dashboard/dashboard.php';
	}

	private static function view_args_counters() {
		$latest_available_form = FrmFormsController::get_latest_form();
		$counters_value        = array(
			'forms'   => FrmFormsController::get_forms_count(),
			'entries' => FrmEntriesController::get_entries_count(),
		);
		$lite_counters         = array(
			array(
				'heading' => 'Total Forms',
				'type'    => 'default',
				'counter' => $counters_value['forms'],
			),
			array(
				'heading' => 'Total Entries',
				'type'    => 'default',
				'cta'     => array(
					'display' => self::display_counter_cta( 'entries', $counters_value['entries'], $latest_available_form ),
					'title'   => 'Add Entry',
					'link'    => admin_url( 'admin.php?page=formidable-entries&frm_action=new&form=' . $latest_available_form->id ),
				),
				'counter' => $counters_value['entries'],
			),
		);

		$pro_counters_placeholder = array(
			array(
				'heading'  => 'All Views',
				'counter'  => 0,
				'type'     => 'default',
				'disabled' => true,
				'tooltip'  => 'Views are available with a PRO plan only',
			),
			array(
				'heading'  => 'Installed Apps',
				'counter'  => 0,
				'type'     => 'default',
				'disabled' => true,
				'tooltip'  => 'Aplications are available with a PRO plan only',
			),
		);

		if ( class_exists( 'FrmProDashboardController' ) ) {
			$pro_counters = FrmProDashboardController::get_counters();
			return array_merge( $lite_counters, $pro_counters );
		}

		return array_merge( $lite_counters, $pro_counters_placeholder );

	}

	private static function view_args_licence() {
		if ( class_exists( 'FrmProDashboardController' ) ) {
			return FrmProDashboardController::view_args_licence();
		}
		return array(
			'heading' => 'License Key',
			'copy'    => 'You\'re using Formidable Forms Lite - no license needed. Enjoy! 🙂',
			'buttons' => array(
				array(
					'label'  => 'Connect Account',
					'link'   => FrmAddonsController::connect_link(),
					'action' => 'default',
					'type'   => 'primary',
				),
				array(
					'label'  => 'Get Formidable PRO',
					'link'   => FrmAppHelper::admin_upgrade_link( 'settings-license' ),
					'action' => 'default',
					'type'   => 'secondary',
				),
			),
		);
	}

	public static function display_counter_cta( $counter_type, $counter_value, $latest_available_form = null ) {
		if ( $counter_value > 0 ) {
			return false;
		}

		if ( 'entries' === $counter_type && null !== $latest_available_form && is_object( $latest_available_form ) ) {
			return true;
		}

		return false;
	}

	public static function ajax_requests() {
		$dashboard_action = FrmAppHelper::get_post_param( 'dashboard_action', '', 'sanitize_text_field' );

		if ( 'welcome-banner-cookie' === $dashboard_action ) {
			if ( true === self::ajax_set_cookie_banner( FrmAppHelper::get_post_param( 'banner_has_closed' ) ) ) {
				echo wp_json_encode( array( 'success' => true ) );
				wp_die();
			}
			echo wp_json_encode( array( 'success' => false ) );
			wp_die();
		}
	}

	private static function ajax_set_cookie_banner( $banner_has_closed ) {
		if ( 1 === (int) $banner_has_closed ) {
			$expiration_time = time() + ( 400 * 24 * 60 * 60 ); // 400 days. Maximum expiration time allowed by Chrome.
			setcookie( self::$banner_closed_cookie_name, 1 . '|' . $expiration_time, $expiration_time );
			return true;
		}
		return false;
	}

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

		if ( 'formidable-dashboard' !== FrmAppHelper::simple_get( 'page', 'sanitize_title' ) ) {
			return;
		}

		wp_enqueue_style( self::$assets_handle_name );
		wp_enqueue_script( self::$assets_handle_name );
	}

}
