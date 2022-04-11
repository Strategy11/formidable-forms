<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since x.x
 */
class FrmApplicationsController {

	/**
	 * Add Applications menu item to sidebar and define Applications index page.
	 *
	 * @return void
	 */
	public static function menu() {
		$label    = __( 'Applications', 'formidable' );
		$new_pill = '<span class="frm-new-pill">NEW</span>';
		add_submenu_page( 'formidable', 'Formidable | ' . $label, $label . $new_pill, 'frm_edit_forms', 'formidable-applications', array( __CLASS__, 'landing_page' ) );
	}

	/**
	 * Render Applications index page.
	 *
	 * @return void
	 */
	public static function landing_page() {
		require self::get_view_path() . 'index.php';
	}

	/**
	 * Get path to application views.
	 *
	 * @return string
	 */
	private static function get_view_path() {
		return FrmAppHelper::plugin_path() . '/classes/views/applications/';
	}

	/**
	 * Get information about applications via AJAX action.
	 *
	 * @return void
	 */
	public static function get_applications_data() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );

		$view = FrmAppHelper::get_param( 'view', '', 'get', 'sanitize_text_field' );
		$data = array();
		if ( 'applications' !== $view ) {
			$data['templates'] = self::get_prepared_template_data();
		}

		/**
		 * @param array $data
		 */
		$data = apply_filters( 'frm_applications_data', $data );

		wp_send_json_success( $data );
	}

	/**
	 * Retrieve API data and return a reduced set back with some restructuring applied to make it easier to read.
	 *
	 * @return array<array>
	 */
	private static function get_prepared_template_data() {
		$api              = new FrmApplicationApi();
		$applications     = $api->get_api_info();
		$applications     = array_filter( $applications, 'is_array' );
		$applications     = self::sort_templates( $applications );
		$keys             = apply_filters( 'frm_application_data_keys', array( 'key', 'name', 'description', 'link' ) );
		$keys_with_images = self::get_template_keys_with_local_images();

		return array_reduce(
			$applications,
			/**
			 * @param array $total the accumulated array of reduced application data.
			 * @param array $current data for the current template from the API.
			 * @return array<array>
			 */
			function( $total, $current ) use ( $keys, $keys_with_images ) {
				$application = array();
				foreach ( $keys as $key ) {
					$value = $current[ $key ];

					if ( 'icon' === $key ) {
						// Icon is an array. The first array item is the image URL.
						$application[ $key ] = reset( $value );
					} else {
						if ( 'name' === $key && ' Template' === substr( $value, -9 ) ) {
							// Strip off the " Template" text at the end of the name as it takes up space.
							$value = substr( $value, 0, -9 );
						}
						$application[ $key ] = $value;
					}
				}

				$application['hasLiteThumbnail'] = in_array( $application['key'], $keys_with_images, true );

				$total[] = $application;

				return $total;
			},
			array()
		);
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
	 * Sort applications alphabetically.
	 *
	 * @param array<array> $applications
	 * @return array<array>
	 */
	private static function sort_templates( $applications ) {
		usort(
			$applications,
			function( $a, $b ) {
				return strcmp( $a['name'], $b['name'] );
			}
		);
		return $applications;
	}

	/**
	 * @return void
	 */
	public static function load_assets() {
		$plugin_url = FrmAppHelper::plugin_url();
		$version    = FrmAppHelper::plugin_version();

		wp_enqueue_style( 'formidable-admin' );
		wp_enqueue_style( 'formidable-grids' );

		$js_dependencies = array(
			'wp-i18n',
			'formidable_dom',
		);
		wp_register_script( 'formidable_applications', $plugin_url . '/js/admin/applications.js', $js_dependencies, $version, true );
		wp_register_style( 'formidable_applications', $plugin_url . '/css/admin/applications.css', array(), $version );

		wp_enqueue_script( 'formidable_applications' );
		wp_enqueue_style( 'formidable_applications' );

		do_action( 'frm_applications_assets' );
	}

	/**
	 * @return void
	 */
	public static function dequeue_scripts() {
		wp_dequeue_script( 'frm-surveys-admin' );
	}

	/**
	 * @param string $title
	 * @param string $context values include 'index', 'list', and 'edit'.
	 * @return void
	 */
	public static function render_applications_header( $title, $context ) {
		require self::get_view_path() . 'header.php';
	}
}
