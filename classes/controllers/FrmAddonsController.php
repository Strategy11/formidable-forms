<?php

class FrmAddonsController {

	public static function menu() {
		add_submenu_page( 'formidable', 'Formidable | ' . __( 'AddOns', 'formidable' ), __( 'AddOns', 'formidable' ), 'frm_view_forms', 'formidable-addons', 'FrmAddonsController::list_addons' );

		if ( ! FrmAppHelper::pro_is_installed() ) {
			add_submenu_page( 'formidable', 'Formidable | ' . __( 'Upgrade to Pro', 'formidable' ), __( 'Upgrade to Pro', 'formidable' ), 'frm_view_forms', 'formidable-pro-upgrade', 'FrmAddonsController::upgrade_to_pro' );
		}
	}

	public static function list_addons() {
		$installed_addons = apply_filters( 'frm_installed_addons', array() );

		$addons = self::get_api_addons();
		self::prepare_addons( $addons );

		$site_url = 'https://formidableforms.com/';

		include( FrmAppHelper::plugin_path() . '/classes/views/addons/list.php' );
	}

	public static function license_settings() {
		$plugins = apply_filters( 'frm_installed_addons', array() );
		if ( empty( $plugins ) ) {
			_e( 'There are no plugins on your site that require a license', 'formidable' );
			return;
		}

		$allow_autofill = self::allow_autofill();

		include( FrmAppHelper::plugin_path() . '/classes/views/addons/settings.php' );
	}

	/**
	 * Don't allow subsite addon licenses to be fetched
	 * unless the current user has super admin permissions
	 *
	 * @since 2.03.10
	 */
	private static function allow_autofill() {
		$allow_autofill = FrmAppHelper::pro_is_installed();
		if ( $allow_autofill && is_multisite() ) {
			$sitewide_activated = get_site_option( 'frmpro-wpmu-sitewide' );
			if ( $sitewide_activated ) {
				$allow_autofill = current_user_can( 'setup_network' );
			}
		}
		return $allow_autofill;
	}

	private static function get_api_addons() {
		$addons = array(
			'formidable-pro' => array(
				'title'   => 'Formidable Pro',
				'link'    => 'pricing/',
				'docs'    => '',
				'file'    => 'formidable/pro',
				'excerpt' => 'Enhance your basic Formidable forms with a plethora of Pro field types and features. Create advanced forms and data-driven applications in minutes.',
			),
			'mailchimp' => array(
				'title'   => 'MailChimp',
				'excerpt' => 'Get on the path to more sales and leads in a matter of minutes. Add leads to a MailChimp mailing list when they submit forms and update their information along with the entry.',
			),
			'registration' => array(
				'title'   => 'User Registration',
				'link'    => 'downloads/user-registration/',
				'excerpt' => 'Give new users access to your site as quickly and painlessly as possible. Allow users to register, edit and be able to login to their profiles on your site from the front end in a clean, customized registration form.',
			),
			'paypal' => array(
				'title'   => 'PayPal Standard',
				'link'    => 'downloads/paypal-standard/',
				'excerpt' => 'Automate your business by collecting instant payments from your clients. Collect information, calculate a total, and send them on to PayPal. Require a payment before publishing content on your site.',
			),
			'stripe' => array(
				'title'   => 'Stripe',
				'docs'    => 'stripe/',
				'excerpt' => 'Any Formidable forms on your site can accept credit card payments without users ever leaving your site.',
			),
			'authorize-net' => array(
				'title'   => 'Authorize.net AIM',
				'link'    => 'downloads/authorize-net-aim/',
				'docs'    => 'authorize-net-aim/',
				'excerpt' => 'Accept one-time payments directly on your site, using Authorize.net AIM.',
			),
			'woocommerce' => array(
				'title'   => 'WooCommerce',
				'excerpt' => 'Use a Formidable form on your WooCommerce product pages.',
			),
			'autoresponder' => array(
				'title'   => 'Form Action Automation',
				'docs'    => 'schedule-autoresponder/',
				'excerpt' => 'Schedule email notifications, SMS messages, and API actions.',
			),
			'modal' => array(
				'title'   => 'Bootstrap Modal',
				'link'    => 'downloads/bootstrap-modal/',
				'docs'    => 'bootstrap-modal/',
				'excerpt' => 'Open a view or form in a Bootstrap popup.',
			),
			'bootstrap' => array(
				'title'   => 'Bootstrap',
				'excerpt' => 'Instantly add Bootstrap styling to all your Formidable forms.',
			),
			'zapier' => array(
				'title'   => 'Zapier',
				'excerpt' => 'Connect with hundreds of different applications through Zapier. Insert a new row in a Google docs spreadsheet, post on Twitter, or add a new Dropbox file with your form.',
			),
			'signature' => array(
				'title'   => 'Signature',
				'excerpt' => 'Add a signature field to your form. The user may write their signature with a trackpad/mouse or just type it.',
			),
			'api' => array(
				'title'   => 'Formidable API',
				'link'    => 'downloads/formidable-api/',
				'excerpt' => 'Send entry results to any other site that has a Rest API. This includes the option of sending entries from one Formidable site to another.',
			),
			'twilio' => array(
				'title'   => 'Twilio',
				'docs'    => 'twilio-add-on/',
				'excerpt' => 'Allow users to text their votes for polls created by Formidable Forms, or send SMS notifications when entries are submitted or updated.',
			),
			'aweber' => array(
				'title'   => 'AWeber',
				'excerpt' => 'Subscribe users to an AWeber mailing list when they submit a form. AWeber is a powerful email marketing service.',
			),
			'highrise' => array(
				'title'   => 'Highrise',
				'excerpt' => 'Add your leads to your Highrise CRM account any time a Formidable form is submitted.',
			),
			'wpml' => array(
				'title'   => 'WP Multilingual',
				'link'    => 'downloads/wp-multilingual/',
				'docs'    => 'formidable-multi-language/',
				'excerpt' => 'Translate your forms into multiple languages using the Formidable-integrated WPML plugin.',
			),
			'polylang' => array(
				'title'   => 'Polylang',
				'excerpt' => 'Create bilingual or multilingual forms with help from Polylang.',
			),
			'math-captcha' => array(
				'title'   => 'Math Captcha',
				'excerpt' => 'Require users to perform a simple calculation before submitting a form to prevent spam. This add-on extends BestWebSoft\'s Captcha plugin.',
			),
			'locations' => array(
				'title'   => 'Locations',
				'excerpt' => 'Populate fields with Countries, States/Provinces, U.S. Counties, and U.S. Cities. This data can then be used in dependent Data from Entries fields.',
			),
			'user-tracking' => array(
				'title'   => 'User Tracking',
				'excerpt' => 'Track which pages a user visits prior to submitting a form.',
			),
		);

		return $addons;
	}

	private static function prepare_addons( &$addons ) {
		$activate_url = '';
		if ( current_user_can( 'activate_plugins' ) ) {
			$activate_url = add_query_arg( array( 'action' => 'activate' ), admin_url( 'plugins.php' ) );
		}

		$loop_addons = $addons;
		foreach ( $loop_addons as $slug => $addon ) {
			if ( isset( $addon['file'] ) ) {
				$base_file = $addon['file'];
			} else {
				$base_file = 'formidable-' . $slug;
			}
			$file = WP_PLUGIN_DIR . '/' . $base_file;

			$addon['installed'] = is_dir( $file );
			$addon['activate_url'] = '';
			if ( $addon['installed'] && ! empty( $activate_url ) ) {
				if ( file_exists( $file . '/' . $base_file . '.php' ) ) {
					$file_name = $base_file . '/' . $base_file . '.php';
					if ( ! is_plugin_active( $file_name ) ) {
						$addon['activate_url'] = add_query_arg( array(
							'_wpnonce'    => wp_create_nonce( 'activate-plugin_' . $file_name ),
							'plugin'      => $file_name,
						), $activate_url );
					}
				}
			}

			if ( ! isset( $addon['docs'] ) ) {
				$addon['docs'] = 'formidable-' . $slug . '/';
			}

			if ( ! isset( $addon['link'] ) ) {
				$addon['link'] = 'downloads/' . $slug . '/';
			}
			$addon['link'] = FrmAppHelper::make_affiliate_url( $addon['link'] );

			$addons[ $slug ] = $addon;
		}
	}

	public static function get_licenses() {
		$allow_autofill = self::allow_autofill();
		$required_role = $allow_autofill ? 'setup_network' : 'frm_change_settings';
		FrmAppHelper::permission_check( $required_role );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		if ( is_multisite() && get_site_option( 'frmpro-wpmu-sitewide' ) ) {
			$license = get_site_option( 'frmpro-credentials' );
		} else {
			$license = get_option( 'frmpro-credentials' );
		}

		if ( $license && is_array( $license ) && isset( $license['license'] ) ) {
			$url = 'https://formidableforms.com/frm-edd-api/licenses?l=' . urlencode( base64_encode( $license['license'] ) );
			$licenses = self::send_api_request( $url, array( 'name' => 'frm_api_licence', 'expires' => 60 * 60 * 5 ) );
			echo json_encode( $licenses );
		}

		wp_die();
	}

	private static function send_api_request( $url, $transient = array() ) {
		$data = get_transient( $transient['name'] );
		if ( $data !== false ) {
			return $data;
		}

		$arg_array = array(
			'body'      => array(
				'url'   => home_url(),
			),
			'timeout'   => 15,
			'sslverify' => false,
			'user-agent' => 'Formidable/' . FrmAppHelper::$plug_version . '; ' . home_url(),
		);

		$response = wp_remote_post( $url, $arg_array );
		$body = wp_remote_retrieve_body( $response );
		$data = false;
		if ( ! is_wp_error( $response ) && ! is_wp_error( $body ) ) {
			$data = json_decode( $body, true );
			set_transient( $transient['name'], $data, $transient['expires'] );
		}

		return $data;
	}

	public static function upgrade_to_pro() {
		$pro_pricing = self::prepare_pro_info();

		include( FrmAppHelper::plugin_path() . '/classes/views/addons/upgrade_to_pro.php' );
	}

	private static function prepare_pro_info() {
		$pro_pricing = array(
			'personal'      => array( 'id' => 5, 'price' => '49.00', 'name' => 'Personal' ),
			'professional'  => array( 'id' => 6, 'price' => '99.00', 'name' => 'Professional' ),
			'smallbusiness' => array( 'id' => 3, 'price' => '199.00', 'name' => 'Small Business' ),
			'enterprise'    => array( 'id' => 4, 'price' => '399.00', 'name' => 'Enterprise' ),
		);

		return $pro_pricing;
	}

	/**
	 * Add a filter to shorten the EDD filename for Formidable plugin, and add-on, updates
	 *
	 * @since 2.03.08
	 *
	 * @param boolean $return
	 * @param string $package
	 *
	 * @return boolean
	 */
	public static function add_shorten_edd_filename_filter( $return, $package ) {
		if ( strpos( $package, '/edd-sl/package_download/' ) !== false && strpos( $package, 'formidableforms.com' ) !== false ) {
			add_filter( 'wp_unique_filename', 'FrmAddonsController::shorten_edd_filename', 10, 2 );
		}

		return $return;
	}

	/**
	 * Shorten the EDD filename for automatic updates
	 * Decreases size of file path so file path limit is not hit on Windows servers
	 *
	 * @since 2.03.08
	 *
	 * @param string $filename
	 * @param string $ext
	 *
	 * @return string
	 */
	public static function shorten_edd_filename( $filename, $ext ) {
		$filename = substr( $filename, 0, 50 ) . $ext;
		remove_filter( 'wp_unique_filename', 'FrmAddonsController::shorten_edd_filename', 10 );

		return $filename;
	}
}
