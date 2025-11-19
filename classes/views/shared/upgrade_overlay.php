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
				<div class="frm-circled-icon frm-flex-center frm-mb-sm">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm_filled_lock_icon frm_locked', array( 'aria-hidden' => 'true' ) ); ?>
				</div>

				<h2>
					<?php
					printf(
						/* translators: %$1s: Feature name, %$2s: open span tag, %$3s: close span tag, %$4s: open prefix span tag, %$5s: close prefix span tag, %$6s: open suffix span tag, %$7s: close suffix span tag. */
						esc_html__( '%4$sActivate the %5$s%1$s %2$sare not available%3$s%6$s are now activated%7$s', 'formidable' ),
						'<span class="frm_feature_label"></span>',
						'<span class="frm_are_not_installed">',
						'</span>',
						'<span class="frm-upgrade-modal-title-prefix">',
						'</span>',
						'<span class="frm-upgrade-modal-title-suffix">',
						'</span>'
					);
					?>
				</h2>
				<div class="cta-inside">
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
					<p class="frm-upgrade-message frm-my-xs" data-default="<?php echo esc_attr( $message ); ?>">
						<?php FrmAppHelper::kses_echo( $message, array( 'span' ) ); ?>
					</p>
					<?php if ( $is_pro ) { ?>
						<a href="<?php echo esc_url( $default_link ); ?>" class="button button-primary frm-button-primary frm-upgrade-link" data-default="<?php echo esc_url( $default_link ); ?>">
							<?php
							if ( FrmAddonsController::is_license_expired() ) {
								esc_html_e( 'Renew', 'formidable' );
							} else {
								printf(
									/* translators: %s: Plan name */
									esc_html__( 'Upgrade to %s', 'formidable' ),
									'<span class="license-level">Pro</span>'
								);
							}
							?>
						</a>
					<?php } else { ?>
						<div class="frm-upgrade-modal-actions frm-flex frm-flex-row-reverse frm-items-center frm-gap-xs">
							<a href="<?php echo esc_url( $default_link ); ?>" class="button button-primary frm-button-primary frm-upgrade-link" target="_blank" rel="noopener noreferrer" data-default="<?php echo esc_url( $default_link ); ?>">
								<?php
								printf(
									/* translators: %s: Plan name */
									esc_html__( 'Upgrade to %s', 'formidable' ),
									'<span class="license-level">Pro</span>'
								);
								?>
							</a>
							<a href="#" class="button button-secondary frm-button-secondary" target="_blank" rel="noopener noreferrer">
								<?php echo esc_html_x( 'Learn More', 'upgrade overlay', 'formidable' ); ?>
							</a>
						</div>
						<?php
					}//end if
					?>
					<p class="frm-oneclick frm_hidden"></p>
					<p class="frm-addon-status"></p>
					<a class="button button-primary frm-button-primary frm_hidden frm-oneclick-button">
						<?php esc_html_e( 'Install', 'formidable' ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>
