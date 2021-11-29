<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm_upgrade_modal" class="frm_hidden settings-lite-cta">
	<div class="metabox-holder">
		<div class="postbox">
			<a href="#" class="dismiss alignright" title="<?php esc_attr_e( 'Dismiss this message', 'formidable' ); ?>">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_close_icon', array( 'aria-label' => 'Dismiss' ) ); ?>
			</a>
			<div class="inside">

				<?php FrmAppHelper::icon_by_class( 'frmfont frm_lock_icon frm_locked', array( 'aria-hidden' => 'true' ) ); ?>
				<h2>
					<?php
					printf(
						/* translators: %s: Feature name */
						esc_html__( '%s are not installed', 'formidable' ),
						'<span class="frm_feature_label"></span>'
					);
					?>
				</h2>
				<div class="cta-inside">

					<p id="frm-oneclick" class="frm_hidden">
						<?php esc_html_e( 'That add-on is not installed. Would you like to install it now?', 'formidable' ); ?>
					</p>
					<p id="frm-addon-status"></p>

					<a class="button button-primary frm-button-primary frm_hidden" id="frm-oneclick-button">
						<?php esc_html_e( 'Install', 'formidable' ); ?>
					</a>

					<?php
					if ( $is_pro ) {
						/* translators: %s: Feature name */
						$message = __( '%s are not available on your plan. Please upgrade or renew your license to unlock more awesome features.', 'formidable' );
					} else {
						/* translators: %s: Feature name */
						$message = __( '%s are not available on your plan. Did you know you can upgrade to PRO to unlock more awesome features?', 'formidable' );
					}
					$message = sprintf( esc_html( $message ), '<span class="frm_feature_label"></span>' );
					?>
					<p id="frm-upgrade-message" data-default="<?php echo esc_attr( $message ); ?>">
						<?php echo FrmAppHelper::kses( $message, array( 'span' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</p>
					<?php if ( $is_pro ) { ?>
						<a href="<?php echo esc_url( $default_link ); ?>" class="button button-primary frm-button-primary" id="frm-upgrade-modal-link" data-default="<?php echo esc_url( $default_link ); ?>">
							<?php
							printf(
								/* translators: %s: Plan name */
								esc_html__( 'Upgrade to %s', 'formidable' ),
								'<span class="license-level">Pro</span>'
							);
							?>
						</a>
					<?php } else { ?>
						<a href="<?php echo esc_url( $default_link ); ?>" class="button button-primary frm-button-primary" target="_blank" rel="noopener noreferrer" id="frm-upgrade-modal-link" data-default="<?php echo esc_url( $default_link ); ?>">
							<?php
							printf(
								/* translators: %s: Plan name */
								esc_html__( 'Upgrade to %s', 'formidable' ),
								'<span class="license-level">Pro</span>'
							);
							?>
						</a>

						<p>
							<a href="<?php echo esc_url( FrmAppHelper::admin_upgrade_link( $upgrade_link, 'knowledgebase/install-formidable-forms/' ) ); ?>" target="_blank" class="frm-link-secondary">
								<?php esc_html_e( 'Already purchased?', 'formidable' ); ?>
							</a>
						</p>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>
