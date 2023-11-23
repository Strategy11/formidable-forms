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
	<div class="frm_modal_top frm-mt-xs">
		<div class="frm-upgrade-modal-lock-icon frm-flex-center frm-mb-sm">
			<?php FrmAppHelper::icon_by_class( 'frmfont frm_filled_lock_icon' ); ?>
		</div>

		<div class="frm-modal-title">
			<h2 class="frm-font-semibold frm-m-0 frm-p-0">
				<?php
				printf(
					/* translators: %1$s: Open span tag, %2$s: Close span tag */
					esc_html__( 'Get Access to %1$s%2$s+ Pre-built Forms', 'formidable' ),
					'<span class="frm-form-templates-extra-templates-count">',
					'</span>'
				);
				?>
			</h2>
		</div>
	</div>

	<div class="inside frm-flex-col frm-gap-sm frm-px-md frm-mt-xs frm-mb-2xs">
		<p class="frm-m-0">
			<?php esc_html_e( 'That template is not available on your plan. Please renew to unlock this and more awesome templates.', 'formidable' ); ?>
		</p>
	</div>

	<div class="frm_modal_footer frm-flex-box frm-justify-end frm-pt-sm frm-pb-md">
		<a href="#" class="button button-secondary frm-button-secondary frm-modal-close dismiss" role="button">
			<?php esc_html_e( 'Close', 'formidable' ); ?>
		</a>
		<a href="<?php echo esc_url( $renew_link ); ?>" class="button button-primary frm-button-primary" target="_blank" rel="noopener">
			<?php esc_html_e( 'Renew Now', 'formidable' ); ?>
		</a>
	</div>
</div>
