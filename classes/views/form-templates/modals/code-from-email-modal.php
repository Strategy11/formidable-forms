<?php
/**
 * Form Templates - Code from email modal.
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
<div id="frm-code-from-email-modal" class="frm-form-templates-modal-item frm_hidden">
	<div class="frm_modal_top">
		<div class="frm-modal-title">
			<span id="frm-code-modal-back-button" role="button" title="<?php esc_html_e( 'Back', 'formidable' ); ?>">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_back' ); ?>
			</span>

			<span class="frm-modal-title-text"><?php esc_html_e( 'Leave your email address', 'formidable' ); ?></span>
		</div>
	</div>

	<div class="inside">
		<div class="frmcenter">
			<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/form-templates/leave-email.svg' ); ?>" />
			<h3><?php esc_html_e( 'Check your inbox', 'formidable' ); ?></h3>
			<p><?php esc_html_e( 'Enter code that we\'ve sent to your email address', 'formidable' ); ?></p>

			<div class="frm-form-templates-modal-fieldset">
				<input id="frm_code_from_email" type="text" placeholder="<?php esc_attr_e( 'Code from email', 'formidable' ); ?>" />

				<span id="frm_code_from_email_error" class="frm-form-templates-modal-error frm_hidden">
					<span frm-error="custom"></span>
					<span frm-error="wrong-code"><?php esc_html_e( 'Verification code is wrong', 'formidable' ); ?></span>
					<span frm-error="empty"><?php esc_html_e( 'Verification code is empty', 'formidable' ); ?></span>
				</span>
			</div>

			<div id="frm_code_from_email_options" class="frm_hidden">
				<a href="#" id="frm-change-email-address"><?php esc_html_e( 'Change email address', 'formidable' ); ?></a>
				<span>|</span>
				<a href="#" id="frm-resend-code"><?php esc_html_e( 'Resend code', 'formidable' ); ?></a>
			</div>
		</div>
	</div>

	<div class="frm_modal_footer">
		<a href="#" class="button button-secondary frm-button-secondary frm-modal-close dismiss" role="button">
			<?php esc_html_e( 'Close', 'formidable' ); ?>
		</a>
		<a href="#" id="frm-confirm-email-address" class="button button-primary frm-button-primary" role="button">
			<?php esc_html_e( 'Save Code', 'formidable' ); ?>
		</a>
	</div>
</div>
