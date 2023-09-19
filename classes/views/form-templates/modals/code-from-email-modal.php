<?php
/**
 * Form Templates - Code from email modal.
 *
 * @package   Strategy11/FormidableForms
 * @copyright 2010 Formidable Forms
 * @license   GNU General Public License, version 2
 * @link      https://formidableforms.com/
 */

/**
 * Copyright (C) 2023 Formidable Forms
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm-code-from-email-modal" class="frm_hidden">
	<!-- Modal Header -->
	<div class="frm_modal_top">
		<div class="frm-modal-title">
			<span id="frm-code-modal-back-button" role="button" title="<?php esc_html_e( 'Back', 'formidable' ); ?>">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_back' ); ?>
			</span><!-- #frm-code-modal-back-button -->

			<span class="frm-modal-title-text"><?php esc_html_e( 'Leave your email address', 'formidable' ); ?></span>
		</div><!-- .frm-modal-title -->
	</div><!-- .frm_modal_top -->

	<!-- Modal Body -->
	<div class="inside">
		<div class="frmcenter">
			<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/form-templates/leave-email.svg' ); ?>" />
			<h3><?php esc_html_e( 'Check your inbox', 'formidable' ); ?></h3>
			<p><?php esc_html_e( 'Enter code that we\'ve sent to your email address', 'formidable' ); ?></p>

			<div class="frm-form-templates-modal-inputs">
				<input id="frm_code_from_email" type="text" placeholder="<?php esc_attr_e( 'Code from email', 'formidable' ); ?>" />
				<span id="frm_code_from_email_error" class="frm_hidden">
					<span frm-error="custom"></span>
					<span frm-error="wrong-code"><?php esc_html_e( 'Verification code is wrong', 'formidable' ); ?></span>
					<span frm-error="empty"><?php esc_html_e( 'Verification code is empty', 'formidable' ); ?></span>
				</span>
			</div><!-- .frm-form-templates-modal-inputs -->

			<div id="frm_code_from_email_options" class="frm_hidden">
				<a href="#" id="frm-change-email-address"><?php esc_html_e( 'Change email address', 'formidable' ); ?></a>
				<span class="frm-inline-divider">|</span>
				<a href="#" id="frm-resend-code"><?php esc_html_e( 'Resend code', 'formidable' ); ?></a>
			</div><!-- #frm_code_from_email_options -->
		</div><!-- .frmcenter -->
	</div><!-- .inside -->

	<!-- Modal Footer -->
	<div class="frm_modal_footer">
		<a href="#" class="button button-secondary frm-button-secondary frm-modal-close dismiss" role="button">
			<?php esc_html_e( 'Close', 'formidable' ); ?>
		</a>
		<a href="#" class="button button-primary frm-button-primary frm-confirm-email-address" role="button">
			<?php esc_html_e( 'Save Code', 'formidable' ); ?>
		</a>
	</div><!-- .frm_modal_footer -->
</div>
