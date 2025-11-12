<?php
/**
 * Welcome Tour - Completed state (similar to form-templates/template.php).
 *
 * @since 6.25.1
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-checklist__completed frm-flex-col frm-items-start frm-p-sm frm-gap-xs">
	<p class="frm-font-bold"><?php esc_html_e( 'Congratulations! 🎉', 'formidable' ); ?></p>
	<p><?php esc_html_e( 'Setup is complete and your form is ready to use. Thank you for building with Formidable Forms!', 'formidable' ); ?></p>

	<p class="frm-mt-sm"><?php esc_html_e( 'What\'s next for you?', 'formidable' ); ?></p>

	<?php FrmWelcomeTourController::show_completed_links( $current_form_id ); ?>

	<p class="frm-self-normal frm-bg-grey-50 frm-p-xs frm-rounded-sm frm-mt-auto">
		<?php
		printf(
			/* translators: %s is the link to the documentation */
			esc_html__( 'Check %s to learn more.', 'formidable' ),
			'<a href="' . esc_url( FrmWelcomeTourController::make_tracked_url( 'https://formidableforms.com/knowledgebase/' ) ) . '" class="frm-underline frm-usage-tracking-flow-click" target="_blank" rel="noopener" data-tracking-key="welcome_tour_completed_link_click" data-tracking-value="knowledge-base">' . esc_html__( 'Docs & Support', 'formidable' ) . '</a>'
		);
		?>
	</p>
</div>
