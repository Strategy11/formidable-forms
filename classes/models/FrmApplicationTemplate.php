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
		self::$keys             = apply_filters( 'frm_application_data_keys', array( 'key', 'name', 'description', 'link', 'categories', 'views', 'forms' ) );
		self::$keys_with_images = self::get_template_keys_with_local_images();
		self::$categories       = array();
	}

	/**
	 * Return the template keys that have embedded images. Otherwise, we want to avoid trying to load the URL and use the placeholder instead.
	 *
	 * @return array<string>
	 */
	private static function get_template_keys_with_local_images() {
		return array( 'business-hours', 'faq-template-wordpress', 'restaurant-menu', 'team-directory' );
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
			$application['upgradeUrl'] = $this->get_admin_upgrade_link();
		}

		return $application;
	}

	/**
	 * @return string
	 */
	private function get_admin_upgrade_link() {
		return FrmAppHelper::admin_upgrade_link(
			array(
				'medium'  => 'application-template-' . $this->api_data['key'],
				'content' => 'applications',
			)
		);
	}
}
