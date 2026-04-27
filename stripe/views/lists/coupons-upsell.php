<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$images_folder_url = FrmAppHelper::plugin_url() . '/images/coupons/';
?>
<div class="frm-coupons-upsell-wrapper">
	<h2><?php esc_html_e( 'Coupons', 'formidable' ); ?></h2>

	<p>
		<?php esc_html_e( 'Offer discounts with custom coupon codes. Add a Coupon field to your payment form and set percentage or fixed discounts, usage limits, and availability. Requires a Pro license or higher.', 'formidable' ); ?>
	</p>

	<?php
	FrmAddonsController::conditional_action_button(
		'coupons',
		array(
			'campaign' => 'coupons-upsell',
			'content'  => 'coupons-upsell-button',
		)
	);
	?>

	<div class="frm-coupons-upsell frm-main-coupons-upsell">
		<img src="<?php echo esc_url( $images_folder_url . 'main-upsell.png' ); ?>" alt="<?php esc_attr_e( 'New Coupon Settings', 'formidable' ); ?>" />
	</div>

	<div class="frm_grid_container frm-secondary-coupons-upsells">
		<div class="frm6 frm-coupons-upsell frm-left-coupons-upsell">
			<img src="<?php echo esc_url( $images_folder_url . 'left-upsell.png' ); ?>" alt="<?php esc_attr_e( 'Coupon List', 'formidable' ); ?>" />
		</div>
		<div class="frm6 frm-coupons-upsell frm-right-coupons-upsell">
			<img src="<?php echo esc_url( $images_folder_url . 'right-upsell.png' ); ?>" alt="<?php esc_attr_e( 'Coupon Field Settings', 'formidable' ); ?>" />
		</div>
	</div>

</div>
