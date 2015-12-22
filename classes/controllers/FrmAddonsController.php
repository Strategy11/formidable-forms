<?php

class FrmAddonsController {

	public static function menu() {
		add_submenu_page( 'formidable', 'Formidable | '. __( 'AddOns', 'formidable' ), __( 'AddOns', 'formidable' ), 'frm_view_forms', 'formidable-addons', 'FrmAddonsController::list_addons' );
	}

	public static function list_addons() {
		$installed_addons = apply_filters( 'frm_installed_addons', array() );

		$pro_link = 'http://formidablepro.com/pricing';
		$addons = self::get_api_addons();
		if ( ! is_array( $addons ) ) {
			$addons = array(
				array( 'url' => $pro_link, 'name' => 'Formidable Pro', 'slug' => 'formidable_pro' ),
			);
		} else {
			$addons = $addons['products'];
		}
		$addons = array_reverse( $addons );
		$append_affiliate = FrmAppHelper::affiliate();

		$plugin_names = array(
			'formidable-pro' => 'formidable/pro', 'wp-multilingual' => 'formidable-wpml',
			'registration-lite' => 'formidable-registration', 'bootstrap-modal' => 'formidable-modal',
			'paypal-standard' => 'formidable-paypal', 'formidable-api' => 'formidable-api',
		);

		include( FrmAppHelper::plugin_path() . '/classes/views/addons/list.php' );
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
		if ( ! is_wp_error( $response ) && ! is_wp_error( $body ) ) {
			$addons = json_decode( $body, true );
			set_transient( 'frm_api_addons', $addons, 60 * 60 * 24 * 5 ); // check every 5 days
			if ( is_array( $addons ) ) {
				return $addons;
			}
		}

		return false;
	}
}
