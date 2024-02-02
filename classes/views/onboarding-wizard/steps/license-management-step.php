<?php
/**
 * Onboarding Wizard - License Management Step.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>

<section id="frm-onboarding-license-management-step" class="frm-onboarding-step frm-card-box frm-has-progress-bar frm_hidden" data-step-name="license-management">
	<span class="frm-card-box-progress-bar"><span></span></span>

	<div class="frm-card-box-header">
		<div class="frm-circled-icon frm-circled-icon-large frm-flex-center">
			<?php FrmAppHelper::icon_by_class( 'frmfont frm_filled_key_icon' ); ?>
		</div>
	</div>

	<div class="frm-card-box-content frm-fields">
		<h2 class="frm-card-box-title frmcenter"><?php esc_html_e( 'License Management', 'formidable' ); ?></h2>
		<p class="frm-card-box-text frm-px-lg frmcenter"><?php esc_html_e( 'To get started, please activate your license by following the simple steps below.', 'formidable' ); ?></p>

		<div class="frm_form_field frm-mt-lg">
			<p>
				<label for="frm-onboarding-license-key"><?php esc_html_e( 'License key', 'formidable' ); ?></label>
				<input id="frm-onboarding-license-key" class="frm-mt-2xs" type="text" placeholder="<?php esc_html_e( 'Enter your license key', 'formidable' ); ?>" />
			</p>
		</div>
	</div>

	<div class="frm-card-box-footer">
		<a href="#" class="button button-secondary frm-button-secondary frm-onboarding-skip-step" role="button">
			<?php esc_html_e( 'Skip', 'formidable' ); ?>
		</a>
		<a href="#" id="frm-onboarding-save-license-btn" class="button button-primary frm-button-primary frm-onboarding-next-step" role="button">
			<?php esc_html_e( 'Activate & continue', 'formidable' ); ?>
		</a>
	</div>
</section>
