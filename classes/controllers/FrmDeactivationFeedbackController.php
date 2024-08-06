<?php
/**
 * Deactivation feedback controller
 *
 * @package Formidable
 * @since x.x
 */

class FrmDeactivationFeedbackController {

	private static function is_plugins_page() {
		return 'plugins' === get_current_screen()->id;
	}

	public static function enqueue_assets() {
		if ( ! self::is_plugins_page() ) {
			return;
		}
		wp_enqueue_script( 'frm-deactivation-feedback', FrmAppHelper::plugin_url() . '/js/admin/deactivation-feedback.js', array( 'formidable', 'formidable_dom', 'jquery' ), FrmAppHelper::plugin_version(), true );
        wp_enqueue_style( 'formidable-admin' );

		FrmAppHelper::localize_script( 'front' );
	}

	public static function footer_html() {
		if ( ! self::is_plugins_page() ) {
			return;
		}
		?>
		<div id="frmapi-feedback" class="frmapi-form" data-url="https://sandbox.formidableforms.com/api/wp-json/frm/v2/forms/feedback?return=html&exclude_script=jquery&exclude_style=formidable-css">
			<span class="frm-wait frm_visible_spinner"></span>
		</div>
		<div id="frm-deactivation-popup" class="">
			<div class="metabox-holder">
				<div class="postbox">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm_logo_icon' ); ?>
                    <strong><?php esc_html_e( 'Quick Feedback', 'formidable' ); ?></strong>
				</div>
			</div>
		</div><!-- End #frm-deactivation-popup -->
		<?php
	}
}
