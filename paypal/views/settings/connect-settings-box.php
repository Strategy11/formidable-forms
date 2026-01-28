<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$tag_classes = $connected ? 'frm-lt-green-tag' : 'frm-grey-tag';
?>
<div class="frm-card-item frm6">
	<div class="frm-flex-col" style="width: 100%;">
		<div>
			<span style="font-size: var(--text-lg); font-weight: 500; margin-right: 5px;">
				<?php echo $mode === 'test' ? esc_html__( 'Test', 'formidable' ) : esc_html__( 'Live', 'formidable' ); ?>
			</span>
			<div class="frm-meta-tag <?php echo esc_attr( $tag_classes ); ?>" style="font-size: var(--text-sm); font-weight: 600;">
			<?php
			if ( $connected ) {
				FrmAppHelper::icon_by_class( 'frm_icon_font frm_checkmark_icon', array( 'style' => 'width: 10px; position: relative; top: 2px; margin-right: 5px;' ) );
				esc_html_e( 'Connected', 'formidable' );
			} else {
				esc_html_e( 'Not configured', 'formidable' );
			}
			?>
			</div>
		</div>
		<div style="margin-top: 5px; flex: 1;">
			<?php
			if ( 'live' === $mode ) {
				esc_html_e( 'Live version to process real customer transactions', 'formidable' );
			} else {
				esc_html_e( 'Simulate payments and ensure everything works smoothly before going live.', 'formidable' );
			}
			?>
		</div>
		<?php FrmPayPalLiteConnectHelper::render_seller_status( $mode ); ?>
		<div class="frm-card-bottom">
			<?php if ( $connected ) { ?>
				<a id="frm_disconnect_paypal_<?php echo esc_attr( $mode ); ?>" class="button-secondary frm-button-secondary" href="#">
					<?php esc_html_e( 'Disconnect', 'formidable' ); ?>
				</a>
			<?php } else { ?>
				<a class="frm-connect-paypal-with-oauth button-secondary frm-button-secondary" data-mode="<?php echo esc_attr( $mode ); ?>" href="#">
					<?php esc_html_e( 'Connect', 'formidable' ); ?>
				</a>
			<?php } ?>
		</div>
	</div>
</div>
