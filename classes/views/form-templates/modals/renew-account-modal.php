<?php
/**
 * Form Templates - Renew account modal.
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
<div id="frm-renew-modal" class="frm-form-templates-modal-item frm_hidden">
	<!-- Modal Header -->
	<div class="frm_modal_top">
		<div class="frm-modal-title">
			<span class="frm-modal-title-text"><?php esc_html_e( 'Renew your account', 'formidable' ); ?></span>
		</div>
	</div><!-- .frm_modal_top -->

	<!-- Modal Body -->
	<div class="inside">
		<div class="frmcenter">
			<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/form-templates/renew-account.svg' ); ?>" />
			<h3><?php esc_html_e( 'Get access to Formidable Forms templates', 'formidable' ); ?></h3>
			<p><?php esc_html_e( 'Renew your license to create powerful online forms.', 'formidable' ); ?></p>
		</div><!-- .frmcenter -->
	</div><!-- .inside -->

	<!-- Modal Footer -->
	<div class="frm_modal_footer">
		<a href="#" class="button button-secondary frm-button-secondary frm-modal-close dismiss" role="button">
			<?php esc_html_e( 'Close', 'formidable' ); ?>
		</a>
		<a href="<?php echo esc_url( $renew_link ); ?>" class="button button-primary frm-button-primary" target="_blank" rel="noopener">
			<?php esc_html_e( 'Renew my account', 'formidable' ); ?>
		</a>
	</div><!-- .frm_modal_footer -->
</div>
