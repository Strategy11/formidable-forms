<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since x.x
 */
class FrmSalesApi extends FrmFormApi {

	/**
	 * @var FrmSalesApi|null
	 */
	private static $instance;

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

		if ( ! $this->sale_is_active( $sale ) ) {
			return;
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
	 * Check if a sale is within the active period.
	 *
	 * @since x.x
	 *
	 * @param array $sale
	 * @return bool
	 */
	private function sale_is_active( $sale ) {
		$starts  = $sale['starts'];
		$expires = $sale['expires'];
		$date    = new DateTime( 'now', new DateTimeZone( 'America/New_York' ) );
		$today   = $date->getTimestamp();
		return $today >= $starts && $today <= $expires;
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
			if ( ! FrmApiHelper::is_for_user( $sale ) ) {
				continue;
			}

			if ( ! $this->matches_ab_group( $sale ) ) {
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
			'[{"key":"no-brainer","starts":1735689600,"expires":1738281600,"who":["all"],"discount_percent":50,"test_group":1,"lite_banner_cta_link":"https://formidableforms.com/cta1","lite_banner_cta_text":"Lite Banner Text","menu_cta_link":"https://formidableforms.com/cta2","menu_cta_text":"Menu Text","dashboard_license_cta_link":"https://formidableforms.com/cta3","dashboard_license_cta_text":"Dashboard License Text","global_settings_license_cta_link":"https://formidableforms.com/cta4","global_settings_license_cta_text":"License Text","global_settings_unlock_more_cta_link":"https://formidableforms.com/cta5","global_settings_unlock_more_cta_text":"Unlock More Text","global_settings_upgrade_cta_link":"https://formidableforms.com/cta6","builder_sidebar_cta_link":"https://formidableforms.com/cta7","builder_sidebar_cta_text":"Form Builder Text","footer_cta_link":"https://formidableforms.com/cta8","footer_cta_text":"Footer Text"},{"key":"anniversary","starts":1735689600,"expires":1738281600,"who":["all"],"discount_percent":60,"test_group":0,"lite_banner_cta_link":"https://formidableforms.com/cta1","lite_banner_cta_text":"Lite Banner Text","menu_cta_link":"https://formidableforms.com/cta2","menu_cta_text":"Menu Text","dashboard_license_cta_link":"https://formidableforms.com/cta3","dashboard_license_cta_text":"Dashboard License Text","global_settings_license_cta_link":"https://formidableforms.com/cta4","global_settings_license_cta_text":"License Text","global_settings_unlock_more_cta_link":"https://formidableforms.com/cta5","global_settings_unlock_more_cta_text":"Unlock More Text","global_settings_upgrade_cta_link":"https://formidableforms.com/cta6","builder_sidebar_cta_link":"https://formidableforms.com/cta7","builder_sidebar_cta_text":"Form Builder Text","footer_cta_link":"https://formidableforms.com/cta8","footer_cta_text":"Footer Text"}]',
			true
		);
	}

	/**
	 * Get text for best sale if applicable.
	 *
	 * @since x.x
	 *
	 * @param string $key
	 * @return false|string False if no sale is active.
	 */
	public static function get_best_sale_value( $key ) {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new FrmSalesApi();
		}

		$sale = self::$instance->get_best_sale();

		return is_array( $sale ) && ! empty( $sale[ $key ] ) ? $sale[ $key ] : false;
	}

	/**
	 * @since x.x
	 *
	 * @param array $sale
	 * @return bool True if the sale is a match for the applicable group (if one is defined).
	 */
	private function matches_ab_group( $sale ) {
		if ( ! is_numeric( $sale['test_group'] ) ) {
			// No test group, so return true.
			return true;
		}

		$ab_group = $this->get_ab_group_for_current_site();
		return $ab_group === $sale['test_group'];
	}

	/**
	 * @since x.x
	 *
	 * @return int 1 or 0.
	 */
	private function get_ab_group_for_current_site() {
		$option = get_option( 'frm_sale_ab_group' );
		if ( ! is_numeric( $option ) ) {
			// Generate either 0 or 1.
			$option = mt_rand( 0, 1 );
			update_option( 'frm_sale_ab_group', $option, false );
		}
		return (int) $option;
	}
}
