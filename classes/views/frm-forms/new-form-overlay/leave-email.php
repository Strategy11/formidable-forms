<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frmcenter">
	<div id="frmapi-email-form" class="frmapi-form frm_hidden" data-url="<?php echo esc_attr( $url ); ?>">
		<span class="frm-wait"></span>
	</div>
	<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/leave-email.svg' ); ?>" />
	<h3><?php echo esc_html( $title ); ?></h3>
	<p>
		<?php
		echo wp_kses(
			wpautop( esc_html( $description ) ),
			array(
				'p' => true,
				'br' => true,
			)
		);
		?>
	</p>
	<div id="frm_leave_email_wrapper">
		<span class="frm-with-left-icon">
			<?php FrmAppHelper::icon_by_class( 'frmfont frm_email_icon' ); ?>
			<input id="frm_leave_email" type="email" placeholder="<?php esc_html_e( 'Enter your email', 'formidable' ); ?>" value="<?php echo esc_attr( $user->user_email ); ?>" />
		</span>
		<span id="frm_leave_email_error" class="frm_hidden">
			<span frm-error="invalid"><?php esc_html_e( 'Email is invalid', 'formidable' ); ?></span>
			<span frm-error="empty"><?php esc_html_e( 'Email is empty', 'formidable' ); ?></span>
		</span>
		<div class="clear"></div>
	</div>
	<div style="height: 30px;"></div>
</div>
