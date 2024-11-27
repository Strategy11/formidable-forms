<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm_wrap">
	<?php
	FrmAppHelper::get_admin_header(
		array(
			'label' => __( 'Views', 'formidable' ),
			'form'  => $form,
			'close' => $form ? admin_url( 'admin.php?page=formidable&frm_action=views&form=' . $form->id ) : '',
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
			'medium' => 'views-info',
			'plan'   => 'view',
			'class'  => 'frm-mb-md frm-button-primary frm-gradient',
		);
		FrmAddonsController::conditional_action_button( 'views', $upgrade_link_args );
		?>

		<a href="https://formidableforms.com/demos/" class="frm-mb-md frm-ml-xs frm-button-secondary"><?php esc_html_e( 'View Demos', 'formidable' ); ?></a>

		<div class="frm-views-features frm_grid_container">
			<div class="frm4">
				<div class="frm-views-feature__icon">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm-grid' ); ?>
				</div>
				<div class="frm-views-feature__title"><?php esc_html_e( 'Grid', 'formidable' ); ?></div>
				<div class="frm-views-feature__desc">
					<?php esc_html_e( 'Create a view and write less code', 'formidable' ); ?>
				</div>
			</div>
			<div class="frm4 frm-views-feature--calendar">
				<div class="frm-views-feature__icon">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm_calendar_icon' ); ?>
				</div>
				<div class="frm-views-feature__title"><?php esc_html_e( 'Calendar', 'formidable' ); ?></div>
				<div class="frm-views-feature__desc">
					<?php esc_html_e( 'Create a view and write less code', 'formidable' ); ?>
				</div>
			</div>
			<div class="frm4 frm-views-feature--table">
				<div class="frm-views-feature__icon">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm-table' ); ?>
				</div>
				<div class="frm-views-feature__title"><?php esc_html_e( 'Table', 'formidable' ); ?></div>
				<div class="frm-views-feature__desc">
					<?php esc_html_e( 'Create a view and write less code', 'formidable' ); ?>
				</div>
			</div>
			<div class="frm4 frm-views-feature--map">
				<div class="frm-views-feature__icon">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm-pin' ); ?>
				</div>
				<div class="frm-views-feature__title"><?php esc_html_e( 'Map', 'formidable' ); ?></div>
				<div class="frm-views-feature__desc">
					<?php esc_html_e( 'Create a view and write less code', 'formidable' ); ?>
				</div>
			</div>
			<div class="frm4 frm-views-feature--code">
				<div class="frm-views-feature__icon">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm_code_icon' ); ?>
				</div>
				<div class="frm-views-feature__title"><?php esc_html_e( 'Classic', 'formidable' ); ?></div>
				<div class="frm-views-feature__desc">
					<?php esc_html_e( 'Create a view and write less code', 'formidable' ); ?>
				</div>
			</div>
			<div class="frm4 frm-views-feature--">
				<div class="frm-views-feature__icon">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm_folder_icon' ); ?>
				</div>
				<div class="frm-views-feature__title"><?php esc_html_e( 'Ready made solution', 'formidable' ); ?></div>
				<div class="frm-views-feature__desc">
					<?php esc_html_e( 'Create a view and write less code', 'formidable' ); ?>
				</div>
			</div>
		</div><!--- End .frm-views-features -->

		<div class="frm-video-wrapper">
			<iframe width="843" height="474" src="https://www.youtube.com/embed/pmYbQ79wonQ" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
		</div>
	</div>
</div>
