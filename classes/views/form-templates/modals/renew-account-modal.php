<?php
/**
 * Form Templates - Renew account modal.
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
<div id="frm-renew-modal" class="frm-form-templates-modal-item frm_hidden">
	<div class="frm_modal_top">
		<div class="frm-modal-title">
			<div class="frm-upgrade-modal-lock-icon frm-mb-sm">
				<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/form-templates/lock.svg' ); ?>" />
			</div>

			<h2><?php esc_html_e( 'Get Access to 200+ Pre-built Forms', 'formidable' ); ?></h2>
		</div>
	</div>

	<div class="inside">
		<p>
			<?php esc_html_e( 'That template is not available on your plan. Please renew to unlock this and more awesome templates.', 'formidable' ); ?>
		</p>
	</div>

	<div class="frm_modal_footer">
		<a href="#" class="button button-secondary frm-button-secondary frm-modal-close dismiss" role="button">
			<?php esc_html_e( 'Close', 'formidable' ); ?>
		</a>
		<a href="<?php echo esc_url( $renew_link ); ?>" class="button button-primary frm-button-primary" target="_blank" rel="noopener">
			<?php esc_html_e( 'Renew Now', 'formidable' ); ?>
		</a>
	</div>
</div>
