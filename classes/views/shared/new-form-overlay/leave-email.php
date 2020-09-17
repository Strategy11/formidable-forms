<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frmcenter">
	<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/leave-email.svg' ); ?>" />
	<h3><?php esc_html_e( 'Get powerful templates for free', 'formidable' ); ?></h3>
	<p><?php esc_html_e( 'Leave your email address and get free access to a powerful predesign form templates which will cover most of your needs', 'formidable' ); ?></p>
	<div>
		<span class="frm-with-left-icon">
			<?php FrmAppHelper::icon_by_class( 'frmfont frm_email_icon' ); ?>
			<input id="frm_leave_email" type="email" placeholder="<?php esc_html_e( 'Enter your email', 'formidable' ); ?>" />
		</span>
		<div class="clear"></div>
	</div>
	<div style="height: 30px;"></div>
</div>

