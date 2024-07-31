<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

#[\AllowDynamicProperties]
class FrmSettings {
	public $option_name = 'frm_options';
	public $menu;
	public $mu_menu;
	public $use_html;
	public $jquery_css;
	public $accordion_js;
	public $fade_form;
	public $old_css;
	public $admin_bar;

	public $success_msg;
	public $blank_msg;
	public $unique_msg;
	public $invalid_msg;
	public $failed_msg;
	public $submit_value;
	public $login_msg;
	public $admin_permission;

	public $email_to;
	public $load_style;
	public $custom_style;

	public $active_captcha;

	/**
	 * Settings for reCAPTCHA.
	 */

	/**
	 * @var string|null
	 */
	public $pubkey;

	/**
	 * @var string|null
	 */
	public $privkey;
	public $re_lang;
	public $re_type;
	public $re_msg;
	public $re_multi;

	/**
	 * Settings for hCaptcha.
	 */

	/**
	 * @var string
	 */
	public $hcaptcha_pubkey;

	/**
	 * @var string|null
	 */
	public $hcaptcha_privkey;

	/**
	 * Settings for Turnstile.
	 */

	/**
	 * @var string
	 */
	public $turnstile_pubkey;

	/**
	 * @var string|null
	 */
	public $turnstile_privkey;

	public $no_ips;
	public $custom_header_ip;
	public $current_form = 0;
	public $tracking;
	public $summary_emails;
	public $summary_emails_recipients;

	public $default_email;
	public $currency;

	/**
	 * @since 6.0
	 *
	 * @var false|string|null
	 */
	public $custom_css;

	public function __construct( $args = array() ) {
		if ( ! defined( 'ABSPATH' ) ) {
			die( 'You are not allowed to call this page directly.' );
		}

		$settings = get_option( $this->option_name );

		if ( ! is_object( $settings ) ) {
			$settings = $this->translate_settings( $settings );
		}

		foreach ( $settings as $setting_name => $setting ) {
			$this->{$setting_name} = $setting;
			unset( $setting_name, $setting );
		}

		$this->set_default_options();

		$this->maybe_filter_for_form( $args );
	}

	private function translate_settings( $settings ) {
		if ( $settings ) {
			// Workaround for W3 total cache conflict.
			return unserialize( serialize( $settings ) );
		}

		// If unserializing didn't work.
		$settings = $this;

		update_option( $this->option_name, $settings, 'yes' );

		return $settings;
	}

	/**
	 * @return array
	 */
	public function default_options() {
		return array(
			'menu'                      => apply_filters( 'frm_default_menu', 'Formidable' ),
			'mu_menu'                   => 0,
			'use_html'                  => true,
			'jquery_css'                => false,
			'accordion_js'              => false,
			'fade_form'                 => false,
			'old_css'                   => false,
			'admin_bar'                 => false,

			're_multi'                  => 1,

			'success_msg'               => __( 'Your responses were successfully submitted. Thank you!', 'formidable' ),
			// translators: %s: [field_name] shortcode.
			'blank_msg'                 => sprintf( __( '%s cannot be blank.', 'formidable' ), '[field_name]' ),
			// translators: %s: [field_name] shortcode.
			'unique_msg'                => sprintf( __( '%s must be unique.', 'formidable' ), '[field_name]' ),
			'invalid_msg'               => __( 'There was a problem with your submission. Errors are marked below.', 'formidable' ),
			'failed_msg'                => __( 'We\'re sorry. It looks like you\'ve already submitted that.', 'formidable' ),
			'submit_value'              => __( 'Submit', 'formidable' ),
			'login_msg'                 => __( 'You do not have permission to view this form.', 'formidable' ),
			'admin_permission'          => __( 'You do not have permission to do that', 'formidable' ),
			'new_tab_msg'               => __( 'The page has been opened in a new tab.', 'formidable' ),

			'email_to'                  => '[admin_email]',
			'no_ips'                    => 0,
			// Use false by default. We show a warning when this is unset. Once global settings have been saved, this gets saved.
			'custom_header_ip'          => false,
			'tracking'                  => FrmAppHelper::pro_is_installed(),
			// Only enable this by default for the main site.
			'summary_emails'            => get_current_blog_id() === get_main_site_id(),
			'summary_emails_recipients' => '[admin_email]',

			// Normally custom CSS is a string. A false value is used when nothing has been set.
			// When it is false, we try to use the old custom_key value from the default style's post_content array.
			'custom_css'                => false,
		);
	}

	/**
	 * @return void
	 */
	private function set_default_options() {
		$this->fill_captcha_settings();

		if ( ! isset( $this->load_style ) ) {
			if ( ! isset( $this->custom_style ) ) {
				$this->custom_style = true;
			}

			$this->load_style = 'all';
		}

		$this->fill_with_defaults();

		if ( is_multisite() && is_admin() ) {
			$mu_menu = get_site_option( 'frm_admin_menu_name' );
			if ( $mu_menu && ! empty( $mu_menu ) ) {
				$this->menu    = $mu_menu;
				$this->mu_menu = 1;
			}
		}

		$frm_roles = FrmAppHelper::frm_capabilities( 'pro' );
		foreach ( $frm_roles as $frm_role => $frm_role_description ) {
			if ( ! isset( $this->$frm_role ) ) {
				$this->$frm_role = 'administrator';
			}
		}

		if ( ! isset( $this->default_email ) ) {
			$this->default_email = get_option( 'admin_email' );
		}

		if ( ! isset( $this->currency ) ) {
			$this->currency = 'USD';
		}
	}

	/**
	 * @param array $params
	 * @return void
	 */
	public function fill_with_defaults( $params = array() ) {
		$settings    = $this->default_options();
		$filter_html = ! FrmAppHelper::allow_unfiltered_html();

		if ( $filter_html ) {
			$filter_keys = array( 'failed_msg', 'blank_msg', 'invalid_msg', 'admin_permission', 'unique_msg', 'success_msg', 'submit_value', 'login_msg', 'menu' );
			if ( ! empty( $params['additional_filter_keys'] ) ) {
				$filter_keys = array_merge( $filter_keys, $params['additional_filter_keys'] );
			}
		} else {
			$filter_keys = array();
		}

		foreach ( $settings as $setting => $default ) {
			if ( isset( $params[ 'frm_' . $setting ] ) ) {
				$this->{$setting} = $params[ 'frm_' . $setting ];
			} elseif ( ! isset( $this->{$setting} ) ) {
				$this->{$setting} = $default;
			}

			if ( $setting === 'menu' && empty( $this->{$setting} ) ) {
				$this->{$setting} = $default;
			}

			$this->{$setting} = $this->maybe_sanitize_global_setting( $this->{$setting}, $setting, $filter_keys );
			unset( $setting, $default );
		}
	}

	/**
	 * Handle sanitizing for a target global setting key.
	 *
	 * @since 6.0
	 *
	 * @param mixed  $value       The unsanitized global setting value.
	 * @param string $key         The key of the global setting being saved.
	 * @param array  $filter_keys These keys that are filtered with kses.
	 * @return mixed
	 */
	private function maybe_sanitize_global_setting( $value, $key, $filter_keys ) {
		if ( 'custom_css' === $key ) {
			if ( false === $value ) {
				// Avoid changing the false default value to an empty string.
				return $value;
			}
			return sanitize_textarea_field( $value );
		}

		if ( in_array( $key, $filter_keys, true ) ) {
			return FrmAppHelper::kses( $value, 'all' );
		}

		return $value;
	}

	/**
	 * @return void
	 */
	private function fill_captcha_settings() {
		if ( ! isset( $this->active_captcha ) ) {
			$this->active_captcha = 'recaptcha';
		}

		$privkey = '';
		$re_lang = '';

		if ( ! isset( $this->hcaptcha_privkey ) ) {
			$this->hcaptcha_privkey = '';
		}

		if ( ! isset( $this->turnstile_privkey ) ) {
			$this->turnstile_privkey = '';
		}

		if ( ! isset( $this->pubkey ) ) {
			// Get the options from the database.
			$recaptcha_opt = is_multisite() ? get_site_option( 'recaptcha' ) : get_option( 'recaptcha' );
			$this->pubkey  = isset( $recaptcha_opt['pubkey'] ) ? $recaptcha_opt['pubkey'] : '';
			$privkey       = isset( $recaptcha_opt['privkey'] ) ? $recaptcha_opt['privkey'] : $privkey;
			$re_lang       = isset( $recaptcha_opt['re_lang'] ) ? $recaptcha_opt['re_lang'] : $re_lang;
		}

		if ( empty( $this->re_msg ) ) {
			$this->re_msg = __( 'The CAPTCHA was not entered correctly', 'formidable' );
		}

		if ( ! isset( $this->privkey ) ) {
			$this->privkey = $privkey;
		}

		if ( ! isset( $this->re_lang ) ) {
			$this->re_lang = $re_lang;
		}

		if ( ! isset( $this->re_type ) ) {
			$this->re_type = '';
		}

		if ( ! isset( $this->re_threshold ) ) {
			$this->re_threshold = .5;
		}
	}

	/**
	 * Get values that may be shown on the front-end without an override in the form settings.
	 *
	 * @since 3.06.01
	 *
	 * @return string[]
	 */
	public function translatable_strings() {
		return array(
			'invalid_msg',
			'admin_permission',
			'failed_msg',
			'login_msg',
		);
	}

	/**
	 * Allow strings to be filtered when a specific form may be displaying them.
	 *
	 * @since 3.06.01
	 *
	 * @return void
	 */
	public function maybe_filter_for_form( $args ) {
		if ( isset( $args['current_form'] ) && is_numeric( $args['current_form'] ) ) {
			$this->current_form = $args['current_form'];
			foreach ( $this->translatable_strings() as $string ) {
				$this->{$string} = apply_filters( 'frm_global_setting', $this->{$string}, $string, $this );
				$this->{$string} = apply_filters( 'frm_global_' . $string, $this->{$string}, $this );
			}
		}
	}

	/**
	 * @param array $params
	 * @param array $errors
	 */
	public function validate( $params, $errors ) {
		return apply_filters( 'frm_validate_settings', $errors, $params );
	}

	/**
	 * @param array $params
	 *
	 * @return void
	 */
	public function update( $params ) {
		$this->fill_with_defaults( $params );
		$this->update_settings( $params );

		if ( $this->mu_menu ) {
			update_site_option( 'frm_admin_menu_name', $this->menu );
		} elseif ( current_user_can( 'administrator' ) ) {
			update_site_option( 'frm_admin_menu_name', false );
		}

		$this->update_roles( $params );

		do_action( 'frm_update_settings', $params );

		if ( function_exists( 'get_filesystem_method' ) ) {
			// Save styling settings in case fallback setting changes.
			$frm_style = new FrmStyle();
			$frm_style->update( 'default' );
		}
	}

	/**
	 * @param array $params
	 * @return void
	 */
	private function update_settings( $params ) {
		$this->active_captcha    = $params['frm_active_captcha'];
		$this->pubkey            = trim( $params['frm_pubkey'] );
		$this->privkey           = trim( $params['frm_privkey'] );
		$this->re_type           = $params['frm_re_type'];
		$this->re_lang           = $params['frm_re_lang'];
		$this->re_threshold      = floatval( $params['frm_re_threshold'] );
		$this->hcaptcha_pubkey   = trim( $params['frm_hcaptcha_pubkey'] );
		$this->hcaptcha_privkey  = trim( $params['frm_hcaptcha_privkey'] );
		$this->turnstile_pubkey  = trim( $params['frm_turnstile_pubkey'] );
		$this->turnstile_privkey = trim( $params['frm_turnstile_privkey'] );
		$this->load_style        = $params['frm_load_style'];
		$this->custom_css        = $params['frm_custom_css'];
		$this->default_email     = $params['frm_default_email'];
		$this->currency          = $params['frm_currency'];

		$checkboxes = array( 'mu_menu', 're_multi', 'use_html', 'jquery_css', 'accordion_js', 'fade_form', 'no_ips', 'custom_header_ip', 'tracking', 'admin_bar', 'summary_emails' );
		foreach ( $checkboxes as $set ) {
			$this->$set = isset( $params[ 'frm_' . $set ] ) ? absint( $params[ 'frm_' . $set ] ) : 0;
		}
	}

	/**
	 * @return void
	 */
	private function update_roles( $params ) {
		global $wp_roles;

		$frm_roles = FrmAppHelper::frm_capabilities();
		$roles     = get_editable_roles();
		foreach ( $frm_roles as $frm_role => $frm_role_description ) {
			$this->$frm_role = (array) ( isset( $params[ $frm_role ] ) ? $params[ $frm_role ] : 'administrator' );

			// Make sure administrators always have permissions
			if ( ! in_array( 'administrator', $this->$frm_role, true ) ) {
				array_push( $this->$frm_role, 'administrator' );
			}

			foreach ( $roles as $role => $details ) {
				if ( in_array( $role, $this->$frm_role ) ) {
					$wp_roles->add_cap( $role, $frm_role );
				} else {
					$wp_roles->remove_cap( $role, $frm_role );
				}
			}
		}
	}

	/**
	 * Updates a single setting with specified sanitization.
	 *
	 * @since 6.9
	 *
	 * @param string $key The setting key to update.
	 * @param mixed  $value The new value for the setting.
	 * @param string $sanitize The name of the sanitization function to apply to the new value.
	 * @return bool True on success, false on failure.
	 */
	public function update_setting( $key, $value, $sanitize ) {
		if ( ! property_exists( $this, $key ) || ! is_callable( $sanitize ) ) {
			// Setting does not exist or sanitization function name is not callable.
			return false;
		}

		// Update the property value.
		FrmAppHelper::sanitize_value( $sanitize, $value );
		$this->{$key} = $value;

		return true;
	}

	/**
	 * @return void
	 */
	public function store() {
		// Save the posted value in the database

		update_option( 'frm_options', $this );

		delete_transient( 'frm_options' );
		set_transient( 'frm_options', $this );

		do_action( 'frm_store_settings' );
	}
}
