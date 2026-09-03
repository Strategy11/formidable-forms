<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

// Check if this is being called from within a list (no header needed)
$is_in_list = isset( $this ) && method_exists( $this, 'no_items' );
?>

<?php if ( ! $is_in_list ) : ?>
<div class="frm_wrap">
	<?php
	FrmAppHelper::get_admin_header(
		array(
			'label' => __( 'Payments', 'formidable' ),
		)
	);
	?>
	<div class="wrap">
<?php endif; ?>

	<div class="frm-no-payments-placeholder">
		<div class="frm-no-payments-content">
			<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/no-payments.svg' ); ?>" alt="" class="frm-no-payments-image" />
			<h2 class="frm-no-payments-title"><?php esc_html_e( 'Start accepting payments', 'formidable' ); ?></h2>
			<p class="frm-no-payments-description"><?php esc_html_e( 'Connect a payment gateway to collect payments right through your forms. Pick one to get started.', 'formidable' ); ?></p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-settings&t=stripe_settings' ) ); ?>" class="button button-primary frm-button-primary">
				<?php esc_html_e( 'Go to Payment Settings', 'formidable' ); ?>
			</a>
		</div>
	</div>

<?php if ( ! $is_in_list ) : ?>
	</div>
</div>
<?php endif; ?>
