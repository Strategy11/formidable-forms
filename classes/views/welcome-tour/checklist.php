<?php
/**
 * Welcome Tour's Checklist component.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-checklist">
	<div class="frm-checklist__header frm-h-stack frm-justify-between">
		<h2><?php echo esc_html_e( 'Formidable Checklist', 'formidable' ); ?></h2>
		<?php FrmAppHelper::icon_by_class( 'frmfont frm_arrowdown8_icon', array( 'aria-hidden' => 'true' ) ); ?>
	</div>

	<?php if ( ! $completed ) : ?>
		<div class="frm-checklist__progress-bar">
			<div class="frm-checklist__progress-fill"></div>
		</div>
	<?php endif; ?>

	<div class="frm-checklist__steps">
		<?php
		foreach ( $checklist_steps as $step_key => $step ) {
			// require $view_path . 'steps/step.php';
		}
		?>
	</div>
</div>

<div class="frm-checklist__completed">
	<p><strong><?php esc_html_e( 'Congratulations! 🎉', 'formidable' ); ?></strong></p>
	<p><?php esc_html_e( 'Congratulations! 🎉', 'formidable' ); ?></p>
	<p><?php esc_html_e( 'What\'s next for you?', 'formidable' ); ?></p>

	<div class="frm-checklist__cta-buttons">
		<button class="button button-secondary frm-button frm-button-secondary"
				onclick="window.location.href='<?php echo esc_url( $urls['setup_email_notifications'] ); ?>'">
				<?php esc_html_e( 'Setup email notifications', 'formidable' ); ?>
		</button>
		<button class="button button-secondary frm-button frm-button-secondary"
				onclick="window.location.href='<?php echo esc_url( $urls['customize_success_message'] ); ?>'">
			<?php esc_html_e( 'Customize success message', 'formidable' ); ?>
		</button>
		<button class="button button-secondary frm-button frm-button-secondary"
				onclick="window.location.href='<?php echo esc_url( $urls['manage_form_entries'] ); ?>'">
			<?php esc_html_e( 'Manage form entries', 'formidable' ); ?>
		</button>
		<button class="button button-secondary frm-button frm-button-secondary"
				onclick="window.location.href='<?php echo esc_url( $urls['explore_integrations'] ); ?>'">
			<?php esc_html_e( 'Explore integrations', 'formidable' ); ?>
		</button>
	</div>

	<p>
		<?php
		printf(
			/* translators: %s is the link to the documentation */
			esc_html__( 'Check %s to learn more.', 'formidable' ),
			'<a href="' . esc_url( $urls['docs'] ) . '" target="_blank">' . esc_html__( 'Docs & Support', 'formidable' ) . '</a>'
		);
		?>
	</p>
</div>
