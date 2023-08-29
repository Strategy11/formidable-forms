<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>

<!-- Page Title -->
<h2 id="frm-form-templates-page-title" class="frm-form-templates-title"><?php esc_html_e( 'All Templates', 'formidable' ); ?></h2>

<!-- Featured Templates List -->
<ul id="frm-form-templates-featured-list" class="frm-form-templates-list frm_grid_container">
	<?php
	foreach ( $featured_templates as $template ) {
		require $view_path . 'template.php';
	}
	?>
</ul><!-- #frm-form-templates-featured-list -->

<!-- Upsell Banner -->
<?php
FrmAppHelper::show_admin_cta(
	array(
		'title'       => esc_html__( 'Upgrade to get all 200+ templates', 'formidable' ),
		'description' => esc_html__( 'Upgrade to PRO to get access to all of our templates and unlock the full potential of your forms.', 'formidable' ),
		'link_text'   => esc_html__( 'Upgrade to PRO', 'formidable' ),
		'link_url'    => $upgrade_link,
		'id'          => 'frm-form-templates-upsell-banner',
	)
);
?>

<!-- Templates List -->
<ul id="frm-form-templates-list" class="frm-form-templates-list frm_grid_container">
	<?php
	foreach ( $templates as $template ) {
		require $view_path . 'template.php';
	}
	?>
</ul><!-- .frm-form-templates-list -->

<!-- Custom Templates Section -->
<div id="frm-form-templates-custom-list-section">
	<!-- Title for Custom List -->
	<h3 id="frm-form-templates-custom-list-title" class="frm-form-templates-title"><?php esc_html_e( 'Custom List', 'formidable' ); ?></h3>

	<!-- Custom Templates List -->
	<ul id="frm-form-templates-custom-list" class="frm-form-templates-list frm_grid_container frm-form-templates-hidden">
		<?php
		foreach ( $custom_templates as $template ) {
			require $view_path . 'template.php';
		}
		?>
	</ul><!-- .frm-form-templates-list -->
</div><!-- .frm-form-templates-custom-list-section -->
