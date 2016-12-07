<div class="wrap">
	<h1><?php _e( 'Formidable AddOns', 'formidable' ) ?></h1>

	<div id="the-list" class="frm-addons">
		<?php foreach ( $addons as $slug => $addon ) { ?>
			<div class="plugin-card plugin-card-<?php echo esc_attr( $slug ) ?> frm-no-thumb">
				<div class="plugin-card-top">
					<div class="name column-name">
						<h3>
							<a href="<?php echo esc_url( $site_url . $addon['link'] ) ?>">
								<?php echo esc_html( $addon['title'] ) ?>
							</a>
						</h3>
					</div>
					<div class="action-links">
						<ul class="plugin-action-buttons">
							<?php if ( $addon['installed'] ) { ?>
								<?php if ( empty( $addon['activate_url'] ) ) { ?>
									<li><span class="button button-disabled" title="<?php esc_attr_e( 'This plugin is already installed', 'formidable' ) ?>"><?php _e( 'Installed', 'formidable' ) ?></span></li>
								<?php } else { ?>
								<li><a href="<?php echo esc_url( $addon['activate_url'] ) ?>" class="button activate-now"><?php _e( 'Activate', 'formidable' ); ?></a></li>
								<?php } ?>
							<?php } else { ?>
								<li><a class="install-now button" href="<?php echo esc_url( $site_url . $addon['link'] ) ?>" target="_blank" aria-label="<?php esc_attr_e( 'Get Started', 'formidable' ) ?>"><?php _e( 'Get Started', 'formidable' ) ?></a></li>
							<?php } ?>
							<li><a href="<?php echo esc_url( $site_url . 'knowledgebase/' . $addon['docs'] ) ?>" target="_blank" aria-label="<?php esc_attr_e( 'View Docs', 'formidable' ) ?>"><?php _e( 'View Docs', 'formidable' ) ?></a></li>
						</ul>
					</div>
					<div class="desc column-description">
						<p><?php echo wp_kses_post( $addon['excerpt'] ) ?></p>
					</div>
				</div>
			</div>
		<?php } ?>
	</div>
</div>
