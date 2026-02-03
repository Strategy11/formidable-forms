<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-dashboard-banner frm-p-md">
	<span class="frm-dashboard-banner-bg-shape"></span>
	<span class="frm-dashboard-banner-close"><?php FrmAppHelper::icon_by_class( 'frmfont frm_close_icon' ); ?></span>
	<div class="frm-flex-box frm-align-center">
		<?php FrmAppHelper::icon_by_class( 'frmfont frm_speaker_icon' ); ?>
		<span class="frm-vertical-line"></span>
		<div>
			<h4><?php esc_html_e( 'Welcome to Formidable Forms', 'formidable' ); ?> 🎉</h4>
			<p><?php esc_html_e( 'Whether you\'re looking to create simple contact forms or complex survey forms, Formidable Forms has you covered.', 'formidable' ); ?></p>
		</div>
		<div class="frm-shrink-0">
			<a target="_blank" href="<?php echo esc_url( FrmAppHelper::admin_upgrade_link( '', 'features' ) ); ?>" class="frm-button-secondary"><?php esc_html_e( 'Check All Features', 'formidable' ); ?></a>
		</div>
	</div>
</div>
