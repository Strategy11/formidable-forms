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
				'label'       => __( 'Views', 'formidable' ),
				'form'        => $form,
				'close'       => $form ? admin_url( 'admin.php?page=formidable&frm_action=views&form=' . $form->id ) : '',
			)
		);
		?>
		<div class="frmcenter" style="margin-top:10vh">
			<img src="<?php echo esc_attr( FrmAppHelper::plugin_url() . '/images/views.svg' ); ?>" alt="<?php esc_attr_e( 'Create a View', 'formidable' ); ?>" width="403" height="243" />
			<h2><?php esc_html_e( 'Show and Edit Entries with Views', 'formidable' ); ?></h2>
			<p style="max-width:400px;margin:20px auto">
				<?php esc_html_e( 'Bring entries to the front-end of your site for full-featured applications or just to show the content.', 'formidable' ); ?>
			</p>
			<?php FrmAddonsController::conditional_action_button( 'views', 'views-info' ); ?>
		</div>
	</div>
</div>
