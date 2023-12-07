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

<?php
// Show 'upgrade' banner for non-elite users.
if ( 'elite' !== FrmAddonsController::license_type() && ! $expired ) {
	FrmTipsHelper::show_admin_cta(
		array(
			'title'       => sprintf(
				/* translators: %1$s: Open span tag, %2$s: Close span tag */
				esc_html__( 'Get Super Powers with %1$s%2$s+ Pre-built Forms', 'formidable' ) . ' 🦸',
				'<span class="frm-form-templates-extra-templates-count">',
				'</span>'
			),
			'description' => esc_html__( 'Unleash the potential of hundreds of additional form templates and save precious time. Upgrade today for unparalleled form-building capabilities.', 'formidable' ),
			'link_text'   => esc_html__( 'Upgrade to PRO', 'formidable' ),
			'link_url'    => $upgrade_link,
			'id'          => 'frm-upgrade-banner',
		)
	);
}

// Show 'renew' banner for expired users.
if ( $expired ) {
	FrmTipsHelper::show_admin_cta(
		array(
			'title'       => esc_html__( 'Get Super Powers with Pre-built Forms', 'formidable' ),
			'description' => esc_html__( 'Unleash the potential of hundreds of form templates and save precious time. Renew today for unparalleled form-building speed.', 'formidable' ),
			'link_text'   => esc_html__( 'Renew Now', 'formidable' ),
			'link_url'    => $renew_link,
			'id'          => 'frm-renew-subscription-banner',
		)
	);
}
?>

<ul id="frm-form-templates-list" class="frm-form-templates-list frm-form-templates-grid-layout">
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
	<ul id="frm-form-templates-custom-list" class="frm-form-templates-list frm-form-templates-grid-layout frm_hidden">
		<?php
		foreach ( $custom_templates as $template ) {
			require $view_path . 'template.php';
		}
		?>
	</ul>
</div>
