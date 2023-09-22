<?php
/**
 * Form Templates - Leave email modal.
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
<div id="frm-leave-email-modal" class="frm-form-templates-modal-item frm_hidden">
	<!-- Modal Header -->
	<div class="frm_modal_top">
		<div class="frm-modal-title">
			<span class="frm-modal-title-text"><?php esc_html_e( 'Leave your email address', 'formidable' ); ?></span>
		</div>
	</div><!-- .frm_modal_top -->

	<!-- Modal Body -->
	<div class="inside">
		<div class="frmcenter">
			<div id="frmapi-email-form" class="frmapi-form frm_hidden" data-url="https://sandbox.formidableforms.com/api/wp-json/frm/v2/forms/freetemplates?return=html&exclude_script=jquery&exclude_style=formidable-css">
				<span class="frm-wait"></span>
			</div><!-- #frmapi-email-form -->

			<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/form-templates/leave-email.svg' ); ?>" />
			<h3><?php esc_html_e( 'Get 10+ Free Form Templates', 'formidable' ); ?></h3>
			<p><?php esc_html_e( 'Just add your email address and you\'ll get a code for 10+ free form templates.', 'formidable' ); ?></p>

			<div class="frm-form-templates-modal-inputs">
				<span class="frm-with-left-icon">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm_email_icon' ); ?>
					<input id="frm_leave_email" type="email" placeholder="<?php esc_html_e( 'Enter your email', 'formidable' ); ?>" value="<?php echo esc_attr( $user->user_email ); ?>" />
				</span><!-- .frm-with-left-icon -->

				<span id="frm_leave_email_error" class="frm_hidden">
					<span frm-error="invalid"><?php esc_html_e( 'Email is invalid', 'formidable' ); ?></span>
					<span frm-error="empty"><?php esc_html_e( 'Email is empty', 'formidable' ); ?></span>
				</span><!-- #frm_leave_email_error -->
			</div><!-- .frm-form-templates-modal-inputs -->
		</div><!-- .frmcenter -->
	</div><!-- .inside -->

	<!-- Modal Footer -->
	<div class="frm_modal_footer">
		<a href="#" class="button button-secondary frm-button-secondary frm-modal-close dismiss" role="button">
			<?php esc_html_e( 'Close', 'formidable' ); ?>
		</a>
		<a href="#" id="frm-add-my-email-address" class="button button-primary frm-button-primary" role="button">
			<?php esc_html_e( 'Get Code', 'formidable' ); ?>
		</a>
	</div><!-- .frm_modal_footer -->
</div>
