<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmAddonsController {

	/**
	 * @var string
	 */
	protected static $plugin;

	/**
	 * @return void
	 */
	public static function menu() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$label = __( 'Add-Ons', 'formidable' );
		$label = '<span style="color:#1da867">' . $label . '</span>';

		add_submenu_page( 'formidable', 'Formidable | ' . __( 'Add-Ons', 'formidable' ), $label, 'frm_view_forms', 'formidable-addons', 'FrmAddonsController::list_addons' );

		// remove default created subpage, make the page with highest priority as default.
		remove_submenu_page( 'formidable', 'formidable' );

		if ( ! FrmAppHelper::pro_is_installed() ) {
			add_submenu_page(
				'formidable',
				'Formidable | ' . __( 'Upgrade', 'formidable' ),
				'<span class="frm-upgrade-submenu">' . __( 'Upgrade', 'formidable' ) . '</span>',
				'frm_view_forms',
				'formidable-pro-upgrade',
				'FrmAddonsController::upgrade_to_pro'
			);
		} elseif ( 'formidable-pro-upgrade' === FrmAppHelper::get_param( 'page' ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=formidable' ) );
			exit;
		}
	}

	/**
	 * @return void
	 */
	public static function list_addons() {
		FrmAppHelper::include_svg();
		$installed_addons = apply_filters( 'frm_installed_addons', array() );
		$license_type     = '';

		$addons = self::get_api_addons();
		$errors = array();

		if ( isset( $addons['error'] ) ) {
			$api          = new FrmFormApi();
			$errors       = $api->get_error_from_response( $addons );
			$license_type = isset( $addons['error']['type'] ) ? $addons['error']['type'] : '';
			unset( $addons['error'] );
		}

		$pro    = array(
			'pro' => array(
				'title'    => 'Formidable Forms Pro',
				'slug'     => 'formidable-pro',
				'released' => '2011-02-05',
				'docs'     => 'knowledgebase/',
				'excerpt'  => 'Create calculators, surveys, smart forms, and data-driven applications. Build directories, real estate listings, job boards, and much more.',
			),
		);
		$addons = $pro + $addons;
		self::prepare_addons( $addons );

		$pricing = FrmAppHelper::admin_upgrade_link( 'addons' );

		include FrmAppHelper::plugin_path() . '/classes/views/addons/list.php';
	}

	/**
	 * @return void
	 */
	public static function license_settings() {
		$plugins = apply_filters( 'frm_installed_addons', array() );
		if ( empty( $plugins ) ) {
			esc_html_e( 'There are no plugins on your site that require a license', 'formidable' );

			return;
		}

		ksort( $plugins );

		include FrmAppHelper::plugin_path() . '/classes/views/addons/settings.php';
	}

	/**
	 * @return array
	 */
	protected static function get_api_addons() {
		$api    = new FrmFormApi();
		$addons = $api->get_api_info();

		if ( empty( $addons ) ) {
			$addons = self::fallback_plugin_list();
		} else {
			foreach ( $addons as $k => $addon ) {
				if ( empty( $addon['excerpt'] ) && $k !== 'error' ) {
					unset( $addons[ $k ] );
				}
			}
		}

		return $addons;
	}

	/**
	 * Retrieves the count of available addons.
	 *
	 * @since 6.9
	 *
	 * @return int Count of addons.
	 */
	public static function get_addons_count() {
		$addons = self::get_api_addons();

		return count( $addons );
	}

	/**
	 * If the API is unable to connect, show something on the addons page
	 *
	 * @since 3.04.03
	 * @return array
	 */
	protected static function fallback_plugin_list() {
		$list = array(
			'formidable-pro' => array(
				'title'   => 'Formidable Forms Pro',
				'link'    => 'pricing/',
				'docs'    => '',
				'excerpt' => 'Enhance your basic Formidable forms with a plethora of Pro field types and features. Create advanced forms and data-driven applications in minutes.',
			),
			'mailchimp'      => array(
				'title'   => 'Mailchimp Forms',
				'excerpt' => 'Get on the path to more sales and leads in a matter of minutes. Add leads to a Mailchimp mailing list when they submit forms and update their information along with the entry.',
			),
			'registration'   => array(
				'title'   => 'User Registration Forms',
				'link'    => 'downloads/user-registration/',
				'excerpt' => 'Give new users access to your site as quickly and painlessly as possible. Allow users to register, edit and be able to login to their profiles on your site from the front end in a clean, customized registration form.',
			),
			'paypal'         => array(
				'title'   => 'PayPal Standard Forms',
				'link'    => 'downloads/paypal-standard/',
				'excerpt' => 'Automate your business by collecting instant payments from your clients. Collect information, calculate a total, and send them on to PayPal. Require a payment before publishing content on your site.',
			),
			'stripe'         => array(
				'title'   => 'Stripe Forms',
				'docs'    => 'knowledgebase/stripe/',
				'excerpt' => 'Any Formidable forms on your site can accept credit card payments without users ever leaving your site.',
			),
			'authorize-net'  => array(
				'title'   => 'Authorize.net AIM Forms',
				'link'    => 'downloads/authorize-net-aim/',
				'docs'    => 'knowledgebase/authorize-net-aim/',
				'excerpt' => 'Accept one-time payments directly on your site, using Authorize.net AIM.',
			),
			'woocommerce'    => array(
				'title'   => 'WooCommerce Forms',
				'excerpt' => 'Use a Formidable form on your WooCommerce product pages.',
			),
			'autoresponder'  => array(
				'title'   => 'Form Action Automation',
				'docs'    => 'knowledgebase/schedule-autoresponder/',
				'excerpt' => 'Schedule email notifications, SMS messages, and API actions.',
			),
			'modal'          => array(
				'title'   => 'Bootstrap Modal Forms',
				'link'    => 'downloads/bootstrap-modal/',
				'docs'    => 'knowledgebase/bootstrap-modal/',
				'excerpt' => 'Open a view or form in a Bootstrap popup.',
			),
			'bootstrap'      => array(
				'title'   => 'Bootstrap Style Forms',
				'excerpt' => 'Instantly add Bootstrap styling to all your Formidable forms.',
			),
			'zapier'         => array(
				'title'   => 'Zapier Forms',
				'excerpt' => 'Connect with hundreds of different applications through Zapier. Insert a new row in a Google docs spreadsheet, post on Twitter, or add a new Dropbox file with your form.',
			),
			'signature'      => array(
				'title'   => 'Digital Signature Forms',
				'excerpt' => 'Add a signature field to your form. The user may write their signature with a trackpad/mouse or just type it.',
			),
			'api'            => array(
				'title'   => 'Formidable Forms API',
				'link'    => 'downloads/formidable-api/',
				'excerpt' => 'Send entry results to any other site that has a Rest API. This includes the option of sending entries from one Formidable site to another.',
			),
			'twilio'         => array(
				'title'   => 'Twilio SMS Forms',
				'docs'    => 'knowledgebase/twilio-add-on/',
				'excerpt' => 'Allow users to text their votes for polls created by Formidable Forms, or send SMS notifications when entries are submitted or updated.',
			),
			'views'          => array(
				'title'   => 'Formidable Views',
				'excerpt' => 'Add the power of views to your Formidable Forms to display your form submissions in listings, tables, calendars, and more.',
			),
			'quiz_maker'     => array(
				'title'   => 'Quiz Maker',
				'link'    => 'downloads/quiz-maker/',
				'excerpt' => 'Make quizzes, automatically score them and show user scores.',
			),
		);

		$defaults = array(
			'released' => '',
		);

		foreach ( $list as $k => $info ) {
			$info['slug'] = $k;
			$list[ $k ]   = array_merge( $defaults, $info );
		}
		return $list;
	}

	/**
	 * If Pro is missing but has been authenticated, include a download URL
	 *
	 * @since 3.04.03
	 * @return string
	 */
	public static function get_pro_download_url() {
		$license   = self::get_pro_license();
		$api       = new FrmFormApi( $license );
		$downloads = $api->get_api_info();
		$pro       = self::get_pro_from_addons( $downloads );

		return isset( $pro['url'] ) ? $pro['url'] : '';
	}

	/**
	 * @since 4.08
	 */
	public static function get_pro_license() {
		$pro_cred_store = 'frmpro-credentials';
		$pro_wpmu_store = 'frmpro-wpmu-sitewide';
		if ( is_multisite() && get_site_option( $pro_wpmu_store ) ) {
			$creds = get_site_option( $pro_cred_store );
		} else {
			$creds = get_option( $pro_cred_store );
		}

		if ( empty( $creds ) || ! is_array( $creds ) || ! isset( $creds['license'] ) ) {
			return '';
		}

		$license = $creds['license'];
		if ( empty( $license ) ) {
			return '';
		}

		if ( strpos( $license, '-' ) ) {
			// this is a fix for licenses saved in the past
			$license = strtoupper( $license );
		}
		return $license;
	}

	/**
	 * @since 4.08
	 *
	 * @param array $addons
	 * @return array
	 */
	protected static function get_pro_from_addons( $addons ) {
		return isset( $addons['93790'] ) ? $addons['93790'] : array();
	}

	/**
	 * @since 4.06
	 */
	public static function license_type() {
		if ( is_callable( 'FrmProAddonsController::license_type' ) ) {
			return FrmProAddonsController::license_type();
		}

		return 'free';
	}

	/**
	 * @since 4.0.01
	 *
	 * @return bool
	 */
	public static function is_license_expired() {
		$version_info = self::get_primary_license_info();
		if ( ! isset( $version_info['error'] ) ) {
			return false;
		}

		$expires = isset( $version_info['error']['expires'] ) ? $version_info['error']['expires'] : 0;
		if ( empty( $expires ) || $expires > time() ) {
			return false;
		}

		$rate_limited = ! empty( $version_info['response_code'] ) && 429 === (int) $version_info['response_code'];
		if ( $rate_limited ) {
			// Do not return false positives for rate limited responses.
			return false;
		}

		return $version_info['error'];
	}

	/**
	 * @since 4.08
	 *
	 * @return bool|int false or the number of days until expiration.
	 */
	public static function is_license_expiring() {
		if ( is_callable( 'FrmProAddonsController::is_license_expiring' ) ) {
			return FrmProAddonsController::is_license_expiring();
		}

		return false;
	}

	/**
	 * @since 4.08
	 * @since 6.7 This is public.
	 *
	 * @return array|false
	 */
	public static function get_primary_license_info() {
		$installed_addons = apply_filters( 'frm_installed_addons', array() );
		if ( empty( $installed_addons ) || ! isset( $installed_addons['formidable_pro'] ) ) {
			return false;
		}
		$installed_addons = array(
			'formidable_pro' => $installed_addons['formidable_pro'],
		);

		return self::fill_update_addon_info( $installed_addons );
	}

	/**
	 * @since 3.04.03
	 */
	public static function check_update( $transient ) {
		if ( ! FrmAppHelper::pro_is_installed() ) {
			// Don't make any changes if only Lite is installed.
			return $transient;
		}

		if ( ! is_object( $transient ) ) {
			$transient = new stdClass();
		}

		$installed_addons = apply_filters( 'frm_installed_addons', array() );
		if ( empty( $installed_addons ) ) {
			return $transient;
		}

		$version_info            = self::fill_update_addon_info( $installed_addons );
		$transient->last_checked = time();
		$wp_plugins              = self::get_plugins();

		foreach ( $version_info as $plugin ) {
			$plugin = (object) $plugin;

			if ( ! isset( $plugin->new_version ) || ! isset( $plugin->package ) ) {
				continue;
			}

			$folder = $plugin->plugin;
			if ( empty( $folder ) ) {
				continue;
			}

			if ( ! self::is_installed( $folder ) ) {
				// don't show an update if the plugin isn't installed
				continue;
			}

			$wp_plugin    = isset( $wp_plugins[ $folder ] ) ? $wp_plugins[ $folder ] : array();
			$wp_version   = isset( $wp_plugin['Version'] ) ? $wp_plugin['Version'] : '1.0';
			$plugin->slug = explode( '/', $folder )[0];

			if ( version_compare( $wp_version, $plugin->new_version, '<' ) ) {
				$transient->response[ $folder ] = $plugin;
			} else {
				$transient->no_update[ $folder ] = $plugin;
			}

			$transient->checked[ $folder ] = $wp_version;

		}//end foreach

		return $transient;
	}

	/**
	 * Copy of FrmAppHelper::get_plugins.
	 * Because this gets called on "pre_set_site_transient_update_plugins" an old version of FrmAppHelper may be loaded on plugin update.
	 * This means that trying to access FrmAppHelper::get_plugins when upgrading from a Lite version before v5.5 results in a one-off error.
	 *
	 * @since 5.5.2
	 * @return array
	 */
	protected static function get_plugins() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		return get_plugins();
	}

	/**
	 * Check if a plugin is installed before showing an update for it
	 *
	 * @since 3.05
	 *
	 * @param string $plugin The folder/filename.php for a plugin.
	 *
	 * @return bool - True if installed
	 */
	protected static function is_installed( $plugin ) {
		$all_plugins = self::get_plugins();
		return isset( $all_plugins[ $plugin ] );
	}

	/**
	 * @since 3.04.03
	 *
	 * @param array $installed_addons
	 *
	 * @return array
	 */
	protected static function fill_update_addon_info( $installed_addons ) {
		$checked_licenses = array();
		$version_info     = array();

		foreach ( $installed_addons as $addon ) {
			if ( $addon->store_url !== 'https://formidableforms.com' ) {
				// check if this is a third-party addon
				continue;
			}

			$new_license = $addon->license;
			if ( empty( $new_license ) || in_array( $new_license, $checked_licenses ) ) {
				continue;
			}

			$checked_licenses[] = $new_license;

			$api = new FrmFormApi( $new_license );
			if ( empty( $version_info ) ) {
				$version_info = $api->get_api_info();
				continue;
			}

			$plugin = $api->get_addon_for_license( $addon, $version_info );
			if ( empty( $plugin ) ) {
				continue;
			}

			$download_id = isset( $plugin['id'] ) ? $plugin['id'] : 0;
			if ( ! empty( $download_id ) && ! isset( $version_info[ $download_id ]['package'] ) ) {
				// if this addon is using its own license, get the update url
				$addon_info = $api->get_api_info();

				$version_info[ $download_id ] = $addon_info[ $download_id ];
				if ( isset( $addon_info['error'] ) ) {
					$version_info[ $download_id ]['error'] = array(
						'message' => $addon_info['error']['message'],
						'code'    => $addon_info['error']['code'],
					);
				}
			}
		}//end foreach

		return $version_info;
	}

	/**
	 * Get the action link for an addon that isn't active.
	 *
	 * @since 3.06.03
	 * @param string $plugin The plugin slug.
	 * @return array
	 */
	public static function install_link( $plugin ) {
		$link  = array();
		$addon = self::get_addon( $plugin );

		if ( $addon ) {
			if ( $addon['status']['type'] === 'installed' && ! empty( $addon['activate_url'] ) ) {
				$link = array(
					'url'   => $addon['plugin'],
					'class' => 'frm-activate-addon',
				);
			} elseif ( ! empty( $addon['url'] ) ) {
				$link = array(
					'url'   => $addon['url'],
					'class' => 'frm-install-addon',
				);
			} elseif ( ! empty( $addon['categories'] ) ) {
				$link = array(
					'categories' => $addon['categories'],
				);
			}

			if ( ! empty( $link ) ) {
				$link['status'] = $addon['status']['type'];
			}
		} elseif ( current_user_can( 'activate_plugins' ) && self::is_installed( 'formidable-' . $plugin . '/formidable-' . $plugin . '.php' ) ) {
			$link = array(
				'url'   => 'formidable-' . $plugin . '/formidable-' . $plugin . '.php',
				'class' => 'frm-activate-addon',
			);
		}//end if

		return $link;
	}

	/**
	 * @since 4.09
	 * @param string $plugin The plugin slug.
	 * @return array|false
	 */
	public static function get_addon( $plugin ) {
		$addons = self::get_api_addons();
		self::prepare_addons( $addons );
		foreach ( $addons as $addon ) {
			$slug = explode( '/', $addon['plugin'] );
			if ( $slug[0] === 'formidable-' . $plugin ) {
				return $addon;
			}
		}
		return false;
	}

	/**
	 * @since 4.09
	 * @return string
	 */
	protected static function get_license_type() {
		$license_type = '';
		$addons       = self::get_api_addons();
		if ( isset( $addons['error'] ) && isset( $addons['error']['type'] ) ) {
			$license_type = $addons['error']['type'];
		}
		return $license_type;
	}

	/**
	 * @since 3.04.03
	 *
	 * @param array  $addons
	 * @param object $license The FrmAddon object.
	 *
	 * @return array
	 */
	public static function get_addon_for_license( $addons, $license ) {
		$download_id = $license->download_id;
		$plugin      = array();
		if ( empty( $download_id ) && ! empty( $addons ) ) {
			foreach ( $addons as $addon ) {
				if ( strtolower( $license->plugin_name ) === strtolower( $addon['title'] ) ) {
					return $addon;
				}
			}
		} elseif ( isset( $addons[ $download_id ] ) ) {
			$plugin = $addons[ $download_id ];
		}

		return $plugin;
	}

	/**
	 * @return void
	 */
	protected static function prepare_addons( &$addons ) {
		$activate_url = '';
		if ( current_user_can( 'activate_plugins' ) ) {
			$activate_url = add_query_arg( array( 'action' => 'activate' ), admin_url( 'plugins.php' ) );
		}

		$loop_addons = $addons;
		foreach ( $loop_addons as $id => $addon ) {
			if ( is_numeric( $id ) ) {
				$slug      = str_replace( array( '-wordpress-plugin', '-wordpress' ), '', $addon['slug'] );
				$file_name = $addon['plugin'];
			} else {
				$slug = $id;
				if ( isset( $addon['file'] ) ) {
					$base_file = $addon['file'];
				} else {
					$base_file = 'formidable-' . $slug;
				}
				$file_name = $base_file . '/' . $base_file . '.php';
				if ( ! isset( $addon['plugin'] ) ) {
					$addon['plugin'] = $file_name;
				}
			}

			$addon['installed'] = self::is_installed( $file_name );
			if ( $addon['installed'] && 'formidable-views/formidable-views.php' === $file_name ) {
				$active_views_version = self::get_active_views_version();
				if ( false !== $active_views_version && $slug !== $active_views_version ) {
					$addon['installed'] = false;
				}
			}

			$addon['activate_url'] = '';

			if ( $addon['installed'] && ! empty( $activate_url ) && ! self::is_plugin_active( $file_name, $slug ) ) {
				$addon['activate_url'] = add_query_arg(
					array(
						'_wpnonce' => wp_create_nonce( 'activate-plugin_' . $file_name ),
						'plugin'   => $file_name,
					),
					$activate_url
				);
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
		}//end foreach
	}

	/**
	 * @return bool
	 */
	private static function is_plugin_active( $file_name, $slug ) {
		if ( 'formidable-views/formidable-views.php' === $file_name ) {
			return self::get_active_views_version() === $slug;
		}
		return is_plugin_active( $file_name );
	}

	/**
	 * @return false|string either 'visual-views' or 'views', false if one is not found.
	 */
	private static function get_active_views_version() {
		if ( ! is_callable( 'FrmViewsAppHelper::plugin_version' ) ) {
			return false;
		}
		$plugin_version = FrmViewsAppHelper::plugin_version();
		return version_compare( $plugin_version, '5.0', '>=' ) ? 'visual-views' : 'views';
	}

	/**
	 * @since 3.04.02
	 *
	 * @return void
	 */
	protected static function prepare_addon_link( &$link ) {
		$site_url = 'https://formidableforms.com/';
		if ( strpos( $link, 'http' ) !== 0 ) {
			$link = $site_url . $link;
		}
		$link       = FrmAppHelper::make_affiliate_url( $link );
		$query_args = array(
			'utm_source'   => 'WordPress',
			'utm_medium'   => 'addons',
			'utm_campaign' => 'liteplugin',
		);
		$link       = add_query_arg( $query_args, $link );
	}

	/**
	 * Add the status to the addon array. Status options are:
	 * installed, active, not installed
	 *
	 * @since 3.04.02
	 *
	 * @return void
	 */
	protected static function set_addon_status( &$addon ) {
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

	/**
	 * @return void
	 */
	public static function upgrade_to_pro() {
		FrmAppHelper::include_svg();

		$link_parts = array(
			'medium'  => 'upgrade',
			'content' => 'button',
		);

		$features = array(
			'Display Entries'  => array(
				array(
					'label' => 'Display form data with virtually limitless views',
					'link'  => array(
						'content' => 'views',
						'param'   => 'views-display-form-data',
					),
					'lite'  => false,
				),
				array(
					'label' => 'Generate graphs and stats based on your submitted data',
					'link'  => array(
						'content' => 'graphs',
						'param'   => 'statistics-graphs-wordpress-forms',
					),
					'lite'  => false,
				),
			),
			'Entry Management' => array(
				array(
					'label' => 'Import entries from a CSV',
					'link'  => array(
						'content' => 'import-entries',
						'param'   => 'importing-exporting-wordpress-forms',
					),
					'lite'  => false,
				),
				array(
					'label' => 'Logged-in users can save drafts and return later',
					'link'  => array(
						'content' => 'save-drafts',
						'param'   => 'save-drafts-wordpress-form',
					),
					'lite'  => false,
				),
				array(
					'label' => 'Flexibly and powerfully view, edit, and delete entries from anywhere on your site',
					'link'  => array(
						'content' => 'front-edit',
						'param'   => 'wordpress-front-end-editing',
					),
					'lite'  => false,
				),
				array(
					'label' => 'View form submissions from the back-end',
					'lite'  => true,
				),
				array(
					'label' => 'Export your entries to a CSV',
					'lite'  => true,
				),
			),
			'Form Building'    => array(
				array(
					'label' => 'Save a calculated value into a field',
					'link'  => array(
						'content' => 'calculations',
						'param'   => 'field-calculations-wordpress-form',
					),
					'lite'  => false,
				),
				array(
					'label' => 'Allow multiple file uploads',
					'link'  => array(
						'content' => 'file-uploads',
						'param'   => 'wordpress-multi-file-upload-fields',
					),
					'lite'  => false,
				),
				array(
					'label' => 'Repeat sections of fields',
					'link'  => array(
						'content' => 'repeaters',
						'param'   => 'repeatable-sections-forms',
					),
					'lite'  => false,
				),
				array(
					'label' => 'Hide and show fields conditionally based on other fields or the user\'s role',
					'link'  => array(
						'content' => 'conditional-logic',
						'param'   => 'conditional-logic-wordpress-forms',
					),
					'lite'  => false,
				),
				array(
					'label' => 'Confirmation fields',
					'link'  => array(
						'content' => 'confirmation-fields',
						'param'   => 'confirmation-fields-wordpress-forms',
					),
					'lite'  => false,
				),
				array(
					'label' => 'Multi-paged forms',
					'link'  => array(
						'content' => 'page-breaks',
						'param'   => 'wordpress-multi-page-forms',
					),
					'lite'  => false,
				),
				array(
					'label' => 'Include section headings, page breaks, rich text, dates, times, scales, star ratings, sliders, toggles, dynamic fields populated from other forms, passwords, and tags in advanced forms.',
					'lite'  => false,
				),
				array(
					'label' => 'Include text, email, url, paragraph text, radio, checkbox, dropdown fields, hidden fields, user ID fields, and HTML blocks in your form.',
					'lite'  => true,
				),
				array(
					'label' => 'Drag & Drop Form building',
					'link'  => array(
						'content' => 'drag-drop',
						'param'   => 'drag-drop-forms',
					),
					'lite'  => true,
				),
				array(
					'label' => 'Create forms from Templates',
					'link'  => array(
						'content' => 'form-templates',
						'param'   => 'wordpress-form-templates',
					),
					'lite'  => true,
				),
				array(
					'label' => 'Import and export forms with XML',
					'link'  => array(
						'content' => 'import',
						'param'   => 'importing-exporting-wordpress-forms',
					),
					'lite'  => true,
				),
				array(
					'label' => 'Use input placeholder text in your fields that clear when typing starts.',
					'lite'  => true,
				),
			),
			'Form Actions'     => array(
				array(
					'label' => 'Conditionally send your email notifications based on values in your form',
					'link'  => array(
						'content' => 'conditional-emails',
					),
					'lite'  => false,
				),
				array(
					'label' => 'Create and edit WordPress posts or custom posts from the front-end',
					'link'  => array(
						'content' => 'create-posts',
						'param'   => 'create-posts-pages-wordpress-forms',
					),
					'lite'  => false,
				),
				array(
					'label' => 'Send multiple emails and autoresponders',
					'link'  => array(
						'content' => 'multiple-emails',
						'param'   => 'virtually-unlimited-emails',
					),
					'lite'  => true,
				),
			),
			'Form Appearance'  => array(
				array(
					'label' => 'Create Multiple styles for different forms',
					'link'  => array(
						'content' => 'multiple-styles',
						'param'   => 'wordpress-visual-form-styler',
					),
					'lite'  => false,
				),
				array(
					'label' => 'Customizable layout with CSS classes',
					'link'  => array(
						'content' => 'form-layout',
						'param'   => 'wordpress-mobile-friendly-forms',
					),
					'lite'  => true,
				),
				array(
					'label' => 'Customize the HTML for your forms',
					'link'  => array(
						'content' => 'custom-html',
						'param'   => 'customizable-html-wordpress-form',
					),
					'lite'  => true,
				),
				array(
					'label' => 'Style your form with the Visual Form Styler',
					'lite'  => true,
				),
			),
		);

		include FrmAppHelper::plugin_path() . '/classes/views/addons/upgrade_to_pro.php';
	}

	/**
	 * @since 5.5.2
	 *
	 * @param string $plugin
	 * @param string $redirect
	 * @param bool   $network_wide
	 * @param bool   $silent
	 * @return WP_Error|null Null on success, WP_Error on invalid file.
	 */
	protected static function activate_plugin( $plugin, $redirect = '', $network_wide = false, $silent = false ) {
		if ( ! function_exists( 'activate_plugin' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		return activate_plugin( $plugin, $redirect, $network_wide, $silent );
	}

	/**
	 * Deactivate a specified plugin.
	 *
	 * @since 6.8
	 *
	 * @param string $plugin
	 * @param bool   $silent
	 * @return void
	 */
	protected static function deactivate_plugin( $plugin, $silent = false ) {
		if ( ! function_exists( 'deactivate_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		deactivate_plugins( $plugin, $silent );
	}

	/**
	 * Uninstall a specified plugin.
	 *
	 * @since 6.8
	 *
	 * @param string $plugin
	 * @return true|WP_Error True on success, WP_Error on invalid file.
	 */
	protected static function uninstall_plugin( $plugin ) {
		if ( ! current_user_can( 'delete_plugins' ) ) {
			return new WP_Error( 'uninstall_failed', __( 'Current user cannot delete plugins.', 'formidable' ) );
		}

		if ( ! function_exists( 'delete_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		self::deactivate_plugin( $plugin, true );
		$result = delete_plugins( array( $plugin ) );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return true;
	}

	/**
	 * @since 5.0.10
	 *
	 * @return string
	 */
	protected static function get_current_plugin() {
		if ( empty( self::$plugin ) ) {
			self::$plugin = FrmAppHelper::get_param( 'plugin', '', 'post', 'esc_url_raw' );
		}
		return self::$plugin;
	}

	/**
	 * @since 4.08
	 *
	 * @return array|void
	 */
	protected static function download_and_activate() {
		if ( is_admin() ) {
			FrmAppHelper::set_current_screen_and_hook_suffix();
		}

		self::maybe_show_cred_form();

		$installed = self::install_addon();
		if ( is_array( $installed ) && isset( $installed['message'] ) ) {
			return $installed;
		}
		self::handle_addon_action( $installed, 'activate' );
	}

	/**
	 * @since 3.04.02
	 *
	 * @return void
	 */
	protected static function maybe_show_cred_form() {
		if ( ! function_exists( 'request_filesystem_credentials' ) ) {
			include_once ABSPATH . 'wp-admin/includes/file.php';
		}

		// Start output bufferring to catch the filesystem form if credentials are needed.
		ob_start();

		$show_form = false;
		$method    = '';
		$url       = add_query_arg( array( 'page' => 'formidable-settings' ), admin_url( 'admin.php' ) );
		$url       = esc_url_raw( $url );
		$creds     = request_filesystem_credentials( $url, $method, false, false, null );

		if ( false === $creds ) {
			$show_form = true;
		} elseif ( ! WP_Filesystem( $creds ) ) {
			request_filesystem_credentials( $url, $method, true, false, null );
			$show_form = true;
		}

		if ( $show_form ) {
			$form     = ob_get_clean();
			$message  = __( 'Sorry, your site requires FTP authentication. Please download plugins from FormidableForms.com and install them manually.', 'formidable' );
			$data     = $form;
			$response = array(
				'success' => false,
				'message' => $message,
				'form'    => $form,
			);
			wp_send_json( $response );
		}

		ob_end_clean();
	}

	/**
	 * Checks if an addon download url is allowed.
	 *
	 * @since 6.2
	 *
	 * @param string $download_url
	 *
	 * @return bool
	 */
	public static function url_is_allowed( $download_url ) {
		return FrmAppHelper::validate_url_is_in_s3_bucket( $download_url, 'zip' ) || in_array( $download_url, self::allowed_external_urls(), true );
	}

	/**
	 * We do not need any extra credentials if we have gotten this far,
	 * so let's install the plugin.
	 *
	 * @since 3.04.02
	 */
	protected static function install_addon() {
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		$download_url = self::get_current_plugin();

		if ( ! self::url_is_allowed( $download_url ) ) {
			return array(
				'message' => 'Plugin URL is not valid',
				'success' => false,
			);
		}

		// Create the plugin upgrader with our custom skin.
		$installer = new Plugin_Upgrader( new FrmInstallerSkin() );
		$installer->install( $download_url );

		// Flush the cache and return the newly installed plugin basename.
		wp_cache_flush();

		$plugin = $installer->plugin_info();
		if ( ! $plugin ) {
			return array(
				'message' => 'Plugin was not installed. ' . $installer->result,
				'success' => false,
			);
		}
		return $plugin;
	}

	/**
	 * Handle the AJAX request to activate an add-on.
	 *
	 * @since 6.8
	 *
	 * @return void
	 */
	public static function ajax_activate_addon() {
		self::process_addon_action(
			function ( $plugin ) {
				return self::handle_addon_action( $plugin, 'activate' );
			},
			array( 'FrmAddonsController', 'get_addon_activation_response' )
		);
	}

	/**
	 * @since 3.04.02
	 *
	 * @param string $installed The plugin folder name with file name.
	 */
	protected static function maybe_activate_addon( $installed ) {
		self::ajax_activate_addon();
	}

	/**
	 * Handle the AJAX request to deactivate an add-on.
	 *
	 * @since 6.8
	 *
	 * @return void
	 */
	public static function ajax_deactivate_addon() {
		self::process_addon_action(
			function ( $plugin ) {
				return self::handle_addon_action( $plugin, 'deactivate' );
			}
		);
	}

	/**
	 * Handle the AJAX request to uninstall an add-on.
	 *
	 * @since 6.8
	 *
	 * @return void
	 */
	public static function ajax_uninstall_addon() {
		self::process_addon_action(
			function ( $plugin ) {
				return self::handle_addon_action( $plugin, 'uninstall' );
			}
		);
	}

	/**
	 * Process a specific action (activate, deactivate, uninstall) on an add-on.
	 *
	 * @since 6.8
	 *
	 * @param callable      $action_callback The specific add-on action to be executed.
	 * @param callable|null $response_callback Optional. The response handling callback. Default null.
	 * @return void
	 */
	private static function process_addon_action( $action_callback, $response_callback = null ) {
		self::install_addon_permissions();
		FrmAppHelper::set_current_screen_and_hook_suffix();

		$plugin = FrmAppHelper::get_param( 'plugin', '', 'post', 'sanitize_text_field' );
		call_user_func( $action_callback, $plugin );

		if ( is_callable( $response_callback ) ) {
			wp_send_json_success( call_user_func( $response_callback ) );
			return;
		}

		wp_send_json_success();
	}

	/**
	 * Attempt to perform a specific action (activate, deactivate, uninstall) on an add-on.
	 *
	 * @since 6.8
	 *
	 * @param string $installed The plugin folder name with file name.
	 * @param string $action The action type ('activate', 'deactivate', 'uninstall').
	 * @return array|void
	 */
	protected static function handle_addon_action( $installed, $action ) {
		if ( ! $installed || ! $action ) {
			return;
		}

		$result = null;
		switch ( $action ) {
			case 'activate':
				$result = self::activate_plugin( $installed );
				break;
			case 'deactivate':
				self::deactivate_plugin( $installed );
				break;
			case 'uninstall':
				$result = self::uninstall_plugin( $installed );
				break;
		}

		if ( is_wp_error( $result ) ) {
			// Ignore the invalid header message that shows with nested plugins.
			if ( $result->get_error_code() !== 'no_plugin_header' ) {
				if ( wp_doing_ajax() ) {
					wp_send_json_error( array( 'error' => $result->get_error_message() ) );
				}
				return array(
					'message' => $result->get_error_message(),
					'success' => false,
				);
			}
		}

		return $result;
	}

	/**
	 * @return array
	 */
	private static function get_addon_activation_response() {
		$activating_page = self::get_activating_page();

		$message = $activating_page ? __( 'Your plugin has been activated. Would you like to save and reload the page now?', 'formidable' ) : __( 'Your plugin has been activated.', 'formidable' );

		$response = array(
			'message'       => $message,
			'saveAndReload' => $activating_page,
		);

		return $response;
	}

	/**
	 * Return a string that reflects the page from which the addon is being activated on,
	 * if it is from settings or form builder, otherwise return empty string.
	 *
	 * @return string
	 */
	private static function get_activating_page() {
		$referer = FrmAppHelper::get_server_value( 'HTTP_REFERER' );
		if ( false !== strpos( $referer, 'frm_action=settings' ) ) {
			return 'settings';
		}

		if ( false !== strpos( $referer, 'frm_action=edit' ) ) {
			return 'form_builder';
		}

		return '';
	}

	/**
	 * Run security checks before installing
	 *
	 * @since 3.04.02
	 *
	 * @return void
	 */
	protected static function install_addon_permissions() {
		check_ajax_referer( 'frm_ajax', 'nonce' );

		if ( ! current_user_can( 'activate_plugins' ) || ! self::get_current_plugin() ) {
			echo json_encode( true );
			wp_die();
		}
	}

	/**
	 * @since 4.08
	 *
	 * @return string
	 */
	public static function connect_link() {
		$auth = get_option( 'frm_connect_token' );
		if ( empty( $auth ) ) {
			$auth = hash( 'sha512', wp_rand() );
			update_option( 'frm_connect_token', $auth, 'no' );
		}
		$page = FrmAppHelper::simple_get( 'page', 'sanitize_title', 'formidable-settings' );
		$link = 'https://formidableforms.com/api-connect/';
		$args = array(
			'v'       => 2,
			'siteurl' => FrmAppHelper::site_url(),
			'url'     => get_rest_url(),
			'inst'    => (int) FrmAppHelper::pro_is_included(),
			'return'  => $page,
			'token'   => $auth,
			'l'       => self::get_pro_license(),
		);

		return add_query_arg( $args, $link );
	}

	/**
	 * Check the auth value for install permission.
	 *
	 * @since 4.08
	 *
	 * @return bool
	 */
	public static function can_install_addon_api() {
		// Verify params present (auth & download link).
		$post_auth = FrmAppHelper::get_param( 'token', '', 'request', 'sanitize_text_field' );
		$post_url  = FrmAppHelper::get_param( 'file_url', '', 'request', 'sanitize_text_field' );

		// The download link is not required if already installed.
		$is_installed = FrmAppHelper::pro_is_included();
		$file_missing = ! $is_installed && empty( $post_url );
		if ( ! $post_auth || $file_missing ) {
			return false;
		}

		// Verify auth.
		$auth = get_option( 'frm_connect_token' );
		if ( empty( $auth ) || ! hash_equals( $auth, $post_auth ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Install and/or activate the add-on file.
	 *
	 * @since 4.08
	 *
	 * @return array
	 */
	public static function install_addon_api() {
		self::$plugin = FrmAppHelper::get_param( 'file_url', '', 'request', 'esc_url_raw' );

		$error = esc_html__( 'Could not install an upgrade. Please download from formidableforms.com and install manually.', 'formidable' );

		// Delete so cannot replay.
		delete_option( 'frm_connect_token' );

		// It's already installed and active.
		$active = self::activate_plugin( 'formidable-pro/formidable-pro.php', false, false, true );
		if ( is_wp_error( $active ) ) {
			$response = self::maybe_download_and_activate();
			if ( is_array( $response ) ) {
				// The download failed.
				return $response;
			}
		}

		// If empty license, save it now.
		if ( empty( self::get_pro_license() ) && function_exists( 'load_formidable_pro' ) ) {
			load_formidable_pro();
			$license = stripslashes( FrmAppHelper::get_param( 'key', '', 'request', 'sanitize_text_field' ) );
			if ( ! $license ) {
				return array(
					'success' => false,
					'error'   => 'That site does not have a valid license key.',
				);
			}

			$response = FrmAddon::activate_license_for_plugin( $license, 'formidable_pro' );
			if ( ! $response['success'] ) {
				// Could not activate license.
				return $response;
			}
		}

		return array(
			'success' => true,
		);
	}

	/**
	 * @since 6.8.3
	 *
	 * @return array|true
	 */
	private static function maybe_download_and_activate() {
		if ( ! self::$plugin ) {
			return array(
				'success' => false,
				'message' => __( 'The plugin download was not found.', 'formidable' ),
			);
		}

		// Download plugin now.
		$response = self::download_and_activate();
		if ( is_array( $response ) && isset( $response['success'] ) ) {
			// The download failed.
			return $response;
		}

		return true;
	}

	/**
	 * Render a conditional action button for a specified plugin
	 *
	 * @since 4.09
	 *
	 * @param string       $plugin
	 * @param array|string $upgrade_link_args
	 * @return void
	 */
	public static function conditional_action_button( $plugin, $upgrade_link_args ) {
		if ( is_callable( 'FrmProAddonsController::conditional_action_button' ) ) {
			FrmProAddonsController::conditional_action_button( $plugin, $upgrade_link_args );
			return;
		}

		$addon        = self::get_addon( $plugin );
		$upgrade_link = FrmAppHelper::admin_upgrade_link( $upgrade_link_args );

		if ( ! is_array( $upgrade_link_args ) ) {
			// A string $upgrade_link_args is used for the utm-medium value when calling
			// FrmAppHelper::admin_upgrade_link above.
			// For self::addon_upgrade_link, we'll pass just an empty array with the link key (set below).
			$upgrade_link_args = array();
		}

		$upgrade_link_args['link'] = $upgrade_link;

		self::addon_upgrade_link( $addon, $upgrade_link_args );
	}

	/**
	 * Render a conditional action button for an add on
	 *
	 * @since 4.09.01
	 *
	 * @param array $atts {
	 *     Button attributes.
	 *
	 *     @type array $addon
	 *     @type false|string $license_type
	 *     @type string $plan_required
	 *     @type string $upgrade_link
	 * }
	 * @return void
	 */
	public static function show_conditional_action_button( $atts ) {
		if ( is_callable( 'FrmProAddonsController::show_conditional_action_button' ) ) {
			FrmProAddonsController::show_conditional_action_button( $atts );
			return;
		}

		self::addon_upgrade_link( $atts['addon'], $atts['upgrade_link'] );
	}

	/**
	 * @since 4.09.01
	 *
	 * @param array|false  $addon
	 * @param array|string $upgrade_link
	 *
	 * @return void
	 */
	protected static function addon_upgrade_link( $addon, $upgrade_link ) {
		$atts         = is_array( $upgrade_link ) ? $upgrade_link : array();
		$upgrade_link = is_array( $upgrade_link ) ? $upgrade_link['link'] : $upgrade_link;

		if ( $addon ) {
			$upgrade_link .= '&utm_content=' . $addon['slug'];
		}

		if ( $addon && isset( $addon['categories'] ) && in_array( 'Solution', $addon['categories'], true ) ) {
			// Solutions will go to a separate page.
			$upgrade_link = FrmAppHelper::admin_upgrade_link( 'addons', $addon['link'] );
		}

		$class = ! empty( $atts['class'] ) ? $atts['class'] : '';
		if ( strpos( $class, 'frm-button' ) === false ) {
			$class .= ' frm-button-secondary frm-button-sm';
		}
		?>
		<a class="install-now button <?php echo esc_attr( $class ); ?>" href="<?php echo esc_url( $upgrade_link ); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Upgrade Now', 'formidable' ); ?>">
			<?php esc_html_e( 'Upgrade Now', 'formidable' ); ?>
		</a>
		<?php
	}

	/**
	 * @since 3.04.02
	 *
	 * @return void
	 */
	public static function ajax_install_addon() {
		self::install_addon_permissions();

		$result = self::download_and_activate();
		if ( isset( $result['success'] ) && ! $result['success'] ) {
			echo json_encode( $result );
			wp_die();
		}

		echo json_encode( self::get_addon_activation_response() );
		wp_die();
	}

	/**
	 * Allowed URLs used for internal source of plugins installation.
	 *
	 * @since 6.3.1
	 *
	 * @return array
	 */
	private static function allowed_external_urls() {
		$allowed_url_list = array(
			'https://downloads.wordpress.org/plugin/formidable-gravity-forms-importer.zip',
			'https://downloads.wordpress.org/plugin/formidable-import-pirate-forms.zip',
			'https://downloads.wordpress.org/plugin/wp-mail-smtp.zip',
		);

		/**
		 * List of URLs used in plugin formidable internal installation.
		 *
		 * @since 6.3.1
		 *
		 * @param array $allowed_url_list List of URLs.
		 */
		$allowed_url_list = apply_filters( 'frm_allowed_external_urls', $allowed_url_list );

		if ( ! is_array( $allowed_url_list ) ) {
			_doing_it_wrong( __METHOD__, 'Only an array of URLs could be used within this filter.', '6.3.1' );

			return array();
		}

		return $allowed_url_list;
	}

	/**
	 * Gets required plan for an addon.
	 *
	 * @since 6.4.2
	 *
	 * @return string Empty string if no plan is required for active license.
	 */
	public static function get_addon_required_plan( $addon_id ) {
		$api    = new FrmFormApi();
		$addons = $api->get_api_info();

		if ( is_array( $addons ) && array_key_exists( $addon_id, $addons ) ) {
			$dates    = $addons[ $addon_id ];
			$requires = FrmFormsHelper::get_plan_required( $dates );
		}

		if ( ! isset( $requires ) || ! is_string( $requires ) ) {
			$requires = '';
		}

		return $requires;
	}

	/**
	 * @since 4.02.05
	 * @deprecated 6.8.3
	 *
	 * @codeCoverageIgnore
	 *
	 * @return void
	 */
	public static function connect_pro() {
		_deprecated_function( __METHOD__, '6.8.3' );
	}
}
