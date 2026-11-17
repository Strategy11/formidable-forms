<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmPayPalLiteAppHelper {

	/**
	 * @var FrmPayPalLiteSettings|null
	 */
	private static $settings;

	/**
	 * @return string
	 */
	public static function plugin_path() {
		return FrmAppHelper::plugin_path() . '/paypal/';
	}

	/**
	 * @return string
	 */
	public static function plugin_folder() {
		return basename( self::plugin_path() );
	}

	/**
	 * @return string
	 */
	public static function plugin_url() {
		return FrmAppHelper::plugin_url() . '/paypal/';
	}

	/**
	 * @return FrmPayPalLiteSettings
	 */
	public static function get_settings() {
		if ( ! isset( self::$settings ) ) {
			self::$settings = new FrmPayPalLiteSettings();
		}
		return self::$settings;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'live'|'test'
	 */
	public static function active_mode() {
		return self::get_settings()->settings->test_mode ? 'test' : 'live';
	}

	/**
	 * Get the merchant name to show in the Apple Pay sheet.
	 *
	 * Apple rejects the merchant session when the name has characters outside ASCII (like the
	 * en dash in "Page – Site" page titles), so the brand name is reduced to plain ASCII, max 64 characters.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
	public static function get_wallet_display_name() {
		$name = wp_specialchars_decode( wp_strip_all_tags( FrmPayPalLiteConnectHelper::get_brand_name() ), ENT_QUOTES );
		$name = str_replace( array( '–', '—' ), '-', $name );
		$name = remove_accents( $name );
		$name = preg_replace( '/[^\x20-\x7E]/', '', $name );
		$name = trim( substr( $name, 0, 64 ) );

		// Not translated on purpose: a translation could bring back non-ASCII characters.
		return '' === $name ? 'Payment' : $name;
	}

	/**
	 * Get PayPal button style configuration from form action settings
	 *
	 * @param object $form_action The form action object containing post_content.
	 *
	 * @return array PayPal button style configuration
	 */
	public static function get_paypal_button_style( $form_action ) {
		$button_layout        = $form_action->post_content['button_layout'] ?? 'vertical';
		$button_color         = $form_action->post_content['button_color'] ?? 'default';
		$button_label         = $form_action->post_content['button_label'] ?? 'paypal';
		$button_border_radius = $form_action->post_content['button_border_radius'] ?? 10;

		return array(
			'style' => array(
				'layout'       => $button_layout,
				'color'        => $button_color,
				'shape'        => 'rect',
				// Default shape, could be made configurable
				'label'        => $button_label,
				'messaging'    => true,
				// Show messaging under button
				'borderRadius' => (int) $button_border_radius,
			),
		);
	}

	/**
	 * Add education about PayPal fees.
	 *
	 * PayPal Commerce is not included in a grandfathered license, so its fees
	 * always apply there even though Stripe fees do not.
	 *
	 * @param string             $content UTM Content for the admin upgrade link.
	 * @param array|false|string $gateway Gateway or list of gateways this applies to.
	 *
	 * @return void
	 */
	public static function fee_education( $content = 'tip', $gateway = false ) {
		if ( ! FrmAddonsController::payment_fees_apply( 'paypal' ) ) {
			return;
		}

		$classes = 'frm-light-tip show_paypal';

		if ( $gateway && ! array_intersect( (array) $gateway, array( 'paypal' ) ) ) {
			$classes .= ' frm_hidden';
		}

		FrmTipsHelper::show_tip(
			array(
				'link'  => array(
					'campaign' => 'paypal-fee',
					'content'  => $content,
				),
				'tip'   => 'Pay as you go pricing: 3% fee per-transaction + PayPal fees.',
				'call'  => __( 'Upgrade to save on fees.', 'formidable' ),
				'class' => $classes,
			),
			'p'
		);
	}
}
