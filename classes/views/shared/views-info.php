<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm_wrap">
	<?php
	FrmAppHelper::get_admin_header(
		array(
			'close' => $form ? admin_url( 'admin.php?page=formidable&frm_action=views&form=' . $form->id ) : '',
			'form'  => $form,
			'label' => __( 'Views', 'formidable' ),
		)
	);
	?>
	<div class="frmcenter frm-m-12">
		<h2><?php esc_html_e( 'Show and Edit Entries with Views', 'formidable' ); ?></h2>
		<p style="max-width:400px;margin:20px auto">
			<?php esc_html_e( 'Bring entries to the front-end of your site for full-featured applications or just to show the content.', 'formidable' ); ?>
		</p>
		<?php
		$upgrade_link_args = array(
			'class'  => 'frm-mb-md frm-button-primary',
			'medium' => 'views-info',
			'plan'   => 'view',
		);
		FrmAddonsController::conditional_action_button( 'views', $upgrade_link_args );
		?>
		<div class="frm-video-wrapper">
			<iframe width="843" height="474" src="https://www.youtube.com/embed/pmYbQ79wonQ" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
		</div>
	</div>
</div>
