<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmStrpLiteAppHelper {

	/**
	 * @var FrmStrpLiteSettings|null
	 */
	private static $settings;

	/**
	 * @return string
	 */
	public static function plugin_path() {
		return FrmAppHelper::plugin_path() . '/stripe/';
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
		return FrmAppHelper::plugin_url() . '/stripe/';
	}

	/**
	 * @return bool
	 */
	public static function is_debug() {
		return defined( 'WP_DEBUG' ) && WP_DEBUG;
	}

	/**
	 * @param string $function
	 * @param array  ...$params
	 * @return mixed
	 */
	public static function call_stripe_helper_class( $function, ...$params ) {
		if ( self::should_use_stripe_connect() && is_callable( "FrmStrpLiteConnectApiAdapter::$function" ) ) {
			return FrmStrpLiteConnectApiAdapter::$function( ...$params );
		}
		return false;
	}

	/**
	 * @return bool true if we're using connect (versus the legacy integration).
	 */
	public static function should_use_stripe_connect() {
		if ( ! class_exists( 'FrmStrpLiteConnectApiAdapter' ) ) {
			require __DIR__ . '/FrmStrpLiteConnectApiAdapter.php';
		}
		return FrmStrpLiteConnectApiAdapter::initialize_api();
	}

	/**
	 * @return bool true if either connect or the legacy integration is set up.
	 */
	public static function stripe_is_configured() {
		return self::call_stripe_helper_class( 'initialize_api' );
	}

	/**
	 * If test mode is running, save the id somewhere else
	 *
	 * @return string
	 */
	public static function get_customer_id_meta_name() {
		$meta_name = '_frmstrp_customer_id';
		if ( 'test' === self::active_mode() ) {
			$meta_name .= '_test';
		}
		return $meta_name;
	}

	/**
	 * @return FrmStrpLiteSettings
	 */
	public static function get_settings() {
		if ( ! isset( self::$settings ) ) {
			self::$settings = new FrmStrpLiteSettings();
		}
		return self::$settings;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'live'|'test'
	 */
	public static function active_mode() {
		$settings = self::get_settings();
		return $settings->settings->test_mode ? 'test' : 'live';
	}

	/**
	 * Add education about Stripe fees.
	 *
	 * @return void
	 */
	public static function fee_education( $medium = 'tip' ) {
		$license_type = FrmAddonsController::license_type();
		if ( in_array( $license_type, array( 'elite', 'business' ), true ) ) {
			return;
		}

		FrmTipsHelper::show_tip(
			array(
				'link'  => array(
					'content' => 'stripe-fee',
					'medium'  => $medium,
				),
				'tip'   => 'Pay as you go pricing: 3% fee per-transaction + Stripe fees.',
				'call'  => __( 'Upgrade to save on fees.', 'formidable' ),
				'class' => 'frm-light-tip',
			),
			'p'
		);
	}

	/**
	 * Show a message to connect Stripe with link to settings.
	 *
	 * @return void
	 */
	public static function not_connected_warning() {
		?>
		<div class="frm_warning_style frm-with-icon">
			<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_alert_icon', array( 'style' => 'width:24px' ) ); ?>
			<span>
				<?php
				/* translators: %1$s: Link HTML, %2$s: End link */
				printf( esc_html__( 'Credit Cards will not work without %1$sconnecting Stripe%2$s first.', 'formidable' ), '<a href="?page=formidable-settings&t=stripe_settings" target="_blank">', '</a>' );
				?>
			</span>
		</div>
		<?php
	}
}
