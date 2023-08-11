<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>

<!-- Title -->
<h2 class="frm-form-templates-title"><?php esc_html_e( 'All Templates', 'formidable' ); ?></h2>

<!-- Featured Templates List -->
<ul id="frm-form-templates-featured-list" class="frm-form-templates-list frm_grid_container">
	<?php
	// Define additional variables.
	$render_icon = true;

	foreach ( $featured_templates as $template ) {
		require $view_path . 'template.php';
	}
	?>
</ul><!-- #frm-form-templates-featured-list -->

<?php
FrmAppHelper::show_admin_cta(
	array(
		'title'       => esc_html__( 'Upgrade to get all 200+ templates', 'formidable' ),
		'description' => esc_html__( 'Upgrade to PRO to get access to all of our templates and unlock the full potential of your forms.', 'formidable' ),
		'link_text'   => esc_html__( 'Upgrade to PRO', 'formidable' ),
		'link_url'    => $upgrade_link,
	)
);
?>

<!-- Templates List -->
<ul class="frm-form-templates-list frm_grid_container">
	<?php
	// Define additional variables.
	$render_icon = false;

	foreach ( $templates as $template ) {
		require $view_path . 'template.php';
	}
	?>
</ul><!-- .frm-form-templates-list -->
