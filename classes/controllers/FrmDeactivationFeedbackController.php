<?php
/**
 * Deactivation feedback controller
 *
 * @package Formidable
 * @since 6.15
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Class FrmDeactivationFeedbackController
 */
class FrmDeactivationFeedbackController {

	/**
	 * Checks if is plugins page.
	 *
	 * @return bool
	 */
	private static function is_plugins_page() {
		return 'plugins' === get_current_screen()->id;
	}

	/**
	 * Checks if feedback is expired.
	 *
	 * @return bool
	 */
	private static function feedback_is_expired() {
		$feedback_expired = get_option( 'frm_feedback_expired' );
		if ( ! $feedback_expired ) {
			return true;
		}

		$expired_date = strtotime( $feedback_expired );
		if ( ! $expired_date ) {
			return true;
		}

		return $expired_date < time();
	}

	/**
	 * Sets feedback expired date.
	 *
	 * @param string $plugin Path to the plugin file relative to the plugins directory.
	 *
	 * @return void
	 */
	public static function set_feedback_expired_date( $plugin ) {
		if ( empty( $_GET['frm_feedback_submitted'] ) ) {
			return;
		}
		if ( ! strpos( $plugin, 'formidable.php' ) && ! strpos( $plugin, 'formidable-pro.php' ) ) {
			return;
		}
		update_option( 'frm_feedback_expired', gmdate( 'Y-m-d', strtotime( '+ 1 day' ) ) );
	}

	/**
	 * Enqueues assets.
	 *
	 * @return void
	 */
	public static function enqueue_assets() {
		if ( ! self::is_plugins_page() || ! self::feedback_is_expired() ) {
			return;
		}

		wp_enqueue_script(
			'frm-deactivation-feedback',
			FrmAppHelper::plugin_url() . '/js/admin/deactivation-feedback.js',
			array( 'formidable', 'formidable_dom', 'jquery' ),
			FrmAppHelper::plugin_version(),
			true
		);

		wp_enqueue_style( 'formidable-admin' );

		wp_enqueue_style(
			'frm-deactivation-feedback',
			FrmAppHelper::plugin_url() . '/css/admin/deactivation-feedback.css',
			array( 'formidable-admin' ),
			FrmAppHelper::plugin_version()
		);

		FrmAppHelper::localize_script( 'front' );

		wp_localize_script(
			'frm-deactivation-feedback',
			'FrmDeactivationFeedbackI18n',
			array(
				'skip_text' => __( 'Skip & Deactivate', 'formidable' ),
			)
		);
	}

	/**
	 * Prints footer HTML.
	 *
	 * @return void
	 */
	public static function footer_html() {
		if ( ! self::is_plugins_page() || ! self::feedback_is_expired() ) {
			return;
		}
		?>
		<div id="frm-deactivation-modal" style="display: none;">
			<div class="metabox-holder">
				<div class="postbox">
					<a class="frm-modal-close dismiss" title="<?php esc_attr_e( 'Close', 'formidable' ); ?>">
						<svg class="frmsvg" id="frm_close_icon" viewBox="0 0 20 20" width="18px" height="18px" aria-label="<?php esc_attr_e( 'Close', 'formidable' ); ?>">
							<path d="M16.8 4.5l-1.3-1.3L10 8.6 4.5 3.2 3.2 4.5 8.6 10l-5.4 5.5 1.3 1.3 5.5-5.4 5.5 5.4 1.3-1.3-5.4-5.5 5.4-5.5z"/>
						</svg>
					</a>

					<div class="inside">
						<img id="frm-deactivation-modal-icon" class="frmsvg" src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/logo.svg' ); ?>" alt="" />
						<div id="frm-deactivation-form-wrapper" class="frmapi-form">
							<span class="frm-wait frm_visible_spinner"></span>
						</div>
					</div>
				</div>
			</div>
		</div><!-- End #frm-deactivation-popup -->
		<?php
	}
}
