<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm_upgrade_modal" class="frm_hidden frm-modal settings-lite-cta">
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
						/* translators: %$1s: Feature name, %$2s: open span tag, %$3s: close span tag. */
						esc_html__( '%1$s %2$sare not installed%3$s', 'formidable' ),
						'<span class="frm_feature_label"></span>',
						'<span class="frm_are_not_installed">',
						'</span>'
					);
					?>
				</h2>
				<div class="cta-inside">

					<p class="frm-oneclick frm_hidden">
						<?php esc_html_e( 'That add-on is not installed. Would you like to install it now?', 'formidable' ); ?>
					</p>
					<p class="frm-addon-status"></p>

					<a class="button button-primary frm-button-primary frm_hidden frm-oneclick-button">
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
					<p class="frm-upgrade-message" data-default="<?php echo esc_attr( $message ); ?>">
						<?php echo FrmAppHelper::kses( $message, array( 'span' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</p>
					<?php if ( $is_pro ) { ?>
						<a href="<?php echo esc_url( $default_link ); ?>" class="button button-primary frm-button-primary frm-upgrade-link" data-default="<?php echo esc_url( $default_link ); ?>">
							<?php
							printf(
								/* translators: %s: Plan name */
								esc_html__( 'Upgrade to %s', 'formidable' ),
								'<span class="license-level">Pro</span>'
							);
							?>
						</a>
					<?php } else { ?>
						<a href="<?php echo esc_url( $default_link ); ?>" class="button button-primary frm-button-primary frm-upgrade-link" target="_blank" rel="noopener noreferrer" data-default="<?php echo esc_url( $default_link ); ?>">
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
