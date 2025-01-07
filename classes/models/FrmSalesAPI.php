<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since x.x
 */
class FrmSalesAPI extends FrmFormApi {

	protected $cache_key;

	/**
	 * All sales from API data.
	 *
	 * @var array|false
	 */
	private static $sales = false;

	/**
	 * Best sale from API data.
	 *
	 * @var array|false|null
	 */
	private static $best_sale;

	public function __construct( $for_parent = null ) {
		$this->set_cache_key();

		if ( false === self::$sales ) {
			$this->set_sales();
		}
	}

	/**
	 * @since x.x
	 *
	 * @return void
	 */
	protected function set_cache_key() {
		$this->cache_key = 'frm_sales_cache';
	}

	/**
	 * @since x.x
	 *
	 * @return string
	 */
	protected function api_url() {
		return 'https://formidableforms.com/wp-json/s11-sales/v1/list/';
	}

	/**
	 * @since x.x
	 *
	 * @return void
	 */
	private function set_sales() {
		self::$sales = array();

		$api = $this->get_api_info();
		if ( empty( $api ) ) {
			return;
		}

		foreach ( $api as $sale ) {
			$this->add_sale( $sale );
		}
	}

	/**
	 * @param array|string $sale
	 *
	 * @return void
	 */
	private function add_sale( $sale ) {
		if ( ! is_array( $sale ) || ! isset( $sale['key'] ) ) {
			// if the API response is invalid, $sale may not be an array.
			// if there are no sales from the API, it is returning a "No Entries Found" item with no key, so check for a key as well.
			return;
		}

		if ( $this->is_expired( $sale ) ) {
			return;
		}

		if ( isset( self::$sales[ $sale['key'] ] ) ) {
			// Move up and mark as new.
			unset( self::$sales[ $sale['key'] ] );
		}

		self::$sales[ $sale['key'] ] = $this->fill_sale( $sale );
	}

	/**
	 * @param array $sale
	 * @return array
	 */
	private function fill_sale( $sale ) {
		$defaults = array(
			'key'                                  => '',
			'starts'                               => '',
			'expires'                              => '',
			// Use 'free', 'personal', 'business', 'elite', 'grandfathered'.
			'who'                                  => 'all',
			'discount_percent'                     => 0,
			'test_group'                           => '',
			'lite_banner_cta_link'                 => '',
			'lite_banner_cta_text'                 => '',
			'menu_cta_link'                        => '',
			'menu_cta_text'                        => '',
			'dashboard_license_cta_link'           => '',
			'dashboard_license_cta_text'           => '',
			'global_settings_license_cta_link'     => '',
			'global_settings_license_cta_text'     => '',
			'global_settings_unlock_more_cta_link' => '',
			'global_settings_unlock_more_cta_text' => '',
			'global_settings_upgrade_cta_link'     => '',
			'builder_sidebar_cta_link'             => '',
			'builder_sidebar_cta_text'             => '',
		);

		return array_merge( $defaults, $sale );
	}

	/**
	 * @param array $sale
	 *
	 * @return bool
	 */
	private function is_expired( $sale ) {
		return $sale['expires'] < time();
	}

	/**
	 * Show different sales for different accounts.
	 *
	 * @param array $sale
	 * @return bool
	 */
	private function is_for_user( $sale ) {
		if ( ! isset( $sale['who'] ) || $sale['who'] === 'all' ) {
			return true;
		}
		$who = (array) $sale['who'];
		if ( $this->is_for_everyone( $who ) ) {
			return true;
		}
		if ( $this->is_user_type( $who ) ) {
			return true;
		}
		if ( in_array( 'free_first_30', $who, true ) && $this->is_free_first_30() ) {
			return true;
		}
		if ( in_array( 'free_not_first_30', $who, true ) && $this->is_free_not_first_30() ) {
			return true;
		}
		return false;
	}

	/**
	 * @since x.x
	 *
	 * @param array $who
	 * @return bool
	 */
	private function is_for_everyone( $who ) {
		return in_array( 'all', $who, true );
	}

	/**
	 * @since x.x
	 *
	 * @param array $who
	 * @return bool
	 */
	private function is_user_type( $who ) {
		return in_array( $this->get_user_type(), $who, true );
	}

	private function get_user_type() {
		if ( ! FrmAppHelper::pro_is_installed() ) {
			return 'free';
		}

		return FrmAddonsController::license_type();
	}

	/**
	 * Check if user is still using the Lite version only, and within
	 * the first 30 days of activation.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	private function is_free_first_30() {
		return $this->is_free() && $this->is_first_30();
	}

	/**
	 * @since x.x
	 *
	 * @return bool
	 */
	private function is_first_30() {
		$activation_timestamp = get_option( 'frm_first_activation' );
		if ( false === $activation_timestamp ) {
			// If the option does not exist, assume that it is
			// because the user was active before this option was introduced.
			return false;
		}
		$cutoff = strtotime( '-30 days' );
		return $activation_timestamp > $cutoff;
	}

	/**
	 * @since x.x
	 *
	 * @return bool
	 */
	private function is_free_not_first_30() {
		return $this->is_free() && ! $this->is_first_30();
	}

	/**
	 * Check if the Pro plugin is active. If not, consider the user to be on the free version.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	private function is_free() {
		return ! FrmAppHelper::pro_is_included();
	}

	/**
	 * @since x.x
	 *
	 * @return array|false
	 */
	public function get_best_sale() {
		if ( ! self::$sales ) {
			return false;
		}

		if ( isset( self::$best_sale ) ) {
			return self::$best_sale;
		}

		$best_sale = false;
		foreach ( self::$sales as $sale ) {
			if ( ! $this->is_for_user( $sale ) ) {
				continue;
			}

			if ( ! $best_sale || $sale['discount_percent'] > $best_sale['discount_percent'] ) {
				$best_sale = $sale;
			}
		}

		self::$best_sale = $best_sale;
		return self::$best_sale;
	}

	/**
	 * This is just here for testing so skip the API.
	 * Remove this before launching (and inherit from the base class instead).
	 *
	 * @return array
	 */
	public function get_api_info() {
		return json_decode(
			'[{"key":"no-brainer","starts":1735689600,"expires":1738281600,"who":["all"],"discount_percent":50,"test_group":1,"lite_banner_cta_link":"https://formidableforms.com/cta1","lite_banner_cta_text":"Lite Banner Text","menu_cta_link":"https://formidableforms.com/cta2","menu_cta_text":"Menu Text","dashboard_license_cta_link":"https://formidableforms.com/cta3","dashboard_license_cta_text":"Dashboard License Text","global_settings_license_cta_link":"https://formidableforms.com/cta4","global_settings_license_cta_text":"License Text","global_settings_unlock_more_cta_link":"https://formidableforms.com/cta5","global_settings_unlock_more_cta_text":"Unlock More Text","global_settings_upgrade_cta_link":"https://formidableforms.com/cta6","builder_sidebar_cta_link":"https://formidableforms.com/cta7","builder_sidebar_cta_text":"Form Builder Text"},{"key":"anniversary","starts":1735689600,"expires":1738281600,"who":["all"],"discount_percent":60,"test_group":2,"lite_banner_cta_link":"https://formidableforms.com/cta1","lite_banner_cta_text":"Lite Banner Text","menu_cta_link":"https://formidableforms.com/cta2","menu_cta_text":"Menu Text","dashboard_license_cta_link":"https://formidableforms.com/cta3","dashboard_license_cta_text":"Dashboard License Text","global_settings_license_cta_link":"https://formidableforms.com/cta4","global_settings_license_cta_text":"License Text","global_settings_unlock_more_cta_link":"https://formidableforms.com/cta5","global_settings_unlock_more_cta_text":"Unlock More Text","global_settings_upgrade_cta_link":"https://formidableforms.com/cta6","builder_sidebar_cta_link":"https://formidableforms.com/cta7","builder_sidebar_cta_text":"Form Builder Text"}]',
			true
		);
	}
}
