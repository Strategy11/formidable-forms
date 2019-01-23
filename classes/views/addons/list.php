<div class="frm_wrap" id="frm-addons-page">
<div class="wrap">
	<h1><?php esc_html_e( 'Formidable Add-Ons', 'formidable' ); ?></h1>

	<?php include( FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php' ); ?>

	<div id="the-list" class="frm-addons">
		<?php foreach ( $addons as $slug => $addon ) { ?>
			<div class="frm-card plugin-card-<?php echo esc_attr( $slug ); ?> frm-no-thumb frm-addon-<?php echo esc_attr( $addon['status']['type'] ); ?>">
				<div class="plugin-card-top">
					<?php if ( strtotime( $addon['released'] ) > strtotime( '-90 days' ) ) { ?>
						<div class="frm_ribbon">
							<span>New</span>
						</div>
					<?php } ?>
					<h2>
						<?php echo esc_html( $addon['title'] ) ?>
					</h2>
					<p><?php echo esc_html( $addon['excerpt'] ); ?></p>
					<?php if ( isset( $addon['docs'] ) && ! empty( $addon['docs'] ) && $addon['installed'] ) { ?>
						<a href="<?php echo esc_url( $addon['docs'] ); ?>" target="_blank" aria-label="<?php esc_attr_e( 'View Docs', 'formidable' ); ?>">
							<?php esc_html_e( 'View Docs', 'formidable' ); ?>
						</a>
					<?php } ?>
				</div>
				<div class="plugin-card-bottom">
					<span class="addon-status">
						<?php
						printf(
							esc_html__( 'Status: %s', 'formidable' ),
							'<span class="addon-status-label">' . esc_html( $addon['status']['label'] ) . '</span>'
						);
						?>
					</span>
					<?php if ( $addon['status']['type'] === 'installed' ) { ?>
						<a href="<?php echo esc_url( $addon['activate_url'] ) ?>" class="button button-primary frm-button-primary activate-now <?php echo esc_attr( empty( $addon['activate_url'] ) ? 'frm_hidden' : '' ); ?>">
							<?php esc_html_e( 'Activate', 'formidable' ); ?>
						</a>
					<?php } elseif ( isset( $addon['url'] ) && ! empty( $addon['url'] ) ) { ?>
						<a class="frm-install-addon button button-primary frm-button-primary" rel="<?php echo esc_attr( $addon['url'] ); ?>" aria-label="<?php esc_attr_e( 'Install', 'formidable' ); ?>">
							<?php esc_html_e( 'Install', 'formidable' ); ?>
						</a>
						<span class="spinner"></span>
					<?php } else { ?>
						<a class="install-now button button-primary frm-button-primary" href="<?php echo esc_url( $pricing ); ?>" target="_blank" aria-label="<?php esc_attr_e( 'Upgrade Now', 'formidable' ); ?>">
							<?php esc_html_e( 'Upgrade Now', 'formidable' ); ?>
						</a>
					<?php } ?>
				</div>
			</div>
		<?php } ?>
	</div>
</div>
</div>
