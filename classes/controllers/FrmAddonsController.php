<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmAddonsController {

	public static function menu() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$label = __( 'Add-Ons', 'formidable' );
		if ( FrmAppHelper::pro_is_installed() ) {
			$label = '<span style="color:#fe5a1d">' . $label . '</span>';
		}
		add_submenu_page( 'formidable', 'Formidable | ' . __( 'Add-Ons', 'formidable' ), $label, 'frm_view_forms', 'formidable-addons', 'FrmAddonsController::list_addons' );

		if ( ! FrmAppHelper::pro_is_installed() ) {
			add_submenu_page(
				'formidable',
				'Formidable | ' . __( 'Upgrade', 'formidable' ),
				'<span style="color:#fe5a1d">' . __( 'Upgrade', 'formidable' ) . '</span>',
				'frm_view_forms',
				'formidable-pro-upgrade',
				'FrmAddonsController::upgrade_to_pro'
			);
		} elseif ( 'formidable-pro-upgrade' === FrmAppHelper::get_param( 'page' ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=formidable' ) );
			exit;
		}
	}

	public static function list_addons() {
		FrmAppHelper::include_svg();
		$installed_addons = apply_filters( 'frm_installed_addons', array() );
		$license_type     = '';

		$addons = self::get_api_addons();
		$errors = array();

		if ( isset( $addons['error'] ) ) {
			$api    = new FrmFormApi();
			$errors = $api->get_error_from_response( $addons );
			$license_type = isset( $addons['error']['type'] ) ? $addons['error']['type'] : '';
			unset( $addons['error'] );
		}

		$pro = array(
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

		include( FrmAppHelper::plugin_path() . '/classes/views/addons/list.php' );
	}

	public static function license_settings() {
		$plugins = apply_filters( 'frm_installed_addons', array() );
		if ( empty( $plugins ) ) {
			esc_html_e( 'There are no plugins on your site that require a license', 'formidable' );

			return;
		}

		ksort( $plugins );

		include( FrmAppHelper::plugin_path() . '/classes/views/addons/settings.php' );
	}

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
				'title'   => 'MailChimp Forms',
				'excerpt' => 'Get on the path to more sales and leads in a matter of minutes. Add leads to a MailChimp mailing list when they submit forms and update their information along with the entry.',
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
	 */
	public static function is_license_expired() {
		$version_info = self::get_primary_license_info();
		if ( ! isset( $version_info['error'] ) ) {
			return false;
		}

		return $version_info['error'];
	}

	/**
	 * @since 4.08
	 *
	 * @return boolean|int false or the number of days until expiration.
	 */
	public static function is_license_expiring() {
		if ( is_callable( 'FrmProAddonsController::is_license_expiring' ) ) {
			return FrmProAddonsController::is_license_expiring();
		}

		return false;
	}

	/**
	 * @since 4.08
	 */
	protected static function get_primary_license_info() {
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

		$version_info = self::fill_update_addon_info( $installed_addons );

		$transient->last_checked = time();

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$wp_plugins = get_plugins();

		foreach ( $version_info as $id => $plugin ) {
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

			$wp_plugin  = isset( $wp_plugins[ $folder ] ) ? $wp_plugins[ $folder ] : array();
			$wp_version = isset( $wp_plugin['Version'] ) ? $wp_plugin['Version'] : '1.0';

			if ( version_compare( $wp_version, $plugin->new_version, '<' ) ) {
				$slug                           = explode( '/', $folder );
				$plugin->slug                   = $slug[0];
				$transient->response[ $folder ] = $plugin;
			}

			$transient->checked[ $folder ] = $wp_version;

		}

		return $transient;
	}

	/**
	 * Check if a plugin is installed before showing an update for it
	 *
	 * @since 3.05
	 *
	 * @param string $plugin - the folder/filename.php for a plugin
	 *
	 * @return bool - True if installed
	 */
	protected static function is_installed( $plugin ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();

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
		}

		return $version_info;
	}

	/**
	 * Get the action link for an addon that isn't active.
	 *
	 * @since 3.06.03
	 * @param string $plugin The plugin slug
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
			} elseif ( isset( $addon['url'] ) && ! empty( $addon['url'] ) ) {
				$link = array(
					'url'   => $addon['url'],
					'class' => 'frm-install-addon',
				);
			} elseif ( isset( $addon['categories'] ) && ! empty( $addon['categories'] ) ) {
				$link = array(
					'categories' => $addon['categories'],
				);
			}

			if ( ! empty( $link ) ) {
				$link['status'] = $addon['status']['type'];
			}
		}

		return $link;
	}

	/**
	 * @since 4.09
	 * @param string $plugin The plugin slug
	 * @return array|false
	 */
	protected static function get_addon( $plugin ) {
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
	 * @param array $addons
	 * @param object $license The FrmAddon object
	 *
	 * @return array
	 */
	public static function get_addon_for_license( $addons, $license ) {
		$download_id = $license->download_id;
		$plugin      = array();
		if ( empty( $download_id ) && ! empty( $addons ) ) {
			foreach ( $addons as $addon ) {
				if ( strtolower( $license->plugin_name ) == strtolower( $addon['title'] ) ) {
					return $addon;
				}
			}
		} elseif ( isset( $addons[ $download_id ] ) ) {
			$plugin = $addons[ $download_id ];
		}

		return $plugin;
	}

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

			$addon['installed']    = self::is_installed( $file_name );
			$addon['activate_url'] = '';

			if ( $addon['installed'] && ! empty( $activate_url ) && ! is_plugin_active( $file_name ) ) {
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
		}
	}

	/**
	 * @since 3.04.02
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

	public static function upgrade_to_pro() {
		FrmAppHelper::include_svg();

		$link_parts = array(
			'medium'  => 'upgrade',
			'content' => 'button',
		);

		$features = array(
			'Display Entries' => array(
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
			'Form Building' => array(
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
			'Form Actions' => array(
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
			'Form Appearance' => array(
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

		include( FrmAppHelper::plugin_path() . '/classes/views/addons/upgrade_to_pro.php' );
	}

	/**
	 * Install Pro after connection with Formidable.
	 *
	 * @since 4.02.05
	 */
	public static function connect_pro() {
		FrmAppHelper::permission_check( 'install_plugins' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$url = FrmAppHelper::get_post_param( 'plugin', '', 'sanitize_text_field' );
		if ( FrmAppHelper::pro_is_installed() || empty( $url ) ) {
			wp_die();
		}

		$response = array();

		// It's already installed and active.
		$active = activate_plugin( 'formidable-pro/formidable-pro.php', false, false, true );
		if ( is_wp_error( $active ) ) {
			// The plugin was installed, but not active. Download it now.
			self::ajax_install_addon();
		} else {
			$response['active']  = true;
			$response['success'] = true;
		}

		echo json_encode( $response );
		wp_die();
	}

	/**
	 * @since 4.08
	 */
	protected static function download_and_activate() {
		// Set the current screen to avoid undefined notices.
		if ( is_admin() ) {
			global $hook_suffix;
			set_current_screen();
		}

		self::maybe_show_cred_form();

		$installed = self::install_addon();
		if ( is_array( $installed ) && isset( $installed['message'] ) ) {
			return $installed;
		}
		self::maybe_activate_addon( $installed );
	}

	/**
	 * @since 3.04.02
	 */
	protected static function maybe_show_cred_form() {
		if ( ! function_exists( 'request_filesystem_credentials' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/file.php' );
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
	 * We do not need any extra credentials if we have gotten this far,
	 * so let's install the plugin.
	 *
	 * @since 3.04.02
	 */
	protected static function install_addon() {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );

		$download_url = FrmAppHelper::get_param( 'plugin', '', 'post', 'esc_url_raw' );

		// Create the plugin upgrader with our custom skin.
		$installer = new Plugin_Upgrader( new FrmInstallerSkin() );
		$installer->install( $download_url );

		// Flush the cache and return the newly installed plugin basename.
		wp_cache_flush();

		$plugin = $installer->plugin_info();
		if ( empty( $plugin ) ) {
			return array(
				'message' => 'Plugin was not installed. ' . $installer->result,
				'success' => false,
			);
		}
		return $plugin;
	}

	/**
	 * @since 3.06.03
	 */
	public static function ajax_activate_addon() {

		self::install_addon_permissions();

		// Set the current screen to avoid undefined notices.
		global $hook_suffix;
		set_current_screen();

		$plugin = FrmAppHelper::get_param( 'plugin', '', 'post', 'sanitize_text_field' );
		self::maybe_activate_addon( $plugin );

		// Send back a response.
		echo json_encode( __( 'Your plugin has been activated. Please reload the page to see more options.', 'formidable' ) );
		wp_die();
	}

	/**
	 * @since 3.04.02
	 * @param string $installed The plugin folder name with file name
	 */
	protected static function maybe_activate_addon( $installed ) {
		if ( ! $installed ) {
			return;
		}

		$activate = activate_plugin( $installed );
		if ( is_wp_error( $activate ) ) {
			// Ignore the invalid header message that shows with nested plugins.
			if ( $activate->get_error_code() !== 'no_plugin_header' ) {
				if ( wp_doing_ajax() ) {
					echo json_encode( array( 'error' => $activate->get_error_message() ) );
					wp_die();
				}
				return array(
					'message' => $activate->get_error_message(),
					'success' => false,
				);
			}
		}
	}

	/**
	 * Run security checks before installing
	 *
	 * @since 3.04.02
	 */
	protected static function install_addon_permissions() {
		check_ajax_referer( 'frm_ajax', 'nonce' );

		if ( ! current_user_can( 'activate_plugins' ) || ! isset( $_POST['plugin'] ) ) {
			echo json_encode( true );
			wp_die();
		}
	}

	/**
	 * @since 4.08
	 */
	public static function connect_link() {
		$auth = get_option( 'frm_connect_token' );
		if ( empty( $auth ) ) {
			$auth = hash( 'sha512', wp_rand() );
			update_option( 'frm_connect_token', $auth );
		}
		$link = FrmAppHelper::admin_upgrade_link( 'connect', 'api-connect' );
		$args = array(
			'v'       => 2,
			'siteurl' => FrmAppHelper::site_url(),
			'url'     => get_rest_url(),
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

		if ( empty( $post_auth ) || empty( $post_url ) ) {
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
	 */
	public static function install_addon_api() {
		// Sanitize when it's used to prevent double sanitizing.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$_POST['plugin'] = isset( $_REQUEST['file_url'] ) ? $_REQUEST['file_url'] : ''; // Set for later use

		$error = esc_html__( 'Could not install an upgrade. Please download from formidableforms.com and install manually.', 'formidable' );

		// Delete so cannot replay.
		delete_option( 'frm_connect_token' );

		// It's already installed and active.
		$active = activate_plugin( 'formidable-pro/formidable-pro.php', false, false, true );
		if ( is_wp_error( $active ) ) {
			// Download plugin now.
			$response = self::download_and_activate();
			if ( is_array( $response ) && isset( $response['success'] ) ) {
				return $response;
			}
		}

		// If empty license, save it now.
		if ( empty( self::get_pro_license() ) && function_exists( 'load_formidable_pro' ) ) {
			load_formidable_pro();
			$license = stripslashes( FrmAppHelper::get_param( 'key', '', 'request', 'sanitize_text_field' ) );
			if ( empty( $license ) ) {
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
	 * Render a conditional action button for a specified plugin
	 *
	 * @param string $plugin
	 * @param array|string $upgrade_link_args
	 * @since 4.09
	 */
	public static function conditional_action_button( $plugin, $upgrade_link_args ) {
		if ( is_callable( 'FrmProAddonsController::conditional_action_button' ) ) {
			return FrmProAddonsController::conditional_action_button( $plugin, $upgrade_link_args );
		}

		$addon        = self::get_addon( $plugin );
		$upgrade_link = FrmAppHelper::admin_upgrade_link( $upgrade_link_args );
		self::addon_upgrade_link( $addon, $upgrade_link );
	}

	/**
	 * Render a conditional action button for an add on
	 *
	 * @since 4.09.01
	 * @param array $addon
	 * @param string|false $license_type
	 * @param string $plan_required
	 * @param string $upgrade_link
	 */
	public static function show_conditional_action_button( $atts ) {
		if ( is_callable( 'FrmProAddonsController::show_conditional_action_button' ) ) {
			return FrmProAddonsController::show_conditional_action_button( $atts );
		}

		self::addon_upgrade_link( $atts['addon'], $atts['upgrade_link'] );
	}

	/**
	 * @since 4.09.01
	 */
	protected static function addon_upgrade_link( $addon, $upgrade_link ) {
		if ( $addon ) {
			$upgrade_link .= '&utm_content=' . $addon['slug'];
		}

		if ( $addon && isset( $addon['categories'] ) && in_array( 'Solution', $addon['categories'], true ) ) {
			// Solutions will go to a separate page.
			$upgrade_link = FrmAppHelper::admin_upgrade_link( 'addons', $addon['link'] );
		}
		?>
		<a class="install-now button button-secondary frm-button-secondary" href="<?php echo esc_url( $upgrade_link ); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Upgrade Now', 'formidable' ); ?>">
			<?php esc_html_e( 'Upgrade Now', 'formidable' ); ?>
		</a>
		<?php
	}

	/**
	 * @since 3.04.02
	 */
	public static function ajax_install_addon() {
		self::install_addon_permissions();

		self::download_and_activate();

		// Send back a response.
		echo json_encode( __( 'Your plugin has been installed. Please reload the page to see more options.', 'formidable' ) );
		wp_die();
	}

	/**
	 * @since 4.06.02
	 * @deprecated 4.09.01
	 */
	public static function ajax_multiple_addons() {
		FrmDeprecated::ajax_multiple_addons();
	}

	/**
	 * @since 3.04.03
	 * @deprecated 3.06
	 * @codeCoverageIgnore
	 * @return array
	 */
	public static function error_for_license( $license ) {
		return FrmDeprecated::error_for_license( $license );
	}

	/**
	 * @since 3.04.03
	 * @deprecated 3.06
	 * @codeCoverageIgnore
	 */
	public static function get_pro_updater() {
		return FrmDeprecated::get_pro_updater();
	}

	/**
	 * @since 3.04.03
	 * @deprecated 3.06
	 * @codeCoverageIgnore
	 *
	 * @return array
	 */
	public static function get_addon_info( $license = '' ) {
		return FrmDeprecated::get_addon_info( $license );
	}

	/**
	 * @since 3.04.03
	 * @deprecated 3.06
	 * @codeCoverageIgnore
	 *
	 * @return string
	 */
	public static function get_cache_key( $license ) {
		return FrmDeprecated::get_cache_key( $license );
	}

	/**
	 * @since 3.04.03
	 * @deprecated 3.06
	 * @codeCoverageIgnore
	 */
	public static function reset_cached_addons( $license = '' ) {
		FrmDeprecated::reset_cached_addons( $license );
	}

	/**
	 * @since 2.03.08
	 * @deprecated 3.04.03
	 * @codeCoverageIgnore
	 *
	 * @param boolean $return
	 * @param string $package
	 *
	 * @return boolean
	 */
	public static function add_shorten_edd_filename_filter( $return, $package ) {
		return FrmDeprecated::add_shorten_edd_filename_filter( $return, $package );
	}

	/**
	 * @since 2.03.08
	 * @deprecated 3.04.03
	 * @codeCoverageIgnore
	 *
	 * @param string $filename
	 * @param string $ext
	 *
	 * @return string
	 */
	public static function shorten_edd_filename( $filename, $ext ) {
		return FrmDeprecated::shorten_edd_filename( $filename, $ext );
	}

	/**
	 * @deprecated 3.04.03
	 * @codeCoverageIgnore
	 */
	public static function get_licenses() {
		FrmDeprecated::get_licenses();
	}
}
