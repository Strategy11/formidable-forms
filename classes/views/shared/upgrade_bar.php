<?php
/**
 * Upgrade bar view
 *
 * @since x.x
 *
 * @var string $cta_text    Call-to-action text for the upgrade banner
 * @var string $upgrade_link URL for the upgrade link
 * @var array  $utm         UTM parameters for tracking
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-upgrade-bar">
	<div class="frm-upgrade-bar-inner">
		<?php
		$cta_text = FrmSalesApi::get_best_sale_value( 'lite_banner_cta_text' );

		if ( ! $cta_text ) {
			$cta_text = __( 'upgrading to PRO', 'formidable' );
		}

		$upgrade_link = FrmSalesApi::get_best_sale_value( 'lite_banner_cta_link' );
		$utm          = array(
			'campaign' => 'settings-license',
			'content'  => 'lite-banner',
		);

		$upgrade_link = $upgrade_link ? FrmAppHelper::maybe_add_missing_utm( $upgrade_link, $utm ) : FrmAppHelper::admin_upgrade_link( $utm );

		printf(
			/* translators: %1$s: Start link HTML, %2$s: CTA text ("upgrading to PRO" by default), %3$s: End link HTML */
			esc_html__( 'You\'re using Formidable Forms Lite. To unlock more features consider %1$s%2$s%3$s.', 'formidable' ),
			'<a href="' . esc_url( $upgrade_link ) . '">',
			esc_html( $cta_text ),
			'</a>'
		);
		?>
	</div>
</div>
