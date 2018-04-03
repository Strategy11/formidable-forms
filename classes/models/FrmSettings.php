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

    public function __construct() {
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
            'menu'      => apply_filters( 'frm_default_menu', 'Formidable' ),
            'mu_menu'   => 0,
            'use_html'  => true,
            'jquery_css' => false,
            'accordion_js' => false,
			'fade_form' => false,
			'old_css'   => true,

			're_multi'  => 0,

            'success_msg' => __( 'Your responses were successfully submitted. Thank you!', 'formidable' ),
            'blank_msg' => __( 'This field cannot be blank.', 'formidable' ),
            'unique_msg' => __( 'This value must be unique.', 'formidable' ),
            'invalid_msg' => __( 'There was a problem with your submission. Errors are marked below.', 'formidable' ),
            'failed_msg' => __( 'We\'re sorry. It looks like you\'ve already submitted that.', 'formidable' ),
            'submit_value' => __( 'Submit', 'formidable' ),
            'login_msg' => __( 'You do not have permission to view this form.', 'formidable' ),
            'admin_permission' => __( 'You do not have permission to do that', 'formidable' ),

            'email_to' => '[admin_email]',
			'no_ips'   => 0,
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
                $this->menu = $mu_menu;
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
			$this->pubkey = isset( $recaptcha_opt['pubkey'] ) ? $recaptcha_opt['pubkey'] : '';
			$privkey = isset( $recaptcha_opt['privkey'] ) ? $recaptcha_opt['privkey'] : $privkey;
			$re_lang = isset( $recaptcha_opt['re_lang'] ) ? $recaptcha_opt['re_lang'] : $re_lang;
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
		$this->mu_menu = isset( $params['frm_mu_menu'] ) ? $params['frm_mu_menu'] : 0;

		$this->pubkey = trim( $params['frm_pubkey'] );
        $this->privkey = $params['frm_privkey'];
		$this->re_type = $params['frm_re_type'];
        $this->re_lang = $params['frm_re_lang'];
		$this->re_multi = isset( $params['frm_re_multi'] ) ? $params['frm_re_multi'] : 0;

        $this->load_style = $params['frm_load_style'];

		$this->use_html = isset( $params['frm_use_html'] ) ? $params['frm_use_html'] : 0;
		$this->jquery_css = isset( $params['frm_jquery_css'] ) ? absint( $params['frm_jquery_css'] ) : 0;
		$this->accordion_js = isset( $params['frm_accordion_js'] ) ? absint( $params['frm_accordion_js'] ) : 0;
		$this->fade_form = isset( $params['frm_fade_form'] ) ? absint( $params['frm_fade_form'] ) : 0;
		$this->old_css   = isset( $params['frm_old_css'] ) ? absint( $params['frm_old_css'] ) : 0;
		$this->no_ips = isset( $params['frm_no_ips'] ) ? absint( $params['frm_no_ips'] ) : 0;
    }

	private function update_roles( $params ) {
        global $wp_roles;

        $frm_roles = FrmAppHelper::frm_capabilities();
        $roles = get_editable_roles();
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
