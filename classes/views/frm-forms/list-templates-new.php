<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<ul class="frm-featured-forms-new">
	<li class="frm-add-blank-form selectable">
		<div class="frm-new-form-button frm-featured-form">
			<div style="background-color: #F4AD3D;">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_plus_icon' ); ?>
			</div><div>
				<h3><?php esc_html_e( 'Blank Form', 'formidable' ); ?></h3>
				<p><?php esc_html_e( 'Create a new form from scratch', 'formidable' ); ?></p>
			</div>
		</div>
	</li><?php
	foreach ( array( 20872734, 20874748, 20882522, 20874739 ) as $template ) {
		if ( ! isset( $templates[ $template ] ) ) {
			continue;
		}

		$template      = $templates[ $template ];
		$plan_required = FrmFormsHelper::get_plan_required( $template );
		$link          = FrmFormsHelper::get_template_install_link( $template, compact( 'pricing', 'license_type', 'plan_required' ) );
		?><li class="selectable" data-rel="<?php echo esc_url( $link['url'] ); ?>" data-preview="<?php echo esc_url( 'https://sandbox.formidableforms.com/demos/wp-json/frm/v2/forms/' . $template['key'] . '?return=html' ); ?>">
			<div class="frm-featured-form">
				<div>
					<?php FrmFormsHelper::template_icon( isset( $template['categories'] ) ? $template['categories'] : array() ); ?>
				</div><div>
					<h3><?php echo esc_html( preg_replace( '/(\sForm)?(\sTemplate)?$/', '', $template['name'] ) ); ?></h3>
					<p><?php echo esc_html( $template['description'] ); ?></p>
				</div>
			</div>
		</li><?php } ?><li class="selectable" data-href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-import' ) ); ?>">
		<div class="frm-featured-form">
			<div style="background-color: #805EF6;">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_upload_icon' ); ?>
			</div><div>
				<h3><?php esc_html_e( 'Import', 'formidable' ); ?></h3>
				<p><?php esc_html_e( 'Upload your Formidable XML or CSV file to import forms.', 'formidable' ); ?></p>
			</div>
		</div>
	</li>
</ul>
<?php
FrmAppHelper::show_search_box(
	array(
		'input_id'    => 'template',
		'placeholder' => __( 'Search Templates', 'formidable' ),
		'tosearch'    => 'frm-template-row',
	)
);
?>
<div class="accordion-container">
	<ul class="frm-featured-forms-new categories-list">
		<?php foreach ( $templates_by_category as $category => $category_templates ) { ?>
			<li class="control-section accordion-section">
				<div class="frm-featured-form">
					<div style="background-color: #805EF6;">
						<?php FrmFormsHelper::template_icon( array( $category ) ); ?>
					</div><div>
						<div class="accordion-section-title">
							<h3><?php echo esc_attr( $category ); ?></h3>
							<p><?php echo count( $category_templates ); ?> <?php esc_html_e( 'templates', 'formidable' ); ?></p>
						</div>
						<div class="accordion-section-content" aria-expanded="false">
							<ul>
							<?php foreach ( $category_templates as $template ) { ?>
								<?php $link = FrmFormsHelper::get_template_install_link( $template, compact( 'pricing', 'license_type', 'plan_required' ) ); ?>
								<li class="selectable" data-rel="<?php echo esc_url( $link['url'] ); ?>" data-preview="<?php echo esc_url( 'https://sandbox.formidableforms.com/demos/wp-json/frm/v2/forms/' . $template['key'] . '?return=html' ); ?>">
									<div>
										<h3><?php echo esc_html( preg_replace( '/(\sForm)?(\sTemplate)?$/', '', $template['name'] ) ); ?></h3>
										<p><?php echo esc_html( $template['description'] ); ?></p>
									</div>
								</li>
							<?php } ?>
							</ul>
						</div>
					</div>
				</div>
			</li>
		<?php } ?>
	</ul>
</div>
<?php
include FrmAppHelper::plugin_path() . '/classes/views/frm-forms/template-name-overlay.php';
