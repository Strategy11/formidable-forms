<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since x.x
 */
class FrmApplicationsController {

	/**
	 * @todo "NEW" pill beside Applications menu item.
	 *
	 * @return void
	 */
	public static function menu() {
		$label = __( 'Applications', 'formidable' );
		add_submenu_page( 'formidable', 'Formidable | ' . $label, $label, 'frm_edit_forms', 'formidable-applications', array( __CLASS__, 'landing_page' ) );
	}

	/**
	 * @return void
	 */
	public static function landing_page() {
		require FrmAppHelper::plugin_path() . '/classes/views/applications/index.php';
	}

	/**
	 * @return void
	 */
	public static function get_applications_data() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );

		$view = FrmAppHelper::simple_get( 'view' );
		$data = array();
		if ( ! $view || 'index' === $view ) {
			$data['templates'] = self::get_prepared_template_data();
		}

		/**
		 * @param array $data
		 */
		$data = apply_filters( 'frm_applications_data', $data );

		wp_send_json_success( $data );
	}

	/**
	 * @return array
	 */
	private static function get_prepared_template_data() {
		$api          = new FrmApplicationApi();
		$applications = $api->get_api_info();
		$keys         = apply_filters( 'frm_application_data_keys', array( 'name', 'description', 'link' ) );
		return array_reduce(
			$applications,
			function( $total, $current ) use ( $keys ) {
				if ( ! is_array( $current ) ) {
					return $total;
				}

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
				$total[] = $application;

				return $total;
			},
			array()
		);
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
		wp_register_script( 'formidable_applications', $plugin_url . '/js/applications.js', $js_dependencies, $version, true );
		wp_register_style( 'formidable_applications', $plugin_url . '/css/applications.css', array(), $version );

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
	 * @param string $context values include 'index' and 'edit'.
	 * @return void
	 */
	public static function render_applications_header( $title, $context ) {
		require FrmAppHelper::plugin_path() . '/classes/views/applications/header.php';
	}
}
