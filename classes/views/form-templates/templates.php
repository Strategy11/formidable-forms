<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>

<h2 class="frm-form-templates-title"><?php esc_html_e( 'All Templates', 'formidable' ); ?></h2>

<ul id="frm-form-templates-featured-list" class="frm-form-templates-list frm_grid_container">
	<?php
	// Define additional variables.
	$render_icon = true;

	foreach ( $featured_templates as $template ) {
		require $view_path . 'template.php';
	}
	?>
</ul><!-- #frm-form-templates-featured-list -->

<ul class="frm-form-templates-list frm_grid_container">
	<?php
	// Define additional variables.
	$render_icon = false;

	foreach ( $templates as $template ) {
		require $view_path . 'template.php';
	}
	?>
</ul><!-- .frm-form-templates-list -->
