<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frmcenter">
	<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/leave-email.svg' ); ?>" />
	<h3><?php esc_html_e( 'Check your inbox', 'formidable' ); ?></h3>
	<p><?php esc_html_e( 'Enter code that we\'ve sent to your email address', 'formidable' ); ?></p>
	<div>
		<input id="frm_code_from_email" type="text" placeholder="<?php esc_html_e( 'Code from email', 'formidable' ); ?>" />
	</div>
	<div style="height: 30px;"></div>
</div>
