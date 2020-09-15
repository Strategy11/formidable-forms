<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<ul class="frm-featured-forms-new">
	<li class="frm-add-blank-form">
		<a class="frm-new-form-button frm-featured-form" href="#">
			<div>
				<span class="">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm_plus_icon' ); ?>
				</span>
			</div><div>
				<h3><?php esc_html_e( 'Blank Form', 'formidable' ); ?></h3>
				<p><?php esc_html_e( 'Create a new form from scratch', 'formidable' ); ?></p>
			</div>
		</a>
	</li><?php
	foreach ( array( 20872734, 20874748, 20882522, 20874739 ) as $template ) {
		if ( ! isset( $templates[ $template ] ) ) {
			continue;
		}

		$template      = $templates[ $template ];
		$plan_required = FrmFormsHelper::get_plan_required( $template );
		$link          = FrmFormsHelper::get_template_install_link( $template, compact( 'pricing', 'license_type', 'plan_required' ) );
		?><li>
			<?php FrmFormsHelper::template_install_html( $link, 'frm-featured-form' ); ?>
				<div>
					<?php FrmFormsHelper::template_icon( isset( $template['categories'] ) ? $template['categories'] : array() ); ?>
				</div><div>
					<h3"><?php echo esc_html( preg_replace( '/(\sForm)?(\sTemplate)?$/', '', $template['name'] ) ); ?></h3>
					<p><?php // TODO Description ?></p>
				</div>
			</a>
			<a href="#" class="frm-preview-template" rel="https://sandbox.formidableforms.com/demos/wp-json/frm/v2/forms/<?php echo esc_attr( $template['key'] ); ?>?return=html">
				<span>
					<?php FrmAppHelper::icon_by_class( 'frmfont frm_search_icon' ); ?>
				</span>
			</a>
		</li><?php } ?><li>
		<a class="frm-featured-form" href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-import' ) ); ?>">
			<div>
				<span style="background-color:var(--orange)">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm_upload_icon' ); ?>
				</span>
			</div><div>
				<h3><?php esc_html_e( 'Import', 'formidable' ); ?></h3>
				<p><?php esc_html_e( 'Upload your Formidable XML or CSV file to import forms.', 'formidable' ); ?></p>
			</div>
		</a>
	</li>
</ul>