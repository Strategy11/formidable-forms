<?php
/**
 * Onboarding Wizard - Success (You're All Set!) Step.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>

<section id="frm-onboarding-success-step" class="frm-card-box frmcenter frm_hidden" data-step-name="success">
	<div class="frm-card-box-header">
		<div class="frm-circled-icon frm-circled-icon-green frm-flex-center">
			<?php FrmAppHelper::icon_by_class( 'frmfont frm_checkmark_icon' ); ?>
		</div>
	</div>

	<div class="frm-card-box-content frm-mt-sm">
		<h2 class="frm-card-box-title"><?php esc_html_e( 'You\'re All Set!', 'formidable' ); ?></h2>
		<p class="frm-card-box-text"><?php esc_html_e( 'Congratulations on completing the onboarding process! We hope you enjoy using Formidable Forms.', 'formidable' ); ?></p>
	</div>

	<div class="frm-card-box-footer frm-justify-center frm-mt-2xl">
		<a href="#" id="frm-form-templates-create-form" class="button button-secondary frm-button-secondary">
			<?php esc_html_e( 'Create a Form', 'formidable' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . FrmDashboardController::PAGE_SLUG ) ); ?>" class="button button-primary frm-button-primary">
			<?php esc_html_e( 'Go to Dashboard', 'formidable' ); ?>
		</a>
	</div>
</section>
