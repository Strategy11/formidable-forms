<div id="frm_upgrade_modal" class="frm_hidden settings-lite-cta">
	<div class="metabox-holder">
		<div class="postbox">
			<a href="#" class="dismiss alignright" title="<?php esc_attr_e( 'Dismiss this message', 'formidable' ); ?>"><i class="dashicons dashicons-no-alt" aria-label="Dismiss" aria-hidden="true"></i></a>
			<div class="inside">

				<i class="dashicons dashicons-lock"></i>
				<h2>
					<?php
					printf(
						esc_html__( '%s are a Pro feature', 'formidable' ),
						'<span class="frm_feature_label"></span>'
					);
					?> 
				</h2>
				<div class="cta-inside">
					<p>
						<?php
						if ( $is_pro ) {
							$message = __( '%s are not installed on this site. Please see the addons that are included with your plan.', 'formidable' );
						} else {
							$message = __( '%s are not available on your plan. Please upgrade to PRO to unlock more awesome features.', 'formidable' );
						}
						printf( esc_html( $message ), '<span class="frm_feature_label"></span>' );
						?>
					</p>
					<a href="<?php echo esc_url( FrmAppHelper::admin_upgrade_link( 'builder-upgrade' ) ); ?>" class="button button-primary" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'Upgrade to Pro', 'formidable' ); ?>
					</a>
					<p>
						<a href="<?php echo esc_url( FrmAppHelper::make_affiliate_url( FrmAppHelper::admin_upgrade_link( 'builder-upgrade', 'knowledgebase/install-formidable-forms/' ) ) ); ?>" target="_blank" class="frm-link-secondary">
							<?php esc_html_e( 'Already purchased?', 'formidable' ); ?>
						</a>
					</p>
				</div>
			</div>
		</div>
	</div>
</div>
