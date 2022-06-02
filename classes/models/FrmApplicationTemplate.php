<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 5.3
 */
class FrmApplicationTemplate {

	/**
	 * @var array<string>|null $keys
	 */
	private static $keys;

	/**
	 * @var array<string>|null $keys_with_images
	 */
	private static $keys_with_images;

	/**
	 * @var array<string>|null $categories
	 */
	private static $categories;

	/**
	 * @var array $api_data
	 */
	private $api_data;

	/**
	 * @param array<string> $keys
	 * @param array<string> $keys_with_images
	 */
	public static function init() {
		/**
		 * @since 5.3
		 *
		 * @param array $keys
		 */
		self::$keys             = apply_filters(
			'frm_application_data_keys',
			array( 'key', 'name', 'description', 'link', 'categories', 'views', 'forms' )
		);
		self::$keys_with_images = self::get_template_keys_with_local_images();
		self::$categories       = array();
	}

	/**
	 * Return the template keys that have embedded images. Otherwise, we want to avoid trying to load the URL and use the placeholder instead.
	 *
	 * @return array<string>
	 */
	private static function get_template_keys_with_local_images() {
		return array(
			'business-hours',
			'faq-template-wordpress',
			'restaurant-menu',
			'team-directory',
			'product-review',
			'real-estate-listings',
		);
	}

	/**
	 * @param array $api_data
	 * @return void
	 */
	public function __construct( $api_data ) {
		$this->api_data = $api_data;

		if ( ! empty( $api_data['categories'] ) ) {
			self::populate_category_information( $api_data['categories'] );
		}
	}

	/**
	 * @param array<string> $categories
	 * @return void
	 */
	private static function populate_category_information( $categories ) {
		foreach ( $categories as $category ) {
			if ( false !== strpos( $category, '+Views' ) || in_array( $category, self::$categories, true ) ) {
				continue;
			}
			self::$categories[] = $category;
		}
	}

	/**
	 * @return array<string>
	 */
	public static function get_categories() {
		return isset( self::$categories ) ? self::$categories : array();
	}

	/**
	 * @return array
	 */
	public function as_js_object() {
		$application = array();
		foreach ( self::$keys as $key ) {
			if ( ! isset( $this->api_data[ $key ] ) ) {
				continue;
			}

			$value = $this->api_data[ $key ];

			if ( 'icon' === $key ) {
				// Icon is an array. The first array item is the image URL.
				$application[ $key ] = reset( $value );
			} elseif ( 'categories' === $key ) {
				$application[ $key ] = array_values(
					array_filter(
						$value,
						function( $category ) {
							return false === strpos( $category, '+Views' );
						}
					)
				);
			} else {
				if ( 'views' === $key ) {
					$key = 'viewCount';
				} elseif ( 'forms' === $key ) {
					$key = 'formCount';
				}

				if ( 'name' === $key && ' Template' === substr( $value, -9 ) ) {
					// Strip off the " Template" text at the end of the name as it takes up space.
					$value = substr( $value, 0, -9 );
				}
				$application[ $key ] = $value;
			}
		}

		$application['hasLiteThumbnail'] = in_array( $application['key'], self::$keys_with_images, true );

		if ( ! array_key_exists( 'url', $application ) ) {
			$purchase_url = $this->is_available_for_purchase();
			if ( false !== $purchase_url ) {
				$application['forPurchase'] = true;
			}
			$application['upgradeUrl'] = $this->get_admin_upgrade_link();
			$application['link']       = $application['upgradeUrl'];
		}

		return $application;
	}

	/**
	 * @return bool
	 */
	private function is_available_for_purchase() {
		if ( ! array_key_exists( 'min_plan', $this->api_data ) ) {
			return false;
		}

		$license_type = '';
		$api          = new FrmFormApi();
		$addons       = $api->get_api_info();

		if ( ! array_key_exists( 93790, $addons ) ) {
			return false;
		}

		$pro = $addons[93790];
		if ( ! array_key_exists( 'type', $pro ) ) {
			return false;
		}

		$license_type = strtolower( $pro['type'] );
		$args         = array(
			'license_type'  => $license_type,
			'plan_required' => $this->get_required_license(),
		);
		if ( ! FrmFormsHelper::plan_is_allowed( $args ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @return string
	 */
	private function get_required_license() {
		$required_license = strtolower( $this->api_data['min_plan'] );
		if ( 'plus' === $required_license ) {
			$required_license = 'personal';
		}
		return $required_license;
	}

	/**
	 * @return string
	 */
	private function get_admin_upgrade_link() {
		return FrmAppHelper::admin_upgrade_link(
			array(
				'content' => 'upgrade',
				'medium'  => 'applications',
			),
			'/view-templates/' . $this->api_data['slug']
		);
	}
}
