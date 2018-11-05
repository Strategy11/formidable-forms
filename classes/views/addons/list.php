<div class="wrap">
	<h1><?php esc_html_e( 'Formidable AddOns', 'formidable' ) ?></h1>

	<div id="the-list" class="frm-addons">
		<?php foreach ( $addons as $slug => $addon ) { ?>
			<div class="plugin-card plugin-card-<?php echo esc_attr( $slug ); ?> frm-no-thumb frm-addon-<?php echo esc_attr( $addon['status']['type'] ); ?>">
				<div class="plugin-card-top">
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
						<a href="<?php echo esc_url( $addon['activate_url'] ) ?>" class="button activate-now frm_button <?php echo esc_attr( empty( $addon['activate_url'] ) ? 'frm_hidden' : '' ); ?>">
							<?php esc_html_e( 'Activate', 'formidable' ); ?>
						</a>
					<?php } elseif ( isset( $addon['url'] ) && ! empty( $addon['url'] ) ) { ?>
						<a class="frm-install-addon button frm_button" rel="<?php echo esc_attr( $addon['url'] ); ?>" aria-label="<?php esc_attr_e( 'Install', 'formidable' ); ?>">
							<?php esc_html_e( 'Install', 'formidable' ); ?>
						</a>
						<span class="spinner"></span>
					<?php } elseif ( FrmAppHelper::pro_is_installed() ) { ?>
						<a class="install-now button frm_button" href="<?php echo esc_url( $pricing ); ?>" target="_blank" aria-label="<?php esc_attr_e( 'Get Started', 'formidable' ); ?>">
							<?php esc_html_e( 'Get Started', 'formidable' ); ?>
						</a>
					<?php } else { ?>
						<a class="install-now button frm_button" href="<?php echo esc_url( $pricing ); ?>" target="_blank" aria-label="<?php esc_attr_e( 'Upgrade Now', 'formidable' ); ?>">
							<?php esc_html_e( 'Upgrade Now', 'formidable' ); ?>
						</a>
					<?php } ?>
				</div>
			</div>
		<?php } ?>
	</div>
</div>
