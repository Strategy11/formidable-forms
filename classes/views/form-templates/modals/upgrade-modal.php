<?php
/**
 * Form Templates - Upgrade modal.
 *
 * @package   Strategy11/FormidableForms
 * @copyright 2010 Formidable Forms
 * @license   GNU General Public License, version 2
 * @link      https://formidableforms.com/
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

// List of pricing plans.
$plans = array( 'Basic', 'Plus', 'Business', 'Elite' );
?>
<div id="frm-upgrade-modal" class="frm-form-templates-modal-item frm_hidden">
	<div class="inside">
		<span class="frm-form-templates-space-5"></span>

		<div class="frm-upgrade-modal-lock-icon">
			<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/form-templates/lock.svg' ); ?>" />
		</div>

		<div id="frm-upgrade-modal-content">
			<h3>
				<?php
				printf(
					/* translators: %1$s: Open span tag, %2$s: Close span tag */
					esc_html__( '%1$sTEMPLATE NAME%2$s is a PRO Template', 'formidable' ),
					'<span class="frm-upgrade-modal-template-name">',
					'</span>'
				);
				?>
			</h3>
			<p>
				<?php
				printf(
					/* translators: %1$s: Open span tag, %2$s: Close span tag */
					esc_html__( 'We\'re sorry, the %1$sTEMPLATE NAME%2$s is not available on your plan. Please upgrade to the PRO plan to unlock all the awesome templates.', 'formidable' ),
					'<span class="frm-upgrade-modal-template-name">',
					'</span>'
				);
				?>
			</p>
		</div>

		<div class="frm-upgrade-modal-banner">
			<span class="frm-upgrade-modal-banner-title">
				<?php
				printf(
					/* translators: %1$s: Open span tag, %2$s: Close span tag */
					esc_html__( 'Lite users get %1$s50%% OFF%2$s regular price.', 'formidable' ),
					'<span class="frm-upgrade-modal-banner-tag">',
					'</span>'
				);
				?>
			</span>
			<span class="frm-upgrade-modal-banner-text"><?php esc_html_e( 'Discount is automatically applied at checkout.', 'formidable' ); ?></span>
		</div>

		<div id="frm-upgrade-modal-available-plans">
			<span><?php esc_html_e( 'Template available on:', 'formidable' ); ?></span>
			<div id="frm-upgrade-modal-plans">
				<?php foreach ( $plans as $plan ) { ?>
					<span class="frm-upgrade-modal-plan">
						<span class="frm-upgrade-modal-plan-icon" data-plan="<?php echo esc_attr( strtolower( $plan ) ); ?>">
							<?php FrmAppHelper::icon_by_class( 'frmfont frm_close_icon' ); ?>
						</span>
						<span class="frm-upgrade-modal-plan-text"><?php echo esc_html( $plan ); ?></span>
					</span>
				<?php } ?>
			</div>
		</div>
	</div>

	<div class="frm_modal_footer">
		<a href="#" class="button button-secondary frm-button-secondary frm-modal-close dismiss" role="button">
			<?php esc_html_e( 'Close', 'formidable' ); ?>
		</a>
		<a href="<?php echo esc_url( $upgrade_link ); ?>" class="button button-primary frm-button-primary" target="_blank" rel="noopener">
			<?php esc_html_e( 'Upgrade to PRO', 'formidable' ); ?>
		</a>
	</div>
</div>