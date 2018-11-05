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

		$pricing = FrmAppHelper::admin_upgrade_link( 'addons' );

		include( FrmAppHelper::plugin_path() . '/classes/views/addons/list.php' );
	}

	public static function license_settings() {
		$plugins = apply_filters( 'frm_installed_addons', array() );
		if ( empty( $plugins ) ) {
			esc_html_e( 'There are no plugins on your site that require a license', 'formidable' );
			return;
		}

		ksort( $plugins );
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
		$addons = array();
		$url = 'https://formidableforms.com/wp-json/s11edd/v1/updates/';
		if ( FrmAppHelper::pro_is_installed() ) {
			$edd_update = new FrmProEddController();
			$license = $edd_update->get_license();
			if ( ! empty( $license ) ) {
				$url .= '?l=' . urlencode( base64_encode( $license ) );
			}
		}

		$response = wp_remote_get( $url );
		if ( is_array( $response ) && ! is_wp_error( $response ) ) {
		    $addons = $response['body'];
		}

		if ( ! empty( $addons ) ) {
			$addons = json_decode( $addons, true );
			$skip_categories = array( 'WordPress Form Templates', 'WordPress Form Style Templates' );
			foreach ( $addons as $k => $addon ) {
				$cats = array_intersect( $skip_categories, $addon['categories'] );
				if ( empty( $addon['excerpt'] ) || ! empty( $cats ) ) {
					unset( $addons[ $k ] );
				}
			}
			return $addons;
		}

		$addons = array(
			'formidable-pro' => array(
				'title'   => 'Formidable Pro',
				'link'    => 'pricing/',
				'docs'    => '',
				'excerpt' => 'Enhance your basic Formidable forms with a plethora of Pro field types and features. Create advanced forms and data-driven applications in minutes.',
			),
			'mailchimp' => array(
				'title'   => 'MailChimp Forms',
				'excerpt' => 'Get on the path to more sales and leads in a matter of minutes. Add leads to a MailChimp mailing list when they submit forms and update their information along with the entry.',
			),
			'registration' => array(
				'title'   => 'User Registration Forms',
				'link'    => 'downloads/user-registration/',
				'excerpt' => 'Give new users access to your site as quickly and painlessly as possible. Allow users to register, edit and be able to login to their profiles on your site from the front end in a clean, customized registration form.',
			),
			'paypal' => array(
				'title'   => 'PayPal Standard Forms',
				'link'    => 'downloads/paypal-standard/',
				'excerpt' => 'Automate your business by collecting instant payments from your clients. Collect information, calculate a total, and send them on to PayPal. Require a payment before publishing content on your site.',
			),
			'stripe' => array(
				'title'   => 'Stripe Forms',
				'docs'    => 'knowledgebase/stripe/',
				'excerpt' => 'Any Formidable forms on your site can accept credit card payments without users ever leaving your site.',
			),
			'authorize-net' => array(
				'title'   => 'Authorize.net AIM Forms',
				'link'    => 'downloads/authorize-net-aim/',
				'docs'    => 'knowledgebase/authorize-net-aim/',
				'excerpt' => 'Accept one-time payments directly on your site, using Authorize.net AIM.',
			),
			'woocommerce' => array(
				'title'   => 'WooCommerce Forms',
				'excerpt' => 'Use a Formidable form on your WooCommerce product pages.',
			),
			'autoresponder' => array(
				'title'   => 'Form Action Automation',
				'docs'    => 'knowledgebase/schedule-autoresponder/',
				'excerpt' => 'Schedule email notifications, SMS messages, and API actions.',
			),
			'modal' => array(
				'title'   => 'Bootstrap Modal Forms',
				'link'    => 'downloads/bootstrap-modal/',
				'docs'    => 'knowledgebase/bootstrap-modal/',
				'excerpt' => 'Open a view or form in a Bootstrap popup.',
			),
			'bootstrap' => array(
				'title'   => 'Bootstrap Style Forms',
				'excerpt' => 'Instantly add Bootstrap styling to all your Formidable forms.',
			),
			'zapier' => array(
				'title'   => 'Zapier Forms',
				'excerpt' => 'Connect with hundreds of different applications through Zapier. Insert a new row in a Google docs spreadsheet, post on Twitter, or add a new Dropbox file with your form.',
			),
			'signature' => array(
				'title'   => 'Digital Signature Forms',
				'excerpt' => 'Add a signature field to your form. The user may write their signature with a trackpad/mouse or just type it.',
			),
			'api' => array(
				'title'   => 'Formidable Forms API',
				'link'    => 'downloads/formidable-api/',
				'excerpt' => 'Send entry results to any other site that has a Rest API. This includes the option of sending entries from one Formidable site to another.',
			),
			'twilio' => array(
				'title'   => 'Twilio SMS Forms',
				'docs'    => 'knowledgebase/twilio-add-on/',
				'excerpt' => 'Allow users to text their votes for polls created by Formidable Forms, or send SMS notifications when entries are submitted or updated.',
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
		foreach ( $loop_addons as $id => $addon ) {
			if ( is_numeric( $id ) ) {
				$slug = str_replace( array( '-wordpress-plugin', '-wordpress' ), '', $addon['slug'] );
				self::prepare_folder_name( $addon );
			} else {
				$slug = $id;
			}
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
						$addon['activate_url'] = add_query_arg(
							array(
								'_wpnonce'    => wp_create_nonce( 'activate-plugin_' . $file_name ),
								'plugin'      => $file_name,
							),
							$activate_url
						);
					}
				}
			}

			if ( ! isset( $addon['docs'] ) ) {
				$addon['docs'] = 'knowledgebase/formidable-' . $slug . '/';
			}
			self::prepare_addon_link( $addon['docs'] );

			if ( ! isset( $addon['link'] ) ) {
				$addon['link'] = 'downloads/' . $slug . '/';
			}
			self::prepare_addon_link( $addon['link'] );

			self::set_addon_status( $addon );
			$addons[ $id ] = $addon;
		}
	}

	/**
	 * @since 3.04.02
	 */
	private static function prepare_folder_name( &$addon ) {
		if ( isset( $addon['url'] ) ) {
			$url  = explode( '?', $addon['url'] );
			$file = explode( '/', $url[0] );
			$file = end( $file );
			$addon['file'] = str_replace( '-' . $addon['version'] . '.zip', '', $file );
		}
	}

	/**
	 * @since 3.04.02
	 */
	private static function prepare_addon_link( &$link ) {
		$site_url = 'https://formidableforms.com/';
		if ( strpos( $link, 'http' ) !== 0 ) {
			$link = $site_url . $link;
		}
		$link = FrmAppHelper::make_affiliate_url( $link );
		$query_args = array(
			'utm_source'   => 'WordPress',
			'utm_medium'   => 'addons',
			'utm_campaign' => 'liteplugin',
		);
		$link = add_query_arg( $query_args, $link );
	}

	/**
	 * Add the status to the addon array. Status options are:
	 * installed, active, not installed
	 *
	 * @since 3.04.02
	 */
	private static function set_addon_status( &$addon ) {
		if ( ! empty( $addon['activate_url'] ) ) {
			$addon['status'] = array(
				'type'  => 'installed',
				'label' => __( 'Installed', 'formidable' ),
			);
		} elseif ( $addon['installed'] ) {
			$addon['status'] = array(
				'type'  => 'active',
				'label' => __( 'Active', 'formidable' ),
			);
		} else {
			$addon['status'] = array(
				'type'  => 'not-installed',
				'label' => __( 'Not Installed', 'formidable' ),
			);
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
			$licenses = self::send_api_request(
				$url,
				array(
					'name'    => 'frm_api_licence',
					'expires' => 60 * 60 * 5,
				)
			);
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
		return array(
			'personal'     => array(
				'id'       => 2,
				'download' => 19367654,
				'price'    => '49.00',
				'name'     => 'Personal',
			),
			'professional' => array(
				'id'       => 0,
				'download' => 19367001,
				'price'    => '99.00',
				'name'     => 'Creator',
			),
			'smallbusiness' => array(
				'id'       => 0,
				'download' => 19366995,
				'price'    => '199.00',
				'name'     => 'Business',
			),
			'enterprise'   => array(
				'id'       => 0,
				'download' => 19366992,
				'price'    => '399.00',
				'name'     => 'Enterprise',
			),
		);
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

	/**
	 * @since 3.04.02
	 */
	public static function ajax_install_addon() {

		self::install_addon_permissions();

		// Set the current screen to avoid undefined notices.
		global $hook_suffix;
		set_current_screen();

		self::maybe_show_cred_form();

		$installed = self::install_addon();
		self::maybe_activate_addon( $installed );

		// Send back a response.
		echo json_encode( true );
		wp_die();
	}

	/**
	 * @since 3.04.02
	 */
	private static function maybe_show_cred_form() {
		// Start output bufferring to catch the filesystem form if credentials are needed.
		ob_start();

		$show_form = false;
		$method = '';
		$url    = add_query_arg( array( 'page' => 'formidable-settings' ), admin_url( 'admin.php' ) );
		$url    = esc_url_raw( $url );
		$creds  = request_filesystem_credentials( $url, $method, false, false, null );

		if ( false === $creds ) {
			$show_form = true;
		} elseif ( ! WP_Filesystem( $creds ) ) {
			request_filesystem_credentials( $url, $method, true, false, null );
			$show_form = true;
		}

		if ( $show_form ) {
			$form = ob_get_clean();
			//TODO: test this: echo json_encode( array( 'form' => $form ) );
			echo json_encode( array( 'form' => __( 'Sorry, you\'re site requires FTP authentication. Please install plugins manaully.', 'formidable' ) ) );
			wp_die();
		}

		ob_end_clean();
	}

	/**
	 * We do not need any extra credentials if we have gotten this far,
	 * so let's install the plugin.
	 *
	 * @since 3.04.02
	 */
	private static function install_addon() {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );

		$download_url = esc_url_raw( $_POST['plugin'] );

		// Create the plugin upgrader with our custom skin.
		$installer = new Plugin_Upgrader( new FrmInstallerSkin() );
		$installer->install( $download_url );

		// Flush the cache and return the newly installed plugin basename.
		wp_cache_flush();
		return $installer->plugin_info();
	}

	/**
	 * @since 3.04.02
	 */
	private static function maybe_activate_addon( $installed ) {
		if ( ! $installed ) {
			return;
		}

		$activate = activate_plugin( $installed );
		if ( is_wp_error( $activate ) ) {
			echo json_encode( array( 'error' => $activate->get_error_message() ) );
			wp_die();
		}
	}

	/**
	 * Run security checks before installing
	 *
	 * @since 3.04.02
	 */
	private static function install_addon_permissions() {
		check_ajax_referer( 'frm_ajax', 'nonce' );

		if ( ! current_user_can( 'activate_plugins' ) || ! isset( $_POST['plugin'] ) ) {
			echo json_encode( true );
			wp_die();
		}
	}
}
