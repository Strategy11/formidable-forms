<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm_wrap">
	<div class="frm_page_container">
		<?php
		FrmAppHelper::get_admin_header(
			array(
				'label'       => __( 'Reports', 'formidable' ),
				'form'        => $form,
				'close'       => $form ? admin_url( 'admin.php?page=formidable&frm_action=reports&form=' . $form->id ) : '',
			)
		);
		?>
		<div class="frmcenter" style="margin-top:10vh">
			<img src="<?php echo esc_attr( FrmAppHelper::plugin_url() . '/images/reports.svg' ); ?>" alt="<?php esc_attr_e( 'View reports', 'formidable' ); ?>" height="243" />
			<h2><?php esc_html_e( 'Get Live Graphs and Reports', 'formidable' ); ?></h2>
			<p style="max-width:400px;margin:20px auto">
				<?php esc_html_e( 'Get more insight for surveys, polls, daily contacts, and more.', 'formidable' ); ?>
			</p>
			<a class="button button-primary frm-button-primary" href="<?php echo esc_url( FrmAppHelper::admin_upgrade_link( 'reports-info' ) ); ?>" target="_blank" rel="noopener">
				<?php esc_html_e( 'Upgrade Now', 'formidable' ); ?>
			</a>
		</div>
	</div>
</div>
