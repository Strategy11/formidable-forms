<?php
/**
 * Add-Ons helper class.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Provides helper functions for managing add-ons in the admin area.
 *
 * @since x.x
 */
class FrmAddonsHelper {

	/**
	 * Show the CTA to upgrade or renew.
	 *
	 * @since 6.7
	 *
	 * @param array $args {
	 *    Arguments for the CTA.
	 *
	 *    @type string $upgrade_link Upgrade link URL.
	 *    @type string $renew_link Renew link URL.
	 * }
	 * @return void
	 */
	public static function show_upgrade_renew_cta( $args ) {
		// Show 'renew' banner for expired users.
		if ( $args['expired'] ) {
			FrmTipsHelper::show_admin_cta(
				array(
					'title'       => esc_html__( 'Unlock Add-on library', 'formidable' ),
					'description' => esc_html__( 'Renew your subscription today and access our library of add-ons to supercharge your forms.', 'formidable' ),
					'link_text'   => esc_html__( 'Renew Now', 'formidable' ),
					'link_url'    => $args['renew_link'],
					'id'          => 'frm-renew-subscription-banner',
				)
			);
			return;
		}

		// Show 'upgrade' banner for non-elite users.
		if ( ! in_array( FrmAddonsController::license_type(), array( 'elite' ), true ) ) {
			FrmTipsHelper::show_admin_cta(
				array(
					'title'       => esc_html__( 'Unlock Add-on library', 'formidable' ),
					'description' => esc_html__( 'Upgrade to Pro and access our library of add-ons to supercharge your forms.', 'formidable' ),
					'link_text'   => esc_html__( 'Upgrade to PRO', 'formidable' ),
					'link_url'    => $args['upgrade_link'],
					'id'          => 'frm-upgrade-banner',
				)
			);
		}
	}


	public static function get_reconnect_link() {
		if ( FrmAppHelper::pro_is_connected() ) {
			return;
		}
		?>
		<p class="frm-flex frm-gap-xs">
			<span><?php esc_html_e( 'Missing add-ons?', 'formidable' ); ?></span>
			<a href="#" id="frm_reconnect_link" class="frm-show-authorized" data-refresh="1">
				<?php esc_html_e( 'Check now for a recent upgrade or renewal', 'formidable' ); ?>
			</a>
		</p>
		<?php
	}
}
