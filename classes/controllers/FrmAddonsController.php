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

		$pro_link = 'https://formidablepro.com/pricing';
		$addons = self::get_ordered_addons( $pro_link );

		$plugin_names = array(
			'formidable-pro'    => 'formidable/pro',
			'wp-multilingual'   => 'formidable-wpml',
			'registration-lite' => 'formidable-registration',
			'bootstrap-modal'   => 'formidable-modal',
			'paypal-standard'   => 'formidable-paypal',
			'formidable-api'    => 'formidable-api',
			'authorize-net-aim' => 'formidable-authorize-net',
		);

		include( FrmAppHelper::plugin_path() . '/classes/views/addons/list.php' );
	}

	private static function get_ordered_addons( $pro_link = 'https://formidablepro.com/pricing' ) {
		$addons = self::get_api_addons();
		if ( ! is_array( $addons ) ) {
			$addons = array(
				'info' => array( 'link' => $pro_link, 'name' => 'Formidable Pro', 'slug' => 'formidable_pro' ),
			);
		} else {
			$addons = $addons['products'];
		}
		$addons = array_reverse( $addons );

		$keyed_addons = array();
		foreach ( $addons as $addon ) {
			$keyed_addons[ $addon['info']['slug'] ] = $addon;
		}

		$plugin_order = array(
			'formidable-pro', 'mailchimp', 'registration-lite',
			'paypal-standard', 'stripe', 'authorize-net-aim',
			'bootstrap-modal', 'math-captcha',
			'zapier',
		);
		$ordered_addons = array();
		foreach ( $plugin_order as $plugin ) {
			if ( isset( $keyed_addons[ $plugin ] ) ) {
				$ordered_addons[] = $keyed_addons[ $plugin ];
				unset( $keyed_addons[ $plugin ] );
			}
		}
		$addons = $ordered_addons + $keyed_addons;
		return $addons;
	}

	public static function license_settings() {
		$plugins = apply_filters( 'frm_installed_addons', array() );
		if ( empty( $plugins ) ) {
			_e( 'There are no plugins on your site that require a license', 'formidable' );
			return;
		}

		include( FrmAppHelper::plugin_path() . '/classes/views/addons/settings.php' );
	}

	private static function get_api_addons() {
		$addons = get_transient( 'frm_api_addons' );
		if ( $addons !== false ) {
			return $addons;
		}

		$url = 'https://formidablepro.com/edd-api/products?number=40';

		// check every 5 days
		$addons = self::send_api_request( $url, array( 'name' => 'frm_api_addons', 'expires' => 60 * 60 * 24 * 5 ) );
		if ( is_array( $addons ) ) {
			return $addons;
		}

		return false;
	}

	public static function get_licenses() {
		FrmAppHelper::permission_check('frm_change_settings');
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$license = get_option('frmpro-credentials');
		if ( $license && is_array( $license ) && isset( $license['license'] ) ) {
			$url = 'http://formidablepro.com/frm-edd-api/licenses?l=' . urlencode( base64_encode( $license['license'] ) );
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
		$addons = self::get_ordered_addons();
		$pro_pricing = array();
		self::prepare_pro_info( $addons[0], $pro_pricing );

		include( FrmAppHelper::plugin_path() . '/classes/views/addons/upgrade_to_pro.php' );
	}

	private static function prepare_pro_info( $pro, &$pro_pricing ) {
		$pro_pricing = array(
			'personal' => array( 'id' => 5, 'price' => '49.00', 'name' => 'Personal' ),
			'professional' => array( 'id' => 6, 'price' => '99.00', 'name' => 'Professional' ),
			'smallbusiness' => array( 'id' => 3, 'price' => '199.00', 'name' => 'Small Business' ),
			'enterprise' => array( 'id' => 4, 'price' => '399.00', 'name' => 'Enterprise' ),
		);

		foreach ( $pro['pricing'] as $name => $price ) {
			if ( isset( $pro_pricing[ $name ] ) ) {
				$pro_pricing[ $name ]['price'] = $price;
			}
		}
	}
}
