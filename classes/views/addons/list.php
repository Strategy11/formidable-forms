<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm_wrap" id="frm-addons-page">
	<?php
	FrmAppHelper::get_admin_header(
		array(
			'label' => __( 'Formidable Add-Ons', 'formidable' ),
		)
	);
	?>
	<div class="wrap">

	<?php
	include( FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php' );

	FrmAppHelper::show_search_box(
		array(
			'input_id'    => 'addon',
			'placeholder' => __( 'Search Add-ons', 'formidable' ),
			'tosearch'    => 'frm-card',
		)
	);

	if ( FrmAppHelper::pro_is_connected() ) {
		?>
		<p class="alignleft">
			<?php esc_html_e( 'Missing add-ons?', 'formidable' ); ?>
			<a href="#" id="frm_reconnect_link" class="frm-show-authorized" data-refresh="1">
				<?php esc_html_e( 'Check now for a recent upgrade or renewal', 'formidable' ); ?>
			</a>
		</p>
		<?php
	} else {
		FrmSettingsController::license_box();
	}
	?>
	<div class="clear"></div>

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
						<?php echo esc_html( $addon['title'] ); ?>
					</h2>
					<p>
						<?php echo esc_html( $addon['excerpt'] ); ?>
						<?php $show_docs = isset( $addon['docs'] ) && ! empty( $addon['docs'] ) && $addon['installed']; ?>
						<?php if ( $show_docs ) { ?>
							<br/><a href="<?php echo esc_url( $addon['docs'] ); ?>" target="_blank" aria-label="<?php esc_attr_e( 'View Docs', 'formidable' ); ?>">
								<?php esc_html_e( 'View Docs', 'formidable' ); ?>
							</a>
						<?php } ?>
					</p>
					<?php
					$plan_required = FrmFormsHelper::get_plan_required( $addon );
					if ( ! $show_docs ) {
						FrmFormsHelper::show_plan_required( $plan_required, $pricing . '&utm_content=' . $addon['slug'] );
					}
					?>
				</div>
				<div class="plugin-card-bottom">
					<span class="addon-status">
						<?php
						printf(
							/* translators: %s: Status name */
							esc_html__( 'Status: %s', 'formidable' ),
							'<span class="addon-status-label">' . esc_html( $addon['status']['label'] ) . '</span>'
						);
						?>
					</span>
					<?php if ( $addon['status']['type'] === 'installed' ) { ?>
						<a rel="<?php echo esc_attr( $addon['plugin'] ); ?>" class="button button-primary frm-button-primary frm-activate-addon <?php echo esc_attr( empty( $addon['activate_url'] ) ? 'frm_hidden' : '' ); ?>">
							<?php esc_html_e( 'Activate', 'formidable' ); ?>
						</a>
					<?php } elseif ( isset( $addon['url'] ) && ! empty( $addon['url'] ) ) { ?>
						<a class="frm-install-addon button button-primary frm-button-primary" rel="<?php echo esc_attr( $addon['url'] ); ?>" aria-label="<?php esc_attr_e( 'Install', 'formidable' ); ?>">
							<?php esc_html_e( 'Install', 'formidable' ); ?>
						</a>
					<?php } elseif ( ! empty( $license_type ) && $license_type === strtolower( $plan_required ) ) { ?>
						<a class="install-now button button-secondary frm-button-secondary" href="<?php echo esc_url( FrmAppHelper::admin_upgrade_link( 'addons', 'account/downloads/' ) . '&utm_content=' . $addon['slug'] ); ?>" target="_blank" aria-label="<?php esc_attr_e( 'Upgrade Now', 'formidable' ); ?>">
							<?php esc_html_e( 'Renew Now', 'formidable' ); ?>
						</a>
					<?php } else { ?>
						<?php
						if ( isset( $addon['categories'] ) && in_array( 'Solution', $addon['categories'] ) ) {
							// Solutions will go to a separate page.
							$pricing = FrmAppHelper::admin_upgrade_link( 'addons', $addon['link'] );
						}
						?>
						<a class="install-now button button-secondary frm-button-secondary" href="<?php echo esc_url( $pricing . '&utm_content=' . $addon['slug'] ); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Upgrade Now', 'formidable' ); ?>">
							<?php esc_html_e( 'Upgrade Now', 'formidable' ); ?>
						</a>
					<?php } ?>
				</div>
			</div>
		<?php } ?>
	</div>
</div>
</div>
