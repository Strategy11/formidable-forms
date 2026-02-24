<?php
/**
 * Pricing Fields Modal.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm-pricing-fields-modal" class="frm_wrap frm-form-templates-modal-item frm_hidden">
	<div class="frm_modal_top frm-mt-xs">
		<div class="frm-modal-title">
			<h3><?php esc_html_e( 'Start Accepting Payments Today!', 'formidable' ); ?></h3>
		</div>
		<p>
			<?php esc_html_e( 'We\'ve unlocked Product, Quantity, and Total fields for Lite users! You can now transform your forms into checkout pages. To start collecting revenue, simply connect your preferred payment gateway (Stripe, PayPal, or Square) in your settings.', 'formidable' ); ?>
		</p>


		<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/upsell/pricing-fields.png' ); ?>" />

		<div class="frm-modal-actions frm-mt-md">
			<a href="<?php echo esc_url( FrmAppHelper::admin_upgrade_link( array( 'campaign' => 'pricing_fields_modal', 'content' => 'upgrade_button' ) ) ); ?>" class="button button-primary frm-button-primary">
				<?php esc_html_e( 'I\'ll do it later', 'formidable' ); ?>
			</a>
			<button type="button" class="button frm-cancel-modal">
				<?php esc_html_e( 'Setup Payments Now', 'formidable' ); ?>
			</button>
		</div>
	</div>
</div>
