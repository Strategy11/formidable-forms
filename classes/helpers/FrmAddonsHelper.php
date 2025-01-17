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
 * @since 6.15
 */
class FrmAddonsHelper {
	/**
	 * Stores the result of `FrmFormsHelper::get_plan_required`.
	 *
	 * @var string
	 */
	private static $plan_required;

	/**
	 * Show the CTA to upgrade or renew.
	 *
	 * @since 6.15
	 * @return void
	 */
	public static function show_upgrade_renew_cta() {
		if ( FrmAddonsController::is_license_expired() ) {
			self::show_expired_cta();
			return;
		}

		if ( ! FrmAppHelper::pro_is_connected() ) {
			self::show_lite_cta();
			return;
		}

		if ( 'elite' !== FrmAddonsController::license_type() ) {
			self::show_elite_cta();
		}
	}

	/**
	 * Show 'Renew' banner for expired users.
	 *
	 * @since 6.15
	 * @return void
	 */
	private static function show_expired_cta() {
		FrmTipsHelper::show_admin_cta(
			array(
				'class'       => 'frm-gradient',
				'icon'        => 'frmfont frm_speaker_icon',
				'title'       => esc_html__( 'Unlock Add-on library', 'formidable' ),
				'description' => esc_html__( 'Renew your subscription today and access our library of add-ons to supercharge your forms.', 'formidable' ),
				'link_text'   => esc_html__( 'Renew Now', 'formidable' ),
				'link_url'    => FrmAddonsController::is_license_expired(),
				'id'          => 'frm-renew-subscription-banner',
			)
		);
	}

	/**
	 * Show 'Upgrade to Pro' banner for users not connected to Pro.
	 *
	 * @since 6.15
	 * @return void
	 */
	private static function show_lite_cta() {
		FrmTipsHelper::show_admin_cta(
			array(
				'class'       => 'frm-gradient',
				'icon'        => 'frmfont frm_speaker_icon',
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

	/**
	 * Show 'Upgrade' banner for non-elite users.
	 *
	 * @since 6.15
	 * @return void
	 */
	private static function show_elite_cta() {
		FrmTipsHelper::show_admin_cta(
			array(
				'title'       => esc_html__( 'Unlock Even More Add-ons', 'formidable' ),
				'description' => sprintf(
					/* translators: %1$s: Open span tag, %2$s: Close span tag */
					esc_html__( 'Your plan includes %1$s%2$s add-ons. Upgrade to take your forms even farther', 'formidable' ),
					'<span class="frm-addons-available-count">',
					'</span>'
				),
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

	/**
	 * Displays a reconnect link for checking add-ons status.
	 *
	 * @since 6.15
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
	 * @since 6.15
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
			'convertkit'                      => 'convertkit',
		);

		$icon = array_key_exists( $slug, $icons_map ) ? 'frm_' . $icons_map[ $slug ] . '_icon' : 'frm_logo_icon';
		if ( 'ai' === $slug ) {
			$icon = str_replace( '_', '-', $icon );
		}

		FrmAppHelper::icon_by_class( 'frmfont ' . $icon );
	}

	/**
	 * Echo attributes for a given addon.
	 *
	 * @since 6.15
	 *
	 * @param array $addon
	 * @return void
	 */
	public static function add_addon_attributes( $addon ) {
		self::set_plan_required( $addon );

		$attributes = array(
			'tabindex'        => '0',
			'frm-search-text' => strtolower( $addon['title'] . ' ' . esc_html( $addon['excerpt'] ) ),
		);

		// Set 'data-slug' attribute.
		if ( ! empty( $addon['slug'] ) ) {
			$attributes['data-slug'] = $addon['slug'];
		}

		// Set 'data-categories' attribute.
		if ( ! empty( $addon['category-slugs'] ) ) {
			$attributes['data-categories'] = implode( ',', $addon['category-slugs'] );
		}

		$attributes['class'] = self::prepare_single_addon_classes( $addon );

		FrmAppHelper::array_to_html_params( $attributes, true );
	}

	/**
	 * Add classes for a given addon.
	 *
	 * @since 6.15
	 *
	 * @param array $addon
	 * @return string
	 */
	private static function prepare_single_addon_classes( $addon ) {
		$class_names   = array( 'frm-card-item frm-flex-col' );
		$class_names[] = 'plugin-card-' . $addon['slug'];
		$class_names[] = 'frm-addon-' . $addon['status']['type'];

		if ( self::is_locked() ) {
			$class_names[] = 'frm-locked-item';
		}

		return implode( ' ', $class_names );
	}

	/**
	 * Check if a given addon is locked.
	 *
	 * @return bool
	 */
	public static function is_locked() {
		return self::$plan_required || ! FrmAppHelper::pro_is_installed();
	}

	/**
	 * Set the required plan for the given addon.
	 *
	 * @note
	 * Because the `FrmFormsHelper::get_plan_required` changes $addon by reference,
	 * we save the result inside a static field called `$plan_required`.
	 *
	 * @since 6.15
	 *
	 * @param array $addon The addon array that will be modified by reference.
	 * @return void
	 */
	private static function set_plan_required( $addon ) {
		self::$plan_required = FrmFormsHelper::get_plan_required( $addon );
	}

	/**
	 * Get the required plan.
	 *
	 * @since 6.15
	 *
	 * @return false|string
	 */
	public static function get_plan() {
		return self::$plan_required;
	}

	/**
	 * Shows five star rating, used on Views page if only Lite plugins are installed.
	 *
	 * @since 6.17
	 *
	 * @param string $color Star color.
	 * @return void
	 */
	public static function show_five_star_rating( $color = 'black' ) {
		$icon = file_get_contents( FrmAppHelper::plugin_path() . '/images/star.svg' );
		?>
		<span style="color: <?php echo esc_attr( $color ); ?>;">
			<?php
			for ( $i = 0; $i < 5; $i++ ) {
				echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
		</span>
		<?php
	}

	/**
	 * Shows the guarantee icon.
	 *
	 * @since 6.17
	 *
	 * @return void
	 */
	public static function guarantee_icon() {
		?>
		<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/guarantee.svg' ); ?>" alt="" />
		<?php
	}

	/**
	 * Gets reviews text.
	 *
	 * @since 6.17
	 *
	 * @param string $count Review count.
	 * @param string $site  Site name.
	 * @return string
	 */
	public static function get_reviews_text( $count, $site ) {
		return sprintf(
			// Translators: %1$s is the number of reviews, %2$s is the site name.
			__( 'Based on %1$s reviews on %2$s', 'formidable' ),
			$count,
			$site
		);
	}
}
