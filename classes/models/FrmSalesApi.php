<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 6.17
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

	public function __construct() {
		$this->set_cache_key();

		if ( false === self::$sales ) {
			$this->set_sales();
		}
	}

	/**
	 * @since 6.17
	 *
	 * @return void
	 */
	protected function set_cache_key() {
		$this->cache_key = 'frm_sales_cache';
	}

	/**
	 * @since 6.17
	 *
	 * @return string
	 */
	protected function api_url() {
		return 'https://plapi.formidableforms.com/sales/';
	}

	/**
	 * @since 6.17
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
		if ( ! is_array( self::$sales ) ) {
			// This gets set in the constructor.
			// This check is just here for Psalm analysis.
			return;
		}

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
			'banner_title'                         => '',
			'banner_body'                          => '',
			'banner_icon'                          => '',
			'banner_text_color'                    => '',
			'banner_bg_color'                      => '',
			'banner_cta_link'                      => '',
			'banner_cta_text'                      => '',
			'banner_cta_text_color'                => '',
			'banner_cta_bg_color'                  => '',
		);

		return array_merge( $defaults, $sale );
	}

	/**
	 * Check if a sale is within the active period.
	 *
	 * @since 6.17
	 *
	 * @param array $sale
	 * @return bool
	 */
	private function sale_is_active( $sale ) {
		$starts  = $sale['starts'];
		$expires = $sale['expires'] + DAY_IN_SECONDS;
		$date    = new DateTime( 'now', new DateTimeZone( 'America/New_York' ) );
		$today   = $date->getTimestamp();
		return $today >= $starts && $today <= $expires;
	}

	/**
	 * @since 6.17
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
	 * Get text for best sale if applicable.
	 *
	 * @since 6.17
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
	 * @since 6.17
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
	 * @since 6.17
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

	/**
	 * Maybe show banner for the best sale.
	 *
	 * @since 6.21
	 *
	 * @return bool
	 */
	public static function maybe_show_banner() {
		if ( ! current_user_can( 'frm_change_settings' ) ) {
			return false;
		}

		if ( ! isset( self::$instance ) ) {
			self::$instance = new FrmSalesApi();
		}

		$sale = self::$instance->get_best_sale();
		if ( ! $sale || ! is_array( $sale ) ) {
			return false;
		}

		$banner_title = ! empty( $sale['banner_title'] ) ? $sale['banner_title'] : false;
		$banner_body  = ! empty( $sale['banner_body'] ) ? $sale['banner_body'] : false;

		if ( false === $banner_title || false === $banner_body ) {
			return false;
		}

		if ( self::is_banner_dismissed( $sale['key'] ) ) {
			return false;
		}

		$banner_icon       = ! empty( $sale['banner_icon'] ) ? $sale['banner_icon'] : 'generic';
		$banner_bg_color   = ! empty( $sale['banner_bg_color'] ) ? $sale['banner_bg_color'] : false;
		$banner_text_color = ! empty( $sale['banner_text_color'] ) ? $sale['banner_text_color'] : false;
		$banner_cta_link   = ! empty( $sale['banner_cta_link'] ) ? $sale['banner_cta_link'] : false;

		// translators: %s is the discount percentage.
		$banner_cta_text       = ! empty( $sale['banner_cta_text'] ) ? $sale['banner_cta_text'] : sprintf( __( 'GET %s OFF NOW', 'formidable' ), $sale['discount_percent'] . '%' );
		$banner_cta_text_color = ! empty( $sale['banner_cta_text_color'] ) ? $sale['banner_cta_text_color'] : false;
		$banner_cta_bg_color   = ! empty( $sale['banner_cta_bg_color'] ) ? $sale['banner_cta_bg_color'] : false;

		if ( false === $banner_cta_link ) {
			$banner_cta_link = FrmAppHelper::admin_upgrade_link(
				array(
					'medium'  => 'sales-api-banner',
					'content' => $sale['key'],
				)
			);
		}

		$banner_attrs = array(
			'id'       => 'frm_sale_banner',
			'data-url' => $banner_cta_link,
		);

		if ( false === $banner_bg_color || 'gradient' === $banner_bg_color ) {
			$banner_attrs['class'] = 'frm-gradient';
		} else {
			$banner_attrs['style'] = 'background-color: ' . esc_attr( $banner_bg_color ) . ';';
		}

		$cta_attrs = array(
			'href'  => '#',
			'style' => '',
		);
		if ( false !== $banner_cta_text_color ) {
			$cta_attrs['style'] .= 'color: ' . esc_attr( $banner_cta_text_color ) . ';';
		}
		if ( false !== $banner_cta_bg_color ) {
			$cta_attrs['style'] .= 'background-color: ' . esc_attr( $banner_cta_bg_color ) . ';';
		}

		$dismiss_attrs = array(
			'href'  => '#',
			'class' => 'dismiss',
		);

		$content_attrs = array();

		if ( false !== $banner_text_color ) {
			$content_attrs['style'] = 'color: ' . esc_attr( $banner_text_color ) . ';';
			$dismiss_attrs['style'] = 'color: ' . esc_attr( $banner_text_color ) . ';';
		}

		?>
		<div <?php FrmAppHelper::array_to_html_params( $banner_attrs, true ); ?>>
			<div>
				<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/sales/' . $banner_icon . '.svg' ); ?>" alt="<?php echo esc_attr( $banner_title ); ?>" />
			</div>
			<div <?php FrmAppHelper::array_to_html_params( $content_attrs, true ); ?>>
				<div>
					<?php echo esc_html( $banner_title ); ?>
				</div>
				<div>
					<?php echo esc_html( $banner_body ); ?>
				</div>
			</div>
			<div>
				<a <?php FrmAppHelper::array_to_html_params( $cta_attrs, true ); ?>>
					<?php echo esc_html( $banner_cta_text ); ?>
				</a>
			</div>
			<a <?php FrmAppHelper::array_to_html_params( $dismiss_attrs, true ); ?>><?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_close_icon' ); ?></a>
		</div>
		<?php

		return true;
	}

	/**
	 * Dismiss a banner via AJAX hook.
	 *
	 * @since 6.21
	 */
	public static function dismiss_banner() {
		FrmAppHelper::permission_check( 'frm_view_forms' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		if ( ! isset( self::$instance ) ) {
			self::$instance = new FrmSalesApi();
		}

		$sale = self::$instance->get_best_sale();
		if ( ! $sale || ! is_array( $sale ) ) {
			wp_send_json_error();
		}

		$dismissed_sales = get_user_option( 'frm_dismissed_sales', get_current_user_id() );
		if ( ! is_array( $dismissed_sales ) ) {
			$dismissed_sales = array();
		}

		$dismissed_sales[] = $sale['key'];
		update_user_option( get_current_user_id(), 'frm_dismissed_sales', $dismissed_sales );

		wp_send_json_success();
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	private static function is_banner_dismissed( $key ) {
		$dismissed_sales = get_user_option( 'frm_dismissed_sales', get_current_user_id() );
		return is_array( $dismissed_sales ) && in_array( $key, $dismissed_sales, true );
	}
}
