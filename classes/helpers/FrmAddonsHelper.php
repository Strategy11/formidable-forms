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
	 * @since x.x
	 * @return void
	 */
	public static function show_upgrade_renew_cta() {
		// Show 'renew' banner for expired users.
		if ( FrmAddonsController::is_license_expired() ) {
			FrmTipsHelper::show_admin_cta(
				array(
					'title'       => esc_html__( 'Unlock Add-on library', 'formidable' ),
					'description' => esc_html__( 'Renew your subscription today and access our library of add-ons to supercharge your forms.', 'formidable' ),
					'link_text'   => esc_html__( 'Renew Now', 'formidable' ),
					'link_url'    => FrmAddonsController::is_license_expired(),
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
					'link_url'    => FrmAppHelper::admin_upgrade_link(
						array(
							'medium'  => 'addons',
							'content' => 'upgrade-cta',
						)
					),
					'id'          => 'frm-upgrade-banner',
				)
			);
		}
	}

	/**
	 * Show the CTA for activating Formidable Forms Pro.
	 *
	 * @since x.x
	 * @return void
	 */
	public static function show_pro_inactive_cta() {
		global $frm_vars;
		if ( ! FrmAppHelper::pro_is_included() || $frm_vars['pro_is_authorized'] ) {
			return;
		}

		FrmTipsHelper::show_admin_cta(
			array(
				'title'       => esc_html__( 'Formidable Forms Pro installed, but not yet activated.', 'formidable' ),
				'description' => esc_html__( 'Add your license key now to start enjoying all the premium features.', 'formidable' ),
				'link_text'   => esc_html__( 'Go to Settings', 'formidable' ),
				'link_url'    => admin_url( 'admin.php?page=formidable-settings' ),
				'target'      => '_self',
				'id'          => 'frm-pro-inactive-banner',
				'class'       => 'frm-cta-red',
			)
		);
	}

	/**
	 * Displays a reconnect link for checking add-ons status.
	 *
	 * @since x.x
	 * @return void
	 */
	public static function get_reconnect_link() {
		if ( ! FrmAppHelper::pro_is_connected() ) {
			return;
		}
		?>
		<p class="frm-py-2xs">
			<span class="frm-font-medium frm-text-grey-700"><?php esc_html_e( 'Missing add-ons?', 'formidable' ); ?></span>
			<a href="#" id="frm_reconnect_link" class="frm-show-authorized frm-font-semibold" data-refresh="1">
				<?php esc_html_e( 'Check now for a recent upgrade or renewal', 'formidable' ); ?>
			</a>
		</p>
		<?php
	}

	/**
	 * Get the icon for a specific addon.
	 *
	 * @since x.x
	 * @param string $slug The slug of the addon.
	 * @return void
	 */
	public static function get_addon_icon( $slug ) {
		$icons_map = array(
			'acf-forms'                       => 'acfforms',
			'activecampaign-wordpress-plugin' => 'activecampaign',
			'ai'                              => 'ai-form',
			'authorize-net-aim'               => 'authorize',
			'aweber'                          => 'aweber',
			'bootstrap'                       => 'bootstrap',
			'bootstrap-modal'                 => 'bootstrap',
			'campaign-monitor'                => 'campaignmonitor',
			'constant-contact'                => 'constant_contact',
			'getresponse-wordpress-plugin'    => 'getresponse',
			'google-sheets'                   => 'googlesheets',
			'highrise'                        => 'highrise',
			'hubspot-wordpress'               => 'hubspot',
			'mailchimp'                       => 'mailchimp',
			'mailpoet-newsletters'            => 'mailpoet',
			'paypal-standard'                 => 'paypal',
			'polylang'                        => 'polylang',
			'salesforce'                      => 'salesforcealt',
			'stripe'                          => 'stripealt',
			'twilio'                          => 'twilio',
			'woocommerce'                     => 'woocommerce',
			'zapier'                          => 'zapier',
		);

		$icon = array_key_exists( $slug, $icons_map ) ? 'frm_' . $icons_map[ $slug ] . '_icon' : 'frm_logo_icon';
		if ( 'ai' === $slug ) {
			$icon = str_replace( '_', '-', $icon );
		}

		FrmAppHelper::icon_by_class( 'frmfont ' . $icon );
	}
}
