<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-coupons-upsell-wrapper">
	<h3><?php esc_html_e( 'Coupons', 'formidable' ); ?></h3>

	<p>
		<?php esc_html_e( 'Offer discounts with custom coupon codes. Add a Coupon field to your payment form and set percentage or fixed discounts, usage limits, and availability. Requires a Pro license or higher.', 'formidable' ); ?>
	</p>

	<div class="frm-coupons-upsell frm-main-coupons-upsell">
		<?php // TODO: Set a good alt text. ?>
		<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/coupons/main-upsell.png' ); ?>" alt="Coupons" />
	</div>

	<div class="frm_grid_container frm-secondary-coupons-upsells">
		<div class="frm6 frm-coupons-upsell frm-left-coupons-upsell">
			<?php // TODO: Set a good alt text. ?>
			<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/coupons/left-upsell.png' ); ?>" alt="Coupons" />
		</div>
		<div class="frm6 frm-coupons-upsell frm-right-coupons-upsell">
			<?php // TODO: Set a good alt text. ?>
			<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/coupons/right-upsell.png' ); ?>" alt="Coupons" />
		</div>
	</div>

</div>
