<?php

class FrmSettings {
	public $option_name = 'frm_options';
	public $menu;
	public $mu_menu;
	public $use_html;
	public $jquery_css;
	public $accordion_js;
	public $fade_form;
	public $old_css;

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

	public $pubkey;
	public $privkey;
	public $re_lang;
	public $re_type;
	public $re_msg;
	public $re_multi;

	public $no_ips;
	public $current_form = 0;
	public $tracking;

	public function __construct( $args = array() ) {
		if ( ! defined( 'ABSPATH' ) ) {
			die( 'You are not allowed to call this page directly.' );
		}

		$settings = get_transient( $this->option_name );

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
		if ( $settings ) { //workaround for W3 total cache conflict
			return unserialize( serialize( $settings ) );
		}

		$settings = get_option( $this->option_name );
		if ( is_object( $settings ) ) {
			set_transient( $this->option_name, $settings );

			return $settings;
		}

		// If unserializing didn't work
		if ( $settings ) { //workaround for W3 total cache conflict
			$settings = unserialize( serialize( $settings ) );
		} else {
			$settings = $this;
		}

		update_option( $this->option_name, $settings );
		set_transient( $this->option_name, $settings );

		return $settings;
	}

	/**
	 * @return array
	 */
	public function default_options() {
		return array(
			'menu'         => apply_filters( 'frm_default_menu', 'Formidable' ),
			'mu_menu'      => 0,
			'use_html'     => true,
			'jquery_css'   => false,
			'accordion_js' => false,
			'fade_form'    => false,
			'old_css'      => true,

			're_multi' => 1,

			'success_msg'      => __( 'Your responses were successfully submitted. Thank you!', 'formidable' ),
			'blank_msg'        => __( 'This field cannot be blank.', 'formidable' ),
			'unique_msg'       => __( 'This value must be unique.', 'formidable' ),
			'invalid_msg'      => __( 'There was a problem with your submission. Errors are marked below.', 'formidable' ),
			'failed_msg'       => __( 'We\'re sorry. It looks like you\'ve already submitted that.', 'formidable' ),
			'submit_value'     => __( 'Submit', 'formidable' ),
			'login_msg'        => __( 'You do not have permission to view this form.', 'formidable' ),
			'admin_permission' => __( 'You do not have permission to do that', 'formidable' ),

			'email_to' => '[admin_email]',
			'no_ips'   => 0,
			'tracking' => FrmAppHelper::pro_is_installed(),
		);
	}

	private function set_default_options() {
		$this->fill_recaptcha_settings();

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
	}

	public function fill_with_defaults( $params = array() ) {
		$settings = $this->default_options();

		// Use grids and fade in as default for new installs.
		if ( isset( $params['frm_tracking'] ) ) {
			$settings['old_css']   = false;
			$settings['fade_form'] = true;
		}

		foreach ( $settings as $setting => $default ) {
			if ( isset( $params[ 'frm_' . $setting ] ) ) {
				$this->{$setting} = $params[ 'frm_' . $setting ];
			} elseif ( ! isset( $this->{$setting} ) ) {
				$this->{$setting} = $default;
			}

			if ( $setting == 'menu' && empty( $this->{$setting} ) ) {
				$this->{$setting} = $default;
			}

			unset( $setting, $default );
		}
	}

	private function fill_recaptcha_settings() {
		$privkey = '';
		$re_lang = '';

		if ( ! isset( $this->pubkey ) ) {
			// get the options from the database
			$recaptcha_opt = is_multisite() ? get_site_option( 'recaptcha' ) : get_option( 'recaptcha' );
			$this->pubkey  = isset( $recaptcha_opt['pubkey'] ) ? $recaptcha_opt['pubkey'] : '';
			$privkey       = isset( $recaptcha_opt['privkey'] ) ? $recaptcha_opt['privkey'] : $privkey;
			$re_lang       = isset( $recaptcha_opt['re_lang'] ) ? $recaptcha_opt['re_lang'] : $re_lang;
		}

		if ( ! isset( $this->re_msg ) || empty( $this->re_msg ) ) {
			$this->re_msg = __( 'The reCAPTCHA was not entered correctly', 'formidable' );
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
	}

	/**
	 * Get values that may be shown on the front-end without an override in the form settings.
	 *
	 * @since 3.06.01
	 */
	public function translatable_strings() {
		return array(
			'invalid_msg',
			'failed_msg',
			'login_msg',
		);
	}

	/**
	 * Allow strings to be filtered when a specific form may be displaying them.
	 *
	 * @since 3.06.01
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

	public function validate( $params, $errors ) {
		return apply_filters( 'frm_validate_settings', $errors, $params );
	}

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
			// save styling settings in case fallback setting changes
			$frm_style = new FrmStyle();
			$frm_style->update( 'default' );
		}
	}

	private function update_settings( $params ) {
		$this->pubkey   = trim( $params['frm_pubkey'] );
		$this->privkey  = $params['frm_privkey'];
		$this->re_type  = $params['frm_re_type'];
		$this->re_lang  = $params['frm_re_lang'];

		$this->load_style = $params['frm_load_style'];

		$checkboxes = array( 'mu_menu', 're_multi', 'use_html', 'jquery_css', 'accordion_js', 'fade_form', 'old_css', 'no_ips', 'tracking' );
		foreach ( $checkboxes as $set ) {
			$this->$set = isset( $params[ 'frm_' . $set ] ) ? $params[ 'frm_' . $set ] : 0;
		}
	}

	private function update_roles( $params ) {
		global $wp_roles;

		$frm_roles = FrmAppHelper::frm_capabilities();
		$roles     = get_editable_roles();
		foreach ( $frm_roles as $frm_role => $frm_role_description ) {
			$this->$frm_role = (array) ( isset( $params[ $frm_role ] ) ? $params[ $frm_role ] : 'administrator' );

			// Make sure administrators always have permissions
			if ( ! in_array( 'administrator', $this->$frm_role ) ) {
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

	public function store() {
		// Save the posted value in the database

		update_option( 'frm_options', $this );

		delete_transient( 'frm_options' );
		set_transient( 'frm_options', $this );

		do_action( 'frm_store_settings' );
	}
}
