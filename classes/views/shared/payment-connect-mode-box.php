<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Shared view for a payment gateway connect mode box (Live / Test).
 *
 * Required variables:
 * @var string        $mode                   'live' or 'test'.
 * @var bool          $connected              Whether the gateway is connected for this mode.
 * @var string        $column_class           CSS grid column class (e.g. 'frm4', 'frm6').
 * @var string        $gateway_slug           Gateway identifier (e.g. 'paypal', 'square').
 *                                            Used to build the connect button class
 *                                            (frm-connect-{slug}-with-oauth) and the
 *                                            disconnect anchor id (frm_disconnect_{slug}_{mode}).
 * @var string        $icon_font_class        Font class for the checkmark icon (e.g. 'frmfont', 'frm_icon_font').
 * @var callable|null $extra_content_callback Optional callback invoked between the description
 *                                            and the action button. Receives $mode as its only arg.
 */

$tag_classes = $connected ? 'frm-lt-green-tag' : 'frm-grey-tag';
?>
<div class="frm-card-item <?php echo esc_attr( $column_class ); ?>">
	<div class="frm-flex-col" style="width: 100%;">
		<div>
			<span style="font-size: var(--text-lg); font-weight: 500; margin-right: 5px;">
				<?php echo 'test' === $mode ? esc_html__( 'Test', 'formidable' ) : esc_html__( 'Live', 'formidable' ); ?>
			</span>
			<div class="frm-meta-tag <?php echo esc_attr( $tag_classes ); ?>" style="font-size: var(--text-sm); font-weight: 600;">
				<?php
				if ( $connected ) {
					FrmAppHelper::icon_by_class( $icon_font_class . ' frm_checkmark_icon', array( 'style' => 'width: 10px; position: relative; top: 2px; margin-right: 5px;' ) );
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
		<?php
		if ( is_callable( $extra_content_callback ) ) {
			$extra_content_callback( $mode );
		}
		?>
		<div class="frm-card-bottom">
			<?php if ( $connected ) { ?>
				<a id="frm_disconnect_<?php echo esc_attr( $gateway_slug . '_' . $mode ); ?>" class="button-secondary frm-button-secondary" href="#">
					<?php esc_html_e( 'Disconnect', 'formidable' ); ?>
				</a>
			<?php } else { ?>
				<a class="frm-connect-<?php echo esc_attr( $gateway_slug ); ?>-with-oauth button-secondary frm-button-secondary" data-mode="<?php echo esc_attr( $mode ); ?>" href="#">
					<?php esc_html_e( 'Connect', 'formidable' ); ?>
				</a>
			<?php } ?>
		</div>
	</div>
</div>
