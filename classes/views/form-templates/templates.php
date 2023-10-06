<?php
/**
 * Form Templates - Templates.
 *
 * @package   Strategy11/FormidableForms
 * @copyright 2010 Formidable Forms
 * @license   GNU General Public License, version 2
 * @link      https://formidableforms.com/
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<h2 id="frm-form-templates-page-title" class="frm-form-templates-title"><?php esc_html_e( 'All Templates', 'formidable' ); ?></h2>

<ul id="frm-form-templates-featured-list" class="frm-form-templates-list">
	<?php
	foreach ( $featured_templates as $template ) {
		require $view_path . 'template.php';
	}
	?>
</ul>

<?php
// Show 'upgrade' banner for non-elite users.
if ( 'elite' !== FrmAddonsController::license_type() && ! $expired ) {
	FrmAppHelper::show_admin_cta(
		array(
			'title'       => esc_html__( 'Upgrade to get all 200+ templates', 'formidable' ),
			'description' => esc_html__( 'Upgrade to PRO to get access to all of our templates and unlock the full potential of your forms.', 'formidable' ),
			'link_text'   => esc_html__( 'Upgrade to PRO', 'formidable' ),
			'link_url'    => $upgrade_link,
			'id'          => 'frm-upgrade-banner',
		)
	);
}

// TODO: Get the actual text for expired users from Steph
// Show 'renew' banner for expired users.
if ( $expired ) {
	FrmAppHelper::show_admin_cta(
		array(
			'title'       => esc_html__( 'Your Subscription Has Expired', 'formidable' ),
			'description' => esc_html__( 'To continue using all 200+ templates, please renew your subscription.', 'formidable' ),
			'link_text'   => esc_html__( 'Renew Now', 'formidable' ),
			'link_url'    => $renew_link,
			'id'          => 'frm-renew-subscription-banner',
		)
	);
}
?>

<ul id="frm-form-templates-list" class="frm-form-templates-list">
	<?php
	foreach ( $templates as $template ) {
		require $view_path . 'template.php';
	}
	?>
</ul>

<div id="frm-form-templates-custom-list-section">
	<h3 id="frm-form-templates-custom-list-title" class="frm-form-templates-title frm_hidden"><?php esc_html_e( 'Custom List', 'formidable' ); ?></h3>

	<ul id="frm-form-templates-custom-list" class="frm-form-templates-list frm_hidden">
		<?php
		foreach ( $custom_templates as $template ) {
			require $view_path . 'template.php';
		}
		?>
	</ul>
</div>