<?php
/**
 * Welcome Tour - Completed state (similar to form-templates/template.php).
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$button_attrs_html = FrmAppHelper::array_to_html_params(
	array(
		'class'  => 'button frm-button-secondary frm-button-sm frm-mb-2xs',
		'target' => '_blank',
		'rel'    => 'noopener',
	)
);
?>
<div class="frm-checklist__completed frm-flex-col frm-items-start frm-p-sm frm-gap-xs">
	<p class="frm-font-bold"><?php esc_html_e( 'Congratulations! 🎉', 'formidable' ); ?></p>
	<p><?php esc_html_e( 'Setup is complete and your form is ready to use. Thank you for building with Formidable Forms!', 'formidable' ); ?></p>

	<p class="frm-mt-sm"><?php esc_html_e( 'What\'s next for you?', 'formidable' ); ?></p>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable&frm_action=settings&id=' . $current_form_id . '&t=email_settings' ) ); ?>" <?php echo $button_attrs_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<?php esc_html_e( 'Setup email notifications', 'formidable' ); ?>
	</a>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable&frm_action=settings&id=' . $current_form_id . '&t=email_settings' ) ); ?>" <?php echo $button_attrs_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<?php esc_html_e( 'Customize success message', 'formidable' ); ?>
	</a>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-entries' ) ); ?>" <?php echo $button_attrs_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<?php esc_html_e( 'Manage form entries', 'formidable' ); ?>
	</a>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-addons' ) ); ?>" <?php echo $button_attrs_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<?php esc_html_e( 'Explore integrations', 'formidable' ); ?>
	</a>

	<p class="frm-self-normal frm-bg-grey-50 frm-p-xs frm-rounded-sm frm-mt-auto">
		<?php
		printf(
			/* translators: %s is the link to the documentation */
			esc_html__( 'Check %s to learn more.', 'formidable' ),
			'<a href="' . esc_url( FrmWelcomeTourController::make_tracked_url( 'https://formidableforms.com/knowledgebase/' ) ) . '" class="frm-underline" target="_blank" rel="noopener">' . esc_html__( 'Docs & Support', 'formidable' ) . '</a>'
		);
		?>
	</p>
</div>
