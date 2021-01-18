<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm_license_top" class="frm_unauthorized_box">
<p id="frm-connect-btns" class="frm-show-unauthorized">
	<a href="<?php echo esc_url( FrmAddonsController::connect_link() ); ?>" class="button-primary frm-button-primary">
		<?php esc_html_e( 'Connect an Account', 'formidable' ); ?>
	</a>
	or
	<a href="<?php echo esc_url( FrmAppHelper::admin_upgrade_link( 'settings-license' ) ); ?>" target="_blank" class="button-secondary frm-button-secondary">
		<?php esc_html_e( 'Get Formidable Now', 'formidable' ); ?>
	</a>
</p>

<div id="frm-using-lite" class="frm-show-unauthorized">
<p>You're using Formidable Forms Lite - no license needed. Enjoy! 🙂</p>
<p>
		<?php
		printf(
			/* translators: %1$s: Start link HTML, %2$s: End link HTML */
			esc_html__( 'To unlock more features consider %1$supgrading to PRO%2$s.', 'formidable' ),
			'<a href="' . esc_url( FrmAppHelper::admin_upgrade_link( 'settings-license' ) ) . '">',
			'</a>'
		);
		?>
</p>
</div>
</div>

<div class="frm_pro_license_msg frm_hidden"></div>
