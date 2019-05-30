<div class="cta-inside">
	<p id="frm-upgrade-message">
		<?php
		/* translators: %s: Feature name */
		$message = __( '%s are not available on your plan. Did you know you can upgrade to PRO to unlock more awesome features?', 'formidable' );
		printf( esc_html( $message ), '<span class="frm_feature_label">Smart tags</span>' );
		?>
	</p>
	<p>
		<a href="<?php echo esc_url( FrmAppHelper::admin_upgrade_link( $upgrade_link ) ); ?>" class="button button-primary frm-button-primary" target="_blank" rel="noopener noreferrer" id="frm-upgrade-modal-link">
			<?php esc_html_e( 'Upgrade to Pro', 'formidable' ); ?>
		</a>

		<a href="<?php echo esc_url( FrmAppHelper::make_affiliate_url( FrmAppHelper::admin_upgrade_link( $upgrade_link, 'knowledgebase/install-formidable-forms/' ) ) ); ?>" target="_blank" class="frm-link-secondary alignright">
			<?php esc_html_e( 'Already purchased?', 'formidable' ); ?>
		</a>
	</p>
</div>
