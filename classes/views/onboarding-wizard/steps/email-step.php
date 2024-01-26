<?php
/**
 * Onboarding Wizard - Welcome Step.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>

<section id="frm-onboarding-email-step" class="frm-card-box frm_hidden" data-step-name="email">
	<div class="frm-card-box-header">
		<div class="frm-circled-icon frm-flex-center">
			<?php FrmAppHelper::icon_by_class( 'frmfont frm_filled_lock_icon' ); ?>
		</div>
	</div>

	<div class="frm-card-box-content frm-fields">
		<h2 class="frm-card-box-title frmcenter"><?php esc_html_e( 'Default Email Address', 'formidable' ); ?></h2>
		<p class="frm-card-box-text frm-px-lg frmcenter"><?php esc_html_e( 'Subscribe to our mailing list so you know first when we release new features!', 'formidable' ); ?></p>

		<div class="frm_form_field">
			<label for="frm-default-email-field"><?php esc_html_e( 'Default email address', 'formidable' ); ?></label>
			<input id="frm-default-email-field" type="email" placeholder="<?php esc_html_e( 'Enter your email', 'formidable' ); ?>" value="<?php echo esc_attr( $user->user_email ); ?>" />

			<span id="frm-default-email-field-error" class="frm_hidden">
				<span frm-error="invalid"><?php esc_html_e( 'Email is invalid', 'formidable' ); ?></span>
				<span frm-error="empty"><?php esc_html_e( 'Email is empty', 'formidable' ); ?></span>
			</span>
		</div>
	</div>

	<div class="frm-card-box-footer">
		<a href="#" class="button button-secondary frm-button-secondary frm-onboarding-skip-step" role="button">
			<?php esc_html_e( 'Skip', 'formidable' ); ?>
		</a>
		<a href="#" id="frm-onboarding-get-email-btn" class="button button-primary frm-button-primary frm-onboarding-next-step" role="button">
			<?php esc_html_e( 'Next Step', 'formidable' ); ?>
		</a>
	</div>
</section>
