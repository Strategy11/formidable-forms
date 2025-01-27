<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 5.3
 */
class FrmApplicationTemplate {

	/**
	 * @var array<string>|null
	 */
	private static $keys;

	/**
	 * @var array<string>|null
	 */
	private static $keys_with_images;

	/**
	 * @var array<string>|null
	 */
	private static $categories;

	/**
	 * @var array
	 */
	private $api_data;

	/**
	 * @return void
	 */
	public static function init() {
		/**
		 * @since 5.3
		 *
		 * @param array $keys
		 */
		self::$keys             = apply_filters(
			'frm_application_data_keys',
			array( 'key', 'name', 'description', 'link', 'categories', 'views', 'forms', 'used_addons' )
		);
		self::$keys_with_images = array_merge(
			self::get_template_keys_with_local_png_images(),
			self::get_template_keys_with_local_webp_images()
		);
		self::$categories       = array();
	}

	/**
	 * Newer templates now use .webp files instead of .png.
	 *
	 * @since 6.16
	 *
	 * @return array<string>
	 */
	private static function get_template_keys_with_local_webp_images() {
		return array(
			'member-directory',
			'link-in-bio-instagram',
			'letter-of-recommendation',
			'invoice-pdf',
			'freelance-invoice-generator',
			'contract-agreement',
			'charity-tracker',
			'certificate',
			'testimonials',
		);
	}

	/**
	 * Return the template keys that have embedded images. Otherwise, we want to avoid trying to load the URL and use the placeholder instead.
	 *
	 * @return array<string>
	 */
	private static function get_template_keys_with_local_png_images() {
		return array(
			'business-hours',
			'faq-template-wordpress',
			'restaurant-menu',
			'team-directory',
			'product-review',
			'real-estate-listings',
			'business-directory',
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
			if ( self::category_matches_a_license_type( $category ) ) {
				continue;
			}
			if ( in_array( $category, self::$categories, true ) ) {
				continue;
			}
			self::$categories[] = $category;
		}
	}

	/**
	 * @since 5.5.2
	 *
	 * @param string $category
	 * @return bool
	 */
	private static function category_matches_a_license_type( $category ) {
		if ( false !== strpos( $category, '+Views' ) ) {
			return true;
		}
		return in_array( $category, FrmFormsHelper::get_license_types(), true );
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
		if ( ! is_array( self::$keys ) ) {
			return array();
		}

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
						function ( $category ) {
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
			}//end if
		}//end foreach

		$application['hasLiteThumbnail'] = in_array( $application['key'], self::$keys_with_images, true );
		$application['isWebp']           = in_array( $application['key'], self::get_template_keys_with_local_webp_images(), true );

		if ( ! array_key_exists( 'url', $application ) ) {
			$application['requires'] = FrmFormsHelper::get_plan_required( $application );

			if ( false === $application['requires'] ) {
				// Application is invalid if the URL is unavailable and there is no plan required.
				return array();
			}

			$purchase_url = $this->is_available_for_purchase();
			if ( false !== $purchase_url ) {
				$application['forPurchase'] = true;
			}
			$application['upgradeUrl'] = $this->get_admin_upgrade_link();
			$application['link']       = $application['upgradeUrl'];
		}

		$application['isNew'] = $this->is_new();

		$application['usedAddons'] = array();
		if ( isset( $application['used_addons'] ) ) {
			// Change key to camel case.
			$application['usedAddons'] = $application['used_addons'];
			unset( $application['used_addons'] );
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
	 * Check if an application template is new. If it is, we include a "NEW" pill beside the title.
	 *
	 * @since 6.0
	 *
	 * @return bool
	 */
	private function is_new() {
		return ! empty( $this->api_data['is_new'] );
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
			'view-templates/' . $this->api_data['slug']
		);
	}
}
