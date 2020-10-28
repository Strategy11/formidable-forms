<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frmcenter">
	<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/leave-email.svg' ); ?>" />
	<h3><?php esc_html_e( 'Check your inbox', 'formidable' ); ?></h3>
	<p><?php esc_html_e( 'Enter code that we\'ve sent to your email address', 'formidable' ); ?></p>
	<div style="position: relative; width: 600px; margin: 0 auto;">
		<input id="frm_code_from_email" type="text" placeholder="<?php esc_attr_e( 'Code from email', 'formidable' ); ?>" />
		<span id="frm_code_from_email_error" class="frm_hidden" style="position: absolute; right: 71px; top: 9px; color: #973937;">
			<span frm-error="custom"></span>
			<span frm-error="wrong-code"><?php esc_html_e( 'Verification code is wrong', 'formidable' ); ?></span>
			<span frm-error="empty"><?php esc_html_e( 'Verification code is empty', 'formidable' ); ?></span>
		</span>
	</div>
	<div id="frm_code_from_email_options" class="frm_hidden">
		<a href="#" id="frm-change-email-address"><?php esc_html_e( 'Change email address', 'formidable' ); ?></a>
		<span>&nbsp;&nbsp;|&nbsp;&nbsp;</span>
		<a href="#" id="frm-resend-code"><?php esc_html_e( 'Resend code', 'formidable' ); ?></a>
	</div>
	<div style="height: 30px;"></div>
</div>
