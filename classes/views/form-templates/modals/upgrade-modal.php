<?php
/**
 * Form Templates - Upgrade modal.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

// List of pricing plans.
$plans = array( 'Basic', 'Plus', 'Business', 'Elite' );
?>
<div id="frm-form-upgrade-modal" class="frm_wrap frm-form-templates-modal-item frm_hidden">
	<div class="frm_modal_top frm-mt-xs">
		<div class="frm-circled-icon frm-flex-center frm-mb-sm">
			<?php FrmAppHelper::icon_by_class( 'frmfont frm_filled_lock_icon' ); ?>
		</div>

		<div class="frm-modal-title">
			<h2>
				<?php
				printf(
					/* translators: %1$s: Open span tag, %2$s: Close span tag */
					esc_html__( '%1$sTEMPLATE NAME%2$s is a PRO Template', 'formidable' ),
					'<span class="frm-upgrade-modal-template-name frm-capitalize">',
					'</span>'
				);
				?>
			</h2>
		</div>
	</div>

	<div class="inside frm-px-md frm-mt-xs frm-m-0">
		<p>
			<?php
			printf(
				/* translators: %1$s: Open span tag, %2$s: Close span tag */
				esc_html__( 'The %1$sTEMPLATE NAME%2$s is not available on your plan. Please upgrade to unlock this and more awesome templates.', 'formidable' ),
				'<span class="frm-upgrade-modal-template-name frm-capitalize">',
				'</span>'
			);
			?>
		</p>

		<?php if ( 'free' === FrmAddonsController::license_type() ) { ?>
		<div class="frm-cta frm-cta-green">
			<span class="frm-banner-title frm-flex-box frm-items-center frm-font-medium frm-mb-2xs">
				<?php
				printf(
					/* translators: %1$s: Open span tag, %2$s: Close span tag */
					esc_html__( 'Lite users get %1$s50%% OFF%2$s regular price.', 'formidable' ),
					'<span class="frm-meta-tag frm-green-tag">',
					'</span>'
				);
				?>
			</span>
			<span class="frm-banner-text"><?php esc_html_e( 'Discount is automatically applied at checkout.', 'formidable' ); ?></span>
		</div>
		<?php } ?>

		<div id="frm-upgrade-modal-available-plans">
			<p>
				<?php esc_html_e( 'Template available on:', 'formidable' ); ?>
			</p>
			<p class="frm-flex-box frm-gap-md frm-m-0">
				<?php foreach ( $plans as $plan ) { ?>
					<span class="frm-upgrade-modal-plan frm-flex-box frm-gap-xs frm-items-center">
						<span class="frm-upgrade-modal-plan-icon frm-flex-box" data-plan="<?php echo esc_attr( strtolower( $plan ) ); ?>">
							<?php FrmAppHelper::icon_by_class( 'frmfont frm_close_icon' ); ?>
						</span>
						<span class="frm-upgrade-modal-plan-text"><?php echo esc_html( $plan ); ?></span>
					</span>
				<?php } ?>
			</p>
		</div>
	</div>

	<div class="frm_modal_footer frm-flex-box frm-justify-end frm-pt-sm frm-pb-md">
		<a href="#" class="button button-secondary frm-button-secondary frm-modal-close dismiss" role="button">
			<?php esc_html_e( 'Close', 'formidable' ); ?>
		</a>
		<a id="frm-upgrade-modal-link" href="#" class="button button-primary frm-button-primary" target="_blank" rel="noopener">
			<?php
			if ( 'free' === FrmAddonsController::license_type() ) {
				esc_html_e( 'Upgrade to PRO', 'formidable' );
			} else {
				esc_html_e( 'Upgrade Now', 'formidable' );
			}
			?>
		</a>
	</div>
</div>
