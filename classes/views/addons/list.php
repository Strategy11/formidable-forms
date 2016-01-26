<div class="wrap">
	<h2><?php _e( 'Formidable AddOns', 'formidable' ) ?></h2>

	<div id="the-list" class="frm-addons">
		<?php foreach ( $addons as $addon ) {
			if ( empty( $addon['info']['excerpt'] ) ) {
				continue;
			}

			if ( isset( $plugin_names[ $addon['info']['slug'] ] ) ) {
				$installed = is_dir( WP_PLUGIN_DIR . '/' . $plugin_names[ $addon['info']['slug'] ] );
			} else {
				$installed = isset( $installed_addons[ $addon['info']['slug'] ] ) || is_dir( WP_PLUGIN_DIR . '/formidable-' . $addon['info']['slug'] );
			}
			$has_thumbnail = ! empty( $addon['info']['thumbnail'] );
			if ( $addon['info']['slug'] == 'formidable-pro' ) {
				$addon['info']['link'] = $pro_link;
			}
			$addon['info']['link'] = FrmAppHelper::make_affiliate_url( $addon['info']['link'] );

		?>
			<div class="plugin-card plugin-card-<?php echo esc_attr( $addon['info']['slug'] ) ?> <?php echo esc_attr( $has_thumbnail ? '' : 'frm-no-thumb' ) ?>">
				<div class="plugin-card-top">
					<div class="name column-name">
						<h3>
							<a href="<?php echo esc_url( $addon['info']['link'] ) ?>">
								<?php echo esc_html( $addon['info']['title'] ) ?>
								<?php if ( $has_thumbnail ) { ?>
								<img src="<?php echo esc_url( $addon['info']['thumbnail'] ) ?>" class="plugin-icon" alt="" />
								<?php } ?>
							</a>
						</h3>
					</div>
					<div class="action-links">
						<ul class="plugin-action-buttons">
							<?php if ( $installed ) { ?>
								<li><span class="button button-disabled" title="<?php esc_attr_e( 'This plugin is already installed', 'formidable' ) ?>"><?php _e( 'Installed', 'formidable' ) ?></span></li>
							<?php } ?>
							<li><a href="<?php echo esc_url( $addon['info']['link'] ) ?>" target="_blank" aria-label="<?php esc_attr_e( 'More Details', 'formidable' ) ?>"><?php _e( 'More Details', 'formidable' ) ?></a></li>
						</ul>
					</div>
					<div class="desc column-description">
						<p><?php echo wp_kses_post( $addon['info']['excerpt'] ) ?></p>
					</div>
				</div>
			</div>
		<?php } ?>
	</div>
</div>