<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frmcenter">
	<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/leave-email.svg' ); ?>" />
	<h3><?php esc_html_e( 'Get powerful templates for free', 'formidable' ); ?></h3>
	<p><?php esc_html_e( 'Leave your email address and get free access to a powerful predesign form templates which will cover most of your needs', 'formidable' ); ?></p>
	<div style="position: relative; width: 600px; margin: 0 auto;">
		<span class="frm-with-left-icon">
			<?php FrmAppHelper::icon_by_class( 'frmfont frm_email_icon' ); ?>
			<input id="frm_leave_email" type="email" placeholder="<?php esc_html_e( 'Enter your email', 'formidable' ); ?>" value="<?php echo esc_attr( $user->user_email ); ?>" />
		</span>
		<span id="frm_leave_email_error" class="frm_hidden" style="position: absolute; right: 71px; top: 7px; color: #973937;">
			<span frm-error="invalid"><?php esc_html_e( 'Email is invalid', 'formidable' ); ?></span>
			<span frm-error="empty"><?php esc_html_e( 'Email is empty', 'formidable' ); ?></span>
		</span>
		<div class="clear"></div>
	</div>
	<div style="height: 30px;"></div>
</div>
