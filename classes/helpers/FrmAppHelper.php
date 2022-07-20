<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmAppHelper {
	public static $db_version     = 98; // Version of the database we are moving to.
	public static $pro_db_version = 37; //deprecated
	public static $font_version   = 7;

	/**
	 * @var bool $added_gmt_offset_filter
	 */
	private static $added_gmt_offset_filter = false;

	/**
	 * @since 2.0
	 */
	public static $plug_version = '5.4.2';

	/**
	 * @since 1.07.02
	 *
	 * @param none
	 *
	 * @return string The version of this plugin
	 */
	public static function plugin_version() {
		return self::$plug_version;
	}

	public static function plugin_folder() {
		return basename( self::plugin_path() );
	}

	public static function plugin_path() {
		return dirname( dirname( dirname( __FILE__ ) ) );
	}

	public static function plugin_url() {
		// Prevously FRM_URL constant.
		return plugins_url( '', self::plugin_path() . '/formidable.php' );
	}

	public static function relative_plugin_url() {
		return str_replace( array( 'https:', 'http:' ), '', self::plugin_url() );
	}

	/**
	 * @return string Site URL
	 */
	public static function site_url() {
		return site_url();
	}

	/**
	 * Get the name of this site
	 * Used for [sitename] shortcode
	 *
	 * @since 2.0
	 * @return string
	 */
	public static function site_name() {
		return get_option( 'blogname' );
	}

	public static function make_affiliate_url( $url ) {
		$affiliate_id = self::get_affiliate();
		if ( ! empty( $affiliate_id ) ) {
			$url = str_replace( array( 'http://', 'https://' ), '', $url );
			$url = 'http://www.shareasale.com/r.cfm?u=' . absint( $affiliate_id ) . '&b=841990&m=64739&afftrack=plugin&urllink=' . urlencode( $url );
		}

		return $url;
	}

	public static function get_affiliate() {
		return absint( apply_filters( 'frm_affiliate_id', 0 ) );
	}

	/**
	 * @since 3.04.02
	 * @param array|string $args
	 * @param string       $page
	 */
	public static function admin_upgrade_link( $args, $page = '' ) {
		if ( empty( $page ) ) {
			$page = 'https://formidableforms.com/lite-upgrade/';
		} else {
			$page = str_replace( 'https://formidableforms.com/', '', $page );
			$page = 'https://formidableforms.com/' . $page;
		}

		$anchor = '';
		if ( is_array( $args ) ) {
			$medium = $args['medium'];
			if ( isset( $args['content'] ) ) {
				$content = $args['content'];
			}
			if ( isset( $args['anchor'] ) ) {
				$anchor = '#' . $args['anchor'];
			}
		} else {
			$medium = $args;
		}

		$query_args = array(
			'utm_source'   => 'WordPress',
			'utm_medium'   => $medium,
			'utm_campaign' => 'liteplugin',
		);

		if ( isset( $content ) ) {
			$query_args['utm_content'] = $content;
		}

		if ( is_array( $args ) && isset( $args['param'] ) ) {
			$query_args['f'] = $args['param'];
		}

		if ( is_array( $args ) && ! empty( $args['plan'] ) ) {
			$query_args['plan'] = $args['plan'];
		}

		$link = add_query_arg( $query_args, $page ) . $anchor;
		return self::make_affiliate_url( $link );
	}

	/**
	 * Get the Formidable settings
	 *
	 * @since 2.0
	 *
	 * @param array $args - May include the form id when values need translation.
	 * @return FrmSettings $frm_setings
	 */
	public static function get_settings( $args = array() ) {
		global $frm_settings;
		if ( empty( $frm_settings ) ) {
			$frm_settings = new FrmSettings( $args );
		} elseif ( isset( $args['current_form'] ) ) {
			// If the global has already been set, allow strings to be filtered.
			$frm_settings->maybe_filter_for_form( $args );
		}

		return $frm_settings;
	}

	public static function get_menu_name() {
		$frm_settings = self::get_settings();

		return $frm_settings->menu;
	}

	/**
	 * @since 3.05
	 */
	public static function svg_logo( $atts = array() ) {
		$defaults = array(
			'height' => 18,
			'width'  => 18,
			'fill'   => '#4d4d4d',
			'orange' => '#f05a24',
		);
		$atts     = array_merge( $defaults, $atts );

		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 599.68 601.37" width="' . esc_attr( $atts['width'] ) . '" height="' . esc_attr( $atts['height'] ) . '">
			<path fill="' . esc_attr( $atts['orange'] ) . '" d="M289.6 384h140v76h-140z"/>
			<path fill="' . esc_attr( $atts['fill'] ) . '" d="M400.2 147h-200c-17 0-30.6 12.2-30.6 29.3V218h260v-71zM397.9 264H169.6v196h75V340H398a32.2 32.2 0 0 0 30.1-21.4 24.3 24.3 0 0 0 1.7-8.7V264zM299.8 601.4A300.3 300.3 0 0 1 0 300.7a299.8 299.8 0 1 1 511.9 212.6 297.4 297.4 0 0 1-212 88zm0-563A262 262 0 0 0 38.3 300.7a261.6 261.6 0 1 0 446.5-185.5 259.5 259.5 0 0 0-185-76.8z"/>
		</svg>';
	}

	/**
	 * @since 4.0
	 */
	public static function show_logo( $atts = array() ) {
		echo self::kses( self::svg_logo( $atts ), 'all' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * @since 4.03.02
	 */
	public static function show_header_logo() {
		$icon = self::svg_logo(
			array(
				'height' => 35,
				'width'  => 35,
			)
		);

		$new_icon = apply_filters( 'frm_icon', $icon, true );
		if ( $new_icon !== $icon ) {
			if ( strpos( $new_icon, '<svg' ) === 0 ) {
				$icon = str_replace( 'viewBox="0 0 20', 'width="30" height="35" style="color:#929699" viewBox="0 0 20', $new_icon );
			} else {
				// Show nothing if it isn't an SVG.
				$icon = '<div style="height:39px"></div>';
			}
		}
		echo self::kses( $icon, 'all' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * @since 2.02.04
	 */
	public static function ips_saved() {
		$frm_settings = self::get_settings();

		return ! $frm_settings->no_ips;
	}

	public static function pro_is_installed() {
		return apply_filters( 'frm_pro_installed', false );
	}

	/**
	 * @since 4.06.02
	 */
	public static function pro_is_connected() {
		global $frm_vars;
		return self::pro_is_installed() && $frm_vars['pro_is_authorized'];
	}

	/**
	 * @since 4.06
	 */
	public static function is_form_builder_page() {
		$action = self::simple_get( 'frm_action', 'sanitize_title' );
		return self::is_admin_page( 'formidable' ) && ( $action === 'edit' || $action === 'settings' || $action === 'duplicate' );
	}

	public static function is_formidable_admin() {
		$page          = self::simple_get( 'page', 'sanitize_title' );
		$is_formidable = strpos( $page, 'formidable' ) !== false;
		if ( empty( $page ) ) {
			$is_formidable = self::is_view_builder_page();
		}

		return $is_formidable;
	}

	/**
	 * Check for certain page in Formidable settings
	 *
	 * @since 2.0
	 *
	 * @param string $page The name of the page to check
	 *
	 * @return boolean
	 */
	public static function is_admin_page( $page = 'formidable' ) {
		global $pagenow;
		$get_page = self::simple_get( 'page', 'sanitize_title' );
		if ( $pagenow ) {
			// allow this to be true during ajax load i.e. ajax form builder loading
			$is_page = ( $pagenow == 'admin.php' || $pagenow == 'admin-ajax.php' ) && $get_page == $page;
			if ( $is_page ) {
				return true;
			}
		}

		return is_admin() && $get_page == $page;
	}

	/**
	 * If the current page is for editing or creating a view.
	 * Returns false for the views listing page.
	 *
	 * @since 4.0
	 */
	public static function is_view_builder_page() {
		global $pagenow;

		if ( $pagenow !== 'post.php' && $pagenow !== 'post-new.php' && $pagenow !== 'edit.php' ) {
			return false;
		}

		$post_type = self::simple_get( 'post_type', 'sanitize_title' );

		if ( empty( $post_type ) ) {
			$post_id = self::simple_get( 'post', 'absint' );
			$post    = get_post( $post_id );
			$post_type = $post ? $post->post_type : '';
		}

		return $post_type === 'frm_display';
	}

	/**
	 * Check for the form preview page
	 *
	 * @since 2.0
	 *
	 * @param None
	 *
	 * @return boolean
	 */
	public static function is_preview_page() {
		global $pagenow;
		$action = self::simple_get( 'action', 'sanitize_title' );

		return $pagenow && $pagenow == 'admin-ajax.php' && $action == 'frm_forms_preview';
	}

	/**
	 * Check for ajax except the form preview page
	 *
	 * @since 2.0
	 *
	 * @param None
	 *
	 * @return boolean
	 */
	public static function doing_ajax() {
		return wp_doing_ajax() && ! self::is_preview_page();
	}

	public static function js_suffix() {
		return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	}

	/**
	 * @since 2.0.8
	 */
	public static function prevent_caching() {
		global $frm_vars;

		return isset( $frm_vars['prevent_caching'] ) && $frm_vars['prevent_caching'];
	}

	/**
	 * Check if on an admin page
	 *
	 * @since 2.0
	 *
	 * @param None
	 *
	 * @return boolean
	 */
	public static function is_admin() {
		return is_admin() && ! wp_doing_ajax();
	}

	/**
	 * Check if value contains blank value or empty array
	 *
	 * @since 2.0
	 *
	 * @param mixed $value - value to check
	 * @param string
	 *
	 * @return boolean
	 */
	public static function is_empty_value( $value, $empty = '' ) {
		return ( is_array( $value ) && empty( $value ) ) || $value === $empty;
	}

	public static function is_not_empty_value( $value, $empty = '' ) {
		return ! self::is_empty_value( $value, $empty );
	}

	/**
	 * Get any value from the $_SERVER
	 *
	 * @since 2.0
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public static function get_server_value( $value ) {
		return isset( $_SERVER[ $value ] ) ? wp_strip_all_tags( wp_unslash( $_SERVER[ $value ] ) ) : '';
	}

	/**
	 * Check for the IP address in several places
	 * Used by [ip] shortcode
	 *
	 * @return string The IP address of the current user
	 */
	public static function get_ip_address() {
		$ip_options = array(
			'HTTP_CLIENT_IP',
			'HTTP_CF_CONNECTING_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_X_REAL_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);
		$ip = '';
		foreach ( $ip_options as $key ) {
			if ( ! isset( $_SERVER[ $key ] ) ) {
				continue;
			}

			$key = self::get_server_value( $key );
			foreach ( explode( ',', $key ) as $ip ) {
				$ip = trim( $ip ); // just to be safe.

				if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
					return sanitize_text_field( $ip );
				}
			}
		}

		return sanitize_text_field( $ip );
	}

	public static function get_param( $param, $default = '', $src = 'get', $sanitize = '' ) {
		if ( strpos( $param, '[' ) ) {
			$params = explode( '[', $param );
			$param  = $params[0];
		}

		if ( $src === 'get' ) {
			$value = isset( $_POST[ $param ] ) ? wp_unslash( $_POST[ $param ] ) : ( isset( $_GET[ $param ] ) ? wp_unslash( $_GET[ $param ] ) : $default ); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			if ( ! isset( $_POST[ $param ] ) && isset( $_GET[ $param ] ) && ! is_array( $value ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$value = htmlspecialchars_decode( wp_unslash( $_GET[ $param ] ) );
			}
			self::sanitize_value( $sanitize, $value );
		} else {
			$value = self::get_simple_request(
				array(
					'type'     => $src,
					'param'    => $param,
					'default'  => $default,
					'sanitize' => $sanitize,
				)
			);
		}

		if ( isset( $params ) && is_array( $value ) && ! empty( $value ) ) {
			foreach ( $params as $k => $p ) {
				if ( ! $k || ! is_array( $value ) ) {
					continue;
				}

				$p     = trim( $p, ']' );
				$value = isset( $value[ $p ] ) ? $value[ $p ] : $default;
			}
		}

		return $value;
	}

	public static function get_post_param( $param, $default = '', $sanitize = '', $serialized = false ) {
		return self::get_simple_request(
			array(
				'type'     => 'post',
				'param'    => $param,
				'default'  => $default,
				'sanitize' => $sanitize,
				'serialized' => $serialized,
			)
		);
	}

	/**
	 * @since 2.0
	 *
	 * @param string $param
	 * @param string $sanitize
	 * @param string $default
	 *
	 * @return string|array
	 */
	public static function simple_get( $param, $sanitize = 'sanitize_text_field', $default = '' ) {
		return self::get_simple_request(
			array(
				'type'     => 'get',
				'param'    => $param,
				'default'  => $default,
				'sanitize' => $sanitize,
			)
		);
	}

	/**
	 * Get a GET/POST/REQUEST value and sanitize it
	 *
	 * @since 2.0.6
	 *
	 * @param array $args
	 *
	 * @return string|array
	 */
	public static function get_simple_request( $args ) {
		$defaults = array(
			'param'    => '',
			'default'  => '',
			'type'     => 'get',
			'sanitize' => 'sanitize_text_field',
			'serialized' => false,
		);
		$args     = wp_parse_args( $args, $defaults );

		$value = $args['default'];
		if ( $args['type'] == 'get' ) {
			if ( $_GET && isset( $_GET[ $args['param'] ] ) ) {
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
				$value = wp_unslash( $_GET[ $args['param'] ] );
			}
		} elseif ( $args['type'] == 'post' ) {
			if ( isset( $_POST[ $args['param'] ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
				$value = wp_unslash( $_POST[ $args['param'] ] );
				if ( $args['serialized'] === true && is_serialized_string( $value ) && is_serialized( $value ) ) {
					self::unserialize_or_decode( $value );
				}
			}
		} else {
			if ( isset( $_REQUEST[ $args['param'] ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$value = wp_unslash( $_REQUEST[ $args['param'] ] );
			}
		}

		self::sanitize_value( $args['sanitize'], $value );

		return $value;
	}

	/**
	 * Preserve backslashes in a value, but make sure value doesn't get compounding slashes
	 *
	 * @since 2.0.8
	 *
	 * @param string $value
	 *
	 * @return string $value
	 */
	public static function preserve_backslashes( $value ) {
		// If backslashes have already been added, don't add them again
		if ( strpos( $value, '\\\\' ) === false ) {
			$value = addslashes( $value );
		}

		return $value;
	}

	public static function sanitize_value( $sanitize, &$value ) {
		if ( ! empty( $sanitize ) ) {
			if ( is_array( $value ) ) {
				$temp_values = $value;
				foreach ( $temp_values as $k => $v ) {
					self::sanitize_value( $sanitize, $value[ $k ] );
				}
			} else {
				$value = call_user_func( $sanitize, $value );
			}
		}
	}

	public static function sanitize_request( $sanitize_method, &$values ) {
		$temp_values = $values;
		foreach ( $temp_values as $k => $val ) {
			if ( isset( $sanitize_method[ $k ] ) ) {
				$values[ $k ] = call_user_func( $sanitize_method[ $k ], $val );
			}
		}
	}

	/**
	 * @since 4.0.04
	 */
	public static function sanitize_with_html( &$value ) {
		self::sanitize_value( 'wp_kses_post', $value );
		self::decode_specialchars( $value );
	}

	/**
	 * Do wp_specialchars_decode to get back '&' that wp_kses_post might have turned to '&amp;'
	 * this MUST be done, else we'll be back to the '& entity' problem.
	 *
	 * @since 4.0.04
	 */
	public static function decode_specialchars( &$value ) {
		if ( is_array( $value ) ) {
			$temp_values = $value;
			foreach ( $temp_values as $k => $v ) {
				self::decode_specialchars( $value[ $k ] );
			}
		} else {
			self::decode_amp( $value );
		}
	}

	/**
	 * The wp_specialchars_decode function changes too much.
	 * This will leave HTML as is, but still convert &.
	 * Adapted from wp_specialchars_decode().
	 *
	 * @since 4.03.01
	 *
	 * @param string $string The string to prep.
	 */
	private static function decode_amp( &$string ) {
		// Don't bother if there are no entities - saves a lot of processing
		if ( empty( $string ) || strpos( $string, '&' ) === false ) {
			return;
		}

		$translation = array(
			'&quot;'  => '"',
			'&#034;'  => '"',
			'&#x22;'  => '"',
			'&lt; '   => '< ', // The space preserves the HTML.
			'&#060; ' => '< ', // The space preserves the HTML.
			'&gt;'    => '>',
			'&#062;'  => '>',
			'&amp;'   => '&',
			'&#038;'  => '&',
			'&#x26;'  => '&',
		);

		$translation_preg = array(
			'/&#0*34;/'   => '&#034;',
			'/&#x0*22;/i' => '&#x22;',
			'/&#0*60;/'   => '&#060;',
			'/&#0*62;/'   => '&#062;',
			'/&#0*38;/'   => '&#038;',
			'/&#x0*26;/i' => '&#x26;',
		);

		// Remove zero padding on numeric entities
		$string = preg_replace( array_keys( $translation_preg ), array_values( $translation_preg ), $string );

		// Replace characters according to translation table
		$string = strtr( $string, $translation );
	}

	/**
	 * Sanitize the value, and allow some HTML
	 *
	 * @since 2.0
	 *
	 * @param string $value
	 * @param array|string $allowed 'all' for everything included as defaults
	 *
	 * @return string
	 */
	public static function kses( $value, $allowed = array() ) {
		$allowed_html = self::allowed_html( $allowed );

		return wp_kses( $value, $allowed_html );
	}

	/**
	 * The regular kses function strips [button_action] from submit button HTML.
	 *
	 * @since 5.0.13
	 *
	 * @param string $html
	 * @return string
	 */
	public static function kses_submit_button( $html ) {
		$included_button_action = false !== strpos( $html, '[button_action]' );
		$included_back_hook     = false !== strpos( $html, '[back_hook]' );
		$included_draft_hook    = false !== strpos( $html, '[draft_hook]' );
		add_filter( 'safe_style_css', 'FrmAppHelper::allow_visibility_style' );
		add_filter( 'frm_striphtml_allowed_tags', 'FrmAppHelper::add_allowed_submit_button_tags' );
		$html = self::kses( $html, 'all' );
		remove_filter( 'safe_style_css', 'FrmAppHelper::allow_visibility_style' );
		remove_filter( 'frm_striphtml_allowed_tags', 'FrmAppHelper::add_allowed_submit_button_tags' );
		if ( $included_button_action ) {
			if ( false !== strpos( $html, '<input type="submit"' ) ) {
				$pattern = '/(<input type="submit")([^>]*)(\/>)/';
				$html    = preg_replace( $pattern, '$1$2[button_action] $3', $html, 1 );
			} else {
				$pattern = '/(<button)(.*)(class=")(.*)(frm_button_submit)(.*)(")(.*)([^>]+)(>)/';
				$html    = preg_replace( $pattern, '$1$2$3$4$5$6$7 [button_action]$8$9$10', $html, 1 );
			}
		}
		if ( $included_back_hook ) {
			$html = str_replace( 'class="frm_prev_page"', 'class="frm_prev_page" [back_hook]', $html );
		}
		if ( $included_draft_hook ) {
			$html = str_replace( 'class="frm_save_draft"', 'class="frm_save_draft" [draft_hook]', $html );
		}
		return $html;
	}

	/**
	 * @since 5.0.13
	 *
	 * @param array $allowed_attr
	 * @return array
	 */
	public static function allow_visibility_style( $allowed_attr ) {
		$allowed_attr[] = 'visibility';
		return $allowed_attr;
	}

	/**
	 * @since 5.0.13
	 *
	 * @param array $allowed_html
	 * @return array
	 */
	public static function add_allowed_submit_button_tags( $allowed_html ) {
		$allowed_html['input']                    = array(
			'type'           => true,
			'value'          => true,
			'formnovalidate' => true,
			'name'           => true,
			'class'          => true,
		);
		$allowed_html['button']['formnovalidate'] = true;
		$allowed_html['button']['name']           = true;
		$allowed_html['img']['style']             = true;
		return $allowed_html;
	}

	/**
	 * @since 2.05.03
	 */
	private static function allowed_html( $allowed ) {
		$html         = self::safe_html();
		$allowed_html = array();
		if ( $allowed === 'all' ) {
			$allowed_html = $html;
		} elseif ( ! empty( $allowed ) ) {
			foreach ( (array) $allowed as $a ) {
				$allowed_html[ $a ] = isset( $html[ $a ] ) ? $html[ $a ] : array();
			}
		}

		return apply_filters( 'frm_striphtml_allowed_tags', $allowed_html );
	}

	/**
	 * @since 2.05.03
	 */
	private static function safe_html() {
		$allow_class = array(
			'class' => true,
			'id'    => true,
		);

		return array(
			'a'            => array(
				'class'    => true,
				'href'     => true,
				'id'       => true,
				'rel'      => true,
				'target'   => true,
				'title'    => true,
				'tabindex' => true,
			),
			'abbr'       => array(
				'title' => true,
			),
			'aside'      => $allow_class,
			'b'          => array(),
			'blockquote' => array(
				'cite' => true,
			),
			'br'         => array(),
			'cite'       => array(
				'title' => true,
			),
			'code'       => array(),
			'defs'       => array(),
			'del'        => array(
				'datetime' => true,
				'title'    => true,
			),
			'dd'         => array(),
			'div'        => array(
				'class' => true,
				'id'    => true,
				'title' => true,
				'style' => true,
				'role'  => true,
			),
			'dl'         => array(),
			'dt'         => array(),
			'em'         => array(),
			'h1'         => $allow_class,
			'h2'         => $allow_class,
			'h3'         => $allow_class,
			'h4'         => $allow_class,
			'h5'         => $allow_class,
			'h6'         => $allow_class,
			'i'          => array(
				'class' => true,
				'id'    => true,
				'icon'  => true,
				'style' => true,
			),
			'img'        => array(
				'alt'    => true,
				'class'  => true,
				'height' => true,
				'id'     => true,
				'src'    => true,
				'width'  => true,
			),
			'li'         => $allow_class,
			'ol'         => $allow_class,
			'p'          => $allow_class,
			'path'       => array(
				'd'    => true,
				'fill' => true,
			),
			'pre'        => array(),
			'q'          => array(
				'cite'  => true,
				'title' => true,
			),
			'rect'       => array(
				'class'  => true,
				'fill'   => true,
				'height' => true,
				'width'  => true,
				'x'      => true,
				'y'      => true,
				'rx'     => true,
				'stroke' => true,
				'stroke-opacity' => true,
				'stroke-width'   => true,
			),
			'section'    => $allow_class,
			'span'       => array(
				'class'       => true,
				'id'          => true,
				'title'       => true,
				'style'       => true,
				'aria-hidden' => true,
			),
			'strike'     => array(),
			'strong'     => array(),
			'symbol'     => array(
				'class'   => true,
				'id'      => true,
				'viewbox' => true,
			),
			'svg'        => array(
				'class'       => true,
				'id'          => true,
				'xmlns'       => true,
				'viewbox'     => true,
				'width'       => true,
				'height'      => true,
				'style'       => true,
				'fill'        => true,
				'aria-label'  => true,
				'aria-hidden' => true,
			),
			'use'        => array(
				'href'       => true,
				'xlink:href' => true,
			),
			'ul'         => $allow_class,
			'label'      => array(
				'for'    => true,
				'class' => true,
				'id'    => true,
			),
			'button'     => array(
				'class' => true,
				'type'  => true,
			),
			'legend'     => array(
				'class' => true,
			),
		);
	}

	/**
	 * Used when switching the action for a bulk action
	 *
	 * @since 2.0
	 */
	public static function remove_get_action() {
		if ( ! isset( $_GET ) ) {
			return;
		}

		$action_name = isset( $_GET['action'] ) ? 'action' : ( isset( $_GET['action2'] ) ? 'action2' : '' );
		if ( empty( $action_name ) ) {
			return;
		}

		$new_action = self::get_param( $action_name, '', 'get', 'sanitize_text_field' );
		if ( ! empty( $new_action ) ) {
			$_SERVER['REQUEST_URI'] = str_replace( '&action=' . $new_action, '', self::get_server_value( 'REQUEST_URI' ) );
		}
	}

	/**
	 * Check the WP query for a parameter
	 *
	 * @since 2.0
	 * @return string|array
	 */
	public static function get_query_var( $value, $param ) {
		if ( $value != '' ) {
			return $value;
		}

		global $wp_query;
		if ( isset( $wp_query->query_vars[ $param ] ) ) {
			$value = $wp_query->query_vars[ $param ];
		}

		return $value;
	}

	/**
	 * Try to show the SVG if possible. Otherwise, use the font icon.
	 *
	 * @since 4.0.02
	 * @param string $class
	 * @param array  $atts
	 */
	public static function icon_by_class( $class, $atts = array() ) {
		$echo = ! isset( $atts['echo'] ) || $atts['echo'];
		if ( isset( $atts['echo'] ) ) {
			unset( $atts['echo'] );
		}

		$html_atts = self::array_to_html_params( $atts );

		$icon = trim( str_replace( array( 'frm_icon_font', 'frmfont ' ), '', $class ) );
		if ( $icon === $class ) {
			$icon = '<i class="' . esc_attr( $class ) . '"' . $html_atts . '></i>';
		} else {
			$class = strpos( $icon, ' ' ) === false ? '' : ' ' . $icon;
			if ( strpos( $icon, ' ' ) ) {
				$icon = explode( ' ', $icon );
				$icon = reset( $icon );
			}
			$icon = '<svg class="frmsvg' . esc_attr( $class ) . '"' . $html_atts . '>
				<use xlink:href="#' . esc_attr( $icon ) . '" />
			</svg>';
		}

		if ( $echo ) {
			echo self::kses_icon( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			return $icon;
		}
	}

	/**
	 * Run kses for icons. It needs to add a few filters first in order to preserve some custom style values.
	 *
	 * @since 5.0.13
	 *
	 * @param string $icon
	 * @return string
	 */
	public static function kses_icon( $icon ) {
		add_filter( 'safe_style_css', 'FrmAppHelper::allow_vars_in_styles' );
		add_filter( 'safecss_filter_attr_allow_css', 'FrmAppHelper::allow_style', 10, 2 );
		add_filter( 'frm_striphtml_allowed_tags', 'FrmAppHelper::add_allowed_icon_tags' );
		$icon = self::kses( $icon, 'all' );
		remove_filter( 'safe_style_css', 'FrmAppHelper::allow_vars_in_styles' );
		remove_filter( 'safecss_filter_attr_allow_css', 'FrmAppHelper::allow_style' );
		remove_filter( 'frm_striphtml_allowed_tags', 'FrmAppHelper::add_allowed_icon_tags' );
		return $icon;
	}

	/**
	 * @since 5.0.13.1
	 *
	 * @param array $allowed_html
	 * @return array
	 */
	public static function add_allowed_icon_tags( $allowed_html ) {
		$allowed_html['svg']['data-open'] = true;
		$allowed_html['svg']['title']     = true;
		return $allowed_html;
	}

	/**
	 * @since 5.0.13
	 *
	 * @param array $allowed_attr
	 * @return array
	 */
	public static function allow_vars_in_styles( $allowed_attr ) {
		$allowed_attr[] = '--primary-hover';
		return $allowed_attr;
	}

	/**
	 * @since 5.0.13
	 *
	 * @param bool   $allow_css
	 * @param string $css_string
	 */
	public static function allow_style( $allow_css, $css_string ) {
		if ( ! $allow_css && 0 === strpos( $css_string, '--primary-hover:' ) ) {
			$split     = explode( ':', $css_string, 2 );
			$allow_css = 2 === count( $split ) && self::is_a_valid_color( $split[1] );
		}
		return $allow_css;
	}

	/**
	 * @since 5.0.13
	 *
	 * @param string $value
	 * @return bool
	 */
	private static function is_a_valid_color( $value ) {
		$match = 0;
		if ( 0 === strpos( $value, 'rgba(' ) ) {
			$match = preg_match( '/^rgba\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3}),\s*(\d*(?:\.\d+)?)\)$/', $value );
		} elseif ( 0 === strpos( $value, 'rgb(' ) ) {
			$match = preg_match( '/^rgb\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})\)$/', $value );
		} elseif ( 0 === strpos( $value, '#' ) ) {
			$match = preg_match( '/^#([a-f0-9]{6}|[a-f0-9]{3})\b$/', $value );
		}
		return (bool) $match;
	}

	/**
	 * Include svg images.
	 *
	 * @since 4.0.02
	 */
	public static function include_svg() {
		include_once self::plugin_path() . '/images/icons.svg';
	}

	/**
	 * Convert an associative array to HTML values.
	 *
	 * @since 4.0.02
	 * @since 5.0.13 added $echo parameter.
	 *
	 * @param array $atts
	 * @param bool  $echo
	 * @return string|void
	 */
	public static function array_to_html_params( $atts, $echo = false ) {
		$callback = function() use ( $atts ) {
			if ( $atts ) {
				foreach ( $atts as $key => $value ) {
					echo ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
				}
			}
		};
		return self::clip( $callback, $echo );
	}

	/**
	 * Call an echo function and either echo it or return the result as a string.
	 *
	 * @since 5.0.13
	 *
	 * @param Closure $echo_function
	 * @param bool    $echo
	 * @return string|void
	 */
	public static function clip( $echo_function, $echo = false ) {
		if ( ! $echo ) {
			ob_start();
		}

		$echo_function();

		if ( ! $echo ) {
			$return = ob_get_contents();
			ob_end_clean();
			return $return;
		}
	}

	/**
	 * @since 3.0
	 */
	public static function get_admin_header( $atts ) {
		$has_nav = ( isset( $atts['form'] ) && ! empty( $atts['form'] ) && ( ! isset( $atts['is_template'] ) || ! $atts['is_template'] ) );
		if ( ! isset( $atts['close'] ) || empty( $atts['close'] ) ) {
			$atts['close'] = admin_url( 'admin.php?page=formidable' );
		}
		if ( ! isset( $atts['import_link'] ) ) {
			$atts['import_link'] = false;
		}

		include( self::plugin_path() . '/classes/views/shared/admin-header.php' );
	}

	/**
	 * Print applicable admin banner.
	 *
	 * @since 5.4.2
	 *
	 * @param bool $should_show_lite_upgrade
	 * @return void
	 */
	public static function print_admin_banner( $should_show_lite_upgrade ) {
		if ( ! current_user_can( 'administrator' ) ) {
			FrmInbox::maybe_show_banner();
			return;
		}

		if ( self::maybe_show_license_warning() || FrmInbox::maybe_show_banner() || ! $should_show_lite_upgrade || self::pro_is_installed() ) {
			// Print license warning or inbox banner and exit if either prints.
			// And exit before printing the upgrade bar if it shouldn't be shown.
			return;
		}
		?>
		<div class="frm-upgrade-bar">
			<span>You're using Formidable Forms Lite. To unlock more features consider</span>
			<a href="<?php echo esc_url( self::admin_upgrade_link( 'top-bar' ) ); ?>" target="_blank" rel="noopener">upgrading to Pro</a>.
		</div>
		<?php
	}

	/**
	 * @since 5.4.2
	 *
	 * @return bool True if a banner is available and shown.
	 */
	private static function maybe_show_license_warning() {
		return is_callable( 'FrmProAddonsController::admin_banner' ) && FrmProAddonsController::admin_banner();
	}

	/**
	 * Render a button for a new item (Form, Application, etc).
	 *
	 * @since 3.0
	 * @param array $atts {
	 *     @type array  $link_hook    Custom link hook, calls do_action and exits early.
	 *     @type string $new_link     Href value, default #.
	 *     @type string $class        Custom class names, space separated.
	 *     @type string $button_text  Button text. Default "Add New".
	 * }
	 * @return void
	 */
	public static function add_new_item_link( $atts ) {
		if ( isset( $atts['link_hook'] ) ) {
			do_action( $atts['link_hook']['hook'], $atts['link_hook']['param'] );
			return;
		}

		if ( empty( $atts['new_link'] ) && empty( $atts['trigger_new_form_modal'] ) && empty( $atts['class'] ) ) {
			// Do not render a button if none of these attributes are set.
			return;
		}

		$href  = ! empty( $atts['new_link'] ) ? esc_url( $atts['new_link'] ) : '#';
		$class = 'button button-primary frm-button-primary frm-with-plus';

		if ( ! empty( $atts['trigger_new_form_modal'] ) ) {
			$class .= ' frm-trigger-new-form-modal';
		}

		if ( ! empty( $atts['class'] ) ) {
			$class .= ' ' . $atts['class'];
		}

		$button_text = ! empty( $atts['button_text'] ) ? $atts['button_text'] : __( 'Add New', 'formidable' );

		require self::plugin_path() . '/classes/views/shared/add-button.php';
	}

	/**
	 * @since 3.06
	 */
	public static function show_search_box( $atts ) {
		$defaults = array(
			'placeholder' => '',
			'tosearch'    => '',
			'text'        => __( 'Search', 'formidable' ),
			'input_id'    => '',
		);
		$atts = array_merge( $defaults, $atts );

		if ( $atts['input_id'] === 'template' && empty( $atts['tosearch'] ) ) {
			$atts['tosearch'] = 'frm-card';
		}

		$class = 'frm-search-input';
		if ( ! empty( $atts['tosearch'] ) ) {
			$class .= ' frm-auto-search';
		}

		$input_id = $atts['input_id'] . '-search-input';

		?>
		<p class="frm-search">
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>">
				<?php echo esc_html( $atts['text'] ); ?>:
			</label>
			<span class="frmfont frm_search_icon"></span>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s"
				value="<?php _admin_search_query(); ?>" placeholder="<?php echo esc_attr( $atts['placeholder'] ); ?>"
				class="<?php echo esc_attr( $class ); ?>" data-tosearch="<?php echo esc_attr( $atts['tosearch'] ); ?>"
				<?php if ( ! empty( $atts['tosearch'] ) ) { ?>
				autocomplete="off"
				<?php } ?>
				/>
			<?php
			if ( empty( $atts['tosearch'] ) ) {
				submit_button( $atts['text'], 'button-secondary', '', false, array( 'id' => 'search-submit' ) );
			}
			?>
		</p>
		<?php
	}

	/**
	 * @param string $type
	 */
	public static function trigger_hook_load( $type, $object = null ) {
		// Only load the form hooks once.
		$hooks_loaded = apply_filters( 'frm_' . $type . '_hooks_loaded', false, $object );
		if ( ! $hooks_loaded ) {
			do_action( 'frm_load_' . $type . '_hooks' );
		}
	}

	/**
	 * Save all front-end js scripts into a single file
	 *
	 * @since 3.0
	 */
	public static function save_combined_js() {
		$file_atts = apply_filters(
			'frm_js_location',
			array(
				'file_name'     => 'frm.min.js',
				'new_file_path' => self::plugin_path() . '/js',
			)
		);
		$new_file  = new FrmCreateFile( $file_atts );

		$files = array(
			self::plugin_path() . '/js/formidable.min.js',
		);
		$files = apply_filters( 'frm_combined_js_files', $files );
		$new_file->combine_files( $files );
	}

	/**
	 * Check a value from a shortcode to see if true or false.
	 * True when value is 1, true, 'true', 'yes'
	 *
	 * @since 1.07.10
	 *
	 * @param string $value The value to compare
	 *
	 * @return boolean True or False
	 */
	public static function is_true( $value ) {
		return ( true === $value || 1 == $value || 'true' == $value || 'yes' == $value );
	}

	/**
	 * Gets all post from a specific post type.
	 * This gets the entire WP_Post object so it can require a lot of memory. When only id and title are needed, consider using FrmAppHelper::get_post_ids_and_titles instead.
	 *
	 * @since 4.10.01 Add `$post_type` argument.
	 *
	 * @param string $post_type Post type to query. Default is `page`.
	 * @return WP_Post[]
	 */
	public static function get_pages( $post_type = 'page' ) {
		$query = array(
			'post_type'   => $post_type,
			'post_status' => array( 'publish', 'private' ),
			'numberposts' => - 1,
			'orderby'     => 'title',
			'order'       => 'ASC',
		);

		return get_posts( $query );
	}

	/**
	 * Gets post ids and titles for a specific post type.
	 *
	 * @since 5.0.09
	 *
	 * @param string $post_type Post type to query. Default is `page`.
	 * @return array
	 */
	public static function get_post_ids_and_titles( $post_type = 'page' ) {
		return FrmDb::get_results(
			'posts',
			array(
				'post_type'   => $post_type,
				'post_status' => array( 'publish', 'private' ),
			),
			'ID, post_title',
			array(
				'order_by' => 'post_title ASC',
			)
		);
	}

	/**
	 * Renders an autocomplete page selection or a regular dropdown depending on
	 * the total page count
	 *
	 * @since 4.03.06
	 * @since 4.10.01 Added `post_type` and `autocomplete_placeholder` to the arguments array.
	 *
	 * @param array $args Selection arguments.
	 */
	public static function maybe_autocomplete_pages_options( $args ) {
		$args = self::preformat_selection_args( $args );

		$pages_count = wp_count_posts( $args['post_type'] );

		if ( $pages_count->publish <= 50 ) {
			self::wp_pages_dropdown( $args );
			return;
		}

		wp_enqueue_script( 'jquery-ui-autocomplete' );

		$selected = self::get_post_param( $args['field_name'], $args['page_id'], 'absint' );
		$title = '';

		if ( $selected ) {
			$title = get_the_title( $selected );
		}

		?>
		<input type="text" class="frm-page-search"
			data-post-type="<?php echo esc_attr( $args['post_type'] ); ?>"
			placeholder="<?php echo esc_attr( $args['autocomplete_placeholder'] ); ?>"
			value="<?php echo esc_attr( $title ); ?>" />
		<input type="hidden" name="<?php echo esc_attr( $args['field_name'] ); ?>"
			class="frm_autocomplete_value_input"
			value="<?php echo esc_attr( $selected ); ?>" />
		<?php
	}

	/**
	 * @param array   $args
	 * @param string  $page_id Deprecated.
	 * @param boolean $truncate Deprecated.
	 */
	public static function wp_pages_dropdown( $args = array(), $page_id = '', $truncate = false ) {
		self::prep_page_dropdown_params( $page_id, $truncate, $args );

		$pages    = self::get_post_ids_and_titles( $args['post_type'] );
		$selected = self::get_post_param( $args['field_name'], $args['page_id'], 'absint' );

		?>
		<select name="<?php echo esc_attr( $args['field_name'] ); ?>" id="<?php echo esc_attr( $args['field_name'] ); ?>" class="frm-pages-dropdown">
			<option value=""><?php echo esc_html( $args['placeholder'] ); ?></option>
			<?php foreach ( $pages as $page ) { ?>
				<option value="<?php echo esc_attr( $page->ID ); ?>" <?php selected( $selected, $page->ID ); ?>>
					<?php echo esc_html( $args['truncate'] ? self::truncate( $page->post_title, $args['truncate'] ) : $page->post_title ); ?>
				</option>
			<?php } ?>
		</select>
		<?php
	}

	/**
	 * Fill in missing parameters passed to wp_pages_dropdown().
	 * This is for reverse compatibility with switching 3 params to 1.
	 *
	 * @since 4.03.06
	 */
	private static function prep_page_dropdown_params( $page_id, $truncate, &$args ) {
		if ( ! is_array( $args ) ) {
			$args = array(
				'field_name' => $args,
				'page_id'    => $page_id,
				'truncate'   => $truncate,
			);
		}

		$args = self::preformat_selection_args( $args );
	}

	/**
	 * Filter to format args for page dropdown or autocomplete
	 *
	 * @since 4.03.06
	 * @since 4.10.01 Added `post_type` and `autocomplete_placeholder` to the arguments array.
	 */
	private static function preformat_selection_args( $args ) {
		$defaults = array(
			'truncate'    => false,
			'placeholder' => ' ',
			'field_name'  => '',
			'page_id'     => '',
			'post_type'   => 'page',
			'autocomplete_placeholder' => __( 'Select a Page', 'formidable' ),
		);

		return array_merge( $defaults, $args );
	}

	public static function post_edit_link( $post_id ) {
		$post = get_post( $post_id );
		if ( $post ) {
			$post_url = admin_url( 'post.php?post=' . $post_id . '&action=edit' );
			$post_url = self::maybe_full_screen_link( $post_url );

			return '<a href="' . esc_url( $post_url ) . '">' . self::truncate( $post->post_title, 50 ) . '</a>';
		}

		return '';
	}

	/**
	 * Hide the WordPress menus on some pages.
	 *
	 * @since 4.0
	 *
	 * @return bool
	 */
	public static function is_full_screen() {
		return self::is_form_builder_page() ||
			self::is_admin_page( 'formidable-styles' ) ||
			self::is_admin_page( 'formidable-styles2' ) ||
			self::simple_get( 'frm-full', 'absint' ) ||
			self::is_view_builder_page();
	}

	/**
	 * @since 4.0
	 */
	public static function maybe_full_screen_link( $link ) {
		$is_full = self::simple_get( 'frm-full', 'absint' );
		if ( $is_full && ! empty( $link ) && $link !== '#' ) {
			$link .= '&frm-full=1';
		}
		return $link;
	}

	/**
	 * @param string        $field_name
	 * @param string|array  $capability
	 * @param string        $multiple 'single' and 'multiple'
	 */
	public static function wp_roles_dropdown( $field_name, $capability, $multiple = 'single' ) {
		?>
		<select name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( $field_name ); ?>"
			<?php echo ( 'multiple' === $multiple ) ? 'multiple="multiple"' : ''; ?>
			class="frm_multiselect">
			<?php self::roles_options( $capability ); ?>
		</select>
		<?php
	}

	/**
	 * @since 4.07
	 * @param array|string $selected
	 * @param string $current
	 */
	private static function selected( $selected, $current ) {
		if ( is_callable( 'FrmProAppHelper::selected' ) ) {
			FrmProAppHelper::selected( $selected, $current );
		} else {
			selected( in_array( $current, (array) $selected, true ) );
		}
	}

	/**
	 * @param string|array $capability
	 */
	public static function roles_options( $capability ) {
		global $frm_vars;
		if ( isset( $frm_vars['editable_roles'] ) ) {
			$editable_roles = $frm_vars['editable_roles'];
		} else {
			$editable_roles             = get_editable_roles();
			$frm_vars['editable_roles'] = $editable_roles;
		}

		foreach ( $editable_roles as $role => $details ) {
			$name = translate_user_role( $details['name'] );
			?>
			<option value="<?php echo esc_attr( $role ); ?>" <?php self::selected( $capability, $role ); ?>><?php echo esc_attr( $name ); ?> </option>
			<?php
			unset( $role, $details );
		}
	}

	/**
	 * Gets the list of capabilities.
	 *
	 * @since 5.0 Parameter `$type` supports `pro_only` value.
	 *
	 * @param string $type Supports `auto`, `pro`, or `pro_only`.
	 * @return array
	 */
	public static function frm_capabilities( $type = 'auto' ) {
		if ( ! self::pro_is_installed() && ! in_array( $type, array( 'pro', 'pro_only' ), true ) ) {
			return self::get_lite_capabilities();
		}

		$pro_cap = array(
			'frm_create_entries' => __( 'Add Entries from Admin Area', 'formidable' ),
			'frm_edit_entries'   => __( 'Edit Entries from Admin Area', 'formidable' ),
			'frm_view_reports'   => __( 'View Reports', 'formidable' ),
			'frm_edit_displays'  => __( 'Add/Edit Views', 'formidable' ),
		);
		/**
		 * @since 5.3.1
		 *
		 * @param array<string,string> $pro_cap
		 */
		$pro_cap = apply_filters( 'frm_pro_capabilities', $pro_cap );

		if ( 'pro_only' === $type ) {
			return $pro_cap;
		}

		return self::get_lite_capabilities() + $pro_cap;
	}

	/**
	 * Get the list of lite plugin capabilities.
	 *
	 * @since 5.3.1
	 *
	 * @return array<string,string>
	 */
	private static function get_lite_capabilities() {
		return array(
			'frm_view_forms'      => __( 'View Forms', 'formidable' ),
			'frm_edit_forms'      => __( 'Add and Edit Forms', 'formidable' ),
			'frm_delete_forms'    => __( 'Delete Forms', 'formidable' ),
			'frm_change_settings' => __( 'Access this Settings Page', 'formidable' ),
			'frm_view_entries'    => __( 'View Entries from Admin Area', 'formidable' ),
			'frm_delete_entries'  => __( 'Delete Entries from Admin Area', 'formidable' ),
		);
	}

	/**
	 * Call the WordPress current_user_can but also validate empty strings as true for any logged in user
	 *
	 * @since 4.06.03
	 *
	 * @param string $role
	 *
	 * @return bool
	 */
	public static function current_user_can( $role ) {
		if ( $role === '-1' ) {
			return false;
		}

		if ( $role === 'loggedout' ) {
			return ! is_user_logged_in();
		}

		if ( $role === 'loggedin' || ! $role ) {
			return is_user_logged_in();
		}

		if ( $role == 1 ) {
			$role = 'administrator';
		}

		if ( ! is_user_logged_in() ) {
			return false;
		}

		return current_user_can( $role );
	}

	/**
	 * @param string|array $needed_role
	 * @return bool
	 */
	public static function user_has_permission( $needed_role ) {
		if ( is_array( $needed_role ) ) {
			foreach ( $needed_role as $role ) {
				if ( self::current_user_can( $role ) ) {
					return true;
				}
			}

			return false;
		}

		$can = self::current_user_can( $needed_role );

		if ( $can || in_array( $needed_role, array( '-1', 'loggedout' ) ) ) {
			return $can;
		}

		$roles = array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' );
		foreach ( $roles as $role ) {
			if ( current_user_can( $role ) ) {
				return true;
			}
			if ( $role == $needed_role ) {
				break;
			}
		}

		return false;
	}

	/**
	 * Make sure administrators can see Formidable menu
	 *
	 * @since 2.0
	 */
	public static function maybe_add_permissions() {
		self::force_capability( 'frm_view_entries' );

		if ( ! current_user_can( 'administrator' ) || current_user_can( 'frm_view_forms' ) ) {
			return;
		}

		$user_id   = get_current_user_id();
		$user      = new WP_User( $user_id );
		$frm_roles = self::frm_capabilities();
		foreach ( $frm_roles as $frm_role => $frm_role_description ) {
			$user->add_cap( $frm_role );
			unset( $frm_role, $frm_role_description );
		}
	}

	/**
	 * Make sure admins have permission to see the menu items
	 *
	 * @since 2.0.6
	 */
	public static function force_capability( $cap = 'frm_change_settings' ) {
		if ( current_user_can( 'administrator' ) && ! current_user_can( $cap ) ) {
			$role      = get_role( 'administrator' );
			$frm_roles = self::frm_capabilities();
			foreach ( $frm_roles as $frm_role => $frm_role_description ) {
				$role->add_cap( $frm_role );
			}
		}
	}

	/**
	 * Check if the user has permision for action.
	 * Return permission message and stop the action if no permission
	 *
	 * @since 2.0
	 *
	 * @param string $permission
	 */
	public static function permission_check( $permission, $show_message = 'show' ) {
		$permission_error = self::permission_nonce_error( $permission );
		if ( $permission_error !== false ) {
			if ( 'hide' == $show_message ) {
				$permission_error = '';
			}
			wp_die( esc_html( $permission_error ) );
		}
	}

	/**
	 * Check user permission and nonce
	 *
	 * @since 2.0
	 *
	 * @param string $permission
	 *
	 * @return false|string The permission message or false if allowed
	 */
	public static function permission_nonce_error( $permission, $nonce_name = '', $nonce = '' ) {
		if ( ! empty( $permission ) && ! current_user_can( $permission ) && ! current_user_can( 'administrator' ) ) {
			$frm_settings = self::get_settings();

			return $frm_settings->admin_permission;
		}

		$error = false;
		if ( empty( $nonce_name ) ) {
			return $error;
		}

		$nonce_value = ( $_REQUEST && isset( $_REQUEST[ $nonce_name ] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $nonce_name ] ) ) : '';
		if ( $_REQUEST && ( ! isset( $_REQUEST[ $nonce_name ] ) || ! wp_verify_nonce( $nonce_value, $nonce ) ) ) {
			$frm_settings = self::get_settings();
			$error        = $frm_settings->admin_permission;
		}

		return $error;
	}

	public static function checked( $values, $current ) {
		if ( self::check_selected( $values, $current ) ) {
			echo ' checked="checked"';
		}
	}

	public static function check_selected( $values, $current ) {
		$values  = self::recursive_function_map( $values, 'trim' );
		$values  = self::recursive_function_map( $values, 'htmlspecialchars_decode' );
		$current = htmlspecialchars_decode( trim( $current ) );

		return ( is_array( $values ) && in_array( $current, $values ) ) || ( ! is_array( $values ) && $values == $current );
	}

	public static function recursive_function_map( $value, $function ) {
		if ( is_array( $value ) ) {
			$original_function = $function;
			if ( count( $value ) ) {
				$function = explode( ', ', FrmDb::prepare_array_values( $value, $function ) );
			} else {
				$function = array( $function );
			}
			if ( ! self::is_assoc( $value ) ) {
				$value = array_map( array( 'FrmAppHelper', 'recursive_function_map' ), $value, $function );
			} else {
				foreach ( $value as $k => $v ) {
					if ( ! is_array( $v ) ) {
						$value[ $k ] = call_user_func( $original_function, $v );
					}
				}
			}
		} else {
			$value = call_user_func( $function, $value );
		}

		return $value;
	}

	public static function is_assoc( $array ) {
		return (bool) count( array_filter( array_keys( $array ), 'is_string' ) );
	}

	/**
	 * Flatten a multi-dimensional array
	 */
	public static function array_flatten( $array, $keys = 'keep' ) {
		$return = array();
		foreach ( $array as $key => $value ) {
			if ( is_array( $value ) ) {
				$return = array_merge( $return, self::array_flatten( $value, $keys ) );
			} else {
				if ( $keys == 'keep' ) {
					$return[ $key ] = $value;
				} else {
					$return[] = $value;
				}
			}
		}

		return $return;
	}

	public static function esc_textarea( $text, $is_rich_text = false ) {
		$safe_text = str_replace( '&quot;', '"', $text );
		if ( ! $is_rich_text ) {
			$safe_text = htmlspecialchars( $safe_text, ENT_NOQUOTES );
		}
		$safe_text = str_replace( '&amp; ', '& ', $safe_text );

		return apply_filters( 'esc_textarea', $safe_text, $text );
	}

	/**
	 * Add auto paragraphs to text areas
	 *
	 * @since 2.0
	 */
	public static function use_wpautop( $content ) {
		if ( apply_filters( 'frm_use_wpautop', true ) && ! is_array( $content ) ) {
			$content = wpautop( str_replace( '<br>', '<br />', $content ) );
		}

		return $content;
	}

	public static function replace_quotes( $val ) {
		// Replace double quotes.
		$val = str_replace( array( '&#8220;', '&#8221;', '&#8243;' ), '"', $val );

		// Replace single quotes.
		$val = str_replace( array( '&#8216;', '&#8217;', '&#8242;', '&prime;', '&rsquo;', '&lsquo;' ), "'", $val );

		return $val;
	}

	/**
	 * @param string $handle
	 */
	public static function script_version( $handle, $default = 0 ) {
		global $wp_scripts;
		if ( ! $wp_scripts ) {
			return $default;
		}

		$ver = $default;
		if ( ! isset( $wp_scripts->registered[ $handle ] ) ) {
			return $ver;
		}

		$query = $wp_scripts->registered[ $handle ];
		if ( is_object( $query ) && ! empty( $query->ver ) ) {
			$ver = $query->ver;
		}

		return $ver;
	}

	/**
	 * @since 5.0.13 added $echo param.
	 *
	 * @param string $url
	 * @param bool   $echo
	 * @return string|void
	 */
	public static function js_redirect( $url, $echo = false ) {
		$callback = function() use ( $url ) {
			echo '<script type="text/javascript">window.location="' . esc_url_raw( $url ) . '"</script>';
		};
		return self::clip( $callback, $echo );
	}

	public static function get_user_id_param( $user_id ) {
		if ( ! $user_id || is_numeric( $user_id ) ) {
			return $user_id;
		}

		$user_id = sanitize_text_field( $user_id );
		if ( $user_id == 'current' ) {
			$user_id = get_current_user_id();
		} else {
			if ( is_email( $user_id ) ) {
				$user = get_user_by( 'email', $user_id );
			} else {
				$user = get_user_by( 'login', $user_id );
			}

			if ( $user ) {
				$user_id = $user->ID;
			}
			unset( $user );
		}

		return $user_id;
	}

	public static function get_file_contents( $filename, $atts = array() ) {
		if ( ! is_file( $filename ) ) {
			return false;
		}

		extract( $atts ); // phpcs:ignore WordPress.PHP.DontExtract
		ob_start();
		include( $filename );
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}

	/**
	 * @param string $name
	 * @param string $table_name
	 * @param string $column
	 * @param int    $id
	 * @param int    $num_chars
	 */
	public static function get_unique_key( $name, $table_name, $column, $id = 0, $num_chars = 5 ) {
		$key = '';
		if ( $name ) {
			$key = sanitize_key( $name );
			$key = self::maybe_clear_long_key( $key, $column );
		}

		if ( ! $key ) {
			$key = self::generate_new_key( $num_chars );
		}

		$key = self::prevent_numeric_and_reserved_keys( $key );

		$similar_keys = FrmDb::get_col(
			$table_name,
			array(
				$column . ' like%' => $key,
				'ID !'             => $id,
			),
			$column
		);

		// Create a unique field id if it has already been used.
		if ( in_array( $key, $similar_keys, true ) ) {
			$key = self::maybe_truncate_key_before_appending( $column, $key );

			/**
			 * Allow for a custom separator between the attempted key and the generated suffix.
			 *
			 * @since 5.2.03
			 *
			 * @param string $separator. Default empty.
			 * @param string $key the key without the added suffix.
			 */
			$separator = apply_filters( 'frm_unique_' . $column . '_separator', '', $key );

			$suffix = 2;
			do {
				$key_check = $key . $separator . $suffix;
				++$suffix;
			} while ( in_array( $key_check, $similar_keys, true ) );

			$key = $key_check;
		}

		return $key;
	}

	/**
	 * Avoid trying to append to a really long key,
	 * The database limit is 100 for form and field keys so we want to avoid getting too close.
	 *
	 * @param string $column
	 * @param string $key
	 * @return string
	 */
	private static function maybe_truncate_key_before_appending( $column, $key ) {
		if ( in_array( $column, array( 'form_key', 'field_key' ), true ) ) {
			$max_key_length_before_truncating = 60;
			if ( strlen( $key ) > $max_key_length_before_truncating ) {
				$key = substr( $key, 0, $max_key_length_before_truncating );
				if ( is_numeric( $key ) ) {
					$key .= 'a';
				}
			}
		}
		return $key;
	}

	/**
	 * Possibly reset a key to avoid conflicts with column size limits.
	 *
	 * @param string $key
	 * @param string $column
	 * @return string either the original key value, or an empty string if the key was too long.
	 */
	private static function maybe_clear_long_key( $key, $column ) {
		if ( 'field_key' === $column && strlen( $key ) >= 70 ) {
			$key = '';
		}
		return $key;
	}

	/**
	 * @param int $num_chars
	 * @return string
	 */
	private static function generate_new_key( $num_chars ) {
		$max_slug_value = pow( 36, $num_chars );
		$min_slug_value = 37; // we want to have at least 2 characters in the slug
		return base_convert( rand( $min_slug_value, $max_slug_value ), 10, 36 );
	}

	/**
	 * @param string $key
	 * @return string
	 */
	private static function prevent_numeric_and_reserved_keys( $key ) {
		if ( is_numeric( $key ) ) {
			$key .= 'a';
		} else {
			$not_allowed = array(
				'id',
				'key',
				'created-at',
				'detaillink',
				'editlink',
				'siteurl',
				'evenodd',
			);
			if ( in_array( $key, $not_allowed, true ) ) {
				$key .= 'a';
			}
		}
		return $key;
	}

	/**
	 * Editing a Form or Entry
	 *
	 * @param string $table
	 *
	 * @return bool|array
	 */
	public static function setup_edit_vars( $record, $table, $fields = '', $default = false, $post_values = array(), $args = array() ) {
		if ( ! $record ) {
			return false;
		}

		if ( empty( $post_values ) ) {
			$post_values = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}

		$values = array(
			'id'     => $record->id,
			'fields' => array(),
		);

		foreach ( array( 'name', 'description' ) as $var ) {
			$default_val    = isset( $record->{$var} ) ? $record->{$var} : '';
			$values[ $var ] = self::get_param( $var, $default_val, 'get', 'wp_kses_post' );
			unset( $var, $default_val );
		}

		$values['description'] = self::use_wpautop( $values['description'] );

		self::fill_form_opts( $record, $table, $post_values, $values );

		self::prepare_field_arrays( $fields, $record, $values, array_merge( $args, compact( 'default', 'post_values' ) ) );

		if ( $table === 'entries' ) {
			$values = FrmEntriesHelper::setup_edit_vars( $values, $record );
		} elseif ( $table === 'forms' ) {
			$values = FrmFormsHelper::setup_edit_vars( $values, $record, $post_values );
		}

		return $values;
	}

	private static function prepare_field_arrays( $fields, $record, array &$values, $args ) {
		if ( ! empty( $fields ) ) {
			foreach ( (array) $fields as $field ) {
				if ( ! self::is_admin_page() ) {
					// Don't prep default values on the form settings page.
					$field->default_value = apply_filters( 'frm_get_default_value', $field->default_value, $field, true );
				}
				$args['parent_form_id'] = isset( $args['parent_form_id'] ) ? $args['parent_form_id'] : $field->form_id;
				self::fill_field_defaults( $field, $record, $values, $args );
			}
		}
	}

	private static function fill_field_defaults( $field, $record, array &$values, $args ) {
		$post_values = $args['post_values'];

		if ( $args['default'] ) {
			$meta_value = $field->default_value;
		} else {
			if ( $record->post_id && self::pro_is_installed() && isset( $field->field_options['post_field'] ) && $field->field_options['post_field'] ) {
				if ( ! isset( $field->field_options['custom_field'] ) ) {
					$field->field_options['custom_field'] = '';
				}
				$meta_value = FrmProEntryMetaHelper::get_post_value(
					$record->post_id,
					$field->field_options['post_field'],
					$field->field_options['custom_field'],
					array(
						'truncate' => false,
						'type'     => $field->type,
						'form_id'  => $field->form_id,
						'field'    => $field,
					)
				);
			} else {
				$meta_value = FrmEntryMeta::get_meta_value( $record, $field->id );
			}
		}

		$field_type = isset( $post_values['field_options'][ 'type_' . $field->id ] ) ? $post_values['field_options'][ 'type_' . $field->id ] : $field->type;
		if ( isset( $post_values['item_meta'][ $field->id ] ) ) {
			$new_value = $post_values['item_meta'][ $field->id ];
			self::unserialize_or_decode( $new_value );
		} else {
			$new_value = $meta_value;
		}

		$field_array                   = self::start_field_array( $field );
		$field_array['value']          = $new_value;
		$field_array['type']           = apply_filters( 'frm_field_type', $field_type, $field, $new_value );
		$field_array['parent_form_id'] = $args['parent_form_id'];

		$args['field_type'] = $field_type;

		FrmFieldsHelper::prepare_edit_front_field( $field_array, $field, $values['id'], $args );

		if ( ! isset( $field_array['unique'] ) || ! $field_array['unique'] ) {
			$field_array['unique_msg'] = '';
		}

		$field_array = array_merge( (array) $field->field_options, $field_array );

		$values['fields'][ $field->id ] = $field_array;
	}

	/**
	 * @since 3.0
	 *
	 * @param object $field
	 *
	 * @return array
	 */
	public static function start_field_array( $field ) {
		return array(
			'id'            => $field->id,
			'default_value' => $field->default_value,
			'name'          => $field->name,
			'description'   => $field->description,
			'options'       => $field->options,
			'required'      => $field->required,
			'field_key'     => $field->field_key,
			'field_order'   => $field->field_order,
			'form_id'       => $field->form_id,
		);
	}

	/**
	 * @param string $table
	 */
	private static function fill_form_opts( $record, $table, $post_values, array &$values ) {
		if ( $table == 'entries' ) {
			$form = $record->form_id;
			FrmForm::maybe_get_form( $form );
		} else {
			$form = $record;
		}

		if ( ! $form ) {
			return;
		}

		$values['form_name']      = isset( $record->form_id ) ? $form->name : '';
		$values['parent_form_id'] = isset( $record->form_id ) ? $form->parent_form_id : 0;

		if ( ! is_array( $form->options ) ) {
			return;
		}

		foreach ( $form->options as $opt => $value ) {
			if ( isset( $post_values[ $opt ] ) ) {
				$values[ $opt ] = $post_values[ $opt ];
				self::unserialize_or_decode( $values[ $opt ] );
			} else {
				$values[ $opt ] = $value;
			}
		}

		self::fill_form_defaults( $post_values, $values );
	}

	/**
	 * Set to POST value or default
	 */
	private static function fill_form_defaults( $post_values, array &$values ) {
		$form_defaults = FrmFormsHelper::get_default_opts();

		foreach ( $form_defaults as $opt => $default ) {
			if ( ! isset( $values[ $opt ] ) || $values[ $opt ] == '' ) {
				$values[ $opt ] = ( $post_values && isset( $post_values['options'][ $opt ] ) ) ? $post_values['options'][ $opt ] : $default;
			}

			unset( $opt, $default );
		}

		if ( ! isset( $values['custom_style'] ) ) {
			$values['custom_style'] = self::custom_style_value( $post_values );
		}

		foreach ( array( 'before', 'after', 'submit' ) as $h ) {
			if ( ! isset( $values[ $h . '_html' ] ) ) {
				$values[ $h . '_html' ] = ( isset( $post_values['options'][ $h . '_html' ] ) ? $post_values['options'][ $h . '_html' ] : FrmFormsHelper::get_default_html( $h ) );
			}
			unset( $h );
		}
	}

	/**
	 * @since 2.2.10
	 *
	 * @param array $post_values
	 *
	 * @return boolean|int
	 */
	public static function custom_style_value( $post_values ) {
		if ( ! empty( $post_values ) && isset( $post_values['options']['custom_style'] ) ) {
			$custom_style = absint( $post_values['options']['custom_style'] );
		} else {
			$frm_settings = self::get_settings();
			$custom_style = ( $frm_settings->load_style != 'none' );
		}

		return $custom_style;
	}

	public static function truncate( $str, $length, $minword = 3, $continue = '...' ) {
		if ( is_array( $str ) ) {
			return '';
		}

		$length       = (int) $length;
		$str          = wp_strip_all_tags( $str );
		$original_len = self::mb_function( array( 'mb_strlen', 'strlen' ), array( $str ) );

		if ( $length == 0 ) {
			return '';
		} elseif ( $length <= 10 ) {
			$sub = self::mb_function( array( 'mb_substr', 'substr' ), array( $str, 0, $length ) );

			return $sub . ( ( $length < $original_len ) ? $continue : '' );
		}

		$sub = '';
		$len = 0;

		$words = self::mb_function( array( 'mb_split', 'explode' ), array( ' ', $str ) );

		foreach ( $words as $word ) {
			$part      = ( ( $sub != '' ) ? ' ' : '' ) . $word;
			$total_len = self::mb_function( array( 'mb_strlen', 'strlen' ), array( $sub . $part ) );
			if ( $total_len > $length && substr_count( $sub, ' ' ) ) {
				break;
			}

			$sub .= $part;
			$len += self::mb_function( array( 'mb_strlen', 'strlen' ), array( $part ) );

			if ( substr_count( $sub, ' ' ) > $minword && $total_len >= $length ) {
				break;
			}

			unset( $total_len, $word );
		}

		return $sub . ( ( $len < $original_len ) ? $continue : '' );
	}

	public static function mb_function( $function_names, $args ) {
		$mb_function_name = $function_names[0];
		$function_name    = $function_names[1];
		if ( function_exists( $mb_function_name ) ) {
			$function_name = $mb_function_name;
		}

		return call_user_func_array( $function_name, $args );
	}

	public static function get_formatted_time( $date, $date_format = '', $time_format = '' ) {
		if ( empty( $date ) ) {
			return $date;
		}

		if ( empty( $date_format ) ) {
			$date_format = get_option( 'date_format' );
		}

		if ( preg_match( '/^\d{1-2}\/\d{1-2}\/\d{4}$/', $date ) && self::pro_is_installed() ) {
			$frmpro_settings = new FrmProSettings();
			$date            = FrmProAppHelper::convert_date( $date, $frmpro_settings->date_format, 'Y-m-d' );
		}

		$formatted = self::get_localized_date( $date_format, $date );

		$do_time = ( gmdate( 'H:i:s', strtotime( $date ) ) != '00:00:00' );
		if ( $do_time ) {
			$formatted .= self::add_time_to_date( $time_format, $date );
		}

		return $formatted;
	}

	private static function add_time_to_date( $time_format, $date ) {
		if ( empty( $time_format ) ) {
			$time_format = get_option( 'time_format' );
		}

		$trimmed_format = trim( $time_format );
		$time           = '';
		if ( $time_format && ! empty( $trimmed_format ) ) {
			$time = ' ' . __( 'at', 'formidable' ) . ' ' . self::get_localized_date( $time_format, $date );
		}

		return $time;
	}

	/**
	 * @since 2.0.8
	 */
	public static function get_localized_date( $date_format, $date ) {
		$date = get_date_from_gmt( $date );

		return date_i18n( $date_format, strtotime( $date ) );
	}

	/**
	 * Gets the time ago in words
	 *
	 * @param int $from in seconds
	 * @param int|string $to in seconds
	 *
	 * @return string $time_ago
	 */
	public static function human_time_diff( $from, $to = '', $levels = 1 ) {
		if ( empty( $to ) ) {
			$now = new DateTime();
		} else {
			$now = new DateTime( '@' . $to );
		}
		$ago = new DateTime( '@' . $from );

		// Get the time difference
		$diff_object = $now->diff( $ago );
		$diff        = get_object_vars( $diff_object );

		// Add week amount and update day amount
		$diff['w'] = floor( $diff['d'] / 7 );
		$diff['d'] -= $diff['w'] * 7;

		$time_strings = self::get_time_strings();

		if ( ! is_numeric( $levels ) ) {
			// Show time in specified unit.
			$levels = self::get_unit( $levels );
			if ( isset( $time_strings[ $levels ] ) ) {
				$diff = array(
					$levels => self::time_format( $levels, $diff ),
				);
				$time_strings = array(
					$levels => $time_strings[ $levels ],
				);
			}
			$levels = 1;
		}

		foreach ( $time_strings as $k => $v ) {
			if ( isset( $diff[ $k ] ) && $diff[ $k ] ) {
				$time_strings[ $k ] = $diff[ $k ] . ' ' . ( $diff[ $k ] > 1 ? $v[1] : $v[0] );
			} elseif ( isset( $diff[ $k ] ) && count( $time_strings ) === 1 ) {
				// Account for 0.
				$time_strings[ $k ] = $diff[ $k ] . ' ' . $v[1];
			} else {
				unset( $time_strings[ $k ] );
			}
		}

		$levels_deep     = apply_filters( 'frm_time_ago_levels', $levels, compact( 'time_strings', 'from', 'to' ) );
		$time_strings    = array_slice( $time_strings, 0, absint( $levels_deep ) );
		$time_ago_string = implode( ' ', $time_strings );

		return $time_ago_string;
	}

	/**
	 * @since 4.05.01
	 */
	private static function time_format( $unit, $diff ) {
		$return = array(
			'y' => 'y',
			'd' => 'days',
		);
		if ( isset( $return[ $unit ] ) ) {
			return $diff[ $return[ $unit ] ];
		}

		$total = $diff['days'] * self::convert_time( 'd', $unit );

		$times = array( 'h', 'i', 's' );

		foreach ( $times as $time ) {
			if ( ! isset( $diff[ $time ] ) ) {
				continue;
			}

			$total += $diff[ $time ] * self::convert_time( $time, $unit );
		}

		return floor( $total );
	}

	/**
	 * @since 4.05.01
	 */
	private static function convert_time( $from, $to ) {
		$convert = array(
			's' => 1,
			'i' => MINUTE_IN_SECONDS,
			'h' => HOUR_IN_SECONDS,
			'd' => DAY_IN_SECONDS,
			'w' => WEEK_IN_SECONDS,
			'm' => DAY_IN_SECONDS * 30.42,
			'y' => DAY_IN_SECONDS * 365.25,
		);

		return $convert[ $from ] / $convert[ $to ];
	}

	/**
	 * @since 4.05.01
	 */
	private static function get_unit( $unit ) {
		$units = self::get_time_strings();
		if ( isset( $units[ $unit ] ) || is_numeric( $unit ) ) {
			return $unit;
		}

		foreach ( $units as $u => $strings ) {
			if ( in_array( $unit, $strings ) ) {
				return $u;
			}
		}
		return 1;
	}

	/**
	 * Get the translatable time strings. The untranslated version is a failsafe
	 * in case langauges are changing for the unit set in the shortcode.
	 *
	 * @since 2.0.20
	 * @return array
	 */
	private static function get_time_strings() {
		return array(
			'y' => array(
				__( 'year', 'formidable' ),
				__( 'years', 'formidable' ),
				'year',
			),
			'm' => array(
				__( 'month', 'formidable' ),
				__( 'months', 'formidable' ),
				'month',
			),
			'w' => array(
				__( 'week', 'formidable' ),
				__( 'weeks', 'formidable' ),
				'week',
			),
			'd' => array(
				__( 'day', 'formidable' ),
				__( 'days', 'formidable' ),
				'day',
			),
			'h' => array(
				__( 'hour', 'formidable' ),
				__( 'hours', 'formidable' ),
				'hour',
			),
			'i' => array(
				__( 'minute', 'formidable' ),
				__( 'minutes', 'formidable' ),
				'minute',
			),
			's' => array(
				__( 'second', 'formidable' ),
				__( 'seconds', 'formidable' ),
				'second',
			),
		);
	}

	// Pagination Methods.

	/**
	 * @param integer $current_p
	 */
	public static function get_last_record_num( $r_count, $current_p, $p_size ) {
		return ( ( $r_count < ( $current_p * $p_size ) ) ? $r_count : ( $current_p * $p_size ) );
	}

	/**
	 * @param integer $current_p
	 */
	public static function get_first_record_num( $r_count, $current_p, $p_size ) {
		if ( $current_p == 1 ) {
			return 1;
		} else {
			return ( self::get_last_record_num( $r_count, ( $current_p - 1 ), $p_size ) + 1 );
		}
	}

	/**
	 * @return array
	 */
	public static function json_to_array( $json_vars ) {
		$vars = array();
		foreach ( $json_vars as $jv ) {
			$jv_name = explode( '[', $jv['name'] );
			$last    = count( $jv_name ) - 1;
			foreach ( $jv_name as $p => $n ) {
				$name = trim( $n, ']' );
				if ( ! isset( $l1 ) ) {
					$l1 = $name;
				}

				if ( ! isset( $l2 ) ) {
					$l2 = $name;
				}

				if ( ! isset( $l3 ) ) {
					$l3 = $name;
				}

				$this_val = ( $p == $last ) ? $jv['value'] : array();

				switch ( $p ) {
					case 0:
						$l1 = $name;
						self::add_value_to_array( $name, $l1, $this_val, $vars );
						break;

					case 1:
						$l2 = $name;
						self::add_value_to_array( $name, $l2, $this_val, $vars[ $l1 ] );
						break;

					case 2:
						$l3 = $name;
						self::add_value_to_array( $name, $l3, $this_val, $vars[ $l1 ][ $l2 ] );
						break;

					case 3:
						$l4 = $name;
						self::add_value_to_array( $name, $l4, $this_val, $vars[ $l1 ][ $l2 ][ $l3 ] );
				}

				unset( $this_val, $n );
			}

			unset( $last, $jv );
		}

		return $vars;
	}

	/**
	 * @param string $name
	 * @param string $l1
	 */
	public static function add_value_to_array( $name, $l1, $val, &$vars ) {
		if ( $name == '' ) {
			$vars[] = $val;
		} elseif ( ! isset( $vars[ $l1 ] ) ) {
			$vars[ $l1 ] = $val;
		}
	}

	public static function maybe_add_tooltip( $name, $class = 'closed', $form_name = '' ) {
		$tooltips = array(
			'action_title'  => __( 'Give this action a label for easy reference.', 'formidable' ),
			'email_to'      => __( 'Add one or more recipient addresses separated by a ",".  FORMAT: Name <name@email.com> or name@email.com.  [admin_email] is the address set in WP General Settings.', 'formidable' ),
			'cc'            => __( 'Add CC addresses separated by a ",".  FORMAT: Name <name@email.com> or name@email.com.', 'formidable' ),
			'bcc'           => __( 'Add BCC addresses separated by a ",".  FORMAT: Name <name@email.com> or name@email.com.', 'formidable' ),
			'reply_to'      => __( 'If you would like a different reply to address than the "from" address, add a single address here.  FORMAT: Name <name@email.com> or name@email.com.', 'formidable' ),
			'from'          => __( 'Enter the name and/or email address of the sender. FORMAT: John Bates <john@example.com> or john@example.com.', 'formidable' ),
			/* translators: %1$s: Form name, %2$s: Date */
			'email_subject' => esc_attr( sprintf( __( 'If you leave the subject blank, the default will be used: %1$s Form submitted on %2$s', 'formidable' ), $form_name, self::site_name() ) ),
		);

		if ( ! isset( $tooltips[ $name ] ) ) {
			return;
		}

		if ( 'open' == $class ) {
			echo ' frm_help"';
		} else {
			echo ' class="frm_help"';
		}

		echo ' title="' . esc_attr( $tooltips[ $name ] );

		if ( 'open' != $class ) {
			echo '"';
		}
	}

	/**
	 * Add the current_page class to that page in the form nav
	 */
	public static function select_current_page( $page, $current_page, $action = array() ) {
		if ( $current_page != $page ) {
			return;
		}

		$frm_action = self::simple_get( 'frm_action', 'sanitize_title' );
		if ( 'lite-reports' === $frm_action ) {
			$frm_action = 'reports';
		}

		if ( empty( $action ) || ( ! empty( $frm_action ) && in_array( $frm_action, $action ) ) ) {
			echo ' class="current_page"';
		}
	}

	/**
	 * Prepare and json_encode post content
	 *
	 * @since 2.0
	 *
	 * @param array $post_content
	 *
	 * @return string $post_content ( json encoded array )
	 */
	public static function prepare_and_encode( $post_content ) {
		// Loop through array to strip slashes and add only the needed ones.
		foreach ( $post_content as $key => $val ) {
			// Replace problematic characters (like &quot;)
			if ( is_string( $val ) ) {
				$val = str_replace( '&quot;', '"', $val );
			}

			self::prepare_action_slashes( $val, $key, $post_content );
			unset( $key, $val );
		}

		// json_encode the array.
		$post_content = json_encode( $post_content );

		// Add extra slashes for \r\n since WP strips them.
		$post_content = str_replace( array( '\\r', '\\n', '\\u', '\\t' ), array( '\\\\r', '\\\\n', '\\\\u', '\\\\t' ), $post_content );

		// allow for &quot
		$post_content = str_replace( '&quot;', '\\"', $post_content );

		return $post_content;
	}

	private static function prepare_action_slashes( $val, $key, &$post_content ) {
		if ( ! isset( $post_content[ $key ] ) || is_numeric( $val ) ) {
			return;
		}

		if ( is_array( $val ) ) {
			foreach ( $val as $k1 => $v1 ) {
				self::prepare_action_slashes( $v1, $k1, $post_content[ $key ] );
				unset( $k1, $v1 );
			}
		} else {
			// Strip all slashes so everything is the same, no matter where the value is coming from
			$val = stripslashes( $val );

			// Add backslashes before double quotes and forward slashes only
			$post_content[ $key ] = addcslashes( $val, '"\\/' );
		}
	}

	/**
	 * Check for either json or serilized data. This is temporary while transitioning
	 * all data to json.
	 *
	 * @since 4.02.03
	 */
	public static function unserialize_or_decode( &$value ) {
		if ( is_array( $value ) ) {
			return;
		}

		if ( is_serialized( $value ) ) {
			$value = maybe_unserialize( $value );
		} else {
			$value = self::maybe_json_decode( $value, false );
		}
	}

	/**
	 * Decode a JSON string.
	 * Do not switch shortcodes like [24] to array unless intentional ie XML values.
	 *
	 * @param mixed $string
	 * @param bool  $single_to_array
	 * @return mixed
	 */
	public static function maybe_json_decode( $string, $single_to_array = true ) {
		if ( is_array( $string ) || is_null( $string ) ) {
			return $string;
		}

		$new_string = json_decode( $string, true );
		if ( function_exists( 'json_last_error' ) ) {
			// php 5.3+
			$single_value = false;
			if ( ! $single_to_array ) {
				$single_value = is_array( $new_string ) && count( $new_string ) === 1 && isset( $new_string[0] );
			}
			if ( json_last_error() == JSON_ERROR_NONE && is_array( $new_string ) && ! $single_value ) {
				$string = $new_string;
			}
		}

		return $string;
	}

	/**
	 * Reformat the json serialized array in name => value array.
	 *
	 * @since 4.02.03
	 */
	public static function format_form_data( &$form ) {
		$formatted = array();

		foreach ( $form as $input ) {
			if ( ! isset( $input['name'] ) ) {
				continue;
			}
			$key = $input['name'];
			if ( isset( $formatted[ $key ] ) ) {
				if ( is_array( $formatted[ $key ] ) ) {
					$formatted[ $key ][] = $input['value'];
				} else {
					$formatted[ $key ] = array( $formatted[ $key ], $input['value'] );
				}
			} else {
				$formatted[ $key ] = $input['value'];
			}
		}

		parse_str( http_build_query( $formatted ), $form );
	}

	/**
	 * @since 4.02.03
	 */
	public static function maybe_json_encode( $value ) {
		if ( is_array( $value ) ) {
			$value = wp_json_encode( $value );
		}
		return $value;
	}

	/**
	 * @since 1.07.10
	 *
	 * @param string $post_type The name of the post type that may need to be highlighted
	 * echo The javascript to open and highlight the Formidable menu
	 */
	public static function maybe_highlight_menu( $post_type ) {
		global $post;

		if ( isset( $_REQUEST['post_type'] ) && $_REQUEST['post_type'] != $post_type ) {
			return;
		}

		if ( is_object( $post ) && $post->post_type != $post_type ) {
			return;
		}

		self::load_admin_wide_js();
		echo '<script type="text/javascript">jQuery(document).ready(function(){frmSelectSubnav();});</script>';
	}

	/**
	 * Load the JS file on non-Formidable pages in the admin area
	 *
	 * @since 2.0
	 */
	public static function load_admin_wide_js( $load = true ) {
		$version = self::plugin_version();
		wp_register_script( 'formidable_admin_global', self::plugin_url() . '/js/formidable_admin_global.js', array( 'jquery' ), $version );

		$global_strings = array(
			'updating_msg'                  => __( 'Please wait while your site updates.', 'formidable' ),
			'deauthorize'                   => __( 'Are you sure you want to deauthorize Formidable Forms on this site?', 'formidable' ),
			'url'                           => self::plugin_url(),
			'app_url'                       => 'https://formidableforms.com/',
			'applicationsUrl'               => admin_url( 'admin.php?page=formidable-applications' ),
			'canAccessApplicationDashboard' => current_user_can( is_callable( 'FrmProApplicationsHelper::get_required_templates_capability' ) ? FrmProApplicationsHelper::get_required_templates_capability() : 'frm_edit_forms' ),
			'loading'                       => __( 'Loading&hellip;', 'formidable' ),
			'nonce'                         => wp_create_nonce( 'frm_ajax' ),
		);
		wp_localize_script( 'formidable_admin_global', 'frmGlobal', $global_strings );

		if ( $load ) {
			wp_enqueue_script( 'formidable_admin_global' );
		}
	}

	/**
	 * @since 2.0.9
	 */
	public static function load_font_style() {
		wp_enqueue_style( 'frm_fonts', self::plugin_url() . '/css/frm_fonts.css', array(), self::plugin_version() );
	}

	/**
	 * @param string $location
	 */
	public static function localize_script( $location ) {
		global $wp_scripts;

		$ajax_url = admin_url( 'admin-ajax.php', is_ssl() ? 'admin' : 'http' );
		$ajax_url = apply_filters( 'frm_ajax_url', $ajax_url );

		$script_strings = array(
			'ajax_url'           => $ajax_url,
			'images_url'         => self::plugin_url() . '/images',
			'loading'            => __( 'Loading&hellip;', 'formidable' ),
			'remove'             => __( 'Remove', 'formidable' ),
			'offset'             => apply_filters( 'frm_scroll_offset', 4 ),
			'nonce'              => wp_create_nonce( 'frm_ajax' ),
			'id'                 => __( 'ID', 'formidable' ),
			'no_results'         => __( 'No results match', 'formidable' ),
			'file_spam'          => __( 'That file looks like Spam.', 'formidable' ),
			'calc_error'         => __( 'There is an error in the calculation in the field with key', 'formidable' ),
			'empty_fields'       => __( 'Please complete the preceding required fields before uploading a file.', 'formidable' ),
			'focus_first_error'  => self::should_focus_first_error(),
			'include_alert_role' => self::should_include_alert_role_on_field_errors(),
		);

		$data = $wp_scripts->get_data( 'formidable', 'data' );
		if ( empty( $data ) ) {
			wp_localize_script( 'formidable', 'frm_js', $script_strings );
		}

		if ( $location == 'admin' ) {
			$frm_settings         = self::get_settings();
			$admin_script_strings = array(
				'desc'              => __( '(Click to add description)', 'formidable' ),
				'blank'             => __( '(Blank)', 'formidable' ),
				'no_label'          => __( '(no label)', 'formidable' ),
				'saving'            => esc_attr( __( 'Saving', 'formidable' ) ),
				'saved'             => esc_attr( __( 'Saved', 'formidable' ) ),
				'ok'                => __( 'OK', 'formidable' ),
				'cancel'            => __( 'Cancel', 'formidable' ),
				'default_label'     => __( 'Default', 'formidable' ),
				'clear_default'     => __( 'Clear default value when typing', 'formidable' ),
				'no_clear_default'  => __( 'Do not clear default value when typing', 'formidable' ),
				'valid_default'     => __( 'Default value will pass form validation', 'formidable' ),
				'no_valid_default'  => __( 'Default value will NOT pass form validation', 'formidable' ),
				'caution'           => __( 'Heads up', 'formidable' ),
				'confirm'           => __( 'Are you sure?', 'formidable' ),
				'conf_delete'       => __( 'Are you sure you want to delete this field and all data associated with it?', 'formidable' ),
				'conf_delete_sec'   => __( 'All fields inside this Section will be deleted along with their data. Are you sure you want to delete this group of fields?', 'formidable' ),
				'conf_no_repeat'    => __( 'Warning: If you have entries with multiple rows, all but the first row will be lost.', 'formidable' ),
				'default_unique'    => $frm_settings->unique_msg,
				'default_conf'      => __( 'The entered values do not match', 'formidable' ),
				'enter_email'       => __( 'Enter Email', 'formidable' ),
				'confirm_email'     => __( 'Confirm Email', 'formidable' ),
				'conditional_text'  => __( 'Conditional content here', 'formidable' ),
				'new_option'        => __( 'New Option', 'formidable' ),
				'css_invalid_size'  => __( 'In certain browsers (e.g. Firefox) text will not display correctly if the field height is too small relative to the field padding and text size. Please increase your field height or decrease your field padding.', 'formidable' ),
				'enter_password'    => __( 'Enter Password', 'formidable' ),
				'confirm_password'  => __( 'Confirm Password', 'formidable' ),
				'import_complete'   => __( 'Import Complete', 'formidable' ),
				'updating'          => __( 'Please wait while your site updates.', 'formidable' ),
				'no_save_warning'   => __( 'Warning: There is no way to retrieve unsaved entries.', 'formidable' ),
				'private_label'     => __( 'Private', 'formidable' ),
				'jquery_ui_url'     => '',
				'pro_url'           => is_callable( 'FrmProAppHelper::plugin_url' ) ? FrmProAppHelper::plugin_url() : '',
				'no_licenses'       => __( 'No new licenses were found', 'formidable' ),
				'unmatched_parens'  => __( 'This calculation has at least one unmatched ( ) { } [ ].', 'formidable' ),
				'view_shortcodes'   => __( 'This calculation may have shortcodes that work in Views but not forms.', 'formidable' ),
				'text_shortcodes'   => __( 'This calculation may have shortcodes that work in text calculations but not numeric calculations.', 'formidable' ),
				'only_one_action'   => __( 'This form action is limited to one per form. Please edit the existing form action.', 'formidable' ),
				'unsafe_params'     => FrmFormsHelper::reserved_words(),
				/* Translators: %s is the name of a Detail Page Slug that is a reserved word.*/
				'slug_is_reserved' => sprintf( __( 'The Detail Page Slug "%s" is reserved by WordPress. This may cause problems. Is this intentional?', 'formidable' ), '****' ),
				/* Translators: %s is the name of a parameter that is a reserved word.  More than one word could be listed here, though that would not be common. */
				'param_is_reserved' => sprintf( __( 'The parameter "%s" is reserved by WordPress. This may cause problems when included in the URL. Is this intentional? ', 'formidable' ), '****' ),
				'reserved_words'    => __( 'See the list of reserved words in WordPress.', 'formidable' ),
				'repeat_limit_min'  => __( 'Please enter a Repeat Limit that is greater than 1.', 'formidable' ),
				'checkbox_limit'    => __( 'Please select a limit between 0 and 200.', 'formidable' ),
				'install'           => __( 'Install', 'formidable' ),
				'active'            => __( 'Active', 'formidable' ),
				'select_a_field'    => __( 'Select a Field', 'formidable' ),
				'no_items_found'    => __( 'No items found.', 'formidable' ),
			);
			$admin_script_strings = apply_filters( 'frm_admin_script_strings', $admin_script_strings );

			$data = $wp_scripts->get_data( 'formidable_admin', 'data' );
			if ( empty( $data ) ) {
				wp_localize_script( 'formidable_admin', 'frm_admin_js', $admin_script_strings );
			}
		}
	}

	/**
	 * Returns whether or not the first errored input should be auto-focused (default true).
	 *
	 * @since 5.2.05
	 *
	 * @return bool
	 */
	private static function should_focus_first_error() {
		return (bool) apply_filters( 'frm_focus_first_error', true );
	}

	/**
	 * Returns whether or not field errors should include role="alert" (default true).
	 *
	 * @since 5.2.05
	 *
	 * @return bool
	 */
	public static function should_include_alert_role_on_field_errors() {
		return (bool) apply_filters( 'frm_include_alert_role_on_field_errors', true );
	}

	/**
	 * Echo the message on the plugins listing page
	 *
	 * @since 1.07.10
	 *
	 * @param float $min_version The version the add-on requires
	 */
	public static function min_version_notice( $min_version ) {
		$frm_version = self::plugin_version();

		// Check if Formidable meets minimum requirements.
		if ( version_compare( $frm_version, $min_version, '>=' ) ) {
			return;
		}

		$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
		echo '<tr class="plugin-update-tr active"><th colspan="' . absint( $wp_list_table->get_column_count() ) . '" class="check-column plugin-update colspanchange"><div class="update-message">' .
			esc_html__( 'You are running an outdated version of Formidable. This plugin may not work correctly if you do not update Formidable.', 'formidable' ) .
			'</div></td></tr>';
	}

	/**
	 * If Pro is far outdated, show a message.
	 *
	 * @since 4.0.01
	 *
	 * @return void
	 */
	public static function min_pro_version_notice( $min_version ) {
		if ( ! self::is_formidable_admin() ) {
			// Don't show admin-wide.
			return;
		}

		self::php_version_notice();

		$is_pro = self::pro_is_installed() && class_exists( 'FrmProDb' );
		if ( ! $is_pro || self::meets_min_pro_version( $min_version ) ) {
			return;
		}

		$pro_version = FrmProDb::$plug_version;
		$expired = FrmAddonsController::is_license_expired();
		?>
		<div class="error frm_previous_install">
			<?php
			esc_html_e( 'You are running a version of Formidable Forms that may not be compatible with your version of Formidable Forms Pro.', 'formidable' );
			if ( empty( $expired ) ) {
				echo ' Please <a href="' . esc_url( admin_url( 'plugins.php?s=formidable%20forms%20pro' ) ) . '">update now</a>.';
			} else {
				echo '<br/>Please <a href="https://formidableforms.com/account/downloads/?utm_source=WordPress&utm_medium=outdated">renew now</a> to get the latest version.';
			}
			?>
		</div>
		<?php
	}

	/**
	 * If Pro is installed, check the version number.
	 *
	 * @since 4.0.01
	 */
	public static function meets_min_pro_version( $min_version ) {
		return ! class_exists( 'FrmProDb' ) || version_compare( FrmProDb::$plug_version, $min_version, '>=' );
	}

	/**
	 * Show a message if the browser or PHP version is below the recommendations.
	 *
	 * @since 4.0.02
	 */
	private static function php_version_notice() {
		$message = array();
		if ( version_compare( phpversion(), '5.6', '<' ) ) {
			$message[] = __( 'The version of PHP on your server is too low. If this is not corrected, you may see issues with Formidable Forms. Please contact your web host and ask to be updated to PHP 7.0+.', 'formidable' );
		}

		$browser = self::get_server_value( 'HTTP_USER_AGENT' );
		$is_ie   = strpos( $browser, 'MSIE' ) !== false;
		if ( $is_ie ) {
			$message[] = __( 'You are using an outdated browser that is not compatible with Formidable Forms. Please update to a more current browser (we recommend Chrome).', 'formidable' );
		}

		foreach ( $message as $m ) {
			?>
			<div class="error frm_previous_install">
				<?php echo esc_html( $m ); ?>
			</div>
			<?php
		}
	}

	public static function locales( $type = 'date' ) {
		$locales = array(
			'en'     => __( 'English', 'formidable' ),
			'af'     => __( 'Afrikaans', 'formidable' ),
			'sq'     => __( 'Albanian', 'formidable' ),
			'ar'     => __( 'Arabic', 'formidable' ),
			'hy'     => __( 'Armenian', 'formidable' ),
			'az'     => __( 'Azerbaijani', 'formidable' ),
			'eu'     => __( 'Basque', 'formidable' ),
			'bs'     => __( 'Bosnian', 'formidable' ),
			'bg'     => __( 'Bulgarian', 'formidable' ),
			'ca'     => __( 'Catalan', 'formidable' ),
			'zh-HK'  => __( 'Chinese Hong Kong', 'formidable' ),
			'zh-CN'  => __( 'Chinese Simplified', 'formidable' ),
			'zh-TW'  => __( 'Chinese Traditional', 'formidable' ),
			'hr'     => __( 'Croatian', 'formidable' ),
			'cs'     => __( 'Czech', 'formidable' ),
			'da'     => __( 'Danish', 'formidable' ),
			'nl'     => __( 'Dutch', 'formidable' ),
			'en-GB'  => __( 'English/UK', 'formidable' ),
			'eo'     => __( 'Esperanto', 'formidable' ),
			'et'     => __( 'Estonian', 'formidable' ),
			'fo'     => __( 'Faroese', 'formidable' ),
			'fa'     => __( 'Farsi/Persian', 'formidable' ),
			'fil'    => __( 'Filipino', 'formidable' ),
			'fi'     => __( 'Finnish', 'formidable' ),
			'fr'     => __( 'French', 'formidable' ),
			'fr-CA'  => __( 'French/Canadian', 'formidable' ),
			'fr-CH'  => __( 'French/Swiss', 'formidable' ),
			'de'     => __( 'German', 'formidable' ),
			'de-AT'  => __( 'German/Austria', 'formidable' ),
			'de-CH'  => __( 'German/Switzerland', 'formidable' ),
			'el'     => __( 'Greek', 'formidable' ),
			'he'     => __( 'Hebrew', 'formidable' ),
			'iw'     => __( 'Hebrew', 'formidable' ),
			'hi'     => __( 'Hindi', 'formidable' ),
			'hu'     => __( 'Hungarian', 'formidable' ),
			'is'     => __( 'Icelandic', 'formidable' ),
			'id'     => __( 'Indonesian', 'formidable' ),
			'it'     => __( 'Italian', 'formidable' ),
			'ja'     => __( 'Japanese', 'formidable' ),
			'ko'     => __( 'Korean', 'formidable' ),
			'lv'     => __( 'Latvian', 'formidable' ),
			'lt'     => __( 'Lithuanian', 'formidable' ),
			'ms'     => __( 'Malaysian', 'formidable' ),
			'no'     => __( 'Norwegian', 'formidable' ),
			'pl'     => __( 'Polish', 'formidable' ),
			'pt'     => __( 'Portuguese', 'formidable' ),
			'pt-BR'  => __( 'Portuguese/Brazilian', 'formidable' ),
			'pt-PT'  => __( 'Portuguese/Portugal', 'formidable' ),
			'ro'     => __( 'Romanian', 'formidable' ),
			'ru'     => __( 'Russian', 'formidable' ),
			'sr'     => __( 'Serbian', 'formidable' ),
			'sr-SR'  => __( 'Serbian', 'formidable' ),
			'sk'     => __( 'Slovak', 'formidable' ),
			'sl'     => __( 'Slovenian', 'formidable' ),
			'es'     => __( 'Spanish', 'formidable' ),
			'es-419' => __( 'Spanish/Latin America', 'formidable' ),
			'sv'     => __( 'Swedish', 'formidable' ),
			'ta'     => __( 'Tamil', 'formidable' ),
			'th'     => __( 'Thai', 'formidable' ),
			'tr'     => __( 'Turkish', 'formidable' ),
			'uk'     => __( 'Ukrainian', 'formidable' ),
			'vi'     => __( 'Vietnamese', 'formidable' ),
		);

		if ( $type === 'captcha' ) {
			// remove the languages unavailable for the captcha
			$unset = array( 'af', 'sq', 'hy', 'az', 'eu', 'bs', 'zh-HK', 'eo', 'et', 'fo', 'fr-CH', 'he', 'is', 'ms', 'sr-SR', 'ta' );
		} else {
			// remove the languages unavailable for the datepicker
			$unset = array( 'fil', 'fr-CA', 'de-AT', 'de-CH', 'iw', 'hi', 'pt', 'pt-PT', 'es-419' );
		}

		$locales = array_diff_key( $locales, array_flip( $unset ) );
		$locales = apply_filters( 'frm_locales', $locales );

		return $locales;
	}

	/**
	 * Output HTML containing reference text for accessibility
	 *
	 * @deprecated 5.1
	 */
	public static function multiselect_accessibility() {
		_deprecated_function( __METHOD__, '5.1' );
	}

	public static function get_menu_icon_class() {
		if ( is_callable( 'FrmProAppHelper::get_settings' ) ) {
			$settings = FrmProAppHelper::get_settings();
			if ( is_object( $settings ) && ! empty( $settings->menu_icon ) ) {
				return $settings->menu_icon;
			}
		}
		return 'frmfont frm_logo_icon';
	}

	/**
	 * Shows the images dropdown.
	 *
	 * @since 5.0.04
	 *
	 * @param array $args {
	 *     Arguments.
	 *
	 *     @type string  $selected    Selected value.
	 *     @type array[] $options     Array of options with keys are option values and values are array.
	 *                                The option array contains `text`, `svg` and `custom_atts`.
	 *     @type string  $classes     Custom CSS classes for the wrapper element.
	 *     @type array   $input_attrs Attributes of value input.
	 * }
	 */
	public static function images_dropdown( $args ) {
		$args = self::fill_default_images_dropdown_args( $args );

		$input_attrs_str = self::get_images_dropdown_input_attrs( $args );
		ob_start();
		include self::plugin_path() . '/classes/views/shared/images-dropdown.php';
		$output = ob_get_clean();

		/**
		 * Allows modifying the output of FrmAppHelper::images_dropdown() method.
		 *
		 * @since 5.0.04
		 *
		 * @param string $output The output.
		 * @param array  $args   Passed arguments.
		 */
		echo apply_filters( 'frm_images_dropdown_output', $output, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Fills the default images_dropdown() arguments.
	 *
	 * @since 5.0.04
	 *
	 * @param array $args The arguments.
	 * @return array
	 */
	private static function fill_default_images_dropdown_args( $args ) {
		$defaults = array(
			'selected'    => '',
			'options'     => array(),
			'classes'     => '',
			'input_attrs' => array(),
		);
		$new_args = wp_parse_args( $args, $defaults );

		$new_args['options']     = (array) $new_args['options'];
		$new_args['input_attrs'] = (array) $new_args['input_attrs'];

		// Set the number of columns.
		$new_args['col_class'] = ceil( 12 / count( $new_args['options'] ) );
		if ( $new_args['col_class'] > 6 ) {
			$new_args['col_class'] = ceil( $new_args['col_class'] / 2 );
		}

		/**
		 * Allows modifying the arguments of images_dropdown() method.
		 *
		 * @since 5.0.04
		 *
		 * @param array $new_args Arguments after filling the defaults.
		 * @param array $args     Arguments passed to the method, before filling the defaults.
		 */
		return apply_filters( 'frm_images_dropdown_args', $new_args, $args );
	}

	/**
	 * Gets HTML attributes of the input in images_dropdown() method.
	 *
	 * @since 5.0.04
	 *
	 * @param array $args The arguments.
	 * @return string
	 */
	private static function get_images_dropdown_input_attrs( $args ) {
		$input_attrs         = $args['input_attrs'];
		$input_attrs['type'] = 'radio';
		$input_attrs['name'] = $args['name'];

		$input_attrs_str = '';
		foreach ( $input_attrs as $key => $input_attr ) {
			$input_attrs_str .= ' ' . sprintf( '%s="%s"', esc_attr( $key ), esc_attr( $input_attr ) );
		}

		/**
		 * Allows modifying the HTML attributes of the input in images_dropdown() method.
		 *
		 * @since 5.0.04
		 *
		 * @param string $input_attrs_str HTML attributes string.
		 * @param array  $args            The arguments of images_dropdown() method.
		 */
		return apply_filters( 'frm_images_dropdown_input_attrs', $input_attrs_str, $args );
	}

	/**
	 * Gets the image of each option in images_dropdown() method.
	 *
	 * @since 5.0.04
	 *
	 * @param array $option Option data.
	 * @param array $args   The arguments of images_dropdown() method.
	 * @return string
	 */
	private static function get_images_dropdown_option_image( $option, $args ) {
		$image = self::icon_by_class(
			'frmfont ' . $option['svg'],
			array(
				'echo'   => false,
			)
		);

		$args['option'] = $option;

		/**
		 * Allows modifying the image of each option in images_dropdown() method.
		 *
		 * @since 5.0.04
		 *
		 * @param string $image The image HTML.
		 * @param array  $args  The arguments of images_dropdown() method, with `option` array is added.
		 */
		return apply_filters( 'frm_images_dropdown_option_image', $image, $args );
	}

	/**
	 * Gets the HTML classes of each option in images_dropdown() method.
	 *
	 * @since 5.0.04
	 *
	 * @param array $option Option data.
	 * @param array $args   The arguments of images_dropdown() method.
	 * @return string
	 */
	private static function get_images_dropdown_option_classes( $option, $args ) {
		$classes = '';

		if ( ! empty( $option['custom_attrs']['class'] ) ) {
			$classes .= ' ' . $option['custom_attrs']['class'];
		}

		$args['option'] = $option;

		/**
		 * Allows modifying the CSS classes of each option in images_dropdown() method.
		 *
		 * @since 5.0.04
		 *
		 * @param string $classes CSS classes.
		 * @param array  $args    The arguments of images_dropdown() method, with `option` array is added.
		 */
		return apply_filters( 'frm_images_dropdown_option_classes', $classes, $args );
	}

	/**
	 * Gets the custom HTML attributes of each option in images_dropdown() method.
	 *
	 * @since 5.0.04
	 *
	 * @param array $option Option data.
	 * @param array $args   The arguments of images_dropdown() method.
	 * @return string
	 */
	private static function get_images_dropdown_option_html_attrs( $option, $args ) {
		$html_attrs = '';
		if ( ! empty( $option['custom_attrs'] ) && is_array( $option['custom_attrs'] ) ) {
			$html_attrs_arr = array();

			foreach ( $option['custom_attrs'] as $key => $value ) {
				if ( in_array( $key, array( 'type', 'class', 'data-value' ) ) ) {
					continue;
				}

				$html_attrs_arr[] = sprintf( '%s="%s"', esc_attr( $key ), esc_attr( $value ) );
			}

			$html_attrs = implode( ' ', $html_attrs_arr );
		}

		$args['option'] = $option;

		/**
		 * Allows modifying the custom HTML attributes of each option in images_dropdown() method.
		 *
		 * @since 5.0.04
		 *
		 * @param string $html_attrs The HTML attributes string.
		 * @param array  $args       The arguments of images_dropdown() method, with `option` array is added.
		 */
		return apply_filters( 'frm_images_dropdown_option_html_attrs', $html_attrs, $args );
	}

	/**
	 * @since 5.0.07
	 *
	 * @return bool true if the current user is allowed to save unfiltered HTML.
	 */
	public static function allow_unfiltered_html() {
		if ( self::should_never_allow_unfiltered_html() ) {
			return false;
		}
		return current_user_can( 'unfiltered_html' );
	}

	/**
	 * @since 5.0.13
	 *
	 * @return bool
	 */
	public static function should_never_allow_unfiltered_html() {
		if ( defined( 'DISALLOW_UNFILTERED_HTML' ) && DISALLOW_UNFILTERED_HTML ) {
			return true;
		}

		/**
		 * Formidable will check DISALLOW_UNFILTERED_HTML to determine if some form HTML should be filtered or not.
		 * In many cases, scripts are added intentionally to forms and will not be stripped if DISALLOW_UNFILTERED_HTML is not set.
		 * It is also possible to filter Formidable without defining DISALLOW_UNFILTERED_HTML, with add_filter( 'frm_disallow_unfiltered_html', '__return_true' );
		 *
		 * @since 5.0.13
		 */
		return apply_filters( 'frm_disallow_unfiltered_html', false );
	}

	/**
	 * @since 5.0.07
	 *
	 * @param array $values
	 * @param array $keys
	 * @return array
	 */
	public static function maybe_filter_array( $values, $keys ) {
		$allow_unfiltered_html = self::allow_unfiltered_html();

		if ( $allow_unfiltered_html ) {
			return $values;
		}

		foreach ( $keys as $key ) {
			if ( isset( $values[ $key ] ) ) {
				$values[ $key ] = self::kses( $values[ $key ], 'all' );
			}
		}

		return $values;
	}

	/**
	 * Some back end fields allow privleged users to add scripts.
	 * A site that uses the DISALLOW_UNFILTERED_HTML always remove scripts on echo.
	 *
	 * @since 5.0.13
	 *
	 * @param string       $value
	 * @param array|string $allowed 'all' for everything included as defaults
	 * @return string
	 */
	public static function maybe_kses( $value, $allowed = 'all' ) {
		if ( self::should_never_allow_unfiltered_html() ) {
			$value = self::kses( $value, $allowed );
		}
		return $value;
	}

	/**
	 * @since 5.0.16
	 *
	 * @return bool
	 */
	public static function show_landing_pages() {
		return self::show_new_feature( 'landing' );
	}

	/**
	 * @since 5.0.16
	 *
	 * @return array
	 */
	public static function get_landing_page_upgrade_data_params( $medium = 'landing' ) {
		$params = array(
			'medium'     => $medium,
			'upgrade'    => __( 'Form Landing Pages', 'formidable' ),
			'message'    => __( 'Easily manage a landing page for your form. Upgrade to get form landing pages.', 'formidable' ),
			'screenshot' => 'landing.png',
		);
		return self::get_upgrade_data_params( 'landing', $params );
	}

	/**
	 * @since 5.0.17
	 *
	 * @param string $plugin
	 * @return string|false
	 */
	private static function get_plan_required( $plugin ) {
		$link = FrmAddonsController::install_link( $plugin );
		return FrmFormsHelper::get_plan_required( $link );
	}

	/**
	 * @since 5.0.17
	 *
	 * @param string
	 * @return bool
	 */
	public static function show_new_feature( $feature ) {
		$link = FrmAddonsController::install_link( $feature );
		return array_key_exists( 'status', $link ) || array_key_exists( 'class', $link );
	}

	/**
	 * @since 5.0.17
	 *
	 * @param string $plugin
	 * @param array  $params
	 * @return array
	 */
	public static function get_upgrade_data_params( $plugin, $params ) {
		$link = FrmAddonsController::install_link( $plugin );
		if ( ! $link ) {
			return $params;
		}

		if ( ! empty( $link['url'] ) && self::pro_is_installed() ) {
			$params['oneclick'] = json_encode( $link );
			unset( $params['message'] );
			if ( ! isset( $params['medium'] ) ) {
				$params['medium'] = $plugin;
			}
		} else {
			$params['requires'] = FrmFormsHelper::get_plan_required( $link );
		}

		return $params;
	}

	/**
	 * Returns true if every character in text is a hexadecimal 'digit', that is a decimal digit or a character from [A-Fa-f], false otherwise.
	 * Not every server installs the ctype extension, so use a fallback if the function does not exist.
	 *
	 * @since 5.0.17
	 *
	 * @param string $text
	 * @return bool
	 */
	public static function ctype_xdigit( $text ) {
		if ( function_exists( 'ctype_xdigit' ) ) {
			return ctype_xdigit( $text );
		}
		return is_string( $text ) && '' !== $text && ! preg_match( '/[^A-Fa-f0-9]/', $text );
	}

	/**
	 * Set the current screen to avoid undefined notices.
	 *
	 * @since 5.2.01
	 */
	public static function set_current_screen_and_hook_suffix() {
		global $hook_suffix;
		if ( is_null( $hook_suffix ) ) {
			// $hook_suffix gets used in substr so make sure it's not null. PHP 8.1 deprecates null in substr.
			$hook_suffix = ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}
		set_current_screen();
	}

	/**
	 * Shows pill text.
	 *
	 * @since 5.2.02
	 *
	 * @param string $text Text in the pill. Default is NEW.
	 */
	public static function show_pill_text( $text = null ) {
		if ( null === $text ) {
			$text = __( 'NEW', 'formidable' );
		}
		echo '<span class="frm-new-pill">' . esc_html( $text ) . '</span>';
	}

	/**
	 * Count the number of decimals digits.
	 *
	 * @since 5.2.07
	 *
	 * @param mixed $num Number.
	 * @return int|false Returns `false` if the passed parameter is not number.
	 */
	public static function count_decimals( $num ) {
		if ( ! is_numeric( $num ) ) {
			return false;
		}

		$num   = (string) $num;
		$parts = explode( '.', $num );
		if ( 1 === count( $parts ) ) {
			return 0;
		}

		return strlen( $parts[ count( $parts ) - 1 ] );
	}

	/**
	 * Prevent a fatal error in PHP8 if gmt_offset happens to be set an empty string.
	 * This is a bug in WordPress. It isn't safe to call current_time( 'timestamp' ) without this with an empty string offset.
	 * In the future this might be safe to remove. Keep an eye on the current_time function in functions.php.
	 *
	 * @since 5.3.1
	 *
	 * @return void
	 */
	public static function filter_gmt_offset() {
		if ( self::$added_gmt_offset_filter ) {
			// Avoid adding twice.
			return;
		}

		add_filter(
			'option_gmt_offset',
			function( $offset ) {
				if ( ! is_string( $offset ) || is_numeric( $offset ) ) {
					// Leave a valid value alone.
					return $offset;
				}

				return 0;
			}
		);
		self::$added_gmt_offset_filter = true;
	}

	/**
	 * @since 5.3.1
	 *
	 * @return bool
	 */
	public static function on_form_listing_page() {
		if ( ! self::is_admin_page( 'formidable' ) ) {
			return false;
		}

		$action = self::simple_get( 'frm_action', 'sanitize_title' );
		return ! $action || in_array( $action, self::get_form_listing_page_actions(), true );
	}

	/**
	 * Get all actions that also display the forms list.
	 *
	 * @since 5.3.1
	 *
	 * @return array<string>
	 */
	private static function get_form_listing_page_actions() {
		return array( 'list', 'trash', 'untrash', 'destroy' );
	}

	/**
	 * @since 4.08
	 * @deprecated 4.09.01
	 */
	public static function expiring_message() {
		_deprecated_function( __METHOD__, '4.09.01', 'FrmProAddonsController::expiring_message' );
		if ( is_callable( 'FrmProAddonsController::expiring_message' ) ) {
			FrmProAddonsController::expiring_message();
		}
	}

	/**
	 * Use the WP 4.7 wp_doing_ajax function
	 *
	 * @since 2.05.07
	 * @deprecated 4.04.04
	 */
	public static function wp_doing_ajax() {
		_deprecated_function( __METHOD__, '4.04.04', 'wp_doing_ajax' );
		return wp_doing_ajax();
	}

	/**
	 * @deprecated 4.0
	 */
	public static function insert_opt_html( $args ) {
		_deprecated_function( __METHOD__, '4.0', 'FrmFormsHelper::insert_opt_html' );
		FrmFormsHelper::insert_opt_html( $args );
	}

	/**
	 * Used to filter shortcode in text widgets
	 *
	 * @deprecated 2.5.4
	 * @codeCoverageIgnore
	 */
	public static function widget_text_filter_callback( $matches ) {
		return FrmDeprecated::widget_text_filter_callback( $matches );
	}

	/**
	 * @deprecated 3.01
	 * @codeCoverageIgnore
	 */
	public static function sanitize_array( &$values ) {
		FrmDeprecated::sanitize_array( $values );
	}

	/**
	 * @param array $settings
	 * @param string $group
	 *
	 * @since 2.0.6
	 * @deprecated 2.05.06
	 * @codeCoverageIgnore
	 */
	public static function save_settings( $settings, $group ) {
		return FrmDeprecated::save_settings( $settings, $group );
	}

	/**
	 * @since 2.0.4
	 * @deprecated 2.05.06
	 * @codeCoverageIgnore
	 */
	public static function save_json_post( $settings ) {
		return FrmDeprecated::save_json_post( $settings );
	}

	/**
	 * @since 2.0
	 * @deprecated 2.05.06
	 * @codeCoverageIgnore
	 *
	 * @param string $cache_key The unique name for this cache
	 * @param string $group The name of the cache group
	 * @param string $query If blank, don't run a db call
	 * @param string $type The wpdb function to use with this query
	 *
	 * @return mixed $results The cache or query results
	 */
	public static function check_cache( $cache_key, $group = '', $query = '', $type = 'get_var', $time = 300 ) {
		return FrmDeprecated::check_cache( $cache_key, $group, $query, $type, $time );
	}

	/**
	 * @deprecated 2.05.06
	 * @codeCoverageIgnore
	 */
	public static function set_cache( $cache_key, $results, $group = '', $time = 300 ) {
		return FrmDeprecated::set_cache( $cache_key, $results, $group, $time );
	}

	/**
	 * @deprecated 2.05.06
	 * @codeCoverageIgnore
	 */
	public static function add_key_to_group_cache( $key, $group ) {
		FrmDeprecated::add_key_to_group_cache( $key, $group );
	}

	/**
	 * @deprecated 2.05.06
	 * @codeCoverageIgnore
	 */
	public static function get_group_cached_keys( $group ) {
		return FrmDeprecated::get_group_cached_keys( $group );
	}

	/**
	 * @since 2.0
	 * @deprecated 2.05.06
	 * @codeCoverageIgnore
	 * @return mixed The cached value or false
	 */
	public static function check_cache_and_transient( $cache_key ) {
		return FrmDeprecated::check_cache( $cache_key );
	}

	/**
	 * @since 2.0
	 * @deprecated 2.05.06
	 * @codeCoverageIgnore
	 *
	 * @param string $cache_key
	 */
	public static function delete_cache_and_transient( $cache_key, $group = 'default' ) {
		FrmDeprecated::delete_cache_and_transient( $cache_key, $group );
	}

	/**
	 * @since 2.0
	 * @deprecated 2.05.06
	 * @codeCoverageIgnore
	 *
	 * @param string $group The name of the cache group
	 */
	public static function cache_delete_group( $group ) {
		FrmDeprecated::cache_delete_group( $group );
	}

	/**
	 * @since 1.07.10
	 * @deprecated 2.05.06
	 * @codeCoverageIgnore
	 *
	 * @param string $term The value to escape
	 *
	 * @return string The escaped value
	 */
	public static function esc_like( $term ) {
		return FrmDeprecated::esc_like( $term );
	}

	/**
	 * @param string $order_query
	 *
	 * @deprecated 2.05.06
	 * @codeCoverageIgnore
	 */
	public static function esc_order( $order_query ) {
		return FrmDeprecated::esc_order( $order_query );
	}

	/**
	 * @deprecated 2.05.06
	 * @codeCoverageIgnore
	 */
	public static function esc_order_by( &$order_by ) {
		FrmDeprecated::esc_order_by( $order_by );
	}

	/**
	 * @param string $limit
	 *
	 * @deprecated 2.05.06
	 * @codeCoverageIgnore
	 */
	public static function esc_limit( $limit ) {
		return FrmDeprecated::esc_limit( $limit );
	}

	/**
	 * @since 2.0
	 * @deprecated 2.05.06
	 * @codeCoverageIgnore
	 */
	public static function prepare_array_values( $array, $type = '%s' ) {
		return FrmDeprecated::prepare_array_values( $array, $type );
	}

	/**
	 * @deprecated 2.05.06
	 * @codeCoverageIgnore
	 */
	public static function prepend_and_or_where( $starts_with = ' WHERE ', $where = '' ) {
		return FrmDeprecated::prepend_and_or_where( $starts_with, $where );
	}

	/**
	 * @since 2.0
	 * @deprecated 5.0.13
	 *
	 * @return string The base Google APIS url for the current version of jQuery UI
	 */
	public static function jquery_ui_base_url() {
		_deprecated_function( __FUNCTION__, '5.0.13', 'FrmProAppHelper::jquery_ui_base_url' );
		return is_callable( 'FrmProAppHelper::jquery_ui_base_url' ) ? FrmProAppHelper::jquery_ui_base_url() : '';
	}

	/**
	 * @since 4.07
	 * @deprecated x.x
	 */
	public static function renewal_message() {
		_deprecated_function( __METHOD__, 'x.x', 'FrmProAddonsController::renewal_message' );
	}
}
