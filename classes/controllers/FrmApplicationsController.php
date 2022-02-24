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
		?>
		<div id="frm_applications_container"></div>
		<?php
	}

	/**
	 * @return void
	 */
	public static function get_applications_data() {
		$applications = self::get_prepared_application_data();
		$data         = compact( 'applications' );
		wp_send_json_success( $data );
	}

	/**
	 * @return array
	 */
	private static function get_prepared_application_data() {
		$api          = new FrmApplicationApi();
		$applications = $api->get_api_info();
		$keys         = array( 'name', 'description', 'icon', 'link' );
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

		wp_register_script( 'formidable_applications', $plugin_url . '/js/applications.js', array(), $version, true );
		wp_enqueue_script( 'formidable_applications' );

		wp_register_style( 'formidable_applications', $plugin_url . '/css/applications.css', array(), $version );
		wp_enqueue_style( 'formidable_applications' );
	}

	/**
	 * @return void
	 */
	public static function dequeue_scripts() {
		wp_dequeue_script( 'frm-surveys-admin' );
	}
}
