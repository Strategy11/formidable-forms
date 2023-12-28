<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div style="margin-bottom:15px;" data-test-mode="<?php echo $test ? 1 : 0; ?>">
	<span style="margin-bottom: 5px;min-width:40px;display: inline-block;">
		<b style="color: var(--medium-grey);"><?php echo esc_html( $title ); ?></b>
	</span>
	<?php if ( $account_id ) { ?>
		<?php if ( $connected ) { ?>
			<?php FrmAppHelper::icon_by_class( 'frmfont frm_checkmark_icon frm-yes' ); ?>
			<?php esc_html_e( 'Connected', 'formidable' ); ?>
			&nbsp; &nbsp;
		<?php } else { ?>
			<strong>
				<span class="frm-nope" style="color:#B94A48">&#10008;</span>
				<?php esc_html_e( 'Not connected!', 'formidable' ); ?>
			</strong>
			<br/><br/>
			<a id="frm_reauth_stripe" class="button-primary frm-button-primary" href="#">
				<?php FrmStrpLiteConnectHelper::stripe_icon(); ?> &nbsp;
				<?php esc_html_e( 'Finish Stripe Setup', 'formidable' ); ?>
			</a>
			or
		<?php } ?>
		<a id="frm_disconnect_stripe" href="#" style="font-size:13px"><?php esc_html_e( 'Disconnect', 'formidable' ); ?></a>
	<?php } else { ?>
		<br/><br/>
		<a id="frm_connect_with_oauth" class="button-primary frm-button-primary">
			<?php FrmStrpLiteConnectHelper::stripe_icon(); ?> &nbsp;
			<?php esc_html_e( 'Connect to Stripe', 'formidable' ); ?>
		</a>
		<?php
	}//end if
	?>
</div>
