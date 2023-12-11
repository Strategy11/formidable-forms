<?php
/**
 * Form Templates - list.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm-form-templates-page-title" class="frm-flex-box frm-justify-between frm-items-center">
	<h2 id="frm-form-templates-page-title-text" class="frm-form-templates-title frm-text-sm frm-m-0"><?php esc_html_e( 'All Templates', 'formidable' ); ?></h2>
	<a id="frm-show-create-template-modal" href="#" class="button button-primary frm-button-primary frm_hidden" role="button"><?php esc_html_e( 'Create Template', 'formidable' ); ?></a>
</div>
<span id="frm-form-templates-page-title-divider" class="frm-form-templates-divider frm-mb-xs frm_hidden"></span>

<ul id="frm-form-templates-featured-list" class="frm-form-templates-list frm-form-templates-grid-layout">
	<?php
	foreach ( $featured_templates as $template ) {
		require $view_path . 'template.php';
	}
	?>
</ul>

<?php FrmFormTemplatesHelper::show_upgrade_renew_cta( compact( 'expired', 'upgrade_link', 'renew_link' ) ); ?>

<ul id="frm-form-templates-list" class="frm-form-templates-list frm-form-templates-grid-layout frm-mb-xs">
	<?php
	foreach ( $templates as $template ) {
		require $view_path . 'template.php';
	}
	?>
</ul>

<div id="frm-form-templates-custom-list-section">
	<h2 id="frm-form-templates-custom-list-title" class="frm-text-sm frm-mb-sm frm_hidden">
		<?php esc_html_e( 'Custom', 'formidable' ); ?>
	</h2>
	<ul id="frm-form-templates-custom-list" class="frm-form-templates-list frm-form-templates-grid-layout frm-mb-xs frm_hidden">
		<?php
		foreach ( $custom_templates as $template ) {
			require $view_path . 'template.php';
		}
		?>
	</ul>
</div>
